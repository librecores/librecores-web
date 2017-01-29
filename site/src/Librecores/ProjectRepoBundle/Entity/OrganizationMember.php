<?php
namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An Organization Member
 *
 * An organization member is a mapping between a User and an Organization he belongs.
 *
 * @ORM\Table("OrganizationMember")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class OrganizationMember
{
    const PERMISSIONS_REQUEST = 'REQUEST';
    const PERMISSIONS_DENY    = 'DENY';
    const PERMISSIONS_SUPPORT = 'SUPPORT';
    const PERMISSIONS_MEMBER  = 'MEMBER';
    const PERMISSIONS_ADMIN   = 'ADMIN';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="members")
     * @ORM\JoinColumn(name="organizationId", referencedColumnName="id", nullable=FALSE)
     */
    protected $organization;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="organizationMemberships")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", nullable=FALSE)
     */
    protected $user;

    /**
     * @var string
     *
     * @Assert\Choice(choices = {"REQUEST", "DENY", "SUPPORT", "MEMBER", "ADMIN"})
     * @ORM\Column(type="string")
     */
    protected $permissions = self::PERMISSIONS_REQUEST;

    // metadata
    /**
     * When was this mapping created?
     *
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * When was this mapping last updated?
     *
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;


    public function __construct()
    {
 
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
     * Set user
     *
     * @param User $user
     * @return OrganizationMember
     */
    public function setUser(User $user = null)
    {
        if ($this->user !== null) {
            $this->user->removeOrganizationMembership($this);
        }

        if ($user !== null) {
            $user->addOrganizationMembership($this);
        }

        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
    * Set organization
    *
    * @param Organization $organization
    * @return OrganizationMember
    */
    public function setOrganization(Organization $organization = null)
    {
        if ($this->organization !== null) {
            $this->organization->removeMember($this);
        }

        if ($organization !== null) {
            $organization->addMember($this);
        }

        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set permissions
     *
     * @param string $permissions One of the self::PERMISSIONS_* constants
     * @throws \InvalidArgumentException
     */
    public function setPermissions($permissions)
    {
        if (!in_array($permissions, [self::PERMISSIONS_REQUEST,
                                     self::PERMISSIONS_DENY,
                                     self::PERMISSIONS_SUPPORT,
                                     self::PERMISSIONS_MEMBER,
                                     self::PERMISSIONS_ADMIN], false)) {
            throw new \InvalidArgumentException('Invalid Permissions');
        }

        if ($this->permissions === $permissions) {
            return;
        }

        $this->permissions = $permissions;
    }

    /**
     * Get permissions
     *
     * @return string
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}
