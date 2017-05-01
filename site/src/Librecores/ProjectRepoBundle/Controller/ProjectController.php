<?php

namespace Librecores\ProjectRepoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Entity\OrganizationMember;
use Librecores\ProjectRepoBundle\Form\Type\ProjectType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;
use Symfony\Component\Form\FormInterface;
use Librecores\ProjectRepoBundle\Util\GithubApiService;

class ProjectController extends Controller
{
    /**
     * Render the "New Project" page
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function newAction(Request $request)
    {
        $p = new Project();
        $p->setParentUser($this->getUser());

        // construct choices for GitHub repository list
        $githubSourceRepoChoices = [];
        foreach ($this->getGithubRepos() as $repo) {
            $githubSourceRepoChoices[$repo['owner']['login']][$repo['full_name']] = $repo['full_name'];
        }

        // construct choices for the owner
        $username = $this->getUser()->getUsername();
        $parentChoices = array($username => 'u_' . $username);
        foreach ($this->getUser()->getOrganizationMemberships() as $organizationMembership) {
            if ($organizationMembership->getPermission() === OrganizationMember::PERMISSION_MEMBER or
                $organizationMembership->getPermission() === OrganizationMember::PERMISSION_ADMIN) {
                $parentChoices[$organizationMembership->getOrganization()->getName()] =
                    'o_' . $organizationMembership->getOrganization()->getName();
            }
        }

        // build form
        $formBuilder = $this->createFormBuilder($p)
            ->add('parentName', ChoiceType::class, [
                'mapped' => false,
                'choices' => $parentChoices,
                'multiple' => false
            ])
            ->add('name')
            ->add('displayName')
            ->add('gitSourceRepoUrl', UrlType::class, [
                'mapped' => false,
                'label' => 'Git URL',
                'required' => false
            ])
            ->add('saveGitSourceRepoUrl', SubmitType::class, [
                'label' => 'Create Project from Git repository',
                'attr' => [
                    'class' => 'btn-primary'
                ]
            ]);

        // only add GitHub repository selection if the user can actually select
        // something
        $isGithubConnected = $this->getUser()->isConnectedToOAuthService('github');
        $noGithubRepos = empty($githubSourceRepoChoices);
        if ($isGithubConnected && !$noGithubRepos) {
            $formBuilder
                ->add('githubSourceRepo', ChoiceType::class, [
                    'mapped' => false,
                    'choices' => $githubSourceRepoChoices,
                    'multiple' => false,
                    'required' => true,
                ])
                ->add('saveGithubSourceRepo', SubmitType::class, [
                    'label' => 'Import project from GitHub',
                    'attr' => ['class' => 'btn-primary']
                ]);
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        // save project and redirect to project page
        if ($form->isSubmitted() && $form->isValid()) {
            // populate Project with data from "special"/not mapped form fields
            $this->populateProjectFromForm($p, $form);

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
                    'parentName' => $p->getParentName(),
                    'projectName' => $p->getName(),
                ));
        }

        return $this->render(
            'LibrecoresProjectRepoBundle:Project:new.html.twig',
            array(
                'project' => $p,
                'form' => $form->createView(),
                'isGithubConnected' => $isGithubConnected,
                'noGithubRepos' => $noGithubRepos,
            ));
    }

    /**
     * Display the project
     *
     * @param Request $request
     * @param string $parentName URL component: name of the parent
     *                           (user or organization)
     * @param string $projectName URL component: name of the project
     * @return Response
     */
    public function viewAction(Request $request, $parentName, $projectName)
    {
        $p = $this->getDoctrine()
        ->getRepository('LibrecoresProjectRepoBundle:Project')
        ->findProjectWithParent($parentName, $projectName);

        if (!$p) {
            throw $this->createNotFoundException('No project found with that name.');
        }

        // redirect to wait page until processing is done
        if ($p->getInProcessing()) {
            $waitTemplate = 'LibrecoresProjectRepoBundle:Project:view_wait_processing.html.twig';
            $response = new Response(
                $this->renderView($waitTemplate, array('project' => $p)),
                Response::HTTP_OK);
            $response->headers->set('refresh', '5;url='.$request->getUri());
            return $response;
        }

        // the actual project page
        return $this->render('LibrecoresProjectRepoBundle:Project:view.html.twig',
            array('project' => $p));
    }

