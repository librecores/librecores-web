<?php

namespace Librecores\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    // XXX: add caching for static pages
    // see http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/cache.html
    public function pageAction($page)
    {
        // $page can contain anything, sanitize it by stripping all unknown
        // characters out
        $page = preg_replace('|[^a-zA-Z0-9_/-]|i', '', $page);

        // we use a special template for the homepage
        if ($page === 'home') {
            return $this->render('LibrecoresSiteBundle:Default:home.html.twig');
        }

        // everything else goes through markdown and the default content template
        return $this->render(
            'LibrecoresSiteBundle:Default:contentwrapper.html.twig',
            array('page' => '@site_content/'.$page.'.md')
        );
    }
}
