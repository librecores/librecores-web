<?php
/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 12/6/17
 * Time: 9:46 PM
 */

namespace Librecores\ProjectRepoBundle\RepoCrawler;


use Librecores\ProjectRepoBundle\Entity\Commit;
use Librecores\ProjectRepoBundle\Entity\Contributor;
use Librecores\ProjectRepoBundle\Entity\Project;

/**
 * Interface for Metadata manager which stores and provides metadata for Projects
 *
 * This interface is meant to provide a transparent API to fetch project metadata
 * from a meta-data storage. Implementations are expected to perform appropriate
 * optimizations such as caching.
 */
interface MetadataManagerInterface
{
    /**
     * Forces a reload of all project metadata
     *
     * For implementations performing caching, invocation of this method should
     * clear their caches and reload the data from the primary data-source
     *
     * @param Project $project
     * @return mixed
     */
    function refreshMetadata(Project $project);

    /**
     * Fetch the commits in a given project
     * @param Project $project
     * @return array
     */
    function getCommits(Project $project): array;

    /**
     * Fetch the number of commits in a given project
     *
     * @param Project $project
     * @return int
     */
    function getCommitCount(Project $project): int;

    /**
     * Gets the latest commit recorded in meta-data storage
     *
     * @param Project $project
     * @return Commit
     */
    function getLatestCommit(Project $project): Commit;

    /**
     * Gets first commit recorded in meta-data storage
     *
     * @param Project $project
     * @return Commit
     */
    function getFirstCommit(Project $project): Commit;

    /**
     * Gets the contributors to a project
     *
     * @param Project $project
     * @return array
     */
    function getContributors(Project $project): array;

    /**s
     * Get the total number of contributors to a project
     *
     * @param Project $project
     * @return int
     */
    function getContributorsCount(Project $project): int;

    /**
     * Gets the top contributors to the project
     *
     * Contributors are chosen as per number of commits, lines added and deleted
     *
     * @param Project $project
     * @param int $count
     * @return array
     */
    function getTopContributors(Project $project, int $count = 5): array;

    /**
     * Gets the number of commits by a project contributor
     *
     * @param Contributor $contributor
     * @return int
     */
    function getCommitCountForContributor(Contributor $contributor): int;

    /**
     * Get avatar for a Contributor
     *
     * @param Contributor $contributor
     * @return string URL to contributor avatar
     */
    function getContributorAvatar(Contributor $contributor): string;

    /**
     * Get a histogram of commits over a range of dates
     *
     * @param Project $project
     * @param \DateTimeImmutable $start start date of commits
     * @param \DateTimeImmutable $end end date of commits
     * @param int $bucket one of the constants 'INTERVAL_DAY', 'INTERVAL_WEEK'
     *                    'INTERVAL_MONTH', 'INTERVAL_YEAR', which specifies
     *                     the histogram bucket size
     * @return array associative array of a time span index and commits in that
     *               time span
     */
    function getCommitHistogram(
        Project $project,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        int $bucket
    ): array;
}