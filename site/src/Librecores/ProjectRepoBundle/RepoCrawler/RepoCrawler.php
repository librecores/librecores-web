<?php

namespace Librecores\ProjectRepoBundle\RepoCrawler;

use Doctrine\Common\Persistence\ObjectManager;
use Librecores\ProjectRepoBundle\Doctrine\ProjectMetricsProvider;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Librecores\ProjectRepoBundle\Util\MarkupToHtmlConverter;
use Librecores\ProjectRepoBundle\Util\ProcessCreator;
use Psr\Log\LoggerInterface;

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
     * @var ObjectManager
     */
    protected $manager;

    /**
     * RepoCrawler constructor.
     * @param SourceRepo            $repo
     * @param MarkupToHtmlConverter $markupConverter
     * @param ProcessCreator        $processCreator
     * @param ObjectManager         $manager
     * @param LoggerInterface       $logger
     */
    public function __construct(
        SourceRepo $repo,
        MarkupToHtmlConverter $markupConverter,
        ProcessCreator $processCreator,
        ObjectManager $manager,
        LoggerInterface $logger
    ) {
        $this->repo            = $repo;
        $this->markupConverter = $markupConverter;
        $this->logger          = $logger;
        $this->processCreator  = $processCreator;
        $this->manager         = $manager;

        if (!$this->isValidRepoType()) {
            throw new \RuntimeException("Repository type is not supported by this crawler.");
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
     * @param ProjectMetricsProvider $projectMetricsProvider
     * @return bool operation successful?
     */
    abstract public function updateProject(ProjectMetricsProvider $projectMetricsProvider);

    /**
     * Update the source repository entity with information obtained through
     * the crawler
     */
    public function updateSourceRepo()
    {
        // the default implementation is empty
    }
}
