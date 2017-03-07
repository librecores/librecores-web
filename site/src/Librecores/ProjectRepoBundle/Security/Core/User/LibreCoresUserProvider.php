<?php
namespace Librecores\ProjectRepoBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Librecores\ProjectRepoBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Validator\ValidatorInterface;
use Librecores\ProjectRepoBundle\Security\Core\Exception\OAuthUserLinkingException;
use Symfony\Component\HttpFoundation\Session\Session;

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
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager the user manager
     */
    public function __construct(UserManagerInterface $userManager, ValidatorInterface $validator, Session $session)
    {
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->session = $session;
        $this->accessor = PropertyAccess::createPropertyAccessor();
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
        $oAuthUserId = $response->getUsername();
        $serviceName = $response->getResourceOwner()->getName();
        $user = $this->userManager->findUserByOAuth(
            $serviceName,
            $oAuthUserId);

        if ($user === null) {
            $user = $this->registerNewUser($response);
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
        $oAuthUserId = $response->getUsername();
        $serviceName = $response->getResourceOwner()->getName();

        // Disconnect any other LibreCores account which is currently connected
        // to this OAuth account.
        $previousUser = $this->userManager->findUserByOAuth(
            $serviceName,
            $oAuthUserId);
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
     * Disconnect an OAuth service from a user account
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
     * The new user uses the username and email address provided from OAuth.
     *
     * OAuth-created users have a random (unknown) password set, as they usually
     * log in through OAuth where no password is needed. If they want to log in
     * directly on LibreCores, the password reset mechanism can be used to
     * create a password the user actually knows.
     *
     * The User entity is validated before saving, and if the validation fails
     * (e.g. because the user already exists, or the user name does not match
     * our guidelines) an OAuthUserLinkingException is thrown. This exception
     * leads to a redirect to the registration page, where the user can manually
     * register an account. The manually registered account is then
     * connected to the OAuth account the user tried to use when calling this
     * method. In addition, a flash message indicates why the auto-registration
     * failed.
     *
     * @param UserResponseInterface $response
     * @throws OAuthUserLinkingException
     * @return User
     *
     * @see OAuthRegistrationListener
     */
    private function registerNewUser(UserResponseInterface $response)
    {
        $serviceName = $response->getResourceOwner()->getName();

        // try to create a new user
        $user = $this->userManager->createUser();
        $user->setUsername($response->getNickname());
        $user->setEmail($response->getEmail());
        $user->setName($response->getRealName());
        // create a random default password
        $user->setPlainPassword(base64_encode(random_bytes(50)));
        $user->setEnabled(true);
        $this->accessor->setValue($user,
            $this->getAccessTokenProperty($serviceName),
            $response->getAccessToken());
        $this->accessor->setValue($user,
            $this->getUserIdProperty($serviceName),
            $response->getUsername());

        // validate newly created user entity
        $errors = $this->validator->validate($user, array('Registration'));

        // the auto-created user is not valid
        // fall back to our failure path: redirect the user to the registration
        // form, and after the user has completed this form we add back the
        // OAuth credentials so that the newly registered account is connected
        // to the OAuth service automatically.
        if (count($errors) > 0) {
            // Create a HTML-version of the validation errors for the user
            $errorMsgs = '<ul>';
            foreach ($errors as $error) {
                $errorMsgs .= '<li>'.$error->getMessage().'</li>';
            }
            $errorMsgs .= '</ul>';
            // the flash message is shown on top of the registration form
            // to which we forward, informing the user about what went wrong
            $this->session->getFlashBag()->add('warning',
                '<p>We were unable to create a new account for you '.
                'automatically. '.
                'Please register on LibreCores first, you can then log in'.
                'through '.ucfirst($serviceName).' as you just tried to.</p>'.
                '<p>We encountered the following problems: </p>'.$errorMsgs);

            // Throwing this exception effectively forwards the user to the
            // registration form, which has event handlers attached which read
            // the oAuthData created below.
            // See OAuthRegistrationListener if you make changes to this code!
            $e = new OAuthUserLinkingException((string)$errors);
            $oAuthData = [];
            $oAuthData['oAuthServiceName'] = $serviceName;
            $oAuthData['oAuthUserId'] = $response->getUsername();
            $oAuthData['oAuthAccessToken'] = $response->getAccessToken();
            $oAuthData['username'] = $response->getNickname();
            $oAuthData['email'] = $response->getEmail();
            $oAuthData['name'] = $response->getRealName();
            $e->setOAuthData($oAuthData);
            throw $e;
        }

        // if User is valid, save and return
        $this->userManager->updateUser($user);
        return $user;
    }

    /**
     * Get the user id property for a given response
     *
     * @param string $serviceName
     * @return string
     */
    public function getUserIdProperty(string $serviceName)
    {
        return strtolower($serviceName).'OAuthUserId';
    }

    /**
     * Get the access token property for a given response
     *
     * @param string $serviceName
     * @return string
     */
    public function getAccessTokenProperty(string $serviceName)
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
