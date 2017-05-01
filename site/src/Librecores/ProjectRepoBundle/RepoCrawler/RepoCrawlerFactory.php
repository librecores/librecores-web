<?php
namespace Librecores\ProjectRepoBundle\RepoCrawler;

use Psr\Log\LoggerInterface;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;
use Librecores\ProjectRepoBundle\RepoCrawler\GitRepoCrawler;
use Librecores\ProjectRepoBundle\Util\MarkupToHtmlConverter;
use Librecores\ProjectRepoBundle\RepoCrawler\RepoCrawler;

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
     * Constructor: create a new instance
     *
     * @param MarkupToHtmlConverter $markupConverter
     * @param LoggerInterface $logger
     */
    public function __construct(MarkupToHtmlConverter $markupConverter,
        LoggerInterface $logger)
    {
        $this->markupConverter = $markupConverter;
        $this->logger = $logger;
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
        if ($repo instanceof GitSourceRepo) {
            return new GitRepoCrawler($repo,
                $this->markupConverter, $this->logger);
        }

        throw new \InvalidArgumentException("No crawler for source repository ".
            "of type ".get_class($repo)." found.");
    }
}
