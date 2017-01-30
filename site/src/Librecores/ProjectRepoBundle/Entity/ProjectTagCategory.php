<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Librecores\ProjectRepoBundle\Entity\TagCategory
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ProjectTagCategory
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
     * @var string $name @ORM\Column(name="name", type="string", nullable=false)
     *
     */
    private $name;

    /**
     *
     * @var string $color @ORM\Column(name="color", type="string", nullable=false)
     */
    private $color;

    public function getName()
    {
        return $this->name;
    }

    public function getColor()
    {
        return $this->color;
    }
}