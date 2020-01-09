<?php

namespace App\Consumer;

use App\Entity\Project;
use App\RepoCrawler\RepoCrawlerRegistry;
use App\Repository\ProjectRepository;
use App\Service\NotificationCreatorService;
use App\Util\Notification;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Extract and update a project's information with data from a source repository
 *
 * This class handles incoming requests passed through RabbitMQ to update
 * the project information.
 *
 * @author Philipp Wagner <mail@philipp-wagner.com>
 */
class UpdateProjectInformation extends AbstractProjectUpdateConsumer
{

    /**
     * @var RepoCrawlerRegistry
     */
    private $repoCrawlerRegistry;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var NotificationCreatorService
     */
    private $notificationCreator;

    /**
     * UpdateProjectInformation constructor.
     *
     * @param RepoCrawlerRegistry        $repoCrawlerFactory
     * @param LoggerInterface            $logger
     * @param ProjectRepository          $projectRepository
     * @param EntityManagerInterface     $entityManager
     * @param NotificationCreatorService $notificationCreatorService
     */
    public function __construct(
        RepoCrawlerRegistry $repoCrawlerFactory,
        LoggerInterface $logger,
        ProjectRepository $projectRepository,
        EntityManagerInterface $entityManager,
        NotificationCreatorService $notificationCreatorService
    ) {
        parent::__construct($projectRepository, $logger);
        $this->repoCrawlerRegistry = $repoCrawlerFactory;
        $this->entityManager = $entityManager;
        $this->notificationCreator = $notificationCreatorService;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function processProject(Project $project) : bool
    {
        try {
            // check if this project is associated with a source repository
            if ($project->getSourceRepo() === null) {
                $this->logger->error(
                    "Unable to update project with ID {$project->getFqname()}"
                    .": no valid source repository associated."
                );

                $this->markInProcessing($project, false);

                return true; // don't requeue
            }

            // do the actual work: extract data from the repository
            $this->logger->info('Updating project '.$project->getFqname());
            $crawler = $this->repoCrawlerRegistry->getCrawlerForProject($project);
            $crawler->update($project);
            $this->logger->info('Successfully updated '.$project->getFqname());
            // mark project as "done processing"
            // we don't use markInProcessing() to avoid the double DB flush
            $project->setInProcessing(false);

            // persist all changes made to to DB
            $this->entityManager->flush();

            // Send a notification that the project is processed
            $users = new ArrayCollection();
            if ($project->getParentOrganization()) {
                $users = $project->getParentOrganization()->getMemberUsers();
            } else {
                $users->add($project->getParentUser());
            }
            foreach ($users as $user) {
                $notification = new Notification();
                $notification->setSubject("Crawler Success")
                    ->setMessage("Our RepoCrawler has fetched metrics for your project successfully")
                    ->setType("crawler_success")
                    ->setRecipient($user);
                $this->notificationCreator->createNotification($notification);
            }
        } catch (Exception $e) {
            // Try to mark this project as not in progress any more to let
            // people edit it online. The next crawling update will possibly
            //get the changes.
            try {
                $this->markInProcessing($project, false);
            } catch (Exception $e) {
                // Ignore -- we're already in the error handling path.
                // The project will most likely remain in the "in processing"
                // state.
                // Send a notification that the project cannot be processed
                $users = new ArrayCollection();
                if ($project->getParentOrganization()) {
                    $users = $project->getParentOrganization()->getMemberUsers();
                } else {
                    $users->add($project->getParentUser());
                }
                foreach ($users as $user) {
                    $notification = new Notification();
                    $notification->setSubject("Crawler Failure")
                        ->setMessage("Our RepoCrawler has failed to fetch metrics for your project")
                        ->setType("crawler_failure")
                        ->setRecipient($user);
                    $this->notificationCreator->createNotification($notification);
                }
            }
            throw $e;
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
        $this->entityManager->flush();
    }
}
