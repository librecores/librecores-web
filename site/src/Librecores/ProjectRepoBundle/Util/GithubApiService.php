<?php
namespace Librecores\ProjectRepoBundle\Util;

use Librecores\ProjectRepoBundle\Entity\User;
use Librecores\ProjectRepoBundle\Entity\Project;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Github;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Wrap the KnpLabs/php-github-api GitHub API as Symfony service
 *
 * This service wrapper gives access to the GitHub API client as documented
 * at https://github.com/KnpLabs/php-github-api/tree/master/doc. Instead of
 * constructing the client manually, the getClient() and
 * getAuthenticatedClient() methods of this class initialize the object for our
 * use case, including authenticating with a user's GitHub OAuth access token.
 *
 * All requests are cached if a cache pool is given.
 */
class GithubApiService
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var AdapterInterface
     */
    protected $cachePool;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * GitHub client API wrapper
     *
     * @var Github\Client
     */
    private $client = null;

    /**
     * Is $client authenticated?
     *
     * @var bool
     */
    private $clientIsAuthenticated = false;


    /**
     * Constructor
     *
     * Set the user object from the token storage, as injected by Symfony.
     * This class only supports the \Librecores\ProjectRepoBundle\Entity\User
     * user object since it contains the necessary methods to work with Github
     * access tokens.
     *
     * All requests are cached in the given cache pool.
     *
     * @param TokenStorageInterface $tokenStorage token storage service
     * @param AdapterInterface      $cachePool    the cache pool to use for all requests
     * @param RouterInterface       $router
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AdapterInterface $cachePool,
        RouterInterface $router
    ) {
        $token = $tokenStorage->getToken();
        if (null !== $token) {
            $this->user = $token->getUser();
        }

        $this->cachePool = $cachePool;
        $this->router = $router;
    }

    /**
     * Get a (possibly unauthenticated) GitHub API client object
     *
     * If a valid user is available, an authenticated client object is returned.
     * See getAuthenticatedClient() for details what this means. Otherwise
     * an unauthenticated client is returned, allowing the application to make
     * anonymous API calls.
     *
     * @return Github\Client a GitHub client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->initClient();
        }

        return $this->client;
    }

    /**
     * Get an authenticated GitHub API client which acts on behalf
     * of the current user
     *
     * @return Github\Client|NULL an authenticated GitHub client, or NULL
     */
    public function getAuthenticatedClient()
    {
        if (!$this->client) {
            $this->initClient();
        }

        if ($this->clientIsAuthenticated) {
            return $this->client;
        }

        return null;
    }

    /**
     * Get an authenticated GitHub API client for a user
     *
     * The client acts on behalf of the user, and has permissions to
     * all resources the user has, limited by the granted scope.
     * The default requested scope is configured in config.yml.
     * If no authenticated client can be returned (e.g. because no user
     * information is available, or the user has not connected their GitHub
     * account), NULL is returned.
     *
     * @param User $user
     *
     * @return Github\Client|NULL an authenticated GitHub client, or NULL
     */
    public function getAuthenticatedClientForUser(User $user)
    {
        $client = new Github\Client();
        $client->addCache($this->cachePool);

        // try to authenticate as user with its access token
        if (null !== $user && $user->isConnectedToOAuthService('github')) {
            $oauthAccessToken = $user->getGithubOAuthAccessToken();
            $client->authenticate(
                $oauthAccessToken,
                null,
                Github\Client::AUTH_URL_TOKEN
            );

            return $client;
        }

        return null;
    }


    /**
     * Populate a Project with data obtained from the GitHub API
     *
     * @param Project $project
     * @param string  $owner
     * @param string  $repo
     */
    public function populateProject(
        Project $project,
        string $owner,
        string $repo
    ) {
        $repo = $this->getClient()->repo()->show($owner, $repo);

        if ($repo['has_issues']) {
            // the web URL for issues it not part of the API response
            $project->setIssueTracker($repo['html_url'].'/issues');
        }

        if ($repo['homepage']) {
            $project->setProjectUrl($repo['homepage']);
        } else {
            $project->setProjectUrl($repo['html_url']);
        }

        $project->setTagline($repo['description']);

        $sourceRepo = new GitSourceRepo($repo['clone_url']);
        $sourceRepo->setWebViewUrl($repo['html_url']);
        $project->setSourceRepo($sourceRepo);
    }

    /**
     * Install a webhook on a GitHub repository for a given project
     *
     * @param Project $project
     * @param string  $owner
     * @param string  $repo
     */
    public function installHook(Project $project, string $owner, string $repo)
    {
        $webhookUrl = $this->router->generate(
            'librecores_project_repo_project_update',
            [
                'parentName' => $project->getParentName(),
                'projectName' => $project->getName(),
            ],
            RouterInterface::ABSOLUTE_URL
        );
        // see https://developer.github.com/v3/repos/hooks/#create-a-hook for
        // a parameter documentation
        $params = [
            'name' => 'web',
            'config' => [
                'url' => $webhookUrl,
            ],
            'events' => [ 'push' ],
        ];

        $this->getAuthenticatedClient()->repo()
            ->hooks()->create($owner, $repo, $params);
    }

    /**
     * Initialize the client
     *
     * @return boolean
     */
    protected function initClient()
    {
        $this->client = $this->getAuthenticatedClientForUser($this->user);

        if (null !== $this->client) {
            $this->clientIsAuthenticated = true;
        } else {
            $this->client = new Github\Client();
            $this->client->addCache($this->cachePool);
            $this->clientIsAuthenticated = false;
        }

        return true;
    }
}
