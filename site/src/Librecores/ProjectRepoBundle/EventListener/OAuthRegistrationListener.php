<?php

namespace Librecores\ProjectRepoBundle\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Librecores\ProjectRepoBundle\Security\Core\User\LibreCoresUserProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Security;

/**
 * Event listener: Insert data from failed OAuth auto-registration into manual
 * registration
 */
class OAuthRegistrationListener implements EventSubscriberInterface
{
    const FAILED_OAUTH_DATA = '_librecores.oauthregistrationlistener.failed_oauth_data';

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var LibreCoresUserProvider
     */
    protected $userProvider;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    public function __construct(
        UserManagerInterface $userManager,
        LibreCoresUserProvider $userProvider,
        Session $session
    ) {
        $this->userManager = $userManager;
        $this->userProvider = $userProvider;
        $this->session = $session;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_INITIALIZE => 'onRegistrationInitialize',
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        );
    }

    /**
     * Event handler: the registration form was initialized
     *
     * @param GetResponseUserEvent $event
     *
     * @return void
     */
    public function onRegistrationInitialize(GetResponseUserEvent $event)
    {
        $oAuthData = $this->getFailedOAuthData();
        if (!$oAuthData) {
            return;
        }

        $user = $event->getUser();

        $user->setUsername($oAuthData['username']);
        $user->setEmail($oAuthData['email']);
    }

    /**
     * Event handler: the registration form was successfully submitted
     *
     * This event is triggered after the user has completed the registration
     * form. In this handler we connect the user to the OAuth service he/she
     * tried to use during the auto-registration. After this step, logging in
     * through the OAuth provider is possible.
     *
     * @param FormEvent $event
     *
     * @return NULL
     */
    public function onRegistrationSuccess(FormEvent $event)
    {
        $oAuthData = $this->getFailedOAuthData();
        if (!$oAuthData) {
            return null;
        }

        /** @var $user \Librecores\ProjectRepoBundle\Entity\User */
        $user = $event->getForm()->getData();

        // Connect the OAuth account to the LibreCores user
        $this->accessor->setValue(
            $user,
            $this->userProvider->getAccessTokenProperty($oAuthData['oAuthServiceName']),
            $oAuthData['oAuthAccessToken']
        );
        $this->accessor->setValue(
            $user,
            $this->userProvider->getUserIdProperty($oAuthData['oAuthServiceName']),
            $oAuthData['oAuthUserId']
        );

        // set the real name of the user if it was provided by the OAuth service
        if (empty($user->getName()) && $oAuthData['name']) {
            $user->setName($oAuthData['name']);
        }

        $this->userManager->updateUser($user);

        $this->clearFailedOAuthSession();
    }

    /**
     * Get the "leftover" data from the failed OAuth auto-registration
     *
     * This is the data we tried to auto-created an user with in
     * LibreCoresUserProvider but failed to do so. This data is used in this
     * class to pre-fill and extend the user-entered data.
     *
     * @return string[] the data array prepared by LibreCoresUserProvider::registerNewUser()
     *
     * @see LibreCoresUserProvider::registerNewUser()
     *
     */
    private function getFailedOAuthData()
    {
        // $this->session->remove(self::FAILED_OAUTH_DATA);
        if ($this->session->has(self::FAILED_OAUTH_DATA)) {
            return $this->session->get(self::FAILED_OAUTH_DATA);
        }

        // check if the User was passed to us in the form of a security
        // exception from the OAuth auto-registration process
        if ($this->session->has(Security::AUTHENTICATION_ERROR)) {
            $error = $this->session->get(Security::AUTHENTICATION_ERROR);
            if (!$error instanceof \Librecores\ProjectRepoBundle\Security\Core\Exception\OAuthUserLinkingException) {
                return;
            }
            $data = $error->getOAuthData();
            $this->session->set(self::FAILED_OAUTH_DATA, $data);
            $this->session->remove(Security::AUTHENTICATION_ERROR);

            return $data;
        }
    }

    /**
     * Remove the "leftover" data from the OAuth auto-registration
     */
    private function clearFailedOAuthSession()
    {
        $this->session->remove(self::FAILED_OAUTH_DATA);
    }
}
