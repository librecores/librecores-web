<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Notification;
use App\Form\Model\ResendConfirmationEmailRequest;
use App\Form\Type\ResendConfirmationEmailRequestType;
use App\Form\Type\NotificationSubscriptionType;
use App\Form\Type\UserProfileType;
use App\Security\Core\User\LibreCoresUserProvider;
use FOS\UserBundle\Form\Type\ChangePasswordFormType;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Mgilet\NotificationBundle\Manager\NotificationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserController extends AbstractController
{
    /**
     * View a user's public profile
     *
     * @param Request $request
     * @param User    $user
     *
     * @return Response
     */
    public function viewAction(Request $request, User $user)
    {
        return $this->render(
            'user/view.html.twig',
            array('user' => $user)
        );
    }

    /**
     * User profile settings
     *
     * @param Request $request
     *
     * @return Response
     */
    public function profileSettingsAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        return $this->render(
            'user/settings_profile.html.twig',
            array('user' => $user, 'form' => $form->createView())
        );
    }

    /**
     * User connected services settings (such as GitHub or BitBucket)
     *
     * @param Request $request
     *
     * @return Response
     */
    public function connectionsSettingsAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        return $this->render(
            'user/settings_connections.html.twig',
            array('user' => $user)
        );
    }

    /**
     * Successfully connected to an OAuth service
     *
     * As the HWIOAuthBundle doesn't support a nicer way for customization,
     * this action is forwarded from
     * HWI\Bundle\OAuthBundle\Controller\ConnectController:connectServiceAction()
     * through the overwritten template in
     * app/Resources/HWIOAuthBundle/views/Connect/connect_success.html.twig
     *
     * @param Request $request
     * @param string  $serviceName
     *
     * @return Response
     */
    public function connectionSuccessAction(Request $request, $serviceName)
    {
        $this->addFlash(
            'success',
            "You successfully connected your LibreCores account to "
            .ucfirst($serviceName)."."
        );

        return $this->redirectToRoute('librecores.user.settings.connections');
    }

    /**
     * Disconnect the user account from an OAuth service
     *
     * @param Request                $request
     * @param string                 $serviceName
     * @param LibreCoresUserProvider $accountConnector
     *
     * @return Response
     */
    public function disconnectFromOAuthServiceAction(
        Request $request,
        $serviceName,
        LibreCoresUserProvider $accountConnector
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $accountConnector->disconnect($this->getUser(), $serviceName);

        $this->addFlash(
            'success',
            "You successfully disconnected your LibreCores account from "
            .ucfirst($serviceName)."."
        );

        return $this->connectionsSettingsAction($request);
    }

    /**
     * Change user password
     *
     * @param Request              $request
     * @param UserManagerInterface $userManager
     *
     * @return Response
     */
    public function passwordSettingsAction(
        Request $request,
        UserManagerInterface $userManager
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $validationGroups = ['ChangePassword', 'Default'];
        $form = $this->createForm(
            ChangePasswordFormType::class,
            $user,
            ['validation_groups' => $validationGroups]
        );
        $form->add('save', SubmitType::class, array('label' => 'Change password'));
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->updateUser($user);

            $this->addFlash(
                'success',
                "Your password was successfully changed."
            );

            $url = $this->generateUrl('fos_user_profile_show');
            $response = new RedirectResponse($url);

            return $response;
        }

        return $this->render(
            'user/settings_password.html.twig',
            array('user' => $user, 'form' => $form->createView())
        );
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function resendConfirmationEmailAction(
        Request $request,
        MailerInterface $userMailer,
        TokenGeneratorInterface $tokenGenerator
    ) {

        $resendEmailRequest = new ResendConfirmationEmailRequest();
        $form = $this->createForm(ResendConfirmationEmailRequestType::class, $resendEmailRequest);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $resendEmailRequest->getUser();

            if (!$user->isEnabled()) {
                if (null === $user->getConfirmationToken()) {
                    $user->setConfirmationToken($tokenGenerator->generateToken());
                }

                $userMailer->sendConfirmationEmailMessage($resendEmailRequest->getUser());

                $request->getSession()->set('fos_user_send_confirmation_email/email', $user->getEmail());

                return $this->redirectToRoute('fos_user_registration_check_email');
            } else {
                $this->addFlash('warning', 'Account is already confirmed');
            }
        }

        return $this->render('user/resend_confirmation_email.html.twig', [ 'form' => $form->createView() ]);
    }

    /**
     * User Notification Settings
     *
     * @Route("/user/settings/notifications", name="librecores.user.settings.notification")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function notificationSettingsAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $form = $this->createForm(NotificationSubscriptionType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        return $this->render(
            'user/settings_notification.html.twig',
            ['user' => $user, 'form' => $form->createView()]
        );
    }

    /**
     * Mark notifications "seen" in the notification list
     *
     * @Route("/user/notification/seen", name="notification_mark_seen")
     *
     * @Method("POST")
     *
     * @param Request             $request
     * @param NotificationManager $notificationManager
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function markAsSeenAction(Request $request, NotificationManager $notificationManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();


        $notification = $request->get('notification');
        $notifiableEntity = $notificationManager->getNotifiableEntity($user);
        $notifiable = $notificationManager->getNotifiableInterface($notificationManager->getNotifiableEntityById($notifiableEntity));

        $notificationManager->markAsSeen(
            $notifiable,
            $notificationManager->getNotification($notification),
            true
        );
        $count = $notificationManager->getUnseenNotificationCount($notifiable);

        return new JsonResponse($count);
    }

    /**
     * Marks all notifications "seen" in notification list
     *
     * @Route("/user/notification/markallseen", name="notification_mark_all_seen")
     *
     * @Method("POST")
     *
     * @param NotificationManager $notificationManager
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function markAllAsSeenAction(NotificationManager $notificationManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $notifiableUser = $notificationManager->getNotifiableEntity($user);
        $notifiableEntity = $notificationManager->getNotifiableEntityById($notifiableUser);
        $notifiable = $notificationManager->getNotifiableInterface($notifiableEntity);

        $notificationManager->markAllAsSeen(
            $notifiable,
            true
        );
        $count = $notificationManager->getUnseenNotificationCount($notifiable);

        return new JsonResponse($count);
    }

    /**
     * View all notifications in the user's inbox
     *
     * @Route("/user/notification/inbox", name="notification_inbox")
     *
     * @param NotificationManager $notificationManager
     *
     * @return string
     */
    public function inboxAction(NotificationManager $notificationManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $notifications = $notificationManager->getNotifications($user);

        return $this->render(
            'user/notification_inbox.html.twig',
            [
                'notificationList' => $notifications,
            ]
        );
    }

    /**
     * Mark a notification as seen in the notification inbox
     *
     * @Route("/user/notification/seen/{notification}", name="inbox_mark_seen")
     *
     * @param Notification        $notification
     * @param NotificationManager $notificationManager
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Exception
     *
     * @return Response
     */
    public function inboxMarkSeenAction($notification, NotificationManager $notificationManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $notifiableEntity = $notificationManager->getNotifiableEntity($user);
        $notifiable = $notificationManager->getNotifiableInterface($notificationManager->getNotifiableEntityById($notifiableEntity));

        $notificationManager->markAsSeen(
            $notifiable,
            $notificationManager->getNotification($notification),
            true
        );
        $notificationList = $notificationManager->getNotifications(
            $notifiable
        );

        return $this->redirectToRoute(
            'notification_inbox',
            ['notificationList' => $notificationList]
        );
    }

    /**
     * Remove a Notification
     *
     * @Route("/user/notification/remove/{notification}", name="remove_notification")
     *
     * @param Notification        $notification
     * @param NotificationManager $notificationManager
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return Response
     */
    public function removeAction($notification, NotificationManager $notificationManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $notifiableUser = $notificationManager->getNotifiableEntity($user);
        $notifiableEntity = $notificationManager->getNotifiableEntityById($notifiableUser);
        $notifiable = $notificationManager->getNotifiableInterface($notifiableEntity);


        // Deletes the link between notification and notifiable
        $notificationManager->removeNotification(
            [$notifiable],
            $notificationManager->getNotification($notification),
            true
        );

        // Remove the Notification from the database
        // TODO: Let foreign constraints take care of removing notifications, see #400
        $notificationManager->deleteNotification($notificationManager->getNotification($notification), true);

        $notificationList = $notificationManager->getNotifications(
            $notifiable
        );

        return $this->redirectToRoute(
            'notification_inbox',
            ['notificationList' => $notificationList]
        );
    }
}
