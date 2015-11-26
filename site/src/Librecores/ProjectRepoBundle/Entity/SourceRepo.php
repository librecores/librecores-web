<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A source code repository
 *
 * Currently only git and subversion (svn) repositories are supported.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class SourceRepo
{
    const REPO_TYPE_GIT = 'git';
    const REPO_TYPE_SVN = 'svn';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The repository type.
     *
     * @var string One of the REPO_TYPE_* constants
     *
     * @Assert\Choice(choices = {"git", "svn"})
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * The URL to clone/checkout the repository.
     *
     * @var string
     *
     * @Assert\Length(max = 255)
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * Statistics about this souce code repository.
     *
     * @var SourceStats
     *
     * @ORM\OneToOne(targetEntity="SourceStats")
     */
    private $stats;

    /**
     * Projects using this source code repository.
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Project", mappedBy="sourceRepo")
     */
    private $projects;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->projects = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set type
     *
     * @param string $type
     * @return SourceRepo
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return SourceRepo
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set stats
     *
     * @param \Librecores\ProjectRepoBundle\Entity\SourceStats $stats
     * @return SourceRepo
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

    /**
     * Add projects
     *
     * @param \Librecores\ProjectRepoBundle\Entity\Project $projects
     * @return SourceRepo
     */
    public function addProject(\Librecores\ProjectRepoBundle\Entity\Project $projects)
    {
        $this->projects[] = $projects;

        return $this;
    }

    /**
     * Remove projects
     *
     * @param \Librecores\ProjectRepoBundle\Entity\Project $projects
     */
    public function removeProject(\Librecores\ProjectRepoBundle\Entity\Project $projects)
    {
        $this->projects->removeElement($projects);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }
}
