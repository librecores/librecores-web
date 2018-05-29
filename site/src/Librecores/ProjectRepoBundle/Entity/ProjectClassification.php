<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A ProjectClassification contains the classifications for the projects
 * A group up related categories can be used to classify the projects
 *
 * @author Sandip Kumar Bhuyan <sandipbhuyan@gmail.com>
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
     * It contains the classification string which has been classified by
     * facted classification. Classifications are categorization of a project
     * and two consecutive categories will be separated by '::'
     *
     * @var string
     *
     * @ORM\Column(name="categories", type="text")
     */
    private $classification;

    /**
     * It contains the project id and related to Project Entity in
     * One Project can have Many ProjectClassification
     *
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Project", inversedBy = "classifications")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $project;

    /**
     * It conatins the creation date of the classification
     *
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
     * Set classification string for the Project
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
     * Set the Project for the given ProjectClassification object
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
