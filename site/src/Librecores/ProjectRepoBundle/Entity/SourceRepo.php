<?php
namespace Librecores\ProjectRepoBundle\Entity;

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
     * @var string
     *
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
     * @param \Librecores\ProjectRepoBundle\Entity\Project $project
     * @return SourceRepo
     */
    public function setProject(\Librecores\ProjectRepoBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \Librecores\ProjectRepoBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
