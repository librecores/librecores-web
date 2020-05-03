<?php

namespace App\Consumer;

use App\Entity\Project;
use App\RepoCrawler\RepoCrawlerRegistry;
use App\Repository\ProjectRepository;
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
     * UpdateProjectInformation constructor.
     *
     * @param ProjectRepository      $projectRepository
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface        $logger
     * @param RepoCrawlerRegistry    $repoCrawlerFactory
     */
    public function __construct(
        ProjectRepository $projectRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        RepoCrawlerRegistry $repoCrawlerFactory
    ) {
        parent::__construct($projectRepository, $entityManager, $logger);
        $this->repoCrawlerRegistry = $repoCrawlerFactory;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function processProject() : bool
    {
        // check if this project is associated with a source repository
        if ($this->getProject()->getSourceRepo() === null) {
            $this->logger->error(
                "Unable to update project with ID {$this->getProject()->getFqname()}"
                .": no valid source repository associated."
            );

            return self::PROCESSING_SUCCESSFUL;
        }

        // do the actual work: extract data from the repository
        $this->logger->info('Updating project '.$this->getProject()->getFqname());
        $crawler = $this->repoCrawlerRegistry->getCrawlerForProject($this->getProject());
        $crawler->update($this->getProject());
        $this->logger->info('Successfully updated '.$this->getProject()->getFqname());

        // persist all changes made to to DB
        $this->entityManager->flush();

        return self::PROCESSING_SUCCESSFUL;
    }
}
