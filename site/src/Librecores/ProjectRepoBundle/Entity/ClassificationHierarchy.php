<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A classification hierarchy for the projects
 *
 * It contains classification categories that can be use to classify the IP Cores
 * for better categorization
 *
 * @ORM\Table(name="ClassificationHierarchy")
 * @ORM\Entity
 */
class ClassificationHierarchy
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
     * One ClassificationHierarchy has Many ClassificationHierarchy.
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ClassificationHierarchy", mappedBy="parent")
     */
    private $children;

    /**
     * Many ClassificationHierarchy have One ClassificationHierarchy.
     *
     * @var ClassificationHierarchy
     *
     * @ORM\ManyToOne(targetEntity="ClassificationHierarchy", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    private $parent;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime;
        $this->updatedAt = new \DateTime;
        $this->children = new ArrayCollection();
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
     * Set parent
     *
     * @param ClassificationHierarchy $parent
     *
     * @return ClassificationHierarchy
     */
    public function setParent(\Librecores\ProjectRepoBundle\Entity\ClassificationHierarchy $parent = null)
    {
        if ($this->parent !== null)
            $this->parent->removeChild($this);

        if ($parent !== null) {
            $parent->addChild($this);
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return ClassificationHierarchy
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ClassificationHierarchy
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ClassificationHierarchy
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return ClassificationHierarchy
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Add child
     *
     * @param \Librecores\ProjectRepoBundle\Entity\ClassificationHierarchy $child
     *
     * @return ClassificationHierarchy
     */
    public function addChild(\Librecores\ProjectRepoBundle\Entity\ClassificationHierarchy $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \Librecores\ProjectRepoBundle\Entity\ClassificationHierarchy $child
     */
    public function removeChild(\Librecores\ProjectRepoBundle\Entity\ClassificationHierarchy $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }
}
