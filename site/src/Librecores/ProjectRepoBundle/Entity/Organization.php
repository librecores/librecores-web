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
     * @ORM\OneToMany(targetEntity="Project", mappedBy="parentOrganization")
     * @ORM\JoinColumn(name="projectId", referencedColumnName="id")
     */
    private $projects;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="organizationsOwner")
     * @ORM\JoinColumn(name="ownerId", referencedColumnName="id")
     **/
    protected $owner;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="User", inversedBy="organizationsMember")
     * @ORM\JoinTable(name="OrganizationMembers",
     *     joinColumns={@ORM\JoinColumn(name="organizationId", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="memberId", referencedColumnName="id")}
     *     )
     **/
    protected $members;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="User", inversedBy="organizationsRequest")
     * @ORM\JoinTable(name="OrganizationRequests",
     *     joinColumns={@ORM\JoinColumn(name="organizationId", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="requestId", referencedColumnName="id")}
     *     )
     **/
    protected $requests;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->members  = new ArrayCollection();
        $this->requests = new ArrayCollection();
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
     * Add projects
     *
     * @param Project $projects
     * @return Organization
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
     * Set owner
     *
     * @param User $owner
     * @return Organization
     */
    public function setOwner(User $owner = null)
    {
        if ($this->owner !== null)
            $this->owner->removeOrganizationsOwner($this);

        if ($owner !== null)
            $owner->addOrganizationsOwner($this);

        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Add member
     *
     * @param User $member
     * @return Organization
     */
    public function addMember(User $member)
    {
        if ($member !== null)
            $member->addOrganizationsMember($this);

        if (!$this->members->contains($member))
            $this->members[] = $member;

        return $this;
    }

    /**
     * Remove member
     *
     * @param User $member
     */
    public function removeMember(User $member)
    {
        if ($member !== null)
            $member->removeOrganizationsMember($this);

        if ($this->members->contains($member))
            $this->members->removeElement($member);
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
     * Add request
     *
     * @param User $request
     * @return Organization
     */
    public function addRequest(User $request)
    {
        if ($request !== null)
            $request->addOrganizationsRequest($this);

        if (!$this->requests->contains($request))
            $this->requests[] = $request;

        return $this;
    }

    /**
     * Remove request
     *
     * @param User $request
     */
    public function removeRequest(User $request)
    {
        if ($request !== null)
            $request->removeOrganizationsRequest($this);

        if ($this->requests->contains($request))
            $this->requests->removeElement($request);
    }

    /**
     * Get requests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRequests()
    {
        return $this->requests;
    }
}
