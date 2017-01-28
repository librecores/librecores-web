<?php

namespace Librecores\ProjectRepoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Librecores\ProjectRepoBundle\Entity\Organization;
use Librecores\ProjectRepoBundle\Entity\OrganizationMember;
use Librecores\ProjectRepoBundle\Form\Type\OrganizationType;

class OrganizationController extends Controller
{
    /**
     * List all the organizations
     * TODO: Maybe remove?
     * This listing might not be needed since the search will be
     * enough to find an organization to view / join etc.
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('LibrecoresProjectRepoBundle:Organization:index.html.twig');
    }

    /**
     * List the organizations that a user belongs
     *
     * @return Response
     */
    public function listAction()
    {
        $user = $this->getUser();

        $organizationsOwner = $this->getDoctrine()
                                   ->getRepository('LibrecoresProjectRepoBundle:Organization')
                                   ->findAllByOwnerOrderedByName($user);

        $organizationsMember = $this->getDoctrine()
                                    ->getRepository('LibrecoresProjectRepoBundle:Organization')
                                    ->findAllByMemberOrderedByName($user);


        return $this->render('LibrecoresProjectRepoBundle:Organization:list.html.twig',
            array('organizationsOwner'  => $organizationsOwner,
                  'organizationsMember' => $organizationsMember));
    }

    /**
     * Render the "New Organization" page
     *
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $o = new Organization();

        $form = $this->createFormBuilder($o)
                     ->add('name')
                     ->add('displayName')
                     ->add('description')
                     ->add('save', SubmitType::class, array('label' => 'Create Organization'))
                     ->getForm();

        $form->handleRequest($request);

        // Save organization and redirect to organization page
        if ($form->isValid()) {
            $user = $this->getUser();

            // Update and save new org
            $o->setCreator($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($o);

            // Create and save new membership
            $member = new OrganizationMember();
            $member->setOrganization($o);
            $member->setUser($user);
            $member->setPermissions(OrganizationMember::PERMISSIONS_ADMIN);
            $em->persist($member);

            $em->flush();

            // Redirect user to "view organization" page
            return $this->redirectToRoute('librecores_project_repo_user_org_view',
                array('userOrOrganization' => $o->getName()));
        }

        return $this->render('LibrecoresProjectRepoBundle:Organization:new.html.twig',
            array('organization' => $o,
                  'form' => $form->createView()));
    }

    /**
     * View an organization profile
     *
     * @param Organization $organization
     * @return Response
     */
    public function viewAction(Organization $organization)
    {
        return $this->render('LibrecoresProjectRepoBundle:Organization:view.html.twig',
            array('organization' => $organization,
                  'user'         => $this->getUser()));
    }

    /**
     * Display the organization settings page
     *
     * @param Request $request
     * @param Organization $organization  the organization entity
     * @return Response
     */
    public function settingsAction(Request $request, Organization $organization)
    {
        if ($this->getUser() != $organization->getOwner())
            throw $this->createAccessDeniedException("You don't own this organization in order to make changes");

        // Create and show the form
        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($organization);
            $em->flush();
        }

