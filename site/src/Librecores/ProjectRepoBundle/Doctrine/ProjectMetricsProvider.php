<?php

namespace Librecores\ProjectRepoBundle\Doctrine;

use Librecores\ProjectRepoBundle\Entity\Commit;
use Librecores\ProjectRepoBundle\Entity\Contributor;
use Librecores\ProjectRepoBundle\Entity\LanguageStat;
use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Repository\CommitRepository;
use Librecores\ProjectRepoBundle\Repository\ContributorRepository;
use App\Util\Dates;
use App\Util\StatsUtil;

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
     *
     * @param CommitRepository      $commitRepository
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
     *
     * @return bool
     */
    public function refreshMetadata(Project $project): bool
    {
        // Currently noop, as no caching is performed
        return true;
    }

    /**
     * Fetch the commits in a given project
     *
     * @param Project $project
     *
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
     *
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
     *
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
     *
     * @return Commit
     */
    public function getFirstCommit(Project $project): ?Commit
    {
        return $this->commitRepository->getFirstCommit(
            $project->getSourceRepo()
        );
    }

    /**
     * Get the contributors to a project
     *
     * @param Project $project
     *
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
     *
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
     * @param int     $count
     *
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
     *
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
     * @param Project            $project
     * @param int                $bucket  one of the constants 'INTERVAL_DAY', 'INTERVAL_WEEK'
     *                                    'INTERVAL_MONTH', 'INTERVAL_YEAR', which specifies
     *                                    the histogram bucket size
     * @param \DateTimeImmutable $start   start date of commits
     * @param \DateTimeImmutable $end     end date of commits
     *
     * @return array associative array of a time span index and commits in that
     *               time span
     */
    public function getCommitHistogram(
        Project $project,
        int $bucket,
        \DateTimeImmutable $start = null,
        \DateTimeImmutable $end = null
    ): array {
        // TODO: This function needs some form of caching
        // aggregation queries in mysql are very expensive, this function
        // will never use an index and always perform a full table scan
        return $this->commitRepository->getCommitHistogram(
            $project->getSourceRepo(),
            $bucket,
            $start,
            $end
        );
    }

    /**
     * Get a histogram of contributors over a range of dates
     *
     * @param Project            $project
     * @param int                $bucket  one of the constants 'INTERVAL_DAY', 'INTERVAL_WEEK'
     *                                    'INTERVAL_MONTH', 'INTERVAL_YEAR', which specifies
     *                                    the histogram bucket size
     * @param \DateTimeImmutable $start   start date of commits
     * @param \DateTimeImmutable $end     end date of commits
     *
     * @return array associative array of a time span index and commits in that
     *               time span
     */
    public function getContributorHistogram(
        Project $project,
        int $bucket,
        \DateTimeImmutable $start = null,
        \DateTimeImmutable $end = null
    ): array {
        // TODO: This function needs some form of caching
        // aggregation queries in mysql are very expensive, this function
        // will never use an index and always perform a full table scan
        return $this->commitRepository->getCommitContributorHistogram(
            $project->getSourceRepo(),
            $bucket,
            $start,
            $end
        );
    }

    /**
     * Get the major languages used in a project
     *
     * @param Project $project
     *
     * @return array[string]
     */
    public function getMostUsedLanguages(Project $project): array
    {
        if (!$project->getSourceRepo()->getSourceStats()->isAvailable()) {
            return [];
        }
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


        $minValue = 0.05 * $langStats[0]->getFileCount();

        $result = ['Others' => 0];
        foreach ($langStats as $lang) {
            $fc = $lang->getFileCount();
            if ($fc < $minValue) {
                $result['Others'] += $fc;
            } else {
                $result[$lang->getLanguage()] = $fc;
            }
        }

        return $result;
    }

    /**
     * Get the code quality score calculated from various metrics.
     *
     * The formula is explained here:
     * http://www.librecores.org/static/docs/code-quality
     *
     * @param Project $project
     *
     * @return float
     */
    public function getCodeQualityScore(Project $project): float
    {

        // TODO: Some day, we should use a DecisionTreeRegressor here
        // trained on real world projects

        $score = 0;

        // +2 for issue tracker
        if (null !== $project->getIssueTracker()) {
            $score += 2;
        } else {
            $score -= 1;
        }

        // activity max = +2
        $lastActivity = $project->getDateLastActivityOccurred();

        if ($lastActivity) {
            $now = new \DateTimeImmutable();
            $difference = $now->diff($lastActivity);

            if ($difference->days < 30) {
                $score += 2;
            } elseif ($difference->y < 1) {
                $score += 1;
            } elseif ($difference->y < 3) {
                $score += 0.25;
            } else {
                $score -= -0.25;
            }
        }

        // +0.5 for some activity in issue trackers
        if ($project->getOpenIssues()) {
            $score += 0.5;
        }

        // +0.25 for some activity in PRs.
        // Low weight since this feature is GitHub specific
        if ($project->getOpenPullRequests()) {
            $score += 0.25;
        }

        // TODO: +0.5 if issues/PRs were closed within last month

        // all time contributors, max +3
        $contributors = $this->getContributorsCount($project);
        if ($contributors > 20) {
            $score += 3;
        } elseif ($contributors > 8) {
            $score += 1;
        } elseif ($contributors > 3) {
            $score += 0.5;
        } else {
            $score -= 1;
        }

        // Comment to code ratio +2
        if ($project->getSourceRepo()->getSourceStats()->getCommentToCodeRatio() > 0.2) {
            $score += 2;
        } else {
            $score -= 1;
        }

        // Max +2.5
        // Even though it is GitHub specific, it is user feedback and hence
        // a real indicator of project quality
        $stars = $project->getStars();

        if ($stars > 10000) {
            $score += 2.5;
        } elseif ($stars > 1000) {
            $score += 1;
        } elseif ($stars > 100) {
            $score += 0.5;
        }

        // +0.5 for release tags
        if (!empty($project->getReleases())) {
            $score += 0.5;

            // +0.5 for recent release
            if ($project->getReleases()[0]->getPublishedAt()->diff(new \DateTime())->y < 3) {
                $score += 0.5;
            }
        }

        // +0.25 for changelog
        if (preg_match('/change\s?log|release\s?(notes|history)?/i', $project->getDescriptionText())) {
            $score += 0.25;
        }

        // TODO: Handle repositories with CHANGELOG in a file

        // +1 for description
        // It is important for a project to have a description
        if ($project->getDescriptionText()) {
            $score += 1;
        } else {
            $score -= 2;
        }

        // +1 for a license file
        // -3 since this is very important for open source projects
        if ($project->getLicenseText()) {
            $score += 1;
        } else {
            $score -= 3;
        }

        // TODO: This section needs improvement

        // commit activity
        $commitActivity = $this->getPhaseWiseAverageRateOfChangeOfCommits($project);
        $averageCommits = StatsUtil::normalize($this->getPhaseWiseAverageCommitCount($project));

        // +0.5 for stable project with mid term > 0.2
        if ($averageCommits['mid'] > 0.2) {
            $score += 0.5;
        } else {
            $score -= 0.5;
        }

        // +0.5 for constant commits
        if ($averageCommits['end'] > 0.05) {
            $score += 0.5;
        } else {
            $score -= 0.5;
        }

        // +0.5 if commit activity is not decreasing

        if ($commitActivity['mid'] >= 0) {
            $score += 0.25;
        } else {
            $score -= 0.25;
        }

        if ($commitActivity['end'] >= 0) {
            $score += 0.25;
        } else {
            $score -= 0.25;
        }

        // +0.5 if project is still interesting

        if ($this->getAverageRateOfChangeOfYearlyContributors($project) > 0.3) {
            $score += 0.5;
        }

        return max(ceil($score * 5 / 17.5), 0);
    }


    /**
     * Get the average rate of change of commits.
     *
     * It is calculated in 3 phases, which are roughly 1/3 of project's lifetime.
     * For projects< 6 years and > 2years 2 phases - start and mid, are
     * considered for averaging, each consisting of roughly half the
     * time. For projects < 2, average of the entire time is taken as
     * returned as the value for start.
     *
     * @param Project $project
     *
     * @return array average rate of change of number of commits
     *          in three phases - start, mid, end. Value of the
     *          phases that have not been calculated is 0
     */
    public function getPhaseWiseAverageRateOfChangeOfCommits(Project $project)
    {
        $yearlyCommitCount =
            array_values(
                $this->commitRepository->getCommitHistogram(
                    $project->getSourceRepo(),
                    Dates::INTERVAL_YEAR
                )
            );

        if (empty($yearlyCommitCount)) {
            return [
                'start' => -1,
                'mid' => -1,
                'end' => -1,
            ];
        }

        $yearsWithCommits = count($yearlyCommitCount);

        // It makes sense to divide a 6 year old project not a young project
        if ($yearsWithCommits >= 6) {
            list($start, $mid, $end) = array_chunk(
                $yearlyCommitCount,
                ceil(count($yearlyCommitCount) / 3)
            );

            return [
                'start' => StatsUtil::averageRateOfChange($start),
                'mid' => StatsUtil::averageRateOfChange($mid),
                'end' => StatsUtil::averageRateOfChange($end),
            ];
        }

        if ($yearsWithCommits > 2) {
            list($start, $mid) = array_chunk(
                $yearlyCommitCount,
                ceil(count($yearlyCommitCount) / 2)
            );

            return [
                'start' => StatsUtil::averageRateOfChange($start),
                'mid' => StatsUtil::averageRateOfChange($mid),
                'end' => -1,
            ];
        }

        return [
            'start' => StatsUtil::averageRateOfChange($yearlyCommitCount),
            'mid' => -1,
            'end' => -1,
        ];
    }

    /**
     * Get the the average commit count per phase.
     *
     * The average number of commits per year in 3 phases, which are
     * roughly 1/3 of project's lifetime. For projects < 6 years and
     * > 2years 2 phases - start and mid, are considered for averaging,
     * each consisting of roughly half the time. For projects < 2,
     * average of the entire time is taken as returned as the value
     * for start.
     *
     * @param Project $project
     *
     * @return array averages in three phases - start,
     *          mid, end. Value of the phases that have not been
     *          calculated is 0
     */
    public function getPhaseWiseAverageCommitCount(Project $project)
    {
        $yearlyCommitCount =
            array_values(
                $this->commitRepository->getCommitHistogram(
                    $project->getSourceRepo(),
                    Dates::INTERVAL_YEAR
                )
            );

        if (empty($yearlyCommitCount)) {
            return [
                'start' => 0,
                'mid' => 0,
                'end' => 0,
            ];
        }

        $yearsWithCommits = count($yearlyCommitCount);

        // It makes sense to divide a 6 year old project not a young project
        if ($yearsWithCommits >= 6) {
            list($start, $mid, $end) = array_chunk(
                $yearlyCommitCount,
                ceil(count($yearlyCommitCount) / 3)
            );

            return [
                'start' => StatsUtil::average($start),
                'mid' => StatsUtil::average($mid),
                'end' => StatsUtil::average($end),
            ];
        }

        if ($yearsWithCommits > 2) {
            list($start, $mid) = array_chunk(
                $yearlyCommitCount,
                ceil(count($yearlyCommitCount) / 2)
            );

            return [
                'start' => StatsUtil::average($start),
                'mid' => StatsUtil::average($mid),
                'end' => 0,
            ];
        }

        return [
            'start' => StatsUtil::average($yearlyCommitCount),
            'mid' => -1,
            'end' => -1,
        ];
    }

    /**
     * Get the the average rate of change of yearly unique contributors
     * through out the project lifetime.
     *
     * @param Project $project
     *
     * @return float average rate of change of yearly contributors
     */
    public function getAverageRateOfChangeOfYearlyContributors(Project $project)
    {
        $contributorsPerYear = array_values(
            $this->getContributorHistogram(
                $project,
                Dates::INTERVAL_YEAR
            )
        );

        return StatsUtil::averageRateOfChange($contributorsPerYear);
    }

    /**
     * Get the average rate of change of commits per year.
     *
     * @param Project $project
     *
     * @return float average rate of change of commits
     */
    public function getAverageRateOfChangeOfCommits(Project $project)
    {
        $commits = array_values($this->getCommitHistogram($project, Dates::INTERVAL_YEAR));

        return StatsUtil::averageRateOfChange($commits);
    }
}
