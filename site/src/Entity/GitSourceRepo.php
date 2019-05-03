<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A git source code repository
 *
 * @ORM\Entity
 */
class GitSourceRepo extends SourceRepo
{
    /**
     * Create a new GitSourceRepo instance
     *
     * @param string? $url repository URL
     */
    public function __construct($url = null)
    {
        parent::__construct();
        $this->url = $url;
    }

    /**
     * {@inheritDoc}
     * @see \App\Entity\SourceRepo::getType()
     */
    public function getType()
    {
        return self::REPO_TYPE_GIT;
    }
}
