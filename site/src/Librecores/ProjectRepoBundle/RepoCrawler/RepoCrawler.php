<?php

namespace Librecores\ProjectRepoBundle\RepoCrawler;

use Doctrine\Common\Persistence\ObjectManager;
use Librecores\ProjectRepoBundle\Doctrine\ProjectMetricsProvider;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use App\Util\MarkupToHtmlConverter;
use App\Util\ProcessCreator;
use Librecores\ProjectRepoBundle\Repository\CommitRepository;
use Librecores\ProjectRepoBundle\Repository\ContributorRepository;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Repository crawler base class
 *
 * Get contents from a source code repository.
 */
abstract class RepoCrawler
{
    /**
     * @var SourceRepo
     */
    protected $repo;

    /**
     * @var MarkupToHtmlConverter
     */
    protected $markupConverter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProcessCreator
     */
    protected $processCreator;

    /**
     * @var CommitRepository
     */
    protected $commitRepository;

    /**
     * @var ProjectMetricsProvider
     */
    protected $projectMetricsProvider;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var ContributorRepository
     */
    protected $contributorRepository;

    /**
     * RepoCrawler constructor.
     *
     * @param SourceRepo             $repo
     * @param MarkupToHtmlConverter  $markupConverter
     * @param ProcessCreator         $processCreator
     * @param CommitRepository       $commitRepository
     * @param ContributorRepository  $contributorRepository
     * @param ObjectManager          $manager
     * @param LoggerInterface        $logger
     * @param ProjectMetricsProvider $projectMetricsProvider
     */
    public function __construct(
        SourceRepo $repo,
        MarkupToHtmlConverter $markupConverter,
        ProcessCreator $processCreator,
        CommitRepository $commitRepository,
        ContributorRepository $contributorRepository,
        ObjectManager $manager,
        LoggerInterface $logger,
        ProjectMetricsProvider $projectMetricsProvider
    ) {
        $this->repo = $repo;
        $this->markupConverter = $markupConverter;
        $this->logger = $logger;
        $this->processCreator = $processCreator;
        $this->projectMetricsProvider = $projectMetricsProvider;
        $this->commitRepository = $commitRepository;
        $this->manager = $manager;
        $this->contributorRepository = $contributorRepository;

        if (!$this->isValidRepoType()) {
            throw new RuntimeException("Repository type is not supported by this crawler.");
        }
    }

    /**
     * Is the source repository in $repo processable by this crawler?
     *
     * @return boolean
     */
    abstract public function isValidRepoType(): bool;

    /**
     * Update the project associated with the crawled repository with
     * information extracted from the repo
     *
     * @return bool operation successful?
     */
    abstract public function updateProject();

    /**
     * Update the source repository entity with information obtained through
     * the crawler
     */
    public function updateSourceRepo()
    {
        // the default implementation is empty
    }
}
