<?php

namespace Librecores\ProjectRepoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Form\Type\ProjectType;
use Librecores\ProjectRepoBundle\Form\Type\SourceRepoType;
use Librecores\ProjectRepoBundle\Entity\Organization;
use Librecores\ProjectRepoBundle\Entity\User;

class ProjectController extends Controller {
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

        // XXX: We currently only support projects inside the user's own
        //      namespace. Make this dynamic when introducing organizations.
        $p->setParentUser($this->getUser());
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
            /* XXX: currently only the user namespace is supported, see above.
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
             }*/

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
                    'parentName' => $p->getParentUser(),
                    'projectName' => $p->getName(),
                ));
        }

        return $this->render('LibrecoresProjectRepoBundle:Project:new.html.twig',
            array('project' => $p, 'form' => $form->createView()));
    }

    /**
     * Display the project
     *
     * @param string $parentName URL component: name of the parent (user or organization)
     * @param string $projectName URL component: name of the project
     * @return Response
     */
    public function viewAction($parentName, $projectName)
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
            $response->headers->set('refresh', '5;url='.$this->getRequest()->getUri());
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
     * @param string $parentName URL component: name of the parent (user or organization)
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

        if ($form->isValid()) {
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
     * @param string $parentName URL component: name of the parent (user or organization)
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
}
