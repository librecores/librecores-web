<?php
namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A contributor in a source code repository
 *
 * Most source code repositories store the name and email of the committer.
 *
 * @ORM\Table(uniqueConstraints={@UniqueConstraint(columns={"email", "repository_id"})})
 * @ORM\Entity(repositoryClass="Librecores\ProjectRepoBundle\Repository\ContributorRepository")
 */
class Contributor
{
    /**
     * Unique ID to identify this entity in the database
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The source code repository this contributor belongs to
     *
     * This entity belongs to a specific repository due to the fact that
     * a contributor may choose to have different names in different source
     * repositories.
     *
     * XXX: We can use this class to store cross repository statistics,
     * an elegant solution to the problem would be storing the name as the
     * repository dependent entity and email as independent.
     *
     * @var SourceRepo
     *
     * @ORM\ManyToOne(targetEntity="SourceRepo", inversedBy="contributors")
     */
    private $repository;

    /**
     * The name of the contributor
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * The email address of the contributor
     *
     * @var string
     *
     * @Assert\Email()
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * Commits by this contributor
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Librecores\ProjectRepoBundle\Entity\Commit", mappedBy="contributor", cascade={"persist"})
     */
    private $commits;

    /**
     * Constructor
     * @param null|string $name
     * @param null|string $email
     */
    public function __construct(?string $name = null, ?string $email = null)
    {
        $this->commits = new ArrayCollection();
        $this->name = $name;
        $this->email = $email;
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
     * Set repository
     *
     * @param SourceRepo $repository
     *
     * @return Contributor
     */
    public function setRepository(SourceRepo $repository = null)
    {
        $this->repository = $repository;
        $repository->addContributor($this);
        return $this;
    }

    /**
     * Get repository
     *
     * @return SourceRepo
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Contributor
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
     * Set email
     *
     * @param string $email
     * @return Contributor
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Add commit
     *
     * @param Commit $commit
     *
     * @return Contributor
     */
    public function addCommit(Commit $commit)
    {
        $this->commits[] = $commit;

        return $this;
    }

    /**
     * Remove commit
     *
     * @param Commit $commit
     */
    public function removeCommit(Commit $commit)
    {
        $this->commits->removeElement($commit);
    }

    /**
     * Get commits
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommits()
    {
        return $this->commits;
    }
}
