<?php

namespace Librecores\ProjectRepoBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Librecores\ProjectRepoBundle\Entity\Organization;
use Librecores\ProjectRepoBundle\Entity\User;
use Librecores\ProjectRepoBundle\Form\Type\SearchQueryType;
use Librecores\ProjectRepoBundle\Form\Model\SearchQuery;

class DefaultController extends Controller
{
    /**
     * Render the project overview page
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('LibrecoresProjectRepoBundle:Default:index.html.twig');
    }

    /**
     * Get an user or organization entity with the given name
     *
     * @param string $userOrOrgName
     * @return User|Organization|null user or organization entity, or null if
     *   no entity with the given name exists.
     */
    private function getUserOrOrg($userOrOrganization) {
        // try user first
        $user = $this->getDoctrine()
                     ->getRepository('LibrecoresProjectRepoBundle:User')
                     ->findOneByUsername($userOrOrganization);

        if ($user !== null) {
            return $user;
        }

        // then organization
        $org = $this->getDoctrine()
                    ->getRepository('LibrecoresProjectRepoBundle:Organization')
                    ->findOneByName($userOrOrganization);
        return $org;
    }

    /**
     * Display a user or an organization
     *
     * @param string $userOrOrganization
     * @return Response
     */
    public function userOrgViewAction($userOrOrganization)
    {
        $uoo = $this->getUserOrOrg($userOrOrganization);

        if ($uoo instanceof User) {
            return $this->forward('LibrecoresProjectRepoBundle:User:view',
                array('user' => $uoo));
        }
        if ($uoo instanceof Organization) {
            return $this->forward('LibrecoresProjectRepoBundle:Organization:view',
                array('organization' => $uoo));
        }

        // and 404 if it's neither
        throw $this->createNotFoundException('User or organization not found.');
    }

    /**
     * Display the organization settings page
     *
     * @param Request $request
     * @param string $organizationName name of the organization
     * @return Response
     */
    public function userOrgSettingsAction(Request $request, $userOrOrganization)
    {
        $uoo = $this->getUserOrOrg($userOrOrganization);

        if ($uoo instanceof User) {
            if ($uoo->getId() != $this->getUser()->getId()) {
                throw $this->createAccessDeniedException();
            }
            return $this->forward('LibrecoresProjectRepoBundle:User:profileSettings',
                array('user' => $userOrOrganization));
        }

        if ($uoo instanceof Organization) {
            return $this->forward('LibrecoresProjectRepoBundle:Organization:settings',
                array('organization' => $uoo));
        }

        // and 404 if it's neither
        throw $this->createNotFoundException('User or organization not found.');
    }

    /**
     * Search for a project
     *
     * @param Request $req
     * @return Response
     */
    public function searchAction(Request $req)
    {
        $searchQuery = new SearchQuery();
        $searchQueryForm = $this->createForm(SearchQueryType::class, $searchQuery);
        $searchQueryForm->add('search_users', SubmitType::class, array(
            'label' => 'Users',
        ));
        $searchQueryForm->add('search_projects', SubmitType::class, array(
            'label' => 'Projects'
        ));
        $searchQueryForm->add('search_orgs', SubmitType::class, array(
            'label' => 'Organizations'
        ));
        $searchQueryForm->handleRequest($req);

        // Form validation: If we encounter any invalid value, simply
        // redirect to an empty search form.
        if (!empty($searchQuery->getQ()) && !$searchQueryForm->isValid()) {
            return $this->redirectToRoute($req->get('_route'));
        }

        // Handle switching of search type
        // In order to have always copy&paste-able URLs, we adjust the type
        // based on the button click event and then redirect to a "nice" URL
        // with the search results.
        $redirect = false;
        if ($searchQueryForm->get('search_projects')->isClicked()) {
            $searchQuery->setType(SearchQuery::TYPE_PROJECTS);
            $redirect = true;
        }
        if ($searchQueryForm->get('search_users')->isClicked()) {
            $searchQuery->setType(SearchQuery::TYPE_USERS);
            $redirect = true;
        }
        if ($searchQueryForm->get('search_orgs')->isClicked()) {
            $searchQuery->setType(SearchQuery::TYPE_ORGS);
            $redirect = true;
        }
        if ($redirect) {
            return $this->redirectToRoute($req->get('_route'),
                [ 'q' => $searchQuery->getQ(), 'type' => $searchQuery->getType()]);
        }

        // No query string given: no search necessary
        if (empty($searchQuery->getQ())) {
            return $this->render('LibrecoresProjectRepoBundle:Default:project_search.html.twig',
                [
                    'search_query_form' => $searchQueryForm->createView(),
                    'search_query' => $searchQuery,
                    'projects' => [],
                    'users' => [],
                    'orgs' => [],
                ]);
        }

        // Get the results
        $projects = array();
        $users = array();
        $orgs = array();

        // Search for projects
        if ($searchQuery->getType() == SearchQuery::TYPE_PROJECTS) {
            $projects = $this->getDoctrine()
                ->getRepository('LibrecoresProjectRepoBundle:Project')
                ->findByFqnameFragment($searchQuery->getQ());
        }

        // Search for users
        if ($searchQuery->getType() == SearchQuery::TYPE_USERS) {
            $userManager = $this->get('fos_user.user_manager');
            $users = $userManager->findUsersBySearchString($searchQuery->getQ());
        }

        // Search for organizations
        if ($searchQuery->getType() == SearchQuery::TYPE_ORGS) {
            $orgs = $this->getDoctrine()
                ->getRepository('LibrecoresProjectRepoBundle:Organization')
                ->findByFragment($searchQuery->getQ());
        }

        return $this->render('LibrecoresProjectRepoBundle:Default:project_search.html.twig',
            [
                'search_query_form' => $searchQueryForm->createView(),
                'search_query' => $searchQuery,
                'projects' => $projects,
                'users' => $users,
                'orgs' => $orgs,
            ]);
    }


    public function removeTrailingSlashAction(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return $this->redirect($url, 301);
    }
}
