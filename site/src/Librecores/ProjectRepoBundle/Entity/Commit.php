<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A commit in a repository.
 *
 * Associates a commit with a contributor in a repository and also contains information about
 * the change-set of the commit.
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 *
 * @ORM\Table
 * @ORM\Entity
 */
class Commit
{
    /**
     * Unique ID to identify this entity in the database
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Unique ID assigned by the underlying SCM to this specific commit
     *
     * @var string
     *
     * @ORM\Column(name="commitId", type="string", length=255, nullable=false, unique=true)
     */
    private $commitId;

    /**
     * Contributor who created this commit
     *
     * @var Contributor
     *
     * @ORM\ManyToOne(targetEntity="Librecores\ProjectRepoBundle\Entity\Contributor", inversedBy="commits")
     */
    private $contributor;

    /**
     * Time of creation of this commit
     *
     * @var \DateTime
     *
     * @ORM\Column(name="dateCommitted", type="datetime", nullable=false)
     */
    private $dateCommitted;

    /**
     * Number of lines added in this commit
     *
     * @var int
     *
     * @ORM\Column(name="linesAdded", type="integer", nullable=false)
     */
    private $linesAdded;

    /**
     * Number of lines removed in this commit
     *
     * @var int
     *
     * @ORM\Column(name="linesRemoved", type="integer", nullable=false)
     */
    private $linesRemoved;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set commitId
     *
     * @param string $commitId
     *
     * @return Commit
     */
    public function setCommitId($commitId)
    {
        $this->commitId = $commitId;

        return $this;
    }

    /**
     * Get commitId
     *
     * @return string
     */
    public function getCommitId()
    {
        return $this->commitId;
    }

    /**
     * Set contributor
     *
     * @param Contributor $contributor
     *
     * @return Commit
     */
    public function setContributor(Contributor $contributor = null)
    {
        $this->contributor = $contributor;

        return $this;
    }

    /**
     * Get contributor
     *
     * @return Contributor
     */
    public function getContributor()
    {
        return $this->contributor;
    }

    /**
     * Set dateCommitted
     *
     * @param \DateTime $dateCommitted
     *
     * @return Commit
     */
    public function setDateCommitted($dateCommitted)
    {
        $this->dateCommitted = $dateCommitted;

        return $this;
    }

    /**
     * Get dateCommitted
     *
     * @return \DateTime
     */
    public function getDateCommitted()
    {
        return $this->dateCommitted;
    }

    /**
     * Set linesAdded
     *
     * @param integer $linesAdded
     *
     * @return Commit
     */
    public function setLinesAdded($linesAdded)
    {
        $this->linesAdded = $linesAdded;

        return $this;
    }

    /**
     * Get linesAdded
     *
     * @return int
     */
    public function getLinesAdded()
    {
        return $this->linesAdded;
    }

    /**
     * Set linesRemoved
     *
     * @param integer $linesRemoved
     *
     * @return Commit
     */
    public function setLinesRemoved($linesRemoved)
    {
        $this->linesRemoved = $linesRemoved;

        return $this;
    }

    /**
     * Get linesRemoved
     *
     * @return int
     */
    public function getLinesRemoved()
    {
        return $this->linesRemoved;
    }
}
