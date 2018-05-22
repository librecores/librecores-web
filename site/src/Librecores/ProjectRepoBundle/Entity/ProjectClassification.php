<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A
 *
 * @ORM\Table(name="ProjectClassification")
 * @ORM\Entity
 */
class ProjectClassification
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="categories", type="text")
     */
    private $classification;

    /**
     *
     *
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Project", inversedBy = "classifications")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $project;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime;
    }
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
     * Set classification
     *
     * @param string $classification
     *
     * @return ProjectClassification
     */
    public function setClassification($classification)
    {
        $this->classification = $classification;

        return $this;
    }

    /**
     * Get classification
     *
     * @return string
     */
    public function getClassification()
    {
        return $this->classification;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ProjectClassification
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set project
     *
     * @param \Librecores\ProjectRepoBundle\Entity\Project $project
     *
     * @return ProjectClassification
     */
    public function setProject(\Librecores\ProjectRepoBundle\Entity\Project $project = null)
    {
        if ($this->project !== null)
            $this->project->removeClassification($this);

        if ($project !== null) {
            $project->addClassification($this);
        }

        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \Librecores\ProjectRepoBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
