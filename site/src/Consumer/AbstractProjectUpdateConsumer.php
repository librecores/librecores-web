<?php


namespace App\Consumer;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use App\Exception\ProjectNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Base class for project crawlers
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
abstract class AbstractProjectUpdateConsumer implements ConsumerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProjectRepository
     */
    protected $repository;

    /**
     * Project ID
     *
     * @var int|null
     */
    private $projectId;

    /**
     * @var Project|null
     */
    private $project;

    /**
     * AbstractProjectCrawlerConsumer constructor.
     *
     * @param ProjectRepository      $repository
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface        $logger
     */
    public function __construct(
        ProjectRepository $repository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Get the project to be processed
     *
     * @throws ProjectNotFoundException
     */
    public function getProject() : Project
    {
        if ($this->project) {
            return $project;
        }
        if ($this->projectId === null) {
            return null;
        }

        $project = $this->repository->find($this->projectId);
        if ($project === null) {
            throw ProjectNotFoundException::fromProjectId($this->projectId);
        }
        return $project;
    }

    private function decodeAmqpMessage(AMQPMessage $msg)
    {
        $this->projectId = (int) unserialize($msg->body);
    }

    /**
     * @param AMQPMessage $msg The message
     *
     * @return bool false to reject and requeue, any other value to acknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        // We need to be very careful not to fail due to a PHP error or an
        // unhandled exception within a consumer, as this essentially kills our
        // RabbitMQ consumer daemon and ends all processing tasks.
        try {
            $this->decodeAmqpMessage($msg);
            return $this->processProjectInTransaction();
        } catch (ProjectNotFoundException $e) {
            // This should not happen in production, but happens in dev if for
            // some reason we clear the projects table.
            $this->logger->error(
                "Unable to update project with ID {$this->projectId}:"
                ."project does not exist."
            );

            return ConsumerInterface::MSG_REJECT;
        } catch (Exception $e) {
            $this->logException($e);
            $this->exitRestartConsumer();
            return ConsumerInterface::MSG_REJECT_REQUEUE; // never reached.
        }
    }

    /**
     * Log an exception
     *
     * We assume exceptions are an one-off event and log it, but otherwise keep
     * the consumer running for the next requests.
     */
    private function logException(Exception $e)
    {
        $this->logger->error(
            "Processing of repository resulted in an ".get_class($e)
        );
        $this->logger->error('Message: '.$e->getMessage());
        $this->logger->error('Trace: '.$e->getTraceAsString());
    }

    /**
     * Restart the consumer process
     *
     * This relies on a daemon manager (e.g. systemd) respawning the process.
     */
    private function exitRestartConsumer()
    {
        // It seems that the "clean" way of restarting the consumer process
        // is hard/broken/undocumented, so we'll rely on the rough and simple
        // approach here.
        // https://github.com/php-amqplib/RabbitMqBundle/issues/337
        // https://github.com/php-amqplib/RabbitMqBundle/issues/180
        $this->logger->warning("Exiting consumer");
        exit(0);
    }

    /**
     * Call processProject() in a DB transaction
     *
     * This ensures that all project update steps are done in a single database
     * transaction, or rolled back if they cannot be completed in entirely.
     *
     * Roughly EntityManager::transactional() but with more explicit return
     * value handling.
     */
    private function processProjectInTransaction(): bool
    {
        $this->entityManager->beginTransaction();
        try {
            $rv = $this->processProject();
            $this->entityManager->flush();
            $this->entityManager->commit();
            return $rv;
        } catch (Exception $e) {
            // Roll back any database changes.
            $this->entityManager->rollBack();
            $this->entityManager->close();
            throw $e;
        }
    }

    /**
     * Process a project
     *
     * @return bool true when successfully processed and false to retry
     */
    abstract protected function processProject(): bool;
}
