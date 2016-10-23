<?php
namespace Librecores\ProjectRepoBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Librecores\ProjectRepoBundle\Security\Core\Exception\InsufficientOAuthDataProvidedException;
use Librecores\ProjectRepoBundle\Security\Core\Exception\OAuthUserExistsException;

/**
 * Custom user provider for HWIAuth
 *
 * This class takes the data received from the OAuth providers (like Github or
 * Google) and creates our own users with it.
 *
 * This class must be registered as service under the name
 * "librecores_user_provider" in
 * src/Librecores/ProjectRepoBundle/Resources/config/services.yml.
 * It is then used in the following two config files:
 * - app/config/config.yml, key hwi_oauth/connect/account_connector
 * - app/config/security.yml, key
 *   security/firewalls/main/oauth/oauth_user_provider/service
 *
 * See https://gist.github.com/danvbe/4476697,
 * http://praveesh4u.github.io/blog/2014/01/23/oauth-in-symfony/ and
 * http://www.osmialowski.co.uk/symfony-2-oauth-a-better-way-to-integrate-hwioauthbundle-with-fosuserbundle/
 * for more information.
 *
 * @author Barbus Sergiu <danvbe@gmail.com>
 * @author Philipp Wagner <mail@philipp-wagner.com>
 */
class FOSUBUserProvider extends BaseClass
{
    /**
     * Connect the account of the currently logged in LibreCores user with the
     * OAuth account.
     *
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();
        //on connect - get the access token and the user ID
        $service = $response->getResourceOwner()->getName();
        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';
        //we "disconnect" previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }
        //we connect current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        $this->userManager->updateUser($user);
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
     * See doc/usermanagement.md for a full design documentation.
     *
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        /*
         * NOTE:
         * username/nickname are extremely confusing named fields in OAuth
         * responses. Be careful when modifying this code!
         *
         * For the OAuth code below,
         * - username is the user ID assigned to the user by the OAuth provider.
         *   For GitHub, for example, this is a numerical ID.
         * - nickname is what would commonly be called "user name". It's a
         *   string with the user's login name.
         *
         * In FOSUserBundle, username and user id are used in the conventional
         * sense. The "OAuth username" is saved in the User.oAuthUserId field,
         * the "OAuth nickname" is mapped to the User.username field.
         */
        $user = $this->userManager->findUserByOAuth($response->getResourceOwner()->getName(), $response->getUsername());

        if (null === $user) {
            // register new user

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
            $user->setPassword('');
            $user->setEnabled(true);
            $user->setOAuthService($response->getResourceOwner()->getName());
            $user->setOAuthUserId($response->getUsername());
            $user->setOAuthAccessToken($response->getAccessToken());
            $this->userManager->updateUser($user);

            return $user;
        }

        // user already exists
        // XXX: Handle the case of changed username or email address
        $user->setOAuthAccessToken($response->getAccessToken());
        return $user;
    }
}
