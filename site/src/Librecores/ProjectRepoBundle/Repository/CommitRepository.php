<?php

namespace Librecores\ProjectRepoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Librecores\ProjectRepoBundle\Entity\Commit;
use Librecores\ProjectRepoBundle\Entity\Contributor;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Librecores\ProjectRepoBundle\Util\Dates;

/**
 * CommitRepository
 *
 * Extends the default repository with custom functionality.
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
class CommitRepository extends EntityRepository
{
    /**
     * Get the latest commit on the database
     *
     * @param SourceRepo $repo
     * @return Commit|mixed
     */
    public function getLatestCommit(SourceRepo $repo)
    {
        return $this->findOneBy(
            ['sourceRepo' => $repo],
            ['dateCommitted' => 'DESC']
        );
    }

    /**
     * Get the first commit on the database
     *
     * @param SourceRepo $repo
     * @return mixed
     */
    public function getFirstCommit(SourceRepo $repo)
    {
        return $this->findOneBy(
            ['sourceRepo' => $repo],
            ['dateCommitted' => 'ASC']
        );
    }

    /**
     * Delete all commits for the repository
     *
     * @param SourceRepo $repo
     * @return mixed
     */
    public function removeAllCommits(SourceRepo $repo)
    {
        return $this->createQueryBuilder('c')
            ->delete()
            ->where('c.sourceRepo = :repo')
            ->setParameter('repo', $repo)
            ->getQuery()
            ->execute();
    }

    /**
     * Get all commits in the repository
     *
     * @param SourceRepo $repo
     * @return array
     */
    public function getAllCommits(SourceRepo $repo)
    {
        return $this->findBy(
            [
                'sourceRepo' => $repo,
            ]
        );
    }

    /**
     * Gets the number of commits in a repository
     *
     * @param SourceRepo $repo
     * @return mixed
     */
    public function getCommitCount(SourceRepo $repo): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(1)')
            ->where('c.sourceRepo = :repo')
            ->setParameter('repo', $repo)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Gets the number of commits by a project contributor
     *
     * @param Contributor $contributor
     * @return int
     */
    public function getCommitsByContributorCount(Contributor $contributor): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(1)')
            ->where('c.contributor = :contributor')
            ->setParameter('contributor', $contributor)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get a histogram of commits over a range of dates
     *
     * @param SourceRepo $repo
     * @param int $bucket one of the constants 'INTERVAL_DAY', 'INTERVAL_WEEK'
     *                    'INTERVAL_MONTH', 'INTERVAL_YEAR', which specifies
     *                     the histogram bucket size
     * @param \DateTimeImmutable $start start date of commits
     * @param \DateTimeImmutable $end end date of commits
     * @return array associative array of a time span index and commits in that
     *               time span
     */
    public function getCommitHistogram(
        SourceRepo $repo,
        int $bucket,
        \DateTimeImmutable $start = null,
        \DateTimeImmutable $end = null
    ): array {
        // TODO: Implement other aggregations

        switch ($bucket) {
            case Dates::INTERVAL_WEEK:
                return $this->getCommitHistogramByWeek($repo, $start, $end);
            case Dates::INTERVAL_DAY:
                return $this->getCommitHistogramByDay($repo, $start, $end);
            case Dates::INTERVAL_MONTH:
                return $this->getCommitHistogramByMonth($repo, $start, $end);
            case Dates::INTERVAL_YEAR:
                return $this->getCommitHistogramByYear($repo, $start, $end);
            default:
                throw new \InvalidArgumentException(
                    "Invaid value $bucket for \$bucket"
                );
        }
    }

    /**
     * Get a histogram of commits per week
     *
     * @param SourceRepo $repo
     * @param \DateTimeImmutable $start start date of commits
     * @param \DateTimeImmutable $end end date of commits
     *
     * @return array associative array of a time span index and commits in that
     *               time span
     */
    private function getCommitHistogramByWeek(
        SourceRepo $repo,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ) {
        // for week wise histograms, we need (week,year) tuple
        $query = $this->createQueryBuilder('c')
            ->select('YEAR(c.dateCommitted) year')
            ->groupBy('year')
            ->orderBy('year', 'ASC')
            ->addSelect('WEEK(c.dateCommitted) week')
            ->addGroupBy('week')
            ->addOrderBy('week', 'ASC')
            ->addSelect('COUNT(1) as commits')
            ->where('c.sourceRepo = :repo');
        if (null !== $start) {
            $query->andWhere('c.dateCommitted >= :start');
        }

        if (null !== $end) {
            $query->andWhere('c.dateCommitted <= :end');
        }

        $result = $query->setParameters(
                [
                    'repo' => $repo,

                    // set date to first day of the week and time to midnight
                    // to include all activity in that week
                    // "obscure" date time modifications formats described in:
                    // http://php.net/manual/en/datetime.formats.relative.php
                    'start' => $start->modify(
                        'midnight, this week'
                    ),

                    'end' => $end,
                ]
            )
            ->getQuery()
            ->getResult('group');

        if (!empty($result)) {
            // insert missing values
            $startYear = array_keys($result)[0];
            $startWeek = array_keys($result[$startYear])[0];

            list($endYear, $endWeek) = explode(',', date('Y,W'));

            if (null !== $start) {
                list($startYear, $startWeek) = explode(',', $start->format('Y,W'));
            }

            if (null !== $end) {
                list($endYear, $endWeek) = explode(',', $end->format('Y,W'));
            }

            for ($year = $startYear; $year <= $endYear; $year++) {
                if (!array_key_exists($year, $result)) {
                    $result[$year] = [];
                }

                // we use ==  because our keys are int but
                // $startYear and $endYear are string
                $week     = $startYear == $year ? $startWeek : 1;
                $weeklast = $endYear == $year ? $endWeek
                    : \DateTimeImmutable::createFromFormat('d m Y',"31 12 $year")
                                        ->format('W');

                for (; $week <= $weeklast; $week++) {
                    if (!array_key_exists($week, $result[$year])) {
                        $result[$year][$week] = [0];    // fill non-existent values with zero
                    }
                }
                ksort($result[$year], SORT_NUMERIC);
            }
            ksort($result, SORT_NUMERIC);
        }
        return $result;
    }

    /**
     * Get a histogram of commits per week
     *
     * @param SourceRepo $repo
     * @param \DateTimeImmutable $start start date of commits
     * @param \DateTimeImmutable $end end date of commits
     *
     * @return array associative array of a time span index and commits in that
     *               time span
     */
    private function getCommitHistogramByDay(
        $repo,
        \DateTimeImmutable $start = null,
        \DateTimeImmutable $end = null
    ) {
        // for day wise histograms, we need (day,month,year)
        $query = $this->createQueryBuilder('c')
            ->select('YEAR(c.dateCommitted) year')
            ->addSelect('MONTH(c.dateCommitted) month')
            ->addSelect('DAY(c.dateCommitted) day')
            ->addSelect('COUNT(1) as commits');
            //->where('c.sourceRepo = :repo');

        if (null !== $start) {
            $query->andWhere('c.dateCommitted >= :start');
        }

        if (null !== $end) {
            $query->andWhere('c.dateCommitted <= :end');
        }

        $result = $query->groupBy('year')
            ->addGroupBy('month')
            ->addGroupBy('day')
            ->having('c.sourceRepo = :repo')
            ->orderBy('year', 'ASC')
            ->addOrderBy('month', 'ASC')
            ->addOrderBy('day', 'ASC')
            ->setParameters(
                [
                    'repo' => $repo,

                    // set time to midnight to include all activity in that day
                    'start' => $start->modify('midnight'),

                    'end' => $end,
                ]
            )
            ->getQuery()
            ->getResult('group');

        return $result;

    }

    /**
     * Get a histogram of commits per month
     *
     * @param SourceRepo $repo
     * @param \DateTimeImmutable $start start date of commits
     * @param \DateTimeImmutable $end end date of commits
     *
     * @return array associative array of a time span index and commits in that
     *               time span
     */
    private function getCommitHistogramByMonth(
        SourceRepo $repo,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ) {
        // for day wise histograms, we need (month,year)
        $query = $this->createQueryBuilder('c')
            ->select('YEAR(c.dateCommitted) year')
            ->addSelect('MONTH(c.dateCommitted) month')
            ->addSelect('COUNT(1) as commits');

        if (null !== $start) {
            $query->andWhere('c.dateCommitted >= :start');
        }

        if (null !== $end) {
            $query->andWhere('c.dateCommitted <= :end');
        }

        $result = $query->groupBy('year')
            ->addGroupBy('month')
            ->addGroupBy('day')
            ->having('c.sourceRepo = :repo')
            ->orderBy('year', 'ASC')
            ->addOrderBy('month', 'ASC')
            ->setParameters(
                [
                    'repo' => $repo,

                    // set time to midnight to include all activity in that day
                    'start' => $start->modify(
                        'midnight, first day of this month'
                    ),

                    'end' => $end,
                ]
            )
            ->getQuery()
            ->getResult('group');

        return $result;

    }

    /**
     * Get a histogram of commits per year
     *
     * @param SourceRepo $repo
     * @param \DateTimeImmutable $start start date of commits
     * @param \DateTimeImmutable $end end date of commits
     *
     * @return array associative array of a time span index and commits in that
     *               time span
     */
    private function getCommitHistogramByYear(
        SourceRepo $repo,
        \DateTimeImmutable $start = null,
        \DateTimeImmutable $end = null
    ) {

        $params = [ 'repo' => $repo ];

        $query = $this->createQueryBuilder('c')
            ->select('YEAR(c.dateCommitted) year')
            ->addSelect('COUNT(1) as commits')
            ->where('c.sourceRepo = :repo');

        if (null !== $start) {
            $query->andWhere('c.dateCommitted >= :start');
            $params['start'] = $start->modify('midnight, first day of jan, this year');
        }

        if (null !== $end) {
            $query->andWhere('c.dateCommitted <= :end');
            $params['end'] = $end;
        }

        $result = $query->groupBy('year')
            ->orderBy('year', 'ASC')
            ->setParameters($params)
            ->getQuery()
            ->getResult('group');

        return $this->zeroFillMissingYears($result, $start, $end);
    }

    /**
     * Get a histogram of contributors over a range of dates
     *
     * @param SourceRepo $repo
     * @param int $bucket one of the constants 'INTERVAL_DAY', 'INTERVAL_WEEK'
     *                    'INTERVAL_MONTH', 'INTERVAL_YEAR', which specifies
     *                     the histogram bucket size
     * @param \DateTimeImmutable $start start date of commits
     * @param \DateTimeImmutable $end end date of commits
     * @return array associative array of a time span index and commits in that
     *               time span
     */
    public function getCommitContributorHistogram(
        SourceRepo $repo,
        int $bucket,
        \DateTimeImmutable $start = null,
        \DateTimeImmutable $end = null
    ): array {
        // TODO: Implement other aggregations

        switch ($bucket) {
            case Dates::INTERVAL_YEAR:
                return $this->getCommitContributorHistogramByYear($repo, $start, $end);
            default:
                throw new \InvalidArgumentException(
                    "Invaid value $bucket for \$bucket"
                );
        }
    }

    /**
     * Get number of unique contributors per year
     *
     * @param SourceRepo $repo
     * @param \DateTimeImmutable $start start date of commits
     * @param \DateTimeImmutable $end end date of commits
     *
     * @return array associative array of a time span index and commits in that
     *               time span
     */
    private function getCommitContributorHistogramByYear(
        SourceRepo $repo,
        \DateTimeImmutable $start = null,
        \DateTimeImmutable $end = null
    ) {
        $params = [ 'repo' => $repo ];

        $query = $this->createQueryBuilder('c')
                      ->select('YEAR(c.dateCommitted) year')
                      ->addSelect('COUNT(DISTINCT c.contributor) as contributors')
                      ->where('c.sourceRepo = :repo');

        if (null !== $start) {
            $query->andWhere('c.dateCommitted >= :start');
            $params['start'] = $start->modify('midnight, first day of jan, this year');
        }

        if (null !== $end) {
            $query->andWhere('c.dateCommitted <= :end');
            $params['end'] = $end;
        }

        $result = $query->groupBy('year')
                        ->orderBy('year', 'ASC')
                        ->setParameters($params)
                        ->getQuery()
                        ->getResult('group');

        return $this->zeroFillMissingYears($result, $start, $end);
    }

    /**
     * Zero fill missing year keys
     *
     * @param $result
     * @param \DateTimeImmutable|null $start
     * @param \DateTimeImmutable|null $end
     * @return array
     */
    private function zeroFillMissingYears($result,
                                          \DateTimeImmutable $start = null,
                                          \DateTimeImmutable $end = null) : array {
        if (empty($result)) {
            return [];
        }

        $firstYear = array_keys($result)[0];
        $lastYear  = date('Y');

        if (null !== $start) {
            $firstYear = $start->format('Y');
        }

        if (null !== $end) {
            $lastYear = $end->format('Y');
        }

        for ($i = $firstYear; $i <= $lastYear; $i++) {
            if (!array_key_exists($i, $result)) {
                $result[$i] = 0;
            }
        }

        foreach ($result as $key => $value) {
            $result[$key] = $value[0];
        }

        return $result;

    }
}
