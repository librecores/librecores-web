<?php
namespace Librecores\ProjectRepoBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Librecores\ProjectRepoBundle\Security\Core\Exception\InsufficientOAuthDataProvidedException;
use Librecores\ProjectRepoBundle\Security\Core\Exception\OAuthUserExistsException;
use Librecores\ProjectRepoBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Custom user provider for HWIAuth
 *
 * This class takes the data received from the OAuth providers (like Github or
 * Google) and creates our own users with it or connects an existing user to
 * a OAuth service.
 *
 * This class is similar, but different than
 * HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider. Notably we add
 * support for storing the OAuth token and take more information from the OAuth
 * provider when creating an account on LibreCores.
 *
 *
 * NOTE
 * ----
 *
 * username/nickname are extremely confusing named fields in OAuth
 * responses. Be careful when modifying this code!
 *
 * For the OAuth code below,
 * - UserResponseInterface::username is the user ID assigned to the user by the
 *   OAuth provider. For GitHub, for example, this is a numerical ID.
 * - UserResponseInterface::nickname is what would commonly be called "user
 *   name". It's a string with the user's login name.
 *
 * In FOSUserBundle, username and user id are used in the conventional
 * sense. The "OAuth username" is saved in the User.oAuthUserId field,
 * the "OAuth nickname" is mapped to the User.username field.
 *
 * @author Philipp Wagner <mail@philipp-wagner.com>
 */
class LibreCoresUserProvider implements UserProviderInterface,
    AccountConnectorInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager the user manager
     */
    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
        $this->accessor    = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->userManager->findUserByUsername($username);
    }

    /**
     * Load a user object based on the received OAuth response.
     *
     * This can result in three possible actions:
     * - Transparently create a new user, if both username and email address
     *   are not yet used for a LibreCores user.
     * - Update an logged-in user to associate the local user with the OAuth
     *   provider.
     * - Reject the user, if either username or email address are already used
     *   by a local user.
     *
     * See the user management documentation for a full design documentation.
     *
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $oAuthUsername = $response->getUsername();
        $serviceName = $response->getResourceOwner()->getName();
        $user = $this->userManager->findUserByOAuth(
            $serviceName,
            $oAuthUsername);

        if ($user === null) {
            $this->registerNewUser($response);
        }

        // user already exists
        // update OAuth token in user object
        $this->accessor->setValue($user,
            $this->getAccessTokenProperty($serviceName),
            $response->getAccessToken());

        return $user;
    }

    /**
     * Connect the account of the currently logged in LibreCores user with the
     * OAuth account.
     *
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $oAuthUsername = $response->getUsername();
        $serviceName = $response->getResourceOwner()->getName();

        // Disconnect any other LibreCores account which is currently connected
        // to this OAuth account.
        $previousUser = $this->userManager->findUserByOAuth(
            $serviceName,
            $oAuthUsername);
        if ($previousUser !== null) {
            $this->disconnect($previousUser, $serviceName);
        }

        // Connect the OAuth account to the LibreCores user
        $this->accessor->setValue($user,
            $this->getAccessTokenProperty($serviceName),
            $response->getAccessToken());
        $this->accessor->setValue($user,
            $this->getUserIdProperty($serviceName),
            $response->getUsername());
        $this->userManager->updateUser($user);
    }

    /**
     * Disconnects an OAuth service from a user account
     *
     * @param UserInterface $user
     * @param string $serviceName
     */
    public function disconnect(UserInterface $user, $serviceName)
    {
        $this->accessor->setValue($user,
            $this->getAccessTokenProperty($serviceName),
            null);
        $this->accessor->setValue($user,
            $this->getUserIdProperty($serviceName),
            null);

        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $userId = $user->getId();
        $user = $this->userManager->findUserBy(['id' => $userId]);

        if ($user === null) {
            throw new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded.', $userId));
        }

        return $user;
    }

    /**
     * Register a new user with data from an OAuth response
     *
     * @param UserResponseInterface $response
     * @throws InsufficientOAuthDataProvidedException
     * @throws OAuthUserExistsException
     * @return User
     */
    private function registerNewUser(UserResponseInterface $response)
    {
        // check if all required data is provided
        if (!$response->getEmail() || !$response->getNickname()) {
            throw new InsufficientOAuthDataProvidedException();
        }

        // check if user with email address or username already exists
        $existingUser = $this->userManager->findUserByUsernameOrEmail2(
            $response->getNickname(), $response->getEmail());
        if (null !== $existingUser) {
            throw new OAuthUserExistsException();
        }

        // create new user account
        $user = $this->userManager->createUser();
        $user->setUsername($response->getNickname());
        $user->setEmail($response->getEmail());
        $user->setName($response->getRealName());
        $user->setPassword('');
        $user->setEnabled(true);
        $this->accessor->setValue($user,
            $this->getAccessTokenProperty($response),
            $response->getAccessToken());
        $this->accessor->setValue($user,
            $this->getUserIdProperty($response),
            $response->getUsername());
        $this->userManager->updateUser($user);

        return $user;
    }

    /**
     * Get the user id property for a given response
     *
     * @param string $serviceName
     * @return string
     */
    protected function getUserIdProperty(string $serviceName)
    {
        return strtolower($serviceName).'OAuthUserId';
    }

    /**
     * Get the access token property for a given response
     *
     * @param string $serviceName
     * @return string
     */
    protected function getAccessTokenProperty(string $serviceName)
    {
        return strtolower($serviceName).'OAuthAccessToken';
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Security\Core\User\UserProviderInterface::supportsClass()
     */
    public function supportsClass($class)
    {
        $userClass = $this->userManager->getClass();
        return $userClass === $class || is_subclass_of($class, $userClass);
    }
}
