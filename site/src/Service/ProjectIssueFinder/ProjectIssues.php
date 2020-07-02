<?php


namespace App\Service\ProjectIssueFinder;

use App\Entity\Project;

class ProjectIssues
{
    /**
     * @var Project
     */
    private $project;

    /**
     * @var bool
     */
    private $licenseMissing = false;

    /**
     * @var bool
     */
    private $homePageMissing = false;

    /**
     * @var bool
     */
    private $issueTrackerMissing = false;

    /**
     * @var bool
     */
    private $readmeMissing = false;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * @return bool
     */
    public function isLicenseMissing(): bool
    {
        return $this->licenseMissing;
    }

    /**
     * @param bool $licenseMissing
     */
    public function setLicenseMissing(bool $licenseMissing): void
    {
        $this->licenseMissing = $licenseMissing;
    }

    /**
     * @return bool
     */
    public function isHomePageMissing(): bool
    {
        return $this->homePageMissing;
    }

    /**
     * @param bool $homePageMissing
     */
    public function setHomePageMissing(bool $homePageMissing): void
    {
        $this->homePageMissing = $homePageMissing;
    }

    /**
     * @return bool
     */
    public function isIssueTrackerMissing(): bool
    {
        return $this->issueTrackerMissing;
    }

    /**
     * @param bool $issueTrackerMissing
     */
    public function setIssueTrackerMissing(bool $issueTrackerMissing): void
    {
        $this->issueTrackerMissing = $issueTrackerMissing;
    }

    /**
     * @return bool
     */
    public function isReadmeMissing(): bool
    {
        return $this->readmeMissing;
    }

    /**
     * @param bool $readmeMissing
     */
    public function setReadmeMissing(bool $readmeMissing): void
    {
        $this->readmeMissing = $readmeMissing;
    }

    /**
     * @return Project
     */
    public function getProject(): Project
    {
        return $this->project;
    }

    public function hasIssues()
    {
        return $this->homePageMissing || $this->readmeMissing ||
            $this->issueTrackerMissing || $this->licenseMissing;
    }
}
