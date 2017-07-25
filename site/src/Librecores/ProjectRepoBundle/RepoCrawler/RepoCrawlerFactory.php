<?php

namespace Librecores\ProjectRepoBundle\RepoCrawler;

use Doctrine\Common\Persistence\ObjectManager;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Librecores\ProjectRepoBundle\Util\MarkupToHtmlConverter;
use Librecores\ProjectRepoBundle\Util\ProcessCreator;
use Psr\Log\LoggerInterface;

/**
 * Repository crawler factory: get an appropriate repository crawler instance
 */
class RepoCrawlerFactory
{
    /**
     * @var MarkupToHtmlConverter
     */
    private $markupConverter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var ProcessCreator
     */
    private $processCreator;

    /**
     * Constructor: create a new instance
     *
     * @param MarkupToHtmlConverter $markupConverter
     * @param LoggerInterface $logger
     * @param ObjectManager $manager
     * @param ProcessCreator $processCreator
     */
    public function __construct(MarkupToHtmlConverter $markupConverter,
                                LoggerInterface $logger, ObjectManager $manager,
                                ProcessCreator $processCreator)
    {
        $this->markupConverter = $markupConverter;
        $this->logger = $logger;
        $this->manager = $manager;
        $this->processCreator = $processCreator;
    }

    /**
     * Get a RepoCrawler subclass for the source repository
     *
     * @param SourceRepo $repo
     * @throws \InvalidArgumentException if the source repository type is not
     *                                   supported by an available crawler
     * @return RepoCrawler
     */
    public function getCrawlerForSourceRepo(SourceRepo $repo): RepoCrawler
    {
        // XXX: Investigate a better method for IoC in this situation
        if ($repo instanceof GitSourceRepo) {
            return new GitRepoCrawler($repo,
                $this->markupConverter, $this->processCreator, $this->manager, $this->logger);
        }

        throw new \InvalidArgumentException("No crawler for source repository " .
            "of type " . get_class($repo) . " found.");
    }
}
