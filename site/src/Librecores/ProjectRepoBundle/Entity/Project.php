<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A project
 *
 * A project is a sufficiently independent piece of software or hardware. It
 * can be associated with a user or with an organization.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Project
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="parentUser", type="integer", nullable=true)
     * @ORM\ManyToOne(targetEntity="User", inversedBy="projects")
     */
    private $parentUser;

    /**
     * @var integer
     *
     * @ORM\Column(name="parentOrganization", type="integer", nullable=true)
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="projects")
     **/
    private $parentOrganization;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="projectUrl", type="string", length=255)
     */
    private $projectUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="issueTracker", type="string", length=255)
     */
    private $issueTracker;

    /**
     * @var SourceRepo
     *
     * @ORM\ManyToOne(targetEntity="SourceRepo", inversedBy="projects")
     */
    private $sourceRepo;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $licenseFileContent;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $readmeFileContent;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set parentUser
     *
     * @param integer $parentUser
     * @return Project
     */
    public function setParentUser($parentUser)
    {
        $this->parentUser = $parentUser;

        return $this;
    }

    /**
     * Get parentUser
     *
     * @return integer 
     */
    public function getParentUser()
    {
        return $this->parentUser;
    }

    /**
     * Set parentOrganization
     *
     * @param integer $parentOrganization
     * @return Project
     */
    public function setParentOrganization($parentOrganization)
    {
        $this->parentOrganization = $parentOrganization;

        return $this;
    }

    /**
     * Get parentOrganization
     *
     * @return integer 
     */
    public function getParentOrganization()
    {
        return $this->parentOrganization;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Project
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
     * Set projectUrl
     *
     * @param string $projectUrl
     * @return Project
     */
    public function setProjectUrl($projectUrl)
    {
        $this->projectUrl = $projectUrl;

        return $this;
    }

    /**
     * Get projectUrl
     *
     * @return string 
     */
    public function getProjectUrl()
    {
        return $this->projectUrl;
    }

    /**
     * Set issueTracker
     *
     * @param string $issueTracker
     * @return Project
     */
    public function setIssueTracker($issueTracker)
    {
        $this->issueTracker = $issueTracker;

        return $this;
    }

    /**
     * Get issueTracker
     *
     * @return string 
     */
    public function getIssueTracker()
    {
        return $this->issueTracker;
    }

    /**
     * Set licenseFileContent
     *
     * @param string $licenseFileContent
     * @return Project
     */
    public function setLicenseFileContent($licenseFileContent)
    {
        $this->licenseFileContent = $licenseFileContent;

        return $this;
    }

    /**
     * Get licenseFileContent
     *
     * @return string 
     */
    public function getLicenseFileContent()
    {
        return $this->licenseFileContent;
    }

    /**
     * Set readmeFileContent
     *
     * @param string $readmeFileContent
     * @return Project
     */
    public function setReadmeFileContent($readmeFileContent)
    {
        $this->readmeFileContent = $readmeFileContent;

        return $this;
    }

    /**
     * Get readmeFileContent
     *
     * @return string 
     */
    public function getReadmeFileContent()
    {
        return $this->readmeFileContent;
    }

    /**
     * Set sourceRepo
     *
     * @param \Librecores\ProjectRepoBundle\Entity\SourceRepo $sourceRepo
     * @return Project
     */
    public function setSourceRepo(\Librecores\ProjectRepoBundle\Entity\SourceRepo $sourceRepo = null)
    {
        $this->sourceRepo = $sourceRepo;

        return $this;
    }

    /**
     * Get sourceRepo
     *
     * @return \Librecores\ProjectRepoBundle\Entity\SourceRepo 
     */
    public function getSourceRepo()
    {
        return $this->sourceRepo;
    }
}
