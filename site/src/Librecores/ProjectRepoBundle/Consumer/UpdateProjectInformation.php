<?php
namespace Librecores\ProjectRepoBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\DBALException;

use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Librecores\ProjectRepoBundle\RepoCrawler\RepoCrawlerFactory;

/**
 * Extract and update a project's information with data from a source repository
 *
 * This class handles incoming requests passed through RabbitMQ to update
 * the project information.
 *
 * @author Philipp Wagner <mail@philipp-wagner.com>
 */
class UpdateProjectInformation implements ConsumerInterface
{
    private $logger;
    private $orm;
    private $repoCrawlerFactory;

    public function __construct(
        RepoCrawlerFactory $repoCrawlerFactory,
        LoggerInterface $logger,
        Registry $doctrine
    ) {
        $this->repoCrawlerFactory = $repoCrawlerFactory;
        $this->logger = $logger;
        $this->orm = $doctrine;
    }

    /**
     * Process a newly received message
     *
     * {@inheritDoc}
     * @see \OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface::execute()
     */
    public function execute(AMQPMessage $msg)
    {
        // We need to be very careful not to fail due to a PHP error or an
        // unhandled exception within a consumer, as this essentially kills our
        // RabbitMQ consumer daemon and ends all processing tasks.
        try {
            $projectId = (int) unserialize($msg->body);

            $project = $this->orm
                ->getRepository('LibrecoresProjectRepoBundle:Project')
                ->find($projectId);
            if (!$project) {
                $this->logger->error(
                    "Unable to update project with ID $projectId: project does "
                    ."not exist."
                );

                $this->markInProcessing($project, false);

                return true; // don't requeue
            }

            // check if this project is associated with a source repository
            if ($project->getSourceRepo() === null) {
                $this->logger->error(
                    "Unable to update project with ID "
                    ."$projectId: no valid source repository associated."
                );

                $this->markInProcessing($project, false);

                return true; // don't requeue
            }
            $sourceRepo = $project->getSourceRepo();

            // do the actual work: extract data from the repository
            $crawler = $this->repoCrawlerFactory->getCrawlerForSourceRepo($sourceRepo);
            $crawler->updateSourceRepo();
            $crawler->updateProject();

            // mark project as "done processing"
            // we don't use markInProcessing() to avoid the double DB flush
            $project->setInProcessing(false);

            // persist all changes made to to DB
            $this->orm->getManager()->flush();
        } catch (DBALException $e) {
            // We assume we got a database exception. Most likely the connection to
            // the DB server died for some reason (probably due to a timeout).
            // Log it and end this script. It will be re-spawned by systemd and
            // a fresh DB connection will be created. The processing request stays
            // in the queue and will be processed once this service returns.
            $this->logger->info(
                "Processing of repository resulted in an ".get_class($e)
                .' with message '.$e->getMessage()
            );
            $this->logger->info(
                "Exiting this script and waiting for it to be re-spawned by "
                ."systemd."
            );
            exit(0);
        } catch (\Exception $e) {
            // We got an unexpected Exception. We assume this is a one-off event
            // and just log it, but otherwise keep the consumer running for the
            // next requests.
            $this->logger->error(
                "Processing of repository resulted in an ".get_class($e)
            );
            $this->logger->error('Message: '.$e->getMessage());
            $this->logger->error('Trace: '.$e->getTraceAsString());

            // Try to mark this project as not in progress any more to let people
            // edit it online. The next crawling update will possibly get the
            // changes.
            try {
                $this->markInProcessing($project, false);
            } catch (\Exception $e) {
                // Ignore -- we're already in the error handling path.
                // The project will most likely remain in the "in processing"
                // state.
            }
        }

        // remove element from queue
        return true;
    }

    /**
     * Set the processing status of a project
     *
     * If a project is in processing, it's not shown to users; they are
     * presented a "Please wait" page instead.
     *
     * @param Project $project
     * @param bool    $isInProcessing
     */
    private function markInProcessing(Project $project, $isInProcessing = true)
    {
        $project->setInProcessing($isInProcessing);
        $this->orm->getManager()->flush();
    }
}
