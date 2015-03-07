<?php

namespace Librecores\CoreRepoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Librecores\CoreRepoBundle\Entity\IpCore;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LibrecoresCoreRepoBundle:Default:index.html.twig');
    }
    
    public function viewCoreAction($vendor, $name)
    {
        $core = $this->getDoctrine()
            ->getRepository('LibrecoresCoreRepoBundle:IpCore')
            ->findOneBy(array('vendor' => $vendor, 'name' => $name));
        
        if (!$core) {
            throw $this->createNotFoundException(
                'No core found for!'
            );
        }
        
        return $this->render('LibrecoresCoreRepoBundle:Default:viewcore.html.twig', array('core' => $core));
    }
}
