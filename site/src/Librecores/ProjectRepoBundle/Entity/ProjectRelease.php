<?php

namespace Librecores\ProjectRepoBundle\Entity;

use DateTime;

class ProjectRelease
{
    /**
     * Name of the release
     *
     * @var string
     */
    private $name;

    /**
     * Date when release was published
     *
     * @var DateTime
     */
    private $publishedAt;

    /**
     * Is the release a pre-release
     *
     * @var bool
     */
    private $isPrerelease;

    /**
     * Commit ID this release is based on, if available
     *
     * @var string|null
     */
    private $commitID;

    /**
     * Get release name.
     *
     * Usually a version string, conforming to sevmer.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set release name
     *
     * @param string $name
     *
     * @return ProjectRelease
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get when this release was published
     *
     * @return DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set when this release was published
     *
     * @param DateTime $publishedAt
     *
     * @return ProjectRelease
     */
    public function setPublishedAt(DateTime $publishedAt)
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * Get if this release is a pre-release
     *
     * @return bool
     */
    public function isPrerelease(): bool
    {
        return $this->isPrerelease;
    }

    /**
     * Set if this release is a pre-release
     *
     * @param bool $isPrerelease
     *
     * @return ProjectRelease
     */
    public function setIsPrerelease(bool $isPrerelease)
    {
        $this->isPrerelease = $isPrerelease;

        return $this;
    }

    /**
     * Get the commit id this release is based on
     *
     * @return string|null
     */
    public function getCommitID(): ?string
    {
        return $this->commitID;
    }

    /**
     * Set the commit id this release is based on
     *
     * @param string $commitID
     *
     * @return ProjectRelease
     */
    public function setCommitID(string $commitID)
    {
        $this->commitID = $commitID;

        return $this;
    }
}
