<?php

namespace Librecores\ProjectRepoBundle\Doctrine;

use Librecores\ProjectRepoBundle\Entity\Commit;
use Librecores\ProjectRepoBundle\Entity\Contributor;
use Librecores\ProjectRepoBundle\Entity\LanguageStat;
use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Repository\CommitRepository;
use Librecores\ProjectRepoBundle\Repository\ContributorRepository;

/**
 * Store and provide metrics for Projects
 *
 * Provide a transparent API to fetch project metadata from a meta-data storage
 * with appropriate optimizations such as caching.
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
class ProjectMetricsProvider
{
    // TODO: Implement caching

    /**
     * @var CommitRepository
     */
    private $commitRepository;

    /**
     * @var ContributorRepository
     */
    private $contributorRepository;

    /**
     * DefaultMetadataManager constructor.
     * @param CommitRepository $commitRepository
     * @param ContributorRepository $contributorRepository
     */
    public function __construct(
        CommitRepository $commitRepository,
        ContributorRepository $contributorRepository
    ) {
        $this->commitRepository = $commitRepository;
        $this->contributorRepository = $contributorRepository;
    }

    /**
     * Force a reload of all project metadata
     *
     * Invalidates all caches and reloads the data from the database.
     *
     * @param Project $project
     * @return bool
     */
    public function refreshMetadata(Project $project): bool
    {
        // Currently noop, as no caching is performed
        return true;
    }

    /**
     * Fetch the commits in a given project
     * @param Project $project
     * @return array
     */
    public function getCommits(Project $project): array
    {
        return $this->commitRepository->getAllCommits(
            $project->getSourceRepo()
        );
    }

    /**
     * Fetch the number of commits in a given project
     *
     * @param Project $project
     * @return int
     */
    public function getCommitCount(Project $project): int
    {
        return $this->commitRepository->getCommitCount(
            $project->getSourceRepo()
        );
    }

    /**
     * Get the latest commit recorded in meta-data storage
     *
     * @param Project $project
     * @return Commit
     */
    public function getLatestCommit(Project $project): ?Commit
    {
        return $this->commitRepository->getLatestCommit(
            $project->getSourceRepo()
        );
    }

    /**
     * Get first commit recorded in meta-data storage
     *
     * @param Project $project
     * @return Commit
     */
    function getFirstCommit(Project $project): ?Commit
    {
        return $this->commitRepository->getFirstCommit(
            $project->getSourceRepo()
        );
    }

    /**
     * Get the contributors to a project
     *
     * @param Project $project
     * @return array
     */
    public function getContributors(Project $project): array
    {
        return $this->contributorRepository->getContributorsForRepository(
            $project->getSourceRepo()
        );
    }

    /**
     * Get the total number of contributors to a project
     *
     * @param Project $project
     * @return int
     */
    public function getContributorsCount(Project $project): int
    {
        return $this->contributorRepository->getContributorCountForRepository(
            $project->getSourceRepo()
        );
    }

    /**
     * Get the top contributors to the project
     *
     * Contributors are chosen as per number of commits, lines added and deleted
     *
     * @param Project $project
     * @param int $count
     * @return array
     */
    public function getTopContributors(Project $project, int $count = 5): array
    {
        return $this->contributorRepository->getTopContributorsForRepository(
            $project->getSourceRepo(),
            $count
        );
    }

    /**
     * Get the number of commits by a project contributor
     *
     * @param Contributor $contributor
     * @return int
     */
    public function getCommitCountForContributor(Contributor $contributor): int
    {
        return $this->commitRepository->getCommitsByContributorCount(
            $contributor
        );
    }

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
    public function getCommitHistogram(
        Project $project,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        int $bucket
    ): array {
        // TODO: This function badly needs some form of caching
        // aggregation queries in mysql are very expensive, this function
        // will never use an index and always perform a full table scan
        return $this->commitRepository->getCommitHistogram(
            $project->getSourceRepo(), $start, $end, $bucket
        );
    }

    /**
     * Get the major languages used in a project
     *
     * @param $project
     *
     * @return array[string]
     */
    public function getMostUsedLanguages(Project $project): array
    {
        $langStats = $project->getSourceRepo()->getSourceStats()->getLanguageStats();

        usort(
            $langStats,
            function (LanguageStat $a, LanguageStat $b) {
                // we compare using file count, not loc
                $aCount = $a->getFileCount();
                $bCount = $b->getFileCount();

                if ($aCount === $bCount) {
                    return 0;
                }

                return ($aCount > $bCount) ? -1 : 1;
            }
        );

        /** @var LanguageStat[] $topLangs */
        $topLangs = array_slice($langStats, 0, 4);

        $result = [];
        if (count($langStats) > 4) {
            $others = 0;

            for ($i = 4; $i < count($langStats); $i++) {
                $others += $langStats[$i]->getLinesOfCode();
            }
            $result['others'] = $others;
        }

        foreach ($topLangs as $lang) {
            $result[$lang->getLanguage()] = $lang->getLinesOfCode();
        }

        sort($result, SORT_NUMERIC);

        return $result;
    }
}
