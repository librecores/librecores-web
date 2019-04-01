<?php

namespace Librecores\ProjectRepoBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
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
    const PERMISSION_REQUEST = 'REQUEST';
    const PERMISSION_DENY = 'DENY';
    const PERMISSION_SUPPORT = 'SUPPORT';
    const PERMISSION_MEMBER = 'MEMBER';
    const PERMISSION_ADMIN = 'ADMIN';
    /**
     * @var Organization
     *
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
     *
     * @ORM\Column(type="string")
     */
    protected $permission = self::PERMISSION_REQUEST;
    /**
     * When was this mapping created?
     *
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    // metadata
    /**
     * When was this mapping last updated?
     *
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
     * Set user
     *
     * @param User $user
     *
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
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     *
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
     * Get permission
     *
     * @return string
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Set permission
     *
     * @param string $permission One of the self::PERMISSION_* constants
     *
     * @throws InvalidArgumentException
     */
    public function setPermission($permission)
    {
        $permissions = [
            self::PERMISSION_REQUEST,
            self::PERMISSION_DENY,
            self::PERMISSION_SUPPORT,
            self::PERMISSION_MEMBER,
            self::PERMISSION_ADMIN,
        ];
        if (!in_array($permission, $permissions, false)) {
            throw new InvalidArgumentException('Invalid Permission');
        }

        if ($this->permission === $permission) {
            return;
        }

        $this->permission = $permission;
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
     * @return OrganizationMember
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
     * @return OrganizationMember
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
