<?php
namespace Librecores\ProjectRepoBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception;
use Doctrine\Bundle\DoctrineBundle\Registry;

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

    public function __construct(RepoCrawlerFactory $repoCrawlerFactory,
        LoggerInterface $logger, Registry $doctrine)
    {
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
            $projectId = (int)unserialize($msg->body);

            $project = $this->orm
                ->getRepository('LibrecoresProjectRepoBundle:Project')
                ->find($projectId);
            if (!$project) {
                $this->logger->error("Unable to update project with ID ".
                    "$projectId: project does not exist.");

                $this->markInProcessing($project, false);
                return true; // don't requeue
            }

            // check if this project is associated with a source repository
            if ($project->getSourceRepo() === null) {
                $this->logger->error("Unable to update project with ID ".
                    "$projectId: no valid source repository associated.");

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
        } catch (\Exception $e) {
            // we need to avoid a project staying in "in processing" state
            // even if anything fails during the processing.
            $this->logger->error("Processing of repository resulted in an ".
                "Exception: ".$e->getMessage());
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
     * @param bool $isInProcessing
     */
    private function markInProcessing(Project $project, $isInProcessing = true)
    {
        $project->setInProcessing($isInProcessing);
        $this->orm->getManager()->flush();
    }
}
