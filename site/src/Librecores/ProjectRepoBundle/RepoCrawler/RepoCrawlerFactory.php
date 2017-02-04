<?php
namespace Librecores\ProjectRepoBundle\RepoCrawler;

use Psr\Log\LoggerInterface;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;
use Librecores\ProjectRepoBundle\RepoCrawler\GitRepoCrawler;
use Librecores\ProjectRepoBundle\Util\MarkupToHtmlConverter;
use Librecores\ProjectRepoBundle\RepoCrawler\RepoCrawler;

class RepoCrawlerFactory
{
    private $markupConverter;
    private $logger;

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
