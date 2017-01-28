<?php
namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
    const GROUP_REQUEST = 'REQUEST';
    const GROUP_DENY    = 'DENY';
    const GROUP_MEMBER  = 'MEMBER';
    const GROUP_ADMIN   = 'ADMIN';

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
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $organization;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="organizations")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $user;

    /**
     * @var string
     *
     * @Assert\Choice(choices = {"REQUEST", "DENY", "MEMBER", "ADMIN"})
     * @ORM\Column(type="string")
     */
    private $group = self::GROUP_REQUEST;

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
        if ($this->user !== null)
            $this->user->removeOrganizationMembership($this);

        if ($user !== null)
            $user->addOrganizationMembership($this);

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
        if ($this->organization !== null)
            $this->organization->removeOrganizationMember($this);

        if ($organization !== null)
            $organization->addOrganizationMember($this);

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
     * Set the group
     *
     * @param string $group one of the self::GROUP_* constants
     * @throws \InvalidArgumentException
     */
    public function setGroup($group)
    {
        if (!in_array($group, [self::GROUP_REQUEST,
                               self::GROUP_DENY,
                               self::GROUP_MEMBER,
                               self::GROUP_ADMIN], false))
            throw new \InvalidArgumentException('Invalid Group');

        if ($this->group === $group)
            return;

        $this->group = $group;
    }

    /**
     * Get group
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }
}
