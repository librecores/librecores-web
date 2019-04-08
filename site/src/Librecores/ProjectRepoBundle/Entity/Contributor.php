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
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 *
 * @ORM\Table(uniqueConstraints={@UniqueConstraint(columns={"email", "sourceRepo_id"})})
 * @ORM\Entity
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
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $sourceRepo;

    /**
     * The name of the contributor
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * The email address of the contributor
     *
     * @var string
     *
     * @Assert\Email()
     *
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * Commits by this contributor
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Commit", mappedBy="contributor",
     *      cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $commits;

    /**
     * Constructor
     *
     * @param null|string $name
     * @param null|string $email
     */
    public function __construct($name = null, $email = null)
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
     * Get repository
     *
     * @return SourceRepo
     */
    public function getSourceRepo()
    {
        return $this->sourceRepo;
    }

    /**
     * Set repository
     *
     * @param SourceRepo $sourceRepo
     *
     * @return Contributor
     */
    public function setSourceRepo(SourceRepo $sourceRepo = null)
    {
        $this->sourceRepo = $sourceRepo;
        $sourceRepo->addContributor($this);

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
     * @return Contributor
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * Set email
     *
     * @param string $email
     *
     * @return Contributor
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
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
     * @return Collection
     */
    public function getCommits()
    {
        return $this->commits;
    }

    /**
     * Get avatar
     *
     * Uses the Gravataar service to provide user avatars
     *
     * @return string URL to contributor avatar
     */
    public function getAvatar(): string
    {
        // https://en.gravatar.com/site/implement/images/php/
        // We use 32x32 px images and a 8 bit retro image as fallback
        // similar to Github
        return 'https://www.gravatar.com/avatar/'
            .md5(strtolower(trim($this->email)))
            .'?s=32&d=retro';
    }
}
