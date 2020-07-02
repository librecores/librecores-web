<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ProjectPreferences
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
     * @var Project
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Project", mappedBy="preferences")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $project;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default": true})
     */
    private $alertForMissingLicenseVisible = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default": true})
     */
    private $alertForMissingHomePageVisible = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default": true})
     */
    private $alertForMissingReadmeVisible = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default": true})
     */
    private $alertForMissingIssueTrackerVisible = true;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Project
     */
    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @param Project $project
     *
     * @return ProjectPreferences
     */
    public function setProject(Project $project): ProjectPreferences
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAlertForMissingLicenseVisible(): bool
    {
        return $this->alertForMissingLicenseVisible;
    }

    /**
     * @param bool $alertForMissingLicenseVisible
     *
     * @return ProjectPreferences
     */
    public function setAlertForMissingLicenseVisible(bool $alertForMissingLicenseVisible): ProjectPreferences
    {
        $this->alertForMissingLicenseVisible = $alertForMissingLicenseVisible;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAlertForMissingHomePageVisible(): bool
    {
        return $this->alertForMissingHomePageVisible;
    }

    /**
     * @param bool $alertForMissingHomePageVisible
     *
     * @return ProjectPreferences
     */
    public function setAlertForMissingHomePageVisible(bool $alertForMissingHomePageVisible): ProjectPreferences
    {
        $this->alertForMissingHomePageVisible = $alertForMissingHomePageVisible;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAlertForMissingReadmeVisible(): bool
    {
        return $this->alertForMissingReadmeVisible;
    }

    /**
     * @param bool $alertForMissingReadmeVisible
     *
     * @return ProjectPreferences
     */
    public function setAlertForMissingReadmeVisible(bool $alertForMissingReadmeVisible): ProjectPreferences
    {
        $this->alertForMissingReadmeVisible = $alertForMissingReadmeVisible;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAlertForMissingIssueTrackerVisible(): bool
    {
        return $this->alertForMissingIssueTrackerVisible;
    }

    /**
     * @param bool $alertForMissingIssueTrackerVisible
     *
     * @return ProjectPreferences
     */
    public function setAlertForMissingIssueTrackerVisible(bool $alertForMissingIssueTrackerVisible): ProjectPreferences
    {
        $this->alertForMissingIssueTrackerVisible = $alertForMissingIssueTrackerVisible;

        return $this;
    }
}
