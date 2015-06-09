<?php
namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SourceStatsCommitHistogram
 *
 * Commit histogram of a source code repository
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class SourceStatsCommitHistogram
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="SourceStats", inversedBy="commitHistogramEntries")
     */
    private $sourceStats;

    /**
     * @ORM\Column(type="integer")
     */
    private $yearMonth;

    /**
     * @ORM\Column(type="integer")
     */
    private $commitCount;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set yearMonth
     *
     * @param integer $yearMonth
     * @return SourceStatsCommitHistogram
     */
    public function setYearMonth($yearMonth)
    {
        $this->yearMonth = $yearMonth;

        return $this;
    }

    /**
     * Get yearMonth
     *
     * @return integer
     */
    public function getYearMonth()
    {
        return $this->yearMonth;
    }

    /**
     * Set commitCount
     *
     * @param integer $commitCount
     * @return SourceStatsCommitHistogram
     */
    public function setCommitCount($commitCount)
    {
        $this->commitCount = $commitCount;

        return $this;
    }

    /**
     * Get commitCount
     *
     * @return integer
     */
    public function getCommitCount()
    {
        return $this->commitCount;
    }

    /**
     * Set sourceStats
     *
     * @param \Librecores\ProjectRepoBundle\Entity\SourceStats $sourceStats
     * @return SourceStatsCommitHistogram
     */
    public function setSourceStats(\Librecores\ProjectRepoBundle\Entity\SourceStats $sourceStats = null)
    {
        $this->sourceStats = $sourceStats;

        return $this;
    }

    /**
     * Get sourceStats
     *
     * @return \Librecores\ProjectRepoBundle\Entity\SourceStats
     */
    public function getSourceStats()
    {
        return $this->sourceStats;
    }
}
