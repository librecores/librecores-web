<?php

namespace Librecores\ProjectRepoBundle\Controller;

use Librecores\ProjectRepoBundle\Entity\ClassificationHierarchy;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;
use Librecores\ProjectRepoBundle\Entity\LanguageStat;
use Librecores\ProjectRepoBundle\Entity\OrganizationMember;
use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Entity\ProjectClassification;
use Librecores\ProjectRepoBundle\Form\Type\ProjectClassificationType;
use Librecores\ProjectRepoBundle\Form\Type\ProjectType;
use Librecores\ProjectRepoBundle\RepoCrawler\GithubRepoCrawler;
use Librecores\ProjectRepoBundle\Util\Dates;
use Librecores\ProjectRepoBundle\Util\GithubApiService;
use Librecores\ProjectRepoBundle\Util\QueueDispatcherService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class ProjectController extends Controller
{
    /**
     * Render the "New Project" page
     *
     * @param Request $request
     *
     * @return Response
     *
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
        $parentChoices = array($username => 'u_'.$username);
        foreach ($this->getUser()->getOrganizationMemberships() as $organizationMembership) {
            if ($organizationMembership->getPermission() === OrganizationMember::PERMISSION_MEMBER ||
                $organizationMembership->getPermission() === OrganizationMember::PERMISSION_ADMIN) {
                $parentChoices[$organizationMembership->getOrganization()->getName()] =
                    'o_'.$organizationMembership->getOrganization()->getName();
            }
        }

        // build form
        $formBuilder = $this->createFormBuilder($p)
            ->add('parentName', ChoiceType::class, [
                'mapped' => false,
                'choices' => $parentChoices,
                'multiple' => false,
            ])
            ->add('name')
            ->add('displayName')
            ->add('gitSourceRepoUrl', UrlType::class, [
                'mapped' => false,
                'label' => 'Git URL',
                'required' => false,
                'constraints' => [
                    new NotBlank(['groups' => ["git_url"]]),
                    new Url(['groups' => ["git_url"]]),
                ],
            ])
            ->add('saveGitSourceRepoUrl', SubmitType::class, [
                'label' => 'Create Project from Git repository',
                'attr' => [
                    'class' => 'btn-primary',
                ],
                'validation_groups' => ['Default', 'git_url'],
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
                    'attr' => ['class' => 'btn-primary'],
                    'validation_groups' => ['Default', 'github'],
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
            $this->getQueueDispatcherService()->updateProjectInfo($p);

            // redirect user to "view project" page
            return $this->redirectToRoute(
                'librecores_project_repo_project_view',
                array(
                    'parentName' => $p->getParentName(),
                    'projectName' => $p->getName(),
                )
            );
        }

        // determine which tab to show to select the source from
        $activeSourcePanel = 'git_url';
        if ($form->isSubmitted()) {
            $activeSourcePanel = $this->getSourceTypeFromForm($form);
        } else {
            if ($isGithubConnected) {
                $activeSourcePanel = 'github';
            }
        }

        return $this->render(
            'LibrecoresProjectRepoBundle:Project:new.html.twig',
            array(
                'project' => $p,
                'form' => $form->createView(),
                'isGithubConnected' => $isGithubConnected,
                'noGithubRepos' => $noGithubRepos,
                'activeSourcePanel' => $activeSourcePanel,
            )
        );
    }

    /**
     * Display the project
     *
     * @param Request $request
     * @param string  $parentName  URL component: name of the parent
     *                             (user or organization)
     * @param string  $projectName URL component: name of the project
     *
     * @return Response
     */
    public function viewAction(Request $request, $parentName, $projectName)
    {
        $p = $this->getProject($parentName, $projectName);

        // redirect to wait page until processing is done
        if ($p->getInProcessing()) {
            $waitTemplate = 'LibrecoresProjectRepoBundle:Project:view_wait_processing.html.twig';
            $response = new Response(
                $this->renderView($waitTemplate, array('project' => $p)),
                Response::HTTP_OK
            );
            $response->headers->set('refresh', '5;url='.$request->getUri());

            return $response;
        }

        // fetch project metadata
        $projectMetricsProvider = $this->get('librecores.project_metrics_provider');

        $current = new \DateTimeImmutable();

        $qualityScore = $projectMetricsProvider->getCodeQualityScore($p);
        $twoTimesQualityScore = (int) ($qualityScore * 2);
        $metadata = [
            'qualityScore' => [
                'fullStars' => (int) ($twoTimesQualityScore / 2),
                'halfStars' => $twoTimesQualityScore % 2,
                'value' => $qualityScore,
            ],
            'latestCommit' => $projectMetricsProvider->getLatestCommit($p),
            'firstCommit' => $projectMetricsProvider->getFirstCommit($p),
            'contributorCount' => $projectMetricsProvider->getContributorsCount($p),
            'commitCount' => $projectMetricsProvider->getCommitCount($p),
            'topContributors' => $projectMetricsProvider->getTopContributors($p),
            'activityGraph' => $this->makeActivityGraph(
                $projectMetricsProvider->getCommitHistogram(
                    $p,
                    Dates::INTERVAL_WEEK,
                    $current->sub(
                        \DateInterval::createFromDateString('1 year')
                    ),
                    $current
                )
            ),
            'commitGraph' => $this->makeGraph(
                $projectMetricsProvider->getCommitHistogram(
                    $p,
                    Dates::INTERVAL_YEAR
                )
            ),
            'languageGraph' => $this->makeGraph(
                $projectMetricsProvider->getMostUsedLanguages($p),
                false
            ),
            'contributorsGraph' => $this->makeGraph(
                $projectMetricsProvider->getContributorHistogram($p, Dates::INTERVAL_YEAR)
            ),
            'isHostedOnGithub' => GithubRepoCrawler::isGithubRepoUrl($p->getSourceRepo()->getUrl()),
        ];

        // the actual project page
        return $this->render(
            'LibrecoresProjectRepoBundle:Project:view.html.twig',
            [
                'project' => $p,
                'metadata' => $metadata,
            ]
        );
    }

    /**
     * Display the project settings page
     *
     * @param Request $request
     * @param string  $parentName  URL component: name of the parent
     *                             (user or organization)
     * @param string  $projectName URL component: name of the project
     *
     * @return Response
     */
    public function settingsAction(Request $request, $parentName, $projectName)
    {
        $p = $this->getProject($parentName, $projectName);

        // check permissions
        $this->denyAccessUnlessGranted('edit', $p);

        // create and show form
        $form = $this->createForm(ProjectType::class, $p);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($p);
            $em->flush();
        }

        // retrive classification hierarchy and send it to settings page
        $classificationCategories = $this->getDoctrine()->getManager()
            ->getRepository(ClassificationHierarchy::class)
            ->findAll();

        $classificationHierarchy = array();
        $id = 0;
        foreach ($classificationCategories as $category) {
            $temp = array(
                1 => $category->getId(),
                2 => $category->getParent() == null ?
                    $category->getParent(): $category->getParent()->getId(),
                3 => $category->getName(),
            );
            $classificationHierarchy[$id++] = $temp;
        }

        return $this->render(
            'LibrecoresProjectRepoBundle:Project:settings.html.twig',
            array(
                'project' => $p,
                'form' => $form->createView(),
                'classificationHierarchy' => $classificationHierarchy,
            )
        );
    }

    /**
     * Render the project settings -> team page
     *
     * @param string $parentName  URL component: name of the parent
     *                            (user or organization)
     * @param string $projectName URL component: name of the project
     *
     * @return Response
     */
    public function settingsTeamAction($parentName, $projectName)
    {
        $p = $this->getProject($parentName, $projectName);

        return $this->render(
            'LibrecoresProjectRepoBundle:Project:settings_team.html.twig',
            array('project' => $p)
        );
    }

    /**
     * List all projects available on LibreCores
     *
     * @todo paginate result
     *
     * @param Request $req
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $req)
    {
        $projects = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Project')
            ->findAll();

        return $this->render(
            'LibrecoresProjectRepoBundle:Project:list.html.twig',
            ['projects' => $projects]
        );
    }

    /**
     * Update all data associated with the project from external sources
     *
     * This action triggers a re-scan of associated source repositories, and
     * gets other data from 3rd-party services as needed.
     *
     * Note that this action only *triggers* the update -- the update itself is
     * done asynchronously through RabbitMQ.
     *
     * @param Request $req
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $req, $parentName, $projectName)
    {
        if ($ghEvent = $req->headers->has('X-GitHub-Event')) {
            if ($ghEvent != 'push') {
                // We only react to push events, all other events signal any
                // change we are interested in.
                return new Response();
            }
            // XXX: In the future, this could be extended to use information
            // contained in the notification directly.
        }

        // queue an update of the project's information
        $p = $this->getProject($parentName, $projectName);
        $this->getQueueDispatcherService()->updateProjectInfo($p);

        return new Response(
            'project update queued',
            200,
            [ 'Content-Type' => 'text/plain' ]
        );
    }

    /**
     * Set The Classifications for a project
     *
     * This method helps to specify classification for a project
     *
     * @param Request $request
     *
     * @return Response
     */
    public function insertClassificationAction(Request $request)
    {
        // check Authentication
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $p = $this->getProject($request->get('parentName'), $request->get('projectName'));
        $em = $this->getDoctrine()->getManager();
        $projectClassification = new ProjectClassification();
        $projectClassification->setProject($p);
        $projectClassification->setClassification($request->get('classification'));
        $projectClassification->setCreatedBy($p->getParentUser());
        $em->persist($projectClassification);
        $em->flush();

        $response = new Response("success", Response::HTTP_OK);

        return $response;
    }

    /**
     * Get The Classifications for a project
     *
     * This method retrives the classifications that are specified
     * for a given project.
     *
     * @param string $parentName  URL component: name of the parent
     *                            (user or organization)
     * @param string $projectName URL component: name of the project
     *
     * @return JsonResponse
     */
    public function getClassificationAction($parentName, $projectName)
    {
        $p = $this->getProject($parentName, $projectName);

        // check Authentication
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $classifications = $this->getDoctrine()->getManager()
            ->getRepository(ProjectClassification::class)
            ->findBy(['project' => $p->getId()]);
        $response = array();
        $id = 0;
        foreach ($classifications as $classification) {
            $temp = array(
                'id' => $classification->getId(),
                'classification' => $classification->getClassification(),
                'project' => $p->getId(),
            );
            $response[$id++] = $temp;
        }

        return new JsonResponse($response);
    }

    /**
     * Delete a ProjectClassification object
     *
     * @param int $classificationId URL component: id of a project classification
     *
     * @return Response
     */
    public function deleteClassificationAction($classificationId)
    {
        // check Authentication
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $em = $this->getDoctrine()->getManager();
        $projectClassification = $em->getRepository(ProjectClassification::class)->find($classificationId);
        $em->remove($projectClassification);
        $em->flush();

        $response = new Response("success", Response::HTTP_OK);

        return $response;
    }

    /**
     * Update a Project Classification
     *
     * @param Request $request
     *
     * @param string  $parentName       URL component: name of the parent
     * @param string  $projectName      URL component: name of the project
     *
     * @param int     $classificationId URL component: id of a project classification
     *
     * @return Response
     */
    public function updateClassificationAction(Request $request, $parentName, $projectName, $classificationId)
    {
        $em = $this->getDoctrine()->getManager();
        $projectClassification = $em->getRepository(ProjectClassification::class)->find($classificationId);
        // check Authentication
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $form = $this->createForm(ProjectClassificationType::class, $projectClassification);
        $p = $this->getProject($parentName, $projectName);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($projectClassification);
            $em->flush();

            return $this->redirectToRoute(
                'librecores_project_repo_project_settings',
                array(
                    'parentName' => $parentName,
                    'projectName' => $projectName,
                )
            );
        }

        // retrive classification hierarchy and send it to settings page
        $classificationCategories = $this->getDoctrine()->getManager()
            ->getRepository(ClassificationHierarchy::class)
            ->findAll();

        $classificationHierarchy = array();
        $id = 0;
        foreach ($classificationCategories as $category) {
            $temp = array(
                1 => $category->getId(),
                2 => $category->getParent() == null ?
                    $category->getParent(): $category->getParent()->getId(),
                3 => $category->getName(),
            );
            $classificationHierarchy[$id++] = $temp;
        }

        return $this->render(
            'LibrecoresProjectRepoBundle:Project:update_classification.html.twig',
            array(
                'project' => $p,
                'form' => $form->createView(),
                'classificationHierarchy' => $classificationHierarchy,
            )
        );
    }

    /**
     * @return GithubApiService
     */
    private function getGithubApiService()
    {
        return $this->get('librecores.util.githubapiservice');
    }

    /**
     * @return QueueDispatcherService
     */
    private function getQueueDispatcherService()
    {
        return $this->get('librecores.util.queuedispatcherservice');
    }

    /**
     * Get project object from parentName and projectName
     *
     * @param string $parentName
     * @param string $projectName
     *
     * @return Project
     *
     * @throws NotFoundHttpException
     */
    private function getProject($parentName, $projectName)
    {
        $p = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Project')
            ->findProjectWithParent($parentName, $projectName);

        if (!$p) {
            throw $this->createNotFoundException('No project found with that name.');
        }

        return $p;
    }

    /**
     * Get all GitHub repositories accessible by the current user
     *
     * @return array
     */
    private function getGithubRepos()
    {
        $githubClient = $this->getGithubApiService()->getAuthenticatedClient();
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
     * Get the type of source (repository) from the form
     *
     * @param FormInterface $form
     *
     * @return NULL|string 'git_url' or 'github'
     */
    private function getSourceTypeFromForm(FormInterface $form)
    {
        $sourceType = null;
        if ($form->get('saveGitSourceRepoUrl')->isClicked()) {
            $sourceType = 'git_url';
        } elseif ($form->get('saveGithubSourceRepo')->isClicked()) {
            $sourceType = 'github';
        } else {
            new \Exception('No submit button with associated source type clicked?');
        }

        return $sourceType;
    }

    /**
     * Map the special form fields to the Project object
     *
     * The "New project" form contains a number of special form fields, which
     * can not directly be mapped by Symfony. This method contains code to do
     * this mapping. In addition, this method sets all project properties which
     * are not part of the form, but filled with default values.
     *
     * @param Project       $p
     * @param FormInterface $form
     */
    private function populateProjectFromForm(Project $p, FormInterface $form)
    {
        // set parent (extract from string selection box)
        $this->projectSetParentFromForm($p, $form);

        $sourceType = $this->getSourceTypeFromForm($form);

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
                // populate the project with some data from GitHub
                $this->getGithubApiService()->populateProject($p, $owner, $name);
                // and install a webhook to notify us of all pushes to the repo
                $this->getGithubApiService()->installHook($p, $owner, $name);
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
     * @param Project       $p
     * @param FormInterface $form
     *
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

            if (null === $user) {
                throw new \Exception("form manipulated");
            }

            $p->setParentUser($user);
        } elseif ($formParentType === 'o') {
            $organization = $this->getDoctrine()
                ->getRepository('LibrecoresProjectRepoBundle:Organization')
                ->findOneByName($formParentName);

            if (null === $organization) {
                throw new \Exception("form manipulated");
            }

            $p->setParentOrganization($organization);
        }
    }

    /**
     * Discards any labels and retains the value of a histogram
     *
     * @param array $histogram
     *
     * @return array
     */
    private function makeActivityGraph($histogram)
    {
        $values = [];
        foreach ($histogram as $i) {
            foreach ($i as $j) {
                $values[] = $j[0];
            }
        }

        return $values;
    }

    /**
     * Make a graph data object suitable for rendering a
     * graph in frontend.
     *
     * Uses array keys as labels and values as series.
     *
     * @param LanguageStat[] $languages
     * @param bool           $multiDim  does the graph expect multidimensional
     *                                  series. Defaults to true
     *
     * @return array data object for graph
     */
    private function makeGraph($languages, $multiDim = true)
    {
        $graph = [
            'labels' => array_keys($languages),
            'series' => [array_values($languages)],
        ];
        if (!$multiDim) {
            $graph['series'] = $graph['series'][0];
        }

        return $graph;
    }
}
