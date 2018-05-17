<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A
 *
 * @ORM\Table(name="project_classification")
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
     * User owning this project
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="projectClassification")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $parentUser;

    /**
     * @var string
     *
     * @ORM\Column(name="categories", type="text")
     */
    private $categories;

    /**
     *
     *
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Project", inversedBy = "projects")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $project;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;


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
     * Set parentUser
     *
     * @param integer $parentUser
     *
     * @return ProjectClassification
     */
    public function setParentUser($parentUser)
    {
        $this->parentUser = $parentUser;

        return $this;
    }

    /**
     * Get parentUser
     *
     * @return int
     */
    public function getParentUser()
    {
        return $this->parentUser;
    }

    /**
     * Set categories
     *
     * @param string $categories
     *
     * @return ProjectClassification
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Get categories
     *
     * @return string
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set projectId
     *
     * @param integer $projectId
     *
     * @return ProjectClassification
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;

        return $this;
    }

    /**
     * Get projectId
     *
     * @return int
     */
    public function getProjectId()
    {
        return $this->projectId;
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
}
