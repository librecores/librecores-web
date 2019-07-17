<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Mgilet\NotificationBundle\Annotation\Notifiable;
use Mgilet\NotificationBundle\NotifiableInterface;

/**
 * A user
 *
 * A user is an individual who can own projects.
 *
 * @ORM\Table("User")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @Notifiable(name="User")
 */
class User extends BaseUser implements NotifiableInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    // OAuth-related entries

    /**
     * GitHub User ID provided by the OAuth service.
     *
     * @var string
     *
     * @ORM\Column(name="githubOAuthUserId", type="string", length=255, nullable=true)
     */
    protected $githubOAuthUserId;

    /**
     * GitHub OAuth access token.
     *
     * With this token, authenticated requests to the OAuth API can be
     * performed.
     *
     * @var string
     *
     * @ORM\Column(name="githubOAuthAccessToken", type="string", length=255, nullable=true)
     */
    protected $githubOAuthAccessToken;

    /**
     * Google User ID provided by the OAuth service.
     *
     * @var string
     *
     * @ORM\Column(name="googleOAuthUserId", type="string", length=255, nullable=true)
     */
    protected $googleOAuthUserId;

    /**
     * Google OAuth access token.
     *
     * With this token, authenticated requests to the OAuth API can be
     * performed.
     *
     * @var string
     *
     * @ORM\Column(name="googleOAuthAccessToken", type="string", length=255, nullable=true)
     */
    protected $googleOAuthAccessToken;

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
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * When was this user last updated?
     *
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * Has the user subscribed to email notifications?
     *
     * @var $subscribedToEmailNotifications
     *
     * @ORM\Column(type="boolean", options={"default" : true})
     */
    protected $subscribedToEmailNotifications = true;

    public function __construct()
    {
        parent::__construct();
        $this->projects = new ArrayCollection();
        $this->organizationsCreated = new ArrayCollection();
        $this->organizationMemberships = new ArrayCollection();
    }

    /**
     * Hook on the first storing of this object
     *
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    /**
     * Hook before each update
     *
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updatedAt = new DateTime();
    }

    /**
     * Get the names of all OAuth services connected to this user
     *
     * @return string[]
     */
    public function getConnectedOAuthServices()
    {
        $result = [];
        if ($this->googleOAuthUserId) {
            $result[] = 'google';
        }
        if ($this->googleOAuthUserId) {
            $result[] = 'github';
        }

        return $result;
    }

    /**
     * Is the user account connected to an OAuth service with a given name?
     *
     * @param string $serviceName
     *
     * @return boolean
     */
    public function isConnectedToOAuthService($serviceName)
    {
        if ($serviceName === 'github') {
            return $this->githubOAuthUserId !== null;
        }
        if ($serviceName === 'google') {
            return $this->googleOAuthUserId !== null;
        }

        return false;
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
     *
     * @return User
     */
    public function addProject(Project $project)
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
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
        }
    }

    /**
     * Get projects
     *
     * @return Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add organization created
     *
     * @param Organization $organization
     *
     * @return User
     */
    public function addOrganizationCreated(Organization $organization)
    {
        if (!$this->organizationsCreated->contains($organization)) {
            $this->organizationsCreated[] = $organization;
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
        }
    }

    /**
     * Get organizations created
     *
     * @return Collection
     */
    public function getOrganizationsCreated()
    {
        return $this->organizationsCreated;
    }

    /**
     * Add organization membership
     *
     * @param OrganizationMember $organizationMember
     *
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
     * @return Collection
     */
    public function getOrganizationMemberships()
    {
        return $this->organizationMemberships;
    }

    /**
     * Get organizations mapped through organization memberships
     *
     * @return Collection
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
     * Get createdAt
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     *
     * @param DateTime $updatedAt
     *
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

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
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get githubOAuthUserId
     *
     * @return string
     */
    public function getGithubOAuthUserId()
    {
        return $this->githubOAuthUserId;
    }

    /**
     * Set githubOAuthUserId
     *
     * @param string $githubOAuthUserId
     *
     * @return User
     */
    public function setGithubOAuthUserId($githubOAuthUserId)
    {
        $this->githubOAuthUserId = $githubOAuthUserId;

        return $this;
    }

    /**
     * Get githubOAuthAccessToken
     *
     * @return string
     */
    public function getGithubOAuthAccessToken()
    {
        return $this->githubOAuthAccessToken;
    }

    /**
     * Set githubOAuthAccessToken
     *
     * @param string $githubOAuthAccessToken
     *
     * @return User
     */
    public function setGithubOAuthAccessToken($githubOAuthAccessToken)
    {
        $this->githubOAuthAccessToken = $githubOAuthAccessToken;

        return $this;
    }

    /**
     * Get googleOAuthUserId
     *
     * @return string
     */
    public function getGoogleOAuthUserId()
    {
        return $this->googleOAuthUserId;
    }

    /**
     * Set googleOAuthUserId
     *
     * @param string $googleOAuthUserId
     *
     * @return User
     */
    public function setGoogleOAuthUserId($googleOAuthUserId)
    {
        $this->googleOAuthUserId = $googleOAuthUserId;

        return $this;
    }

    /**
     * Get googleOAuthAccessToken
     *
     * @return string
     */
    public function getGoogleOAuthAccessToken()
    {
        return $this->googleOAuthAccessToken;
    }

    /**
     * Set googleOAuthAccessToken
     *
     * @param string $googleOAuthAccessToken
     *
     * @return User
     */
    public function setGoogleOAuthAccessToken($googleOAuthAccessToken)
    {
        $this->googleOAuthAccessToken = $googleOAuthAccessToken;

        return $this;
    }

    /**
     * Add organizationsCreated
     *
     * @param Organization $organizationsCreated
     *
     * @return User
     */
    public function addOrganizationsCreated(Organization $organizationsCreated)
    {
        $this->organizationsCreated[] = $organizationsCreated;

        return $this;
    }

    /**
     * Remove organizationsCreated
     *
     * @param Organization $organizationsCreated
     */
    public function removeOrganizationsCreated(Organization $organizationsCreated)
    {
        $this->organizationsCreated->removeElement($organizationsCreated);
    }

    /**
     * Has the user subscribed to email notifications?
     *
     * @return bool
     */
    public function isSubscribedToEmailNotifications(): bool
    {
        return $this->subscribedToEmailNotifications;
    }

    /**
     * Set email subscription for a given user
     *
     * @param bool $subscribedToEmailNotifications
     *
     * @return $this
     */
    public function setSubscribedToEmailNotifications(bool $subscribedToEmailNotifications)
    {
        $this->subscribedToEmailNotifications = $subscribedToEmailNotifications;

        return $this;
    }
}
