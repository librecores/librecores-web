<?php

namespace Librecores\ProjectRepoBundle\Controller;

use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Form\Type\ProjectType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Librecores\ProjectRepoBundle\Form\Type\SourceRepoType;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DefaultController extends Controller
{
    /**
     * Render the project overview page
     */
    public function indexAction()
    {
        return $this->render('LibrecoresProjectRepoBundle:Default:index.html.twig');
    }

    /**
     * Render the "New Project" page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function projectNewAction(Request $request)
    {
        $p = new Project();

        // XXX: make this dynamic
        $parentChoices = array('openrisc' => 'o_opencores', 'imphil' => 'u_imphil');
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

        // parent selection
        //$form->get('parentName')->setData('u_imphil');

        $form->handleRequest($request);

        // save project and redirect to project page
        if ($form->isValid()) {
            // set parent (extract from string selection box)
            $formParent = $form->get('parentName')->getData();
            if (!preg_match('/^[up]_.+$/', $formParent)) {
                throw new \Exception("form manipulated");
            }
            list($formParentType, $formParentName) = explode('_', $formParent, 2);
            if ($formParentType == 'u') {
                $userManager = $this->container->get('fos_user.user_manager');
                //$user = $userManager->findUserBy(array('username_' => $username))
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

            $em = $this->getDoctrine()->getManager();
            $em->persist($p);
            $em->flush();
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function projectViewAction($parentName, $projectName)
    {
        $p = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Project')
            ->findProjectWithParent($parentName, $projectName);

        if (!$p) {
            throw $this->createNotFoundException('No project found with that name.');
        }

        return $this->render('LibrecoresProjectRepoBundle:Default:project_view.html.twig',
            array('project' => $p));
    }

    /**
     * Display the project settings page
     *
     * @param Request $request
     * @param string $parentName URL component: name of the parent (user or organization)
     * @param string $projectName URL component: name of the project
     */
    public function projectSettingsAction(Request $request, $parentName, $projectName)
    {
        $p = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Project')
            ->findProjectWithParent($parentName, $projectName);

        if (!$p) {
            throw $this->createNotFoundException('No project found with that name.');
        }

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
     * @return \Symfony\Component\HttpFoundation\Response
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
}
