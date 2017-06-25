<?php
namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A source code repository (base entity)
 *
 * This entity contains all general information regarding a source code
 * repository. The items specific to a certain type of SCM tool (like git or
 * subversion) are inside the entities which inherit from this one.
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"git" = "GitSourceRepo"})
 */
abstract class SourceRepo
{
    // keep in sync with the DiscriminatorMap values above!
    const REPO_TYPE_GIT = 'git';
    const REPO_TYPE_SVN = 'svn';

    /**
     * Unique ID to identify this entity in the database
     *
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * The URL to clone/checkout the repository.
     *
     * XXX: We don't add a Assert\Url validator here since we don't know
     * which protocols are supported by classes inheriting from this class.
     * For example, in GitSourceRepo, git:// URLs are valid. Once we figure out
     * a way to add validators in child classes, that should be added.
     *
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(max = 255)
     * @ORM\Column(type="string", length=255)
     */
    protected $url;

    /**
     * Project associated with this source repository
     *
     * @var Project
     *
     * @ORM\OneToOne(targetEntity="Project", mappedBy="sourceRepo", cascade={"persist"})
     */
    protected $project;

    /**
     * URL of the web site where the repository contents can be viewed with a
     * regular web browser
     *
     * @var string?
     *
     * @Assert\Url()
     * @Assert\Length(max = 255)
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $webViewUrl = null;

    /**
     * Contributors of this source repository
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Contributor", mappedBy="repository", cascade={"persist", "remove"},
     *                orphanRemoval=true)
     */
    protected $contributors;

    /**
     * Commits of this source repository
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Commit", mappedBy="repository", cascade={"persist", "remove"},
     *                orphanRemoval=true)
     */
    protected $commits;

    /**
     * Statistics about the source code of this repository
     * @var SourceStats
     *
     * @ORM\Embedded(class="SourceStats", columnPrefix="source_stats_")
     */
    protected $sourceStats;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contributors = new ArrayCollection();
        $this->commits = new ArrayCollection();
        $this->sourceStats = new SourceStats();
    }

    /**
     * Get the type of source repository
     *
     * Doctrine unfortunately provides no easy way to access the discriminator
     * value as string to use it inside templates.
     * For PHP code, use something like |$var instanceof GitSourceRepo| instead.
     *
     * @return string one of the REPO_TYPE_* constants
     */
    abstract public function getType();

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
     * Set url
     *
     * @param string $url
     * @return SourceRepo
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set project
     *
     * @param Project $project
     * @return SourceRepo
     */
    public function setProject(Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set webViewUrl
     *
     * @param string $webViewUrl
     *
     * @return SourceRepo
     */
    public function setWebViewUrl($webViewUrl)
    {
        $this->webViewUrl = $webViewUrl;

        return $this;
    }

    /**
     * Get webViewUrl
     *
     * @return string
     */
    public function getWebViewUrl()
    {
        return $this->webViewUrl;
    }

    /**
     * Add contributor
     *
     * @param Contributor $contributor
     *
     * @return SourceRepo
     */
    public function addContributor(Contributor $contributor)
    {
        $this->contributors[] = $contributor;

        return $this;
    }

    /**
     * Remove contributor
     *
     * @param Contributor $contributor
     */
    public function removeContributor(Contributor $contributor)
    {
        $this->contributors->removeElement($contributor);
    }

    /**
     * Get contributors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContributors()
    {
        return $this->contributors;
    }

    /**
     * Add commit
     *
     * @param Commit $commit
     *
     * @return SourceRepo
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
     * Set sourceStats
     *
     * @param SourceStats $sourceStats
     *
     * @return SourceRepo
     */
    public function setSourceStats(SourceStats $sourceStats)
    {
        $this->sourceStats = $sourceStats;

        return $this;
    }

    /**
     * Get sourceStats
     *
     * @return SourceStats
     */
    public function getSourceStats()
    {
        return $this->sourceStats;
    }
}
