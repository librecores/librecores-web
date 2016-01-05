<?php
namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * A user
 *
 * A user is an individual who can own projects.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class User extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    // OAuth-related entries
    /**
     * Name of the OAuth service provider
     *
     * @var string $oAuthService
     *
     * @ORM\Column(name="oauth_service", type="string", length=255, nullable=true)
     */
    protected $oAuthService;

    /**
     * User ID provided by the OAuth service.
     *
     * @var string $oAuthId
     *
     * @ORM\Column(name="oauth_user_id", type="string", length=255, nullable=true)
     */
    protected $oAuthUserId;

    /**
     * OAuth access token.
     *
     * With this token, authenticated requests to the OAuth API can be
     * performed.
     *
     * @var string $oAuthAccessToken
     *
     * @ORM\Column(name="oauth_access_token", type="string", length=255, nullable=true)
     */
    protected $oAuthAccessToken;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Project", mappedBy="parentUser")
     */
    protected $projects;


    public function __construct()
    {
        parent::__construct();
        $this->projects = new ArrayCollection();
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
     * Add projects
     *
     * @param \Librecores\ProjectRepoBundle\Entity\Project $projects
     * @return User
     */
    public function addProject(\Librecores\ProjectRepoBundle\Entity\Project $projects)
    {
        $this->projects[] = $projects;

        return $this;
    }

    /**
     * Remove projects
     *
     * @param \Librecores\ProjectRepoBundle\Entity\Project $projects
     */
    public function removeProject(\Librecores\ProjectRepoBundle\Entity\Project $projects)
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
     * Set oAuthService
     *
     * @param string $oAuthService
     * @return User
     */
    public function setOAuthService($oAuthService)
    {
        $this->oAuthService = $oAuthService;

        return $this;
    }

    /**
     * Get oAuthService
     *
     * @return string
     */
    public function getOAuthService()
    {
        return $this->oAuthService;
    }

    /**
     * Set oAuthUserId
     *
     * @param string $oAuthUserId
     * @return User
     */
    public function setOAuthUserId($oAuthUserId)
    {
        $this->oAuthUserId = $oAuthUserId;

        return $this;
    }

    /**
     * Get oAuthUserId
     *
     * @return string
     */
    public function getOAuthUserId()
    {
        return $this->oAuthUserId;
    }

    /**
     * Set oAuthAccessToken
     *
     * @param string $oAuthAccessToken
     * @return User
     */
    public function setOAuthAccessToken($oAuthAccessToken)
    {
        $this->oAuthAccessToken = $oAuthAccessToken;

        return $this;
    }

    /**
     * Get oAuthAccessToken
     *
     * @return string
     */
    public function getOAuthAccessToken()
    {
        return $this->oAuthAccessToken;
    }
}
