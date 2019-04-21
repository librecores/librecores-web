<?php

namespace Librecores\ProjectRepoBundle\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Librecores\ProjectRepoBundle\Entity\Organization;
use Librecores\ProjectRepoBundle\Entity\User;
use Librecores\ProjectRepoBundle\Repository\OrganizationRepository;
use Librecores\ProjectRepoBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    /**
     * Render the project overview page
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * Display a user or an organization
     *
     * @param string                 $userOrOrganization
     * @param OrganizationRepository $organizationRepository
     * @param UserRepository         $userRepository
     *
     * @return Response
     *
     * @throws NonUniqueResultException
     */
    public function userOrgViewAction(
        string $userOrOrganization,
        OrganizationRepository $organizationRepository,
        UserRepository $userRepository
    ) {
        $uoo = $this->getUserOrOrg(
            $userOrOrganization,
            $organizationRepository,
            $userRepository
        );

        if ($uoo instanceof User) {
            return $this->forward(
                'LibrecoresProjectRepoBundle:User:view',
                array('user' => $uoo)
            );
        }
        if ($uoo instanceof Organization) {
            return $this->forward(
                'LibrecoresProjectRepoBundle:Organization:view',
                array('organization' => $uoo)
            );
        }

        // and 404 if it's neither
        throw $this->createNotFoundException('User or organization not found.');
    }

    /**
     * Display the organization settings page
     *
     * @param string                 $userOrOrganization     name of the user
     *                                                       or organization
     * @param OrganizationRepository $organizationRepository autowired
     * @param UserRepository         $userRepository         autowired
     *
     * @return Response
     *
     * @throws NonUniqueResultException
     */
    public function userOrgSettingsAction(
        $userOrOrganization,
        OrganizationRepository $organizationRepository,
        UserRepository $userRepository
    ) {
        $uoo = $this->getUserOrOrg(
            $userOrOrganization,
            $organizationRepository,
            $userRepository
        );

        if ($uoo instanceof User) {
            if ($uoo->getId() !== $this->getUser()->getId()) {
                throw $this->createAccessDeniedException();
            }

            return $this->forward(
                'LibrecoresProjectRepoBundle:User:profileSettings',
                array('user' => $userOrOrganization)
            );
        }

        if ($uoo instanceof Organization) {
            return $this->forward(
                'LibrecoresProjectRepoBundle:Organization:settings',
                array('organization' => $uoo)
            );
        }

        // and 404 if it's neither
        throw $this->createNotFoundException('User or organization not found.');
    }

    /**
     * Search for a project
     *
     * @param Request $req
     *
     * @return Response
     */
    public function searchAction(Request $req)
    {
        $searchType = $req->get('type');
        $searchQuery = $req->get('query');

        $searchType = ($searchType === null ? 'projects' : $searchType);

        return $this->render(
            'default/project_search.html.twig',
            [
                'searchType' => $searchType,
                'searchQuery' => $searchQuery,
            ]
        );
    }


    public function removeTrailingSlashAction(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return $this->redirect($url, 301);
    }

    /**
     * Get an user or organization entity with the given name
     *
     * @param string                 $userOrOrganization
     * @param OrganizationRepository $organizationRepository
     * @param UserRepository         $userRepository
     *
     * @return User|Organization|null user or organization entity, or null if
     *   no entity with the given name exists.
     * @throws NonUniqueResultException
     */
    private function getUserOrOrg(
        $userOrOrganization,
        OrganizationRepository $organizationRepository,
        UserRepository $userRepository
    ) {
        // try user first
        $user = $userRepository->findOneByUsername($userOrOrganization);

        if ($user !== null) {
            return $user;
        }

        // then organization
        $org = $organizationRepository->findOneByName($userOrOrganization);

        return $org;
    }
}
