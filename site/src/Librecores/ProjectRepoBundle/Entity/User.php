<?php
namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * A user
 *
 * A user is an individual who can own projects.
 *
 * @ORM\Table("User")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class User extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    // OAuth-related entries
    /**
     * Name of the OAuth service provider
     *
     * @var string $oAuthService
     *
     * @ORM\Column(name="oauth_service", type="string", length=255, nullable=true)
     */
    protected $oAuthService;

    /**
     * User ID provided by the OAuth service.
     *
     * @var string $oAuthId
     *
     * @ORM\Column(name="oauth_user_id", type="string", length=255, nullable=true)
     */
    protected $oAuthUserId;

    /**
     * OAuth access token.
     *
     * With this token, authenticated requests to the OAuth API can be
     * performed.
     *
     * @var string $oAuthAccessToken
     *
     * @ORM\Column(name="oauth_access_token", type="string", length=255, nullable=true)
     */
    protected $oAuthAccessToken;

    // associations
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Project", mappedBy="parentUser")
     * @ORM\JoinColumn(name="projectId", referencedColumnName="id")
     */
    protected $projects;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Organization", mappedBy="creator")
     * @ORM\JoinColumn(name="organizationId", referencedColumnName="id")
     */
    protected $organizationsCreated;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="OrganizationMember", mappedBy="user", cascade={"remove"})
     * @ORM\JoinColumn(name="membershipId", referencedColumnName="id")
     **/
    protected $organizationMemberships;

    // profile data
    /**
     * Full (real) name of the user
     *
     * @var string $name
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    // metadata
    /**
     * When was this user created?
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * When was this user last updated?
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;


    public function __construct()
    {
        parent::__construct();
        $this->projects                = new ArrayCollection();
        $this->organizationsCreated    = new ArrayCollection();
        $this->organizationMemberships = new ArrayCollection();
    }

    /**
     * Hook on the first storing of this object
     *
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime;
        $this->updatedAt = new \DateTime;
    }

    /**
     * Hook before each update
     *
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime;
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
     * Add project
     *
     * @param Project $project
     * @return User
     */
    public function addProject(Project $project)
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setParentUser($this);
        }

        return $this;
    }

    /**
     * Remove project
     *
     * @param Project $project
     */
    public function removeProject(Project $project)
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            $project->setParentUser(null);
        }
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add organization created
     *
     * @param Organization $organization
     * @return User
     */
    public function addOrganizationCreated(Organization $organization)
    {
        if (!$this->organizationsCreated->contains($organization)) {
            $this->organizationsCreated[] = $organization;
            $organization->setCreator($this);
        }

        return $this;
    }

    /**
     * Remove organization created
     *
     * @param Organization $organization
     */
    public function removeOrganizationCreated(Organization $organization)
    {
        if ($this->organizationsCreated->contains($organization)) {
            $this->organizationsCreated->removeElement($organization);
            $organization->setCreator(null);
        }
    }

    /**
     * Get organizations created
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizationsCreated()
    {
        return $this->organizationsCreated;
    }

    /**
     * Add organization membership
     *
     * @param OrganizationMember $organizationMember
     * @return User
     */
    public function addOrganizationMembership(OrganizationMember $organizationMember)
    {
        if (!$this->organizationMemberships->contains($organizationMember)) {
            $this->organizationMemberships[] = $organizationMember;
        }

        return $this;
    }

    /**
     * Remove organization membership
     *
     * @param OrganizationMember $organizationMember
     */
    public function removeOrganizationMembership(OrganizationMember $organizationMember)
    {
        if ($this->organizationMemberships->contains($organizationMember)) {
            $this->organizationMemberships->removeElement($organizationMember);
        }
    }

    /**
     * Get organization memberships
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizationMemberships()
    {
        return $this->organizationMemberships;
    }

    /**
     * Get organizations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizations()
    {
        return array_map(
            function (OrganizationMember $organizationMember) {
                return $organizationMember->getOrganization();
            },
            $this->organizationMemberships->toArray()
        );
    }

    /**
     * Set oAuthService
     *
     * @param string $oAuthService
     * @return User
     */
    public function setOAuthService($oAuthService)
    {
        $this->oAuthService = $oAuthService;

        return $this;
    }

    /**
     * Get oAuthService
     *
     * @return string
     */
    public function getOAuthService()
    {
        return $this->oAuthService;
    }

    /**
     * Set oAuthUserId
     *
     * @param string $oAuthUserId
     * @return User
     */
    public function setOAuthUserId($oAuthUserId)
    {
        $this->oAuthUserId = $oAuthUserId;

        return $this;
    }

    /**
     * Get oAuthUserId
     *
     * @return string
     */
    public function getOAuthUserId()
    {
        return $this->oAuthUserId;
    }

    /**
     * Set oAuthAccessToken
     *
     * @param string $oAuthAccessToken
     * @return User
     */
    public function setOAuthAccessToken($oAuthAccessToken)
    {
        $this->oAuthAccessToken = $oAuthAccessToken;

        return $this;
    }

    /**
     * Get oAuthAccessToken
     *
     * @return string
     */
    public function getOAuthAccessToken()
    {
        return $this->oAuthAccessToken;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return User
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
}
