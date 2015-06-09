<?php
namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SourceStatsAuthor
 *
 * Author statistics in a source code repository
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class SourceStatsAuthor
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="SourceStats", inversedBy="authors")
     */
    private $sourceStats;

    /**
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $linesInserted;

    /**
     * @ORM\Column(type="integer")
     */
    private $linesDeleted;

    /**
     * @ORM\Column(type="integer")
     */
    private $commits;

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
     * Set email
     *
     * @param string $email
     * @return SourceStatsAuthor
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return SourceStatsAuthor
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set linesInserted
     *
     * @param integer $linesInserted
     * @return SourceStatsAuthor
     */
    public function setLinesInserted($linesInserted)
    {
        $this->linesInserted = $linesInserted;

        return $this;
    }

    /**
     * Get linesInserted
     *
     * @return integer
     */
    public function getLinesInserted()
    {
        return $this->linesInserted;
    }

    /**
     * Set linesDeleted
     *
     * @param integer $linesDeleted
     * @return SourceStatsAuthor
     */
    public function setLinesDeleted($linesDeleted)
    {
        $this->linesDeleted = $linesDeleted;

        return $this;
    }

    /**
     * Get linesDeleted
     *
     * @return integer
     */
    public function getLinesDeleted()
    {
        return $this->linesDeleted;
    }

    /**
     * Set commits
     *
     * @param integer $commits
     * @return SourceStatsAuthor
     */
    public function setCommits($commits)
    {
        $this->commits = $commits;

        return $this;
    }

    /**
     * Get commits
     *
     * @return integer
     */
    public function getCommits()
    {
        return $this->commits;
    }

    /**
     * Set sourceStats
     *
     * @param \Librecores\ProjectRepoBundle\Entity\SourceStats $sourceStats
     * @return SourceStatsAuthor
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