        return $this->render('LibrecoresProjectRepoBundle:Organization:settings.html.twig',
            array('organization' => $organization,
                  'form' => $form->createView()));
    }

    /**
     * Request to join an organization
     *
     * @param string $organizationName
     * @return Response
     */
    public function joinAction($organizationName)
    {
        $o = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Organization')
            ->findOneByName($organizationName);

        if (!$o)
            throw $this->createNotFoundException('No organization found with that name.');

        $user = $this->getUser();
        $member = new OrganizationMember();
        $member->setOrganization($o);
        $member->setUser($user);
        $member->setPermissions(OrganizationMember::PERMISSIONS_REQUEST);
        $em = $this->getDoctrine()->getManager();
        $em->persist($member);
        $em->flush();

        return $this->render('LibrecoresProjectRepoBundle:Organization:join.html.twig',
            array('organization' => $o));
    }

    /**
     * Leave from an organization
     *
     * @param string $organizationName
     * @return Response
     */
    public function leaveAction($organizationName)
    {
        $o = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Organization')
            ->findOneByName($organizationName);

        if (!$o)
            throw $this->createNotFoundException('No organization found with that name.');

        $user = $this->getUser();
        $member = $this->getDoctrine()
                       ->getRepository('LibrecoresProjectRepoBundle:OrganizationMember')
                       ->findOneBy(['organization' => $o, 'user' => $user]);

        $member->setOrganization(null);
        $member->setUser(null);

        $em = $this->getDoctrine()->getManager();
        $em->persist($o);
        $em->persist($user);
        $em->remove($member);
        $em->flush();

        return $this->render('LibrecoresProjectRepoBundle:Organization:leave.html.twig',
            array('organization' => $o));
    }

    /**
     * Delete an organization
     *
     * @param string $organizationName
     * @return Response
     */
    public function deleteAction($organizationName)
    {
        $o = $this->getDoctrine()
                  ->getRepository('LibrecoresProjectRepoBundle:Organization')
                  ->findOneByName($organizationName);

        if (!$o)
            throw $this->createNotFoundException('No organization found with that name.');

        if ($this->getUser() != $o->getOwner())
            throw $this->createAccessDeniedException("You don't own this organization in order to delete it");

        // We don't support this yet so just show 'this is an unsupported operation'
        // message to the user until we provide support

        // $em = $this->getDoctrine()->getManager();
        // $em->remove($o);
        // $em->flush();

        return $this->render('LibrecoresProjectRepoBundle:Organization:remove.html.twig',
            array('organization' => $o));
    }


    /**
     * Approve an organization join request
     *
     * @param string $organizationName
     * @param string $userName
     * @return Response
     */
    public function approveAction($organizationName, $userName)
    {
        $o = $this->getDoctrine()
                  ->getRepository('LibrecoresProjectRepoBundle:Organization')
                  ->findOneByName($organizationName);

        if (!$o)
            throw $this->createNotFoundException('No organization found with that name.');

        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($userName);

        if (!$user)
            throw $this->createNotFoundException("No user found with that username");

        $user = $this->getUser();
        $member = $this->getDoctrine()
                       ->getRepository('LibrecoresProjectRepoBundle:OrganizationMember')
                       ->findOneBy(['organization' => $o, 'user' => $user]);
        $member->setPermissions(OrganizationMember::PERMISSIONS_MEMBER);
        $em = $this->getDoctrine()->getManager();
        $em->persist($member);
        $em->flush();

        return $this->render('LibrecoresProjectRepoBundle:Organization:approve.html.twig',
            array('organization' => $o,
                  'user'         => $user));
    }

    /**
     * Deny an organization join request
     *
     * @param string $organizationName
     * @param string $userName
     * @return Response
     */
    public function denyAction($organizationName, $userName)
    {
        $o = $this->getDoctrine()
                  ->getRepository('LibrecoresProjectRepoBundle:Organization')
                  ->findOneByName($organizationName);

        if (!$o)
            throw $this->createNotFoundException('No organization found with that name.');

        // Deny the organization join request

        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($userName);

        if (!$user)
            throw $this->createNotFoundException("No user found with that username");

        $user = $this->getUser();
        $member = $this->getDoctrine()
                       ->getRepository('LibrecoresProjectRepoBundle:OrganizationMember')
                       ->findOneBy(['organization' => $o, 'user' => $user]);
        $member->setPermissions(OrganizationMember::PERMISSIONS_DENY);
        $em = $this->getDoctrine()->getManager();
        $em->persist($member);
        $em->flush();

        return $this->render('LibrecoresProjectRepoBundle:Organization:deny.html.twig',
            array('organization' => $o,
                  'user'         => $user));
    }

    /**
     * Remove an organization member
     *
     * @param string $organizationName
     * @param string $userName
     * @return Response
     */
    public function removeAction($organizationName, $userName)
    {
        $o = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Organization')
            ->findOneByName($organizationName);

        if (!$o)
            throw $this->createNotFoundException('No organization found with that name.');

        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($userName);

        if (!$user)
            throw $this->createNotFoundException("No user found with that username");

        $member = $this->getDoctrine()
                       ->getRepository('LibrecoresProjectRepoBundle:OrganizationMember')
                       ->findOneBy(['organization' => $o, 'user' => $user]);

        $member->setOrganization(null);
        $member->setUser(null);

        $em = $this->getDoctrine()->getManager();
        $em->persist($o);
        $em->persist($user);
        $em->remove($member);
        $em->flush();


        return $this->render('LibrecoresProjectRepoBundle:Organization:deny.html.twig',
            array('organization' => $o,
                  'user'         => $user));
    }
}
