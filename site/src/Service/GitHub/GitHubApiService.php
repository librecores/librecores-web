<?php

namespace App\Service\GitHub;

use App\Entity\GitSourceRepo;
use App\Entity\Project;
use App\Entity\User;
use Github\Client;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Routing\RouterInterface;
use Psr\Log\LoggerInterface;

/**
 * Wrap the KnpLabs/php-github-api GitHub API as Symfony service
 *
 * This service wrapper gives access to the GitHub API client as documented
 * at https://github.com/KnpLabs/php-github-api/tree/master/doc. Instead of
 * constructing the client manually, the getClientForUser() and
 * getAuthenticatedClientForUser() methods of this class initialize the object
 * for our use case, including authenticating with a user's GitHub OAuth access
 * token.
 *
 * All requests are cached if a cache pool is given.
 */
class GitHubApiService
{
    private const GH_REGEX =
        '/^https:\/\/github\.com\/([^\/]+)\/([^\/]+?)(?:\.git)?$/';

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
     * @var LoggerInterface
     */
    protected $logger;


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
     * @param LoggerInterface  $logger
     */
    public function __construct(
        AdapterInterface $cachePool,
        RouterInterface $router,
        LoggerInterface $logger
    ) {
        $this->cachePool = $cachePool;
        $this->router = $router;
        $this->logger = $logger;
    }

    /**
     * Checks whether the supplied repository URL is a GitHub URL
     *
     * @param string $url
     *
     * @return bool
     */
    public static function isGitHubRepoUrl(string $url): bool
    {
        return preg_match(self::GH_REGEX, $url);
    }

    /**
     * Parse a GitHub URL into username and repository name
     *
     * @param string $url
     *
     * @return string[]
     */
    public static function parseGitHubRepoUrl(string $url): array
    {
        $matches = [];
        if (preg_match(self::GH_REGEX, $url, $matches)) {
            return [
                'user' => $matches[1],
                'repository' => $matches[2],
            ];
        }
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
     * Get a GitHub API client object
     *
     * If a valid user is available, an authenticated client object is returned.
     * See getAuthenticatedClientForUser() for details what this means.
     * Otherwise an unauthenticated client is returned, allowing the application
     * to make anonymous API calls.
     *
     * @return Client a GitHub client
     */
    public function getClientForUser(User $user) : Client
    {
        $client = $this->createClient();
        $client->addCache($this->cachePool);

        // Try to authenticate as user, but ignore failures.
        if (!$this->authenticateClientAsUser($user, $client)) {
            $this->logger->debug("Unable to authenticate to GitHub API as user.");

            if (!$this->authenticateClientAsApplication($client)) {
                $this->logger->error("Unable to authenticate to GitHub API as application.");
                // Failure isn't fatal, but only decreases our rate limit. But
                // failing still indicates something is wrong with the OAuth
                // tokens, which should be diagnosed and fixed.
            }
        }

        return $client;
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
    public function getAuthenticatedClientForUser(User $user) : ?Client
    {
        $this->logger->debug("Trying to get authenticated GitHub client for ".
                             "user \"{$user->getUsername()}\".");
        $client = $this->createClient();
        $client->addCache($this->cachePool);
        if ($this->authenticateClientAsUser($user, $client)) {
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

    /**
     * Try to authenticate as user with its access token
     *
     * @return bool success?
     */
    private function authenticateClientAsUser(User $user, Client $client) : bool
    {
        if ($user === null || !$user->isConnectedToOAuthService('github')) {
            return false;
        }

        $oauthAccessToken = $user->getGithubOAuthAccessToken();
        $client->authenticate(
            $oauthAccessToken,
            null,
            Client::AUTH_HTTP_TOKEN
        );

        return true;
    }

    /**
     * Authenticate to GitHub as application
     *
     * This authentication doesn't make the GitHub client "authenticated", but
     * increases the rate limit.
     *
     * See https://developer.github.com/v3/#increasing-the-unauthenticated-rate-limit-for-oauth-applications
     * for details.
     *
     * @param Client $client the client to authenticate
     * @return bool successful?
     */
    private function authenticateClientAsApplication(Client $client) : bool
    {
        // TODO: Implement this. Use github_site_id/github_site_secret from
        // configuration.
        // https://github.com/librecores/librecores-web/issues/447
        return true;
    }
}
