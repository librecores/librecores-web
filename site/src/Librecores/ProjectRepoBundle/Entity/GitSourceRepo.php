<?php
namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A git source code repository
 *
 * @ORM\Entity
 */
class GitSourceRepo extends SourceRepo
{
    /**
     * Statistics about this souce code repository.
     *
     * @var SourceStats
     *
     * @ORM\OneToOne(targetEntity="SourceStats")
     */
    protected $stats;

    /**
     * Create a new GitSourceRepo instance
     *
     * @param string? $url repository URL
     */
    public function __construct($url = null)
    {
        $this->url = $url;
    }

    /**
     * {@inheritDoc}
     * @see \Librecores\ProjectRepoBundle\Entity\SourceRepo::getType()
     */
    public function getType()
    {
        return self::REPO_TYPE_GIT;
    }

    /**
     * Set stats
     *
     * @param \Librecores\ProjectRepoBundle\Entity\SourceStats $stats
     * @return GitSourceRepo
     */
    public function setStats(\Librecores\ProjectRepoBundle\Entity\SourceStats $stats = null)
    {
        $this->stats = $stats;

        return $this;
    }

    /**
     * Get stats
     *
     * @return \Librecores\ProjectRepoBundle\Entity\SourceStats
     */
    public function getStats()
    {
        return $this->stats;
    }
}
