<?php
namespace Librecores\ProjectRepoBundle\Util;

use Librecores\ProjectRepoBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Github;
use Symfony\Component\Cache\Adapter\AdapterInterface;

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
     * GitHub client API wrapper
     *
     * @var \Github\Client
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
     * @param AdapterInterface $cachePool the cache pool to use for all requests
     */
    public function __construct(TokenStorageInterface $tokenStorage,
                                AdapterInterface $cachePool)
    {
        $user = $tokenStorage->getToken()->getUser();
        if (!$user instanceof \Librecores\ProjectRepoBundle\Entity\User) {
            $this->user = null;
        }
        $this->user = $user;
        $this->cachePool = $cachePool;
    }

    /**
     * Get a (possibly unauthenticated) GitHub API client object
     *
     * If a valid user is available, an authenticated client object is returned.
     * See getAuthenticatedClient() for details what this means. Otherwise
     * an unauthenticated client is returned, allowing the application to make
     * anonymous API calls.
     *
     * @return \Github\Client a GitHub client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->initClient();
        }

        return $this->client;
    }

    /**
     * Get an authenticated GitHub API client
     *
     * The client acts on behalf of the current user, and has permissions to
     * all resources the user has, limited by the granted scope.
     * The default requested scope is configured in config.yml.
     * If no authenticated client can be returned (e.g. because no user
     * information is available, or the user has not connected his/her GitHub
     * account), NULL is returned.
     *
     * @return \Github\Client|NULL an authenticated GitHub client, or NULL
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
     * Initialize the client
     *
     * @return boolean
     */
    protected function initClient()
    {
        $this->client= new \Github\Client();
        $this->client->addCache($this->cachePool);
        $this->clientIsAuthenticated = false;

        // try to authenticate as user with its access token
        if ($this->user->isConnectedToOAuthService('github')) {
            $oauthAccessToken = $this->user->getGithubOAuthAccessToken();
            $this->client->authenticate($oauthAccessToken, null,
                                        Github\Client::AUTH_URL_TOKEN);

            $this->clientIsAuthenticated = true;
        }

        return true;
    }
}
