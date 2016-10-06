<?php

namespace Librecores\ProjectRepoBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Form\Type\ProjectType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Librecores\ProjectRepoBundle\Form\Type\SourceRepoType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
     * Display a user or an organization
     *
     * @param string $userOrOrganization
     * @return Response
     */
    public function userOrgViewAction($userOrOrganization)
    {
        // try user first
        $user = $this->getDoctrine()
                     ->getRepository('LibrecoresProjectRepoBundle:User')
                     ->findOneByUsername($userOrOrganization);

        if ($user !== null) {
            return $this->userViewAction($user);
        }

        // then organization
        $org = $this->getDoctrine()
                    ->getRepository('LibrecoresProjectRepoBundle:Organization')
                    ->findOneByName($userOrOrganization);

        if ($org !== null) {
            return $this->forward('LibrecoresProjectRepoBundle:Organization:view',
                array('organization' => $org));
        }

        // and 404 if it's neither
        throw $this->createNotFoundException('User or organization not found.');
    }

    /**
     * View a user's profile
     *
     * @param User $user
     * @return Response
     */
    public function userViewAction(User $user)
    {
        return $this->render('LibrecoresProjectRepoBundle:Default:user_view.html.twig',
            array('user' => $user));
    }

    /**
     * Render the "New Project" page
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function projectNewAction(Request $request)
    {
        $p = new Project();

        // XXX: make this dynamic
        $username = $this->getUser()->getUsername();
        $parentChoices = array($username => 'u_'.$username);
        $form = $this->createFormBuilder($p)
            ->add('parentName', ChoiceType::class, array(
                'mapped' => false,
                'choices' => $parentChoices,
                'choices_as_values' => true,
                'multiple' => false,
            ))
            ->add('name')
            ->add('sourceRepo', SourceRepoType::class)
            ->add('save', SubmitType::class, array('label' => 'Create Project'))
            ->getForm();

        $form->handleRequest($request);

        // save project and redirect to project page
        if ($form->isValid()) {
            // set parent (extract from string selection box)
            $formParent = $form->get('parentName')->getData();
            if (!preg_match('/^[uo]_.+$/', $formParent)) {
                throw new \Exception("form manipulated");
            }
            list($formParentType, $formParentName) = explode('_', $formParent, 2);
            if ($formParentType == 'u') {
                $userManager = $this->container->get('fos_user.user_manager');
                $user = $userManager->findUserByUsername($formParentName);
                if (null === $user) {
                    throw new \Exception("form manipulated");
                }
                $p->setParentUser($user);
            } else if ($formParentType == 'o') {
                // TODO: Add ability to add projects to organizations here
                throw new \Exception("adding projects to organizations is currently not supported.");
            }

            $p->setStatus(Project::STATUS_ASSIGNED);

            // Mark the project as "in processing". This shows the wait page
            // until the update task has been ran from the RabbitMQ queue
            $p->setInProcessing(true);

            $em = $this->getDoctrine()->getManager();
            $em->persist($p);
            $em->flush();

            // queue data collection from repository
            $this->get('old_sound_rabbit_mq.update_project_info_producer')
                ->publish(serialize($p->getId()));

            // redirect user to "view project" page
            return $this->redirectToRoute(
                'librecores_project_repo_project_view',
                array(
                    'parentName' => $formParentName,
                    'projectName' => $p->getName(),
                ));
        }

        return $this->render('LibrecoresProjectRepoBundle:Default:project_new.html.twig',
            array('project' => $p, 'form' => $form->createView()));
    }

    /**
     * Display the project
     *
     * @param string $parentName URL component: name of the parent (user or organization)
     * @param string $projectName URL component: name of the project
     * @return Response
     */
    public function projectViewAction($parentName, $projectName)
    {
        $p = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Project')
            ->findProjectWithParent($parentName, $projectName);

        if (!$p) {
            throw $this->createNotFoundException('No project found with that name.');
        }

        // redirect to wait page until processing is done
        if ($p->getInProcessing()) {
            $waitTemplate = 'LibrecoresProjectRepoBundle:Default:project_wait_processing.html.twig';
            $response = new Response(
                $this->renderView($waitTemplate, array('project' => $p)),
                Response::HTTP_OK);
            $response->headers->set('refresh', '5;url='.$this->getRequest()->getUri());
            return $response;
        }

        // the actual project page
        return $this->render('LibrecoresProjectRepoBundle:Default:project_view.html.twig',
            array('project' => $p));
    }

    /**
     * Display the project settings page
     *
     * @param Request $request
     * @param string $parentName URL component: name of the parent (user or organization)
     * @param string $projectName URL component: name of the project
     * @return Response
     */
    public function projectSettingsAction(Request $request, $parentName, $projectName)
    {
        $p = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Project')
            ->findProjectWithParent($parentName, $projectName);

        if (!$p) {
            throw $this->createNotFoundException('No project found with that name.');
        }

        // check permissions
        $this->denyAccessUnlessGranted('edit', $p);

        // create and show form
        $form = $this->createForm(ProjectType::class, $p);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($p);
            $em->flush();
            //return $this->redirectToRoute('task_success');
        }

        return $this->render('LibrecoresProjectRepoBundle:Default:project_settings.html.twig',
            array('project' => $p, 'form' => $form->createView()));
    }

    /**
     * Render the project settings -> team page
     *
     * @param string $parentName URL component: name of the parent (user or organization)
     * @param string $projectName URL component: name of the project
     * @return Response
     */
    public function projectSettingsTeamAction($parentName, $projectName)
    {
        $p = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Project')
            ->findProjectWithParent($parentName, $projectName);

        if (!$p) {
            throw $this->createNotFoundException('No project found with that name.');
        }

        return $this->render('LibrecoresProjectRepoBundle:Default:project_settings_team.html.twig',
            array('project' => $p));
    }

    /**
     * Search for a project
     *
     * @param Request $req
     * @return Response
     */
    public function projectSearchAction(Request $req)
    {
        $searchQuery = new SearchQuery();
        $searchQueryForm = $this->createForm(SearchQueryType::class, $searchQuery);
        $searchQueryForm->add('search_users', SubmitType::class, array(
            'label' => 'Users',
        ));
        $searchQueryForm->add('search_projects', SubmitType::class, array(
            'label' => 'Projects'
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
                ]);
        }

        // Get the results
        $projects = array();
        $users = array();

        // Search for projects
        if ($searchQuery->getType() == SearchQuery::TYPE_PROJECTS) {
            $projects = $this->getDoctrine()
                ->getRepository('LibrecoresProjectRepoBundle:Project')
                ->findByFqnameFragment($searchQuery->getQ());
        }

        // Search for users
        if ($searchQuery->getType() == SearchQuery::TYPE_USERS) {
            $userManager = $this->container->get('fos_user.user_manager');
            $users = $userManager->findUsersBySearchString($searchQuery->getQ());
        }

        return $this->render('LibrecoresProjectRepoBundle:Default:project_search.html.twig',
            [
                'search_query_form' => $searchQueryForm->createView(),
                'search_query' => $searchQuery,
                'projects' => $projects,
                'users' => $users,
            ]);
    }
}
