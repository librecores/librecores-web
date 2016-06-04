<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A project
 *
 * A project is a sufficiently independent piece of software or hardware. It
 * can be associated with a user or with an organization.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Librecores\ProjectRepoBundle\Entity\ProjectRepository")
 */
class Project
{
    const STATUS_ASSIGNED = 'ASSIGNED';
    const STATUS_UNASSIGNED = 'UNASSIGNED';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\Choice(choices = {"ASSIGNED", "UNASSIGNED"})
     * @ORM\Column(type="string")
     */
    private $status = self::STATUS_ASSIGNED;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="projects")
     */
    private $parentUser;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="projects")
     **/
    private $parentOrganization;

    /**
     * @var string
     *
     * @Assert\Length(max = 255)
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


    /**
     * The tagline of the project
     *
     * A tagline is a short and to the point description of what the project does.
     *
     * @var string
     *
     * @Assert\Length(max = 140)
     * @ORM\Column(name="tagline", type="string", length=140, nullable=true)
     */
    private $tagline;

    /**
     * Project web site URL
     *
     * @var string
     *
     * @Assert\Length(max = 255)
     * @Assert\Url
     * @ORM\Column(name="projectUrl", type="string", length=255, nullable=true)
     */
    private $projectUrl;

    /**
     * URL to the issue/bug tracker
     *
     * @var string
     *
     * @Assert\Length(max = 255)
     * @Assert\Url
     * @ORM\Column(name="issueTracker", type="string", length=255, nullable=true)
     */
    private $issueTracker;

    /**
     * @var SourceRepo
     *
     * @Assert\Type(type="Librecores\ProjectRepoBundle\Entity\SourceRepo")
     * @Assert\Valid()
     * @ORM\ManyToOne(targetEntity="SourceRepo", inversedBy="projects", cascade={"persist"})
     */
    private $sourceRepo;

    /**
     * Name of the license
     *
     * @var string
     *
     * @Assert\Length(max = 100)
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    private $licenseName;

    /**
     * Full license text in Markdown format
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $licenseText;

    /**
     * Update the license text automatically from the source code repository.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $licenseTextAutoUpdate = true;

    /**
     * Project description in Markdown format
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $descriptionText;

    /**
     * Update the description text automatically from the source code repository
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $descriptionTextAutoUpdate = true;

    /**
     * The project's data is currently being processed
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $inProcessing = false;


    /**
     * Get the name of the "parent" of this project
     *
     * A parent can either be a user or an organization. Use this for display
     * purposes only.
     *
     * @return string the parent name
     *
     * @see getParentOrganization()
     * @see getParentUser()
     */
    public function getParentName()
    {
        if ($this->parentUser !== null) {
            return $this->parentUser->getUsername();
        } else {
            return $this->parentOrganization->getName();
        }
    }


    /**
     * Set the project status
     *
     * @param string $status one of the self::STATUS_* constants
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (!in_array($status, array(self::STATUS_ASSIGNED, self::STATUS_UNASSIGNED))) {
            throw new \InvalidArgumentException("Invalid status");
        }
        if ($this->status == $status) {
            return;
        }

        // all unassigned projects are collected in the "unassigned" organization
        if ($status == self::STATUS_UNASSIGNED) {
            $this->setParentOrganization(Organization::SPECIAL_UNASSIGNED_ID);
        }
        $this->status = $status;
    }

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
        $this->parentOrganization = null;

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
        $this->parentUser = null;

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

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get the "FQDN" of this core
     *
     * This includes the parent name if the project was claimed by someone.
     *
     * @return string
     */
    public function getFullName()
    {
        if ($this->getParentOrganization() !== null) {
            return $this->getParentOrganization()->getName().'/'.$this->getName();
        }
        if ($this->getParentUser() !== null) {
            return $this->getParentUser()->getUsername().'/'.$this->getName();
        }
        return $this->getName();
    }

    /**
     * Is this project assigned to an organization or a user?
     *
     * @return boolean
     */
    public function isAssigned()
    {
        return ($this->getStatus() == self::STATUS_ASSIGNED);
    }

    /**
     * Set licenseName
     *
     * @param string $licenseName
     * @return Project
     */
    public function setLicenseName($licenseName)
    {
        $this->licenseName = $licenseName;

        return $this;
    }

    /**
     * Get licenseName
     *
     * @return string
     */
    public function getLicenseName()
    {
        return $this->licenseName;
    }

    /**
     * Set licenseText
     *
     * @param string $licenseText
     * @return Project
     */
    public function setLicenseText($licenseText)
    {
        $this->licenseText = $licenseText;

        return $this;
    }

    /**
     * Get licenseText
     *
     * @return string
     */
    public function getLicenseText()
    {
        return $this->licenseText;
    }

    /**
     * Set licenseTextAutoUpdate
     *
     * @param boolean $licenseTextAutoUpdate
     * @return Project
     */
    public function setLicenseTextAutoUpdate($licenseTextAutoUpdate)
    {
        $this->licenseTextAutoUpdate = (bool)$licenseTextAutoUpdate;

        return $this;
    }

    /**
     * Get licenseTextAutoUpdate
     *
     * @return boolean
     */
    public function getLicenseTextAutoUpdate()
    {
        return $this->licenseTextAutoUpdate;
    }

    /**
     * Set descriptionText
     *
     * @param string $descriptionText
     * @return Project
     */
    public function setDescriptionText($descriptionText)
    {
        $this->descriptionText = $descriptionText;

        return $this;
    }

    /**
     * Get descriptionText
     *
     * @return string
     */
    public function getDescriptionText()
    {
        return $this->descriptionText;
    }

    /**
     * Set descriptionTextAutoUpdate
     *
     * @param boolean $descriptionTextAutoUpdate
     * @return Project
     */
    public function setDescriptionTextAutoUpdate($descriptionTextAutoUpdate)
    {
        $this->descriptionTextAutoUpdate = (bool)$descriptionTextAutoUpdate;

        return $this;
    }

    /**
     * Get descriptionTextAutoUpdate
     *
     * @return boolean
     */
    public function getDescriptionTextAutoUpdate()
    {
        return $this->descriptionTextAutoUpdate;
    }

    /**
     * Set inProcessing
     *
     * @param boolean $inProcessing
     * @return Project
     */
    public function setInProcessing($inProcessing)
    {
        $this->inProcessing = $inProcessing;

        return $this;
    }

    /**
     * Get inProcessing
     *
     * @return boolean
     */
    public function getInProcessing()
    {
        return $this->inProcessing;
    }

    /**
     * Set tagline
     *
     * @param string $tagline
     * @return Project
     */
    public function setTagline($tagline)
    {
        $this->tagline = $tagline;

        return $this;
    }

    /**
     * Get tagline
     *
     * @return string
     */
    public function getTagline()
    {
        return $this->tagline;
    }
}
