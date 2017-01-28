<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Librecores\ProjectRepoBundle\Validator\Constraints as LcAssert;

/**
 * An organization
 *
 * An organization is a collection of projects. It usually represents a group
 * of developers or an actual organization, which work together on a set of
 * projects.
 *
 * @ORM\Table("Organization")
 * @ORM\Entity(repositoryClass="Librecores\ProjectRepoBundle\Entity\OrganizationRepository")
 * @ORM\HasLifecycleCallbacks
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
     * @LcAssert\UserOrgName
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Project", mappedBy="parentOrganization")
     * @ORM\JoinColumn(name="projectId", referencedColumnName="id")
     */
    private $projects;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="OrganizationMember", mappedBy="organization", cascade={"remove"}))
     **/
    protected $organizationMembers;

    /**
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
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * When was this organization last updated?
     *
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->projects             = new ArrayCollection();
        $this->organizationMembers  = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
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
     * @return Organization
     */
    public function addProject(Project $project)
    {
        if (!$this->projects->contains($project))
            $this->projects[] = $project;

        return $this;
    }

    /**
     * Remove project
     *
     * @param Project $project
     */
    public function removeProject(Project $project)
    {
        if ($this->projects->contains($project))
            $this->projects->removeElement($project);
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
     * Add organization member
     *
     * @param OrganizationMember $organizationMember
     * @return Organization
     */
    public function addOrganizationMember(OrganizationMember $organizationMember)
    {
        if (!$this->organizationMembers->contains($organizationMember))
            $this->organizationMembers[] = $organizationMember;

        return $this;
    }

    /**
     * Remove organization member
     *
     * @param OrganizationMember $organizationMember
     */
    public function removeOrganizationMember(OrganizationMember $organizationMember)
    {
        if ($this->organizationMembers->contains($organizationMember))
            $this->organizationMembers->removeElement($organizationMember);
    }

    /**
     * Get organization members
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizationMembers()
    {
        return $this->organizationMembers;
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return array_map(
            function (OrganizationMember $organizationMember) {
                return $organizationMember->getUser();
            },
            $this->organizationMembers->toArray()
        );
    }

    /**
     * Set creator
     *
     * @param User $creator
     * @return Organization
     */
    public function setCreator(User $creator)
    {
        if ($this->creator !== null)
            $this->creator->removeOrganizationCreated($this);

        if ($creator !== null)
            $creator->addOrganizationCreated($this);

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
}
