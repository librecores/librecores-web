<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * Librecores\ProjectRepoBundle\Entity\Tagging
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ProjectTagging
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="tags")
     */
    private $project;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ProjectTag")
     */
    private $tag;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $taggedAt;

    public function getTag()
    {
        return $this->tag;
    }
}