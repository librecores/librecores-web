<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A classification hierarchy for the projects
 *
 * This class contains classification categories that can be use to classify a project
 * for better categorization
 *
 * @author Sandip Kumar Bhuyan <sandipbhuyan@gmail.com>
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
     * One ClassificationHierarchy has Many subclassifications.
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ClassificationHierarchy", mappedBy="parent", cascade={"persist", "remove"},
     *                orphanRemoval=true)
     */
    private $children;

    /**
     * Many subclassifications have One Parent Classification.
     *
     * @var ClassificationHierarchy
     *
     * @ORM\ManyToOne(targetEntity="ClassificationHierarchy", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $parent;

    /**
     * Category name of the Classification Hierarchy
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    public function __construct()
    {
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
     * Get parent category
     *
     * @return ClassificationHierarchy
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent category
     *
     * @param ClassificationHierarchy $parent
     *
     * @return ClassificationHierarchy
     */
    public function setParent(ClassificationHierarchy $parent = null)
    {
        if ($this->parent !== null) {
            $this->parent->removeChild($this);
        }

        if ($parent !== null) {
            $parent->addChild($this);
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * Get Category name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set Category name
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
     * Add child category
     *
     * @param \Librecores\ProjectRepoBundle\Entity\ClassificationHierarchy $child
     *
     * @return ClassificationHierarchy
     */
    public function addChild(ClassificationHierarchy $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child category
     *
     * @param \Librecores\ProjectRepoBundle\Entity\ClassificationHierarchy $child
     */
    public function removeChild(ClassificationHierarchy $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children category
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }
}
