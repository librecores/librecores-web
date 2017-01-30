<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Librecores\ProjectRepoBundle\Entity\ProjectTag
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Librecores\ProjectRepoBundle\Entity\ProjectTagRepository")
 */
class ProjectTag
{
    /**
     *
     * @var integer $id @ORM\Column(name="id", type="integer")
     *      @ORM\Id
     *      @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @var string $name @ORM\Column(name="name", type="string", nullable=false, unique=true)
     */
    private $name;

    /**
     *
     * @var ProjectTagCategory $category @ORM\ManyToOne(targetEntity="ProjectTagCategory")
     *      @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true)
     */
    private $category;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $createdAt = false;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getCategoryName()
    {
        return $this->category->getName();
    }

    public function getCategoryColor()
    {
        return $this->category->getColor();
    }

    public function getFullName()
    {
        if (strlen($this->getCategoryName()) == 0) {
            return $this->getName();
        } else {
            return $this->getCategoryName() . ":" . $this->getName();
        }
    }
}