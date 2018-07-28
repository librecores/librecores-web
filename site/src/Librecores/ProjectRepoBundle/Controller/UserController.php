<?php

namespace Librecores\ProjectRepoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Librecores\ProjectRepoBundle\Entity\User;
use Librecores\ProjectRepoBundle\Form\Type\UserProfileType;
use FOS\UserBundle\Form\Type\ChangePasswordFormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserController extends Controller
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
            'LibrecoresProjectRepoBundle:User:view.html.twig',
            array('user' => $user)
        );
    }

    /**
     * View a dashboard after Login
     *
     * @param Request  $request
     * @param User     $user
     *
     * @return Response
     */
    public function dashboardAction(Request $request)
    {
        return $this->render(
            'LibrecoresProjectRepoBundle:User:dashboard.html.twig',
            array('user' => $this->getUser())
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
            'LibrecoresProjectRepoBundle:User:settings_profile.html.twig',
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
            'LibrecoresProjectRepoBundle:User:settings_connections.html.twig',
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function connectionSuccessAction(Request $request, $serviceName)
    {
        $this->addFlash(
            'success',
            "You successfully connected your LibreCores account to "
            .ucfirst($serviceName)."."
        );

        return $this->connectionsSettingsAction($request);
    }

    /**
     * Disconnect the user account from an OAuth service
     *
     * @param Request $request
     * @param string  $serviceName
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function disconnectFromOAuthServiceAction(Request $request, $serviceName)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $this->get('hwi_oauth.account.connector')->disconnect($this->getUser(), $serviceName);

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
     * @param Request $request
     *
     * @return Response
     */
    public function passwordSettingsAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordFormType::class, $user);
        $form->add('save', SubmitType::class, array('label' => 'Change password'));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        return $this->render(
            'LibrecoresProjectRepoBundle:User:settings_password.html.twig',
            array('user' => $user, 'form' => $form->createView())
        );
    }
}