    /**
     * Display the project settings page
     *
     * @param Request $request
     * @param string $parentName URL component: name of the parent
     *                           (user or organization)
     * @param string $projectName URL component: name of the project
     * @return Response
     */
    public function settingsAction(Request $request, $parentName, $projectName)
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

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($p);
            $em->flush();
            //return $this->redirectToRoute('task_success');
        }

        return $this->render('LibrecoresProjectRepoBundle:Project:settings.html.twig',
            array('project' => $p, 'form' => $form->createView()));
    }

    /**
     * Render the project settings -> team page
     *
     * @param string $parentName URL component: name of the parent
     *                           (user or organization)
     * @param string $projectName URL component: name of the project
     * @return Response
     */
    public function settingsTeamAction($parentName, $projectName)
    {
        $p = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Project')
            ->findProjectWithParent($parentName, $projectName);

        if (!$p) {
            throw $this->createNotFoundException('No project found with that name.');
        }

        return $this->render('LibrecoresProjectRepoBundle:Project:settings_team.html.twig',
            array('project' => $p));
    }

    /**
     * List all projects available on LibreCores
     *
     * @todo paginate result
     *
     * @param Request $req
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $req)
    {
        $projects = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Project')
            ->findAll();

        return $this->render('LibrecoresProjectRepoBundle:Project:list.html.twig',
            [
                'projects' => $projects,
            ]);
    }

    /**
     * Get all GitHub repositories accessible by the current user
     *
     * @return array
     */
    private function getGithubRepos()
    {
        $githubClient = $this->get('librecores.util.githubapiservice')->getAuthenticatedClient();
        if (!$githubClient) {
            return [];
        }

        // As of today, the API does not provide a wrapper for this call using
        // the visibility/affiliation fields. We therefore manually issue the
        // request (c.f. Github\Api\AbstractApi::get() and
        // \Github\Api\CurrentUser)
        $path = '/user/repos';
        $parameters = array(
            'visibility' => 'all',
            'affiliation' => 'owner,collaborator,organization_member',
            'sort' => 'updated',
            'per_page' => 100,
        );
        if (count($parameters) > 0) {
            $path .= '?'.http_build_query($parameters);
        }

        $response = $githubClient->getHttpClient()->get($path);
        return \Github\HttpClient\Message\ResponseMediator::getContent($response);
    }

    /**
     * Map the special form fields to the Project object
     *
     * The "New project" form contains a number of special form fields, which
     * can not directly be mapped by Symfony. This method contains code to do
     * this mapping. In addition, this method sets all project properties which
     * are not part of the form, but filled with default values.
     *
     * @param Project $p
     * @param FormInterface $form
     */
    private function populateProjectFromForm(Project $p, FormInterface $form)
    {
        // set parent (extract from string selection box)
        $this->projectSetParentFromForm($p, $form);

        $sourceType = null;
        if ($form->get('saveGitSourceRepoUrl')->isClicked()) {
            $sourceType = 'git_url';
        } elseif ($form->get('saveGithubSourceRepo')->isClicked()) {
            $sourceType = 'github';
        } else {
            new \Exception('No submit button with associated source type clicked?');
        }

        // Repository is specified as Git URL
        if ($sourceType == 'git_url') {
            $gitSourceRepoUrl = $form->get('gitSourceRepoUrl')->getData();
            $gitSourceRepo = new GitSourceRepo();
            $gitSourceRepo->setUrl($gitSourceRepoUrl);
            $p->setSourceRepo($gitSourceRepo);
        }

        // Repository is imported from GitHub
        if ($sourceType == 'github') {
            $githubSourceRepoName = $form->get('githubSourceRepo')->getData();
            if (!empty($githubSourceRepoName)) {
                [$owner, $name] = explode('/', $githubSourceRepoName);
                $this->get('librecores.util.githubapiservice')->populateProject($p, $owner, $name);
                // TODO: install commit hook here
            }
        }

        $p->setStatus(Project::STATUS_ASSIGNED);

        // Mark the project as "in processing". This shows the wait page
        // until the update task has been ran from the RabbitMQ queue
        $p->setInProcessing(true);
    }

    /**
     * Set the parent of the project from the submitted data in the form
     *
     * The parent can be both an organization or an user, both of which are
     * represented in one dropdown field. This method takes the submitted string
     * apart and constructs the right parent object out of it.
     *
     * @param FormInterface $form
     * @param Project $p
     * @throws \Exception
     */
    private function projectSetParentFromForm(Project $p, FormInterface $form)
    {
        $formParent = $form->get('parentName')->getData();

        if (!preg_match('/^[uo]_.+$/', $formParent)) {
            throw new \Exception("form manipulated");
        }

        list($formParentType, $formParentName) = explode('_', $formParent, 2);

        if ($formParentType === 'u') {
            $user = $this->container->get('fos_user.user_manager')
            ->findUserByUsername($formParentName);

            if (null === $user)
                throw new \Exception("form manipulated");

                $p->setParentUser($user);

        } else if ($formParentType === 'o') {
            $organization = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Organization')
            ->findOneByName($formParentName);

            if (null === $organization)
                throw new \Exception("form manipulated");

                $p->setParentOrganization($organization);
        }
    }
}
