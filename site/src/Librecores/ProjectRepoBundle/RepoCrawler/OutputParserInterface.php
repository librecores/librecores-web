<?php

namespace Librecores\ProjectRepoBundle\RepoCrawler;
use Librecores\ProjectRepoBundle\Entity\Commit;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;

/**
 * Provides an interface to parse output generated during repository crawling
 *
 * @package Librecores\ProjectRepoBundle\OutputParser
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
interface OutputParserInterface
{
    /**
     * Parses commits from the output from a supported crawler
     *
     * @param array $output raw output from the crawler
     * @return array array of SourceCommit
     * @see Commit
     */
    function parseCommits(SourceRepo $repo, string $output) : array;
}