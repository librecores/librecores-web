<?php
namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A project
 *
 * A project is a sufficiently independent piece of software or hardware. It
 * can be associated with a user or with an organization.
 *
 * @ORM\Table("Project", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="projectname_full",
 *         columns={"name", "parentUser_id", "parentOrganization_id"})
 * })
 * @ORM\Entity(repositoryClass="Librecores\ProjectRepoBundle\Repository\ProjectRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity(
 *     fields={"parentUser", "parentOrganization", "name"},
 *     errorPath="name",
 *     message="A project with that name already exists.",
 *     ignoreNull=false
 * )
 */
class Project implements NormalizableInterface
{
    const STATUS_ASSIGNED   = 'ASSIGNED';
    const STATUS_UNASSIGNED = 'UNASSIGNED';

    /**
     * Internal project ID
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Status of the project
     *
     * @var string
     *
     * @Assert\Choice(choices = {"ASSIGNED", "UNASSIGNED"})
     *
     * @ORM\Column(type="string", options={"default" : Project::STATUS_ASSIGNED})
     */
    private $status = self::STATUS_ASSIGNED;

    /**
     * User owning this project
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="projects")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $parentUser;

    /**
     * Organization owning this project
     *
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="projects")
     * @ORM\JoinColumn(nullable=true)
     */
    private $parentOrganization;

    // Associations
    /**
     * Classifications assigned to this project
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ProjectClassification", mappedBy="project",cascade={"persist", "remove"},
     *                orphanRemoval=true)
     * @ORM\JoinColumn(name="projectId", referencedColumnName="id")
     */
    protected $classifications;

    /**
     * Short name of the project
     *
     * This field is used for the URL component (e.g.
     * http://librecores.org/parent/name). It's user-visible, but is limited
     * in the allowed characters and length. Think of it like a project's
     * username.
     *
     * @var string
     *
     * @Assert\Regex("/^[a-z][a-z0-9-]+$/")
     * @Assert\Length(min = 4, max = 30)
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * Display name of the project
     *
     * This is the "more human-readable" version of Project::$name. Think of it
     * like the real name of a user.
     *
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(min = 5, max = 100)
     *
     * @ORM\Column(name="displayName", type="string", length=100)
     */
    private $displayName;

    /**
     * The tagline of the project
     *
     * A tagline is a short and to the point description of what the project
     * does.
     *
     * @var string
     *
     * @Assert\Length(max = 140)
     *
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
     *
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
     *
     * @ORM\Column(name="issueTracker", type="string", length=255, nullable=true)
     */
    private $issueTracker;

    /**
     * @var SourceRepo
     *
     * @Assert\Type(type="Librecores\ProjectRepoBundle\Entity\SourceRepo")
     * @Assert\Valid()
     *
     * @ORM\OneToOne(targetEntity="SourceRepo", inversedBy="project", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="sourceRepo_id", referencedColumnName="id", nullable=true, onDelete="cascade")
     */
    private $sourceRepo;

    /**
     * Name of the license
     *
     * @var string
     *
     * @Assert\Length(max = 100)
     *
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    private $licenseName;

    /**
     * Full license text in HTML
     *
     * @var string
     *
     * @Assert\Length(max = 64000)
     *
     * @ORM\Column(type="text", nullable=true, length=64000)
     */
    private $licenseText;

    /**
     * Update the license text automatically from the source code repository.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default" : true})
     */
    private $licenseTextAutoUpdate = true;

    /**
     * Project description in HTML
     *
     * @var string
     *
     * @Assert\Length(max = 64000)
     *
     * @ORM\Column(type="text", nullable=true, length=64000)
     */
    private $descriptionText;

    /**
     * Update the description text automatically from the source code repository
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default" : true})
     */
    private $descriptionTextAutoUpdate = true;

    /**
     * The project's data is currently being processed
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $inProcessing = false;

    /**
     * The date when this project was added to LibreCores
     *
     * @var \DateTime date/time in UTC
     *
     * @see __construct()
     *
     * @ORM\Column(type="datetime")
     */
    private $dateAdded;

    /**
     * The date when the metadata of this project (i.e. the fields in this
     * entity) were last modified.
     *
     * This field is updated automatically when saving this entity.
     *
     * @see __construct()
     * @see updateDateLastModified()
     *
     * @var \DateTime date/time in UTC
     *
     * @ORM\Column(type="datetime")
     */
    private $dateLastModified;

    /**
     * The date when the last activity occurred.
     *
     * Commits, discussions in issue trackers, releases, pull requests
     * are treated as activity.
     *
     * @var \DateTime date/time in UTC
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateLastActivityOccurred;

    /**
     * Forks of this repository in the repository host
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $forks;

    /**
     * Forks of this repository in the repository host
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $openPullRequests;

    /**
     * Forks of this repository in the repository host
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $openIssues;

    /**
     * Stars of this repository in the repository host
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $stars;

    /**
     * Watchers of this repository in the repository host
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $watchers;

    /**
     * Releases of this project
     * @var ProjectRelease[]
     *
     * @ORM\Column(type="array")
     */
    private $releases;

    /**
     * Constructor
     */
    public function __construct()
    {
        // record the date/time of the project creation
        $this->setDateAdded(new \DateTime());
        $this->setDateLastModified(new \DateTime());
        $this->classifications = new ArrayCollection();
    }

    /**
     * @param NormalizerInterface $serializer
     * @param null $format
     * @param array $context
     *
     * @return array
     */
    public function normalize(NormalizerInterface $serializer, $format = null, array $context = array()): array
    {
        return [
            'name' => $this->getName(),
            'displayName' => $this->getDisplayName(),
            'tagName' => $this->getTagline(),
        ];
    }
    /**
     * Update $dateLastModified
     *
     * This is called automatically by Doctrine.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateDateLastModified()
    {
        $this->setDateLastModified(new \DateTime());
    }

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
        }

        return $this->parentOrganization->getName();
    }

    /**
     * Get the fully qualified name of this project
     *
     * @return string
     */
    public function getFqname()
    {
        return $this->getParentName().'/'.$this->getName();
    }

    /**
     * Set the project status
     *
     * @param string $status one of the self::STATUS_* constants
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (!in_array($status, array(self::STATUS_ASSIGNED, self::STATUS_UNASSIGNED))) {
            throw new \InvalidArgumentException("Invalid status");
        }
        if ($this->status === $status) {
            return;
        }

        // all unassigned projects are collected in the "unassigned" organization
        if ($status === self::STATUS_UNASSIGNED) {
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
     * @param User $parentUser
     *
     * @return Project
     */
    public function setParentUser(User $parentUser = null)
    {
        if ($this->parentUser !== null) {
            $this->parentUser->removeProject($this);
        }

        if ($parentUser !== null) {
            $parentUser->addProject($this);
            $this->setParentOrganization(null);
        }

        $this->parentUser = $parentUser;

        return $this;
    }

    /**
     * Get parentUser
     *
     * @return User
     */
    public function getParentUser()
    {
        return $this->parentUser;
    }

    /**
     * Set parentOrganization
     *
     * @param Organization $parentOrganization
     *
     * @return Project
     */
    public function setParentOrganization(Organization $parentOrganization = null)
    {
        if ($this->parentOrganization !== null) {
            $this->parentOrganization->removeProject($this);
        }

        if ($parentOrganization !== null) {
            $parentOrganization->addProject($this);
            $this->setParentUser(null);
        }

        $this->parentOrganization = $parentOrganization;

        return $this;
    }

    /**
     * Get parentOrganization
     *
     * @return Organization
     */
    public function getParentOrganization()
    {
        return $this->parentOrganization;
    }

