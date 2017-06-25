<?php

namespace Librecores\ProjectRepoBundle\Repository;

use Doctrine\ORM\EntityRepository;
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
     * @return mixed
     */
    public function latest(SourceRepo $repo)
    {
        return $this->findOneBy(
            ['repository' => $repo],
            ['dateCommitted' => 'DESC']
        );
    }

    /**
     * Get the first commit on the database
     *
     * @param SourceRepo $repo
     * @return mixed
     */
    public function first(SourceRepo $repo)
    {
        return $this->findOneBy(
            ['repository' => $repo],
            ['dateCommitted' => 'ASC']
        );
    }

    /**
     * Delete all commits for the repository
     *
     * @param SourceRepo $repo
     * @return mixed
     */
    public function removeAll(SourceRepo $repo)
    {
        return $this->createQueryBuilder('c')
            ->delete()
            ->where('c.repository = :repo')
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
    public function get(SourceRepo $repo)
    {
        return $this->findBy(
            [
                'repository' => $repo,
            ]
        );
    }

    /**
     * Gets the number of commits in a repository
     *
     * @param SourceRepo $repo
     * @return mixed
     */
    public function count(SourceRepo $repo): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(1)')
            ->where('c.repository = :repo')
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
    public function commitsByContributor(Contributor $contributor): int
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
     * @param \DateTimeImmutable $start start date of commits
     * @param \DateTimeImmutable $end end date of commits
     * @param int $bucket one of the constants 'INTERVAL_DAY', 'INTERVAL_WEEK'
     *                    'INTERVAL_MONTH', 'INTERVAL_YEAR', which specifies
     *                     the histogram bucket size
     * @return array associative array of a time span index and commits in that
     *               time span
     */
    public function histogram(
        SourceRepo $repo,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        int $bucket,
        bool $valuesOnly = false
    ): array {
        // TODO: Implement other aggregations

        switch ($bucket) {
            case Dates::INTERVAL_WEEK:
                return $this->histogramByWeek($repo, $start, $end);
            case Dates::INTERVAL_DAY:
                return $this->histogramByDay($repo, $start, $end);
            case Dates::INTERVAL_MONTH:
                return $this->histogramByMonth($repo, $start, $end);
            case Dates::INTERVAL_YEAR:
                return $this->histogramByYear($repo, $start, $end);
            default:
                throw new \InvalidArgumentException(
                    "Invaid value $bucket for \$bucket"
                );
        }
    }

    private function histogramByWeek(
        SourceRepo $repo,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ) {
        // for week wise histograms, we need (week,year) tuple
        $result = $this->createQueryBuilder('c')
            ->select('YEAR(c.dateCommitted) year')
            ->groupBy('year')
            ->orderBy('year', 'ASC')
            ->addSelect('WEEK(c.dateCommitted) week')
            ->addGroupBy('week')
            ->addOrderBy('week', 'ASC')
            ->addSelect('COUNT(1) as commits')
            ->where('c.repository = :repo')
            ->andWhere('c.dateCommitted >= :start')
            ->andWhere('c.dateCommitted <= :end')
            ->setParameters(
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

        // insert missing values

        list($startYear, $startWeek) = explode(',', $start->format('Y,W'));
        list($endYear, $endWeek) = explode(',', $end->format('Y,W'));

        for($year = $startYear; $year <= $endYear; $year++) {
            if(!array_key_exists($year, $result)) {
                $result[$year] = [];
            }

            // we use ==  because our keys are int but
            // $startYear and $endYear are string
            $week = $startYear == $year ? $startWeek : 1;
            $weeklast = $endYear == $year ? $endWeek : \DateTimeImmutable::createFromFormat('d m Y',"31 12 $year")->format('W');

            for (;$week <= $weeklast; $week++) {
                if(!array_key_exists($week, $result[$year])) {
                    $result[$year][$week] = [0];    // fill non-existent values with zero
                }
            }
            ksort($result[$year], SORT_NUMERIC);
        }
        ksort($result, SORT_NUMERIC);

        return $result;
    }

    private function histogramByDay(
        $repo,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ) {
        // for day wise histograms, we need (day,month,year)
        $result = $this->createQueryBuilder('c')
            ->select('YEAR(c.dateCommitted) year')
            ->addSelect('MONTH(c.dateCommitted) month')
            ->addSelect('DAY(c.dateCommitted) day')
            ->addSelect('COUNT(1) as commits')
            ->where('c.repository = :repo')
            ->andWhere('c.dateCommitted >= :start')
            ->andWhere('c.dateCommitted <= :end')
            ->groupBy('year')
            ->addGroupBy('month')
            ->addGroupBy('day')
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

    private function histogramByMonth(
        SourceRepo $repo,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ) {
        // for day wise histograms, we need (month,year)
        $result = $this->createQueryBuilder('c')
            ->select('YEAR(c.dateCommitted) year')
            ->addSelect('MONTH(c.dateCommitted) month')
            ->addSelect('DAY(c.dateCommitted) day')
            ->addSelect('COUNT(1) as commits')
            ->where('c.repository = :repo')
            ->andWhere('c.dateCommitted >= :start')
            ->andWhere('c.dateCommitted <= :end')
            ->groupBy('year')
            ->addGroupBy('month')
            ->addGroupBy('day')
            ->orderBy('year', 'ASC')
            ->addOrderBy('month', 'ASC')
            ->addGroupBy('day', 'ASC')
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

    private function histogramByYear(
        SourceRepo $repo,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ) {
        $result = $this->createQueryBuilder('c')
            ->select('YEAR(c.dateCommitted) year')
            ->addSelect('COUNT(1) as commits')
            ->where('c.repository = :repo')
            ->andWhere('c.dateCommitted >= :start')
            ->andWhere('c.dateCommitted <= :end')
            ->groupBy('year')
            ->orderBy('year', 'ASC')
            ->setParameters(
                [
                    'repo' => $repo,

                    // set time to midnight to include all activity in that day
                    'start' => $start->modify(
                        'midnight, first day of jan, this year'
                    ),

                    'end' => $end,
                ]
            )
            ->getQuery()
            ->getResult('group');

        return $result;
    }
}
