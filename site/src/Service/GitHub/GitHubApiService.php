<?php

namespace App\Service\GitHub;

use Github;
use Github\Client;
use Github\HttpClient\Builder;
use App\Entity\GitSourceRepo;
use App\Entity\Project;
use App\Entity\User;
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
class GitHubApiService
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
     * @var Client
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
     * This class only supports the \App\Entity\User
     * user object since it contains the necessary methods to work with Github
     * access tokens.
     *
     * All requests are cached in the given cache pool.
     *
     * @param AdapterInterface $cachePool the cache pool to use for all requests
     * @param RouterInterface  $router
     */
    public function __construct(
        AdapterInterface $cachePool,
        RouterInterface $router
    ) {
        $this->cachePool = $cachePool;
        $this->router = $router;
    }

    /**
     * Get a GitHub API client object
     *
     * If a valid user is available, an authenticated client object is returned.
     * See getAuthenticatedClient() for details what this means. Otherwise
     * an unauthenticated client is returned, allowing the application to make
     * anonymous API calls.
     *
     * @return Client a GitHub client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = $this->createClient();
            $this->client->addCache($this->cachePool);
        }

        return $this->client;
    }

    /**
     * Populate a Project with data obtained from the GitHub API
     *
     * @param Project $project
     * @param string  $owner
     * @param string  $repo
     * @param User    $user
     */
    public function populateProject(
        Project $project,
        string $owner,
        string $repo,
        User $user
    ) {
        $repo = $this->getAuthenticatedClientForUser($user)
            ->repo()->show($owner, $repo);

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
     * @return Client|NULL an authenticated GitHub client, or NULL
     */
    public function getAuthenticatedClientForUser(User $user)
    {
        $client = $this->createClient();
        $client->addCache($this->cachePool);

        // try to authenticate as user with its access token
        if (null !== $user && $user->isConnectedToOAuthService('github')) {
            $oauthAccessToken = $user->getGithubOAuthAccessToken();
            $client->authenticate(
                $oauthAccessToken,
                null,
                Client::AUTH_URL_TOKEN
            );

            return $client;
        }

        return null;
    }

    /**
     * Install a webhook on a GitHub repository for a given project
     *
     * @param Project $project
     * @param string  $owner
     * @param string  $repo
     * @param User    $user
     *
     * @throws Github\Exception\MissingArgumentException
     */
    public function installHook(Project $project, string $owner, string $repo, User $user)
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
            'events' => ['push'],
        ];

        $this->getAuthenticatedClientForUser($user)->repo()
            ->hooks()->create($owner, $repo, $params);
    }

    private function createClient(): Client
    {
        return new GithubClient();
    }
}