    /**
     * Set name
     *
     * @param string $name
     *
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
     *
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
     *
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
     *
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
     *
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
     *
     * @return Project
     */
    public function setLicenseTextAutoUpdate($licenseTextAutoUpdate)
    {
        $this->licenseTextAutoUpdate = (bool) $licenseTextAutoUpdate;

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
     *
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
     *
     * @return Project
     */
    public function setDescriptionTextAutoUpdate($descriptionTextAutoUpdate)
    {
        $this->descriptionTextAutoUpdate = (bool) $descriptionTextAutoUpdate;

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
     *
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
     *
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

    /**
     * Set dateAdded
     *
     * @param \DateTime $dateAdded
     *
     * @return Project
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * Set dateLastModified
     *
     * @param \DateTime $dateLastModified
     *
     * @return Project
     */
    public function setDateLastModified($dateLastModified)
    {
        $this->dateLastModified = $dateLastModified;

        return $this;
    }

    /**
     * Get dateLastModified
     *
     * @return \DateTime
     */
    public function getDateLastModified()
    {
        return $this->dateLastModified;
    }

    /**
     * Set dateLastActivityOccurred
     *
     * @param \DateTime $date
     *
     * @return Project
     */
    public function setDateLastActivityOccurred($date)
    {
        $this->dateLastActivityOccurred = $date;

        return $this;
    }

    /**
     * Get dateLastActivityOccurred
     *
     * @return \DateTime
     */
    public function getDateLastActivityOccurred()
    {
        return $this->dateLastActivityOccurred;
    }

    /**
     * Set sourceRepo
     *
     * @param \Librecores\ProjectRepoBundle\Entity\SourceRepo $sourceRepo
     *
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
     * Set displayName
     *
     * @param string $displayName
     *
     * @return Project
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Get displayName
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Get forks
     *
     * @return int
     */
    public function getForks(): ?int
    {
        return $this->forks;
    }

    /**
     * Set forks
     *
     * @param int $forks
     *
     * @return Project
     */
    public function setForks(int $forks)
    {
        $this->forks = $forks;

        return $this;
    }

    /**
     * Set openPullRequests
     *
     * @return int
     */
    public function getOpenPullRequests(): ?int
    {
        return $this->openPullRequests;
    }

    /**
     * Get openPullRequests
     *
     * @param int $openPullRequests
     *
     * @return Project
     */
    public function setOpenPullRequests(int $openPullRequests)
    {
        $this->openPullRequests = $openPullRequests;

        return $this;
    }

    /**
     * Get openIssues
     *
     * @return int
     */
    public function getOpenIssues(): ?int
    {
        return $this->openIssues;
    }

    /**
     * Set openIssues
     *
     * @param int $openIssues
     *
     * @return Project
     */
    public function setOpenIssues(int $openIssues)
    {
        $this->openIssues = $openIssues;

        return $this;
    }

    /**
     * Get stars
     *
     * @return int
     */
    public function getStars(): ?int
    {
        return $this->stars;
    }

    /**
     * Set stars
     *
     * @param int $stars
     *
     * @return Project
     */
    public function setStars(int $stars)
    {
        $this->stars = $stars;

        return $this;
    }

    /**
     * Get watchers
     *
     * @return int
     */
    public function getWatchers(): ?int
    {
        return $this->watchers;
    }

    /**
     * Set watchers
     *
     * @param int $watchers
     *
     * @return Project
     */
    public function setWatchers(int $watchers)
    {
        $this->watchers = $watchers;

        return $this;
    }

    /**
     * Get releases
     *
     * @return ProjectRelease[]
     */
    public function getReleases()
    {
        return $this->releases;
    }

    /**
     * Set releases
     *
     * @param ProjectRelease[] $releases
     *
     * @return Project
     */
    public function setReleases(array $releases)
    {
        $this->releases = $releases;

        return $this;
    }

    /**
     * Add Classification
     *
     * @param \Librecores\ProjectRepoBundle\Entity\ProjectClassification $classifications
     *
     * @return Project
     */
    public function addClassification(ProjectClassification $classifications)
    {
        $this->classifications[] = $classifications;

        return $this;
    }

    /**
     * Remove Classification
     *
     * @param \Librecores\ProjectRepoBundle\Entity\ProjectClassification $classifications
     */
    public function removeClassification(ProjectClassification $classifications)
    {
        $this->classifications->removeElement($classifications);
    }

    /**
     * Get Classification
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClassification()
    {
        return $this->classifications;
    }

    /**
     * Get classifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClassifications()
    {
        return $this->classifications;
    }
}
