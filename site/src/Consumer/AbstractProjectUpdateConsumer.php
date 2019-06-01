<?php


namespace App\Consumer;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * Base class for project crawlers
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
abstract class AbstractProjectUpdateConsumer implements ConsumerInterface
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProjectRepository
     */
    protected $repository;

    /**
     * AbstractProjectCrawlerConsumer constructor.
     *
     * @param ProjectRepository $repository
     * @param LoggerInterface   $logger
     */
    public function __construct(
        ProjectRepository $repository,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->repository = $repository;
    }

    /**
     * @param AMQPMessage $msg The message
     *
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        // We need to be very careful not to fail due to a PHP error or an
        // unhandled exception within a consumer, as this essentially kills our
        // RabbitMQ consumer daemon and ends all processing tasks.
        try {
            $projectId = (int) unserialize($msg->body);
            /** @var Project $project */
            $project = $this->repository->find($projectId);

            if (!$project) {
                // this should not happen in production
                // but happens in dev if for some reason we clear the projects
                // table
                $this->logger->error(
                    "Unable to update project with ID $projectId: project does "
                    ."not exist."
                );

                return true; // don't requeue
            }

            return $this->processProject($project);
        } catch (Exception $e) {
            // We got an unexpected Exception. We assume this is a one-off event
            // and just log it, but otherwise keep the consumer running for the
            // next requests.
            $this->logger->error(
                "Processing of repository resulted in an ".get_class($e)
            );
            $this->logger->error('Message: '.$e->getMessage());
            $this->logger->error('Trace: '.$e->getTraceAsString());

            return false;
        }
    }

    /**
     * Process a project
     *
     * @param Project $project
     *
     * @return mixed
     */
    abstract protected function processProject(Project $project);
}
