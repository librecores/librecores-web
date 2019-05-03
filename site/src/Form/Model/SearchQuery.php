<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Model: a search query
 */
class SearchQuery
{
    const TYPE_USERS = 'users';
    const TYPE_ORGS = 'orgs';
    const TYPE_PROJECTS = 'projects';

    /**
     * The search query string
     *
     * @Assert\Length(max = 200)
     */
    protected $q;

    /**
     * What are type of result are we looking for?
     *
     * @Assert\Choice({"projects", "users", "orgs"})
     */
    protected $type = self::TYPE_PROJECTS;

    /**
     * Get the query string
     *
     * @return string
     */
    public function getQ()
    {
        return $this->q;
    }

    /**
     * Set the query string
     *
     * @param string $q
     */
    public function setQ($q)
    {
        $this->q = $q;
    }

    public function getType()
    {
        if (empty($this->type)) {
            return self::TYPE_PROJECTS;
        }

        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}
