<?php

namespace Librecores\ProjectRepoBundle\RepoCrawler;

use Doctrine\Common\Persistence\ObjectManager;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Librecores\ProjectRepoBundle\Util\MarkupToHtmlConverter;
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
     * @var array
     */
    private $outputParsers;

    /**
     * @var array
     */
    private $sourceCrawlers;

    /**
     * Constructor: create a new instance
     *
     * @param MarkupToHtmlConverter $markupConverter
     * @param LoggerInterface $logger
     * @param ObjectManager $manager
     * @param array $outputParsers
     */
    public function __construct(MarkupToHtmlConverter $markupConverter,
                                LoggerInterface $logger, ObjectManager $manager,
                                array $outputParsers, array $sourceCrawlers)
    {
        $this->markupConverter = $markupConverter;
        $this->logger = $logger;
        $this->outputParsers = $outputParsers;
        $this->manager = $manager;
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
                $this->markupConverter, $this->logger, $this->outputParsers['git'], $this->sourceCrawlers, $this->manager);
        }

        throw new \InvalidArgumentException("No crawler for source repository " .
            "of type " . get_class($repo) . " found.");
    }
}
