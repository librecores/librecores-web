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
     * @param User $parentUser
     *
     * @return ProjectClassification
     */
    public function setParentUser(User $parentUser = null)
    {
      if ($this->parentUser !== null)
          $this->parentUser->removeProjectClassification($this);

      if ($parentUser !== null) {
          $parentUser->addProjectClassification($this);
      }

      $this->parentUser = $parentUser;

        return $this;
    }

    /**
     * Get parentUser
     *
     * @return User
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
