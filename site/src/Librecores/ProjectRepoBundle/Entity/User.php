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
     * @ORM\OneToMany(targetEntity="Project", mappedBy="parentUser")
     * @ORM\JoinColumn(name="projectId", referencedColumnName="id")
     */
    protected $projects;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Organization", mappedBy="owner")
     * @ORM\JoinColumn(name="organizationId", referencedColumnName="id")
     */
    protected $organizationsOwner;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Organization", mappedBy="members")
     * @ORM\JoinTable(name="UsersOrganizations",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="organizationId", referencedColumnName="id")}
     *     )
     **/
    protected $organizationsMember;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Organization", mappedBy="requests")
     * @ORM\JoinTable(name="UsersOrganizationRequests",
     *     joinColumns={@ORM\JoinColumn(name="userId", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="organizationId", referencedColumnName="id")}
     *     )
     **/
    protected $organizationsRequest;

    // profile data
    /**
     * Full (real) name of the user
     *
     * @var string $name
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    // metadata
    /**
     * When was this user created?
     *
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * When was this user last updated?
     *
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;


    public function __construct()
    {
        parent::__construct();
        $this->projects             = new ArrayCollection();
        $this->organizationsOwner   = new ArrayCollection();
        $this->organizationsMember  = new ArrayCollection();
        $this->organizationsRequest = new ArrayCollection();
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
     * Add projects
     *
     * @param Project $projects
     * @return User
     */
    public function addProject(Project $projects)
    {
        $this->projects[] = $projects;

        return $this;
    }

    /**
     * Remove projects
     *
     * @param Project $projects
     */
    public function removeProject(Project $projects)
    {
        $this->projects->removeElement($projects);
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
     * Add organizationsOwner
     *
     * @param Organization $organizationsOwner
     * @return User
     */
    public function addOrganizationsOwner(Organization $organizationsOwner)
    {
        if (!$this->organizationsOwner->contains($organizationsOwner))
            $this->organizationsOwner[] = $organizationsOwner;

        return $this;
    }

    /**
     * Remove organizationsOwner
     *
     * @param Organization $organizationsOwner
     */
    public function removeOrganizationsOwner(Organization $organizationsOwner)
    {
        if ($this->organizationsOwner->contains($organizationsOwner))
            $this->organizationsOwner->removeElement($organizationsOwner);
    }

    /**
     * Get organizationsOwner
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizationsOwner()
    {
        return $this->organizationsOwner;
    }

    /**
     * Add organizationsMember
     *
     * @param Organization $organizationsMember
     * @return User
     */
    public function addOrganizationsMember(Organization $organizationsMember)
    {
        if (!$this->organizationsMember->contains($organizationsMember))
            $this->organizationsMember[] = $organizationsMember;

        return $this;
    }

    /**
     * Remove organizationsMember
     *
     * @param Organization $organizationsMember
     */
    public function removeOrganizationsMember(Organization $organizationsMember)
    {
        if ($this->organizationsMember->contains($organizationsMember))
            $this->organizationsMember->removeElement($organizationsMember);
    }

    /**
     * Get organizationsMember
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizationsMember()
    {
        return $this->organizationsMember;
    }

    /**
     * Add organizationsRequest
     *
     * @param Organization $organizationsRequest
     * @return User
     */
    public function addOrganizationsRequest(Organization $organizationsRequest)
    {
        if (!$this->organizationsRequest->contains($organizationsRequest))
            $this->organizationsRequest[] = $organizationsRequest;

        return $this;
    }

    /**
     * Remove organizationsRequest
     *
     * @param Organization $organizationsRequest
     */
    public function removeOrganizationsRequest(Organization $organizationsRequest)
    {
        if ($this->organizationsRequest->contains($organizationsRequest))
            $this->organizationsRequest->removeElement($organizationsRequest);
    }

    /**
     * Get organizationsRequest
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizationsRequest()
    {
        return $this->organizationsRequest;
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
