<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Librecores\ProjectRepoBundle\Validator\Constraints as LcAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An organization
 *
 * An organization is a collection of projects. It usually represents a group
 * of developers or an actual organization, which work together on a set of
 * projects.
 *
 * @ORM\Table("Organization")
 * @ORM\Entity(repositoryClass="Librecores\ProjectRepoBundle\Repository\OrganizationRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="The organization already exists, please try a different name."
 * )
 */
class Organization
{
    const SPECIAL_UNASSIGNED_ID = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The short name of the organization (used e.g. for the URL fragment)
     *
     * @var string
     *
     * @LcAssert\UserOrgName(payload = {"type" = "org"})
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * The "human readable" name of the organization (used for display purposes)
     *
     * @var string
     *
     * @Assert\Length(max = 255)
     *
     * @ORM\Column(name="displayName", type="string", length=255)
     */
    private $displayName;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * The projects owned by this organization
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Project", mappedBy="parentOrganization")
     * @ORM\JoinColumn(name="projectId", referencedColumnName="id")
     */
    private $projects;

    /**
     * Members of this organization
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="OrganizationMember", mappedBy="organization", cascade={"remove"}))
     **/
    private $members;

    /**
     * Initial creator of the organization
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="organizationsCreated")
     * @ORM\JoinColumn(name="creatorId", referencedColumnName="id")
     */
    private $creator;

    // metadata
    /**
     * When was this organization created?
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * When was this organization last updated?
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $updatedAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->members  = new ArrayCollection();
    }

    /**
     * Hook on the first storing of this object
     *
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Hook before each update
     *
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
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
     * Set name
     *
     * @param string $name
     *
     * @return Organization
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
     * Set displayName
     *
     * @param string $displayName
     *
     * @return Organization
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
     * Set description
     *
     * @param string $description
     *
     * @return Organization
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add project
     *
     * @param Project $project
     *
     * @return Organization
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add member
     *
     * @param OrganizationMember $member
     *
     * @return Organization
     */
    public function addMember(OrganizationMember $member)
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
        }

        return $this;
    }

    /**
     * Remove member
     *
     * @param OrganizationMember $member
     */
    public function removeMember(OrganizationMember $member)
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);
        }
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Get users mapped through organization memberships
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMemberUsers()
    {
        return array_map(
            function (OrganizationMember $member) {
                return $member->getUser();
            },
            $this->members->toArray()
        );
    }

    /**
     * Set creator
     *
     * @param User $creator
     *
     * @return Organization
     */
    public function setCreator(User $creator)
    {
        if ($this->creator !== null) {
            $this->creator->removeOrganizationCreated($this);
        }

        if ($creator !== null) {
            $creator->addOrganizationCreated($this);
        }

        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Organization
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
     *
     * @return Organization
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
}
