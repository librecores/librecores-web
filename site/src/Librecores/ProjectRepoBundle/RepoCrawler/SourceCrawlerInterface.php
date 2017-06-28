<?php

namespace Librecores\ProjectRepoBundle\RepoCrawler;


use Librecores\ProjectRepoBundle\Entity\SourceRepo;

/**
 * Generic interface for all services that scan the repositories source code
 * and extract metrics.
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
interface SourceCrawlerInterface
{
    /**
     * Crawl a repositories' source code
     *
     * @param SourceRepo $repository repository to crawl
     * @param string $srcDir location of repository's source code
     */
    function crawl(SourceRepo $repository, string $srcDir);
}