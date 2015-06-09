<?php

namespace Librecores\ProjectRepoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Librecores\ProjectRepoBundle\Entity\Project;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LibrecoresProjectRepoBundle:Default:index.html.twig');
    }

    public function viewProjectAction($parent, $project)
    {
        // check if parent is user or organization
        // XXX: introduce a faster lookup for this one
        $parentUser = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:User')
            ->findOneByName($parent);
        if (!$parentUser) {
            $parentOrganization = $this->getDoctrine()
            ->getRepository('LibrecoresProjectRepoBundle:Organization')
            ->findOneByName($parent);
            if (!$parentOrganization) {
                throw $this->createNotFoundException(
                    'No user or organization found with that name.'
                );
            }
        }


        // get project
        if ($parentUser) {
            $project = $this->getDoctrine()
                ->getRepository('LibrecoresProjectRepoBundle:Project')
                ->findOneBy(array('parentUser' => $parentUser, 'name' => $project));
        }
        if ($parentOrganization) {
            $this->get('logger')->info("parent is organization");
            $project = $this->getDoctrine()
                ->getRepository('LibrecoresProjectRepoBundle:Project')
                ->findOneBy(array('parentOrganization' => $parentOrganization, 'name' => $project));
        }
        if (!$project) {
            throw $this->createNotFoundException(
                'No project found with the given name!'
            );
        }

        return $this->render('LibrecoresProjectRepoBundle:Default:viewproject.html.twig',
            array('parentName' => $parent, 'project' => $project));
    }
}
