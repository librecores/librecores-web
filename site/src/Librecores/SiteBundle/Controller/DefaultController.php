<?php

namespace Librecores\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Librecores\ProjectRepoBundle\Form\Type\SearchQueryType;
use Librecores\ProjectRepoBundle\Form\Model\SearchQuery;

class DefaultController extends Controller
{
    /**
     * Render the home page
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function homeAction(Request $request)
    {
        $templateArgs = array();

        // search query form
        $searchQueryForm = $this->createForm(SearchQueryType::class,
            new SearchQuery(),
            ['action' => $this->generateUrl('librecores_project_repo_project_search')]);
        $templateArgs['search_query_form'] = $searchQueryForm->createView();

        // blog posts on planet
        $templateArgs['blogposts'] = $this->getBlogPosts();

        // load activity
        $prjrepo = $this->getDoctrine()->getRepository('LibrecoresProjectRepoBundle:Project');
        $templateArgs ['activity'] = $prjrepo->findByRecentActivity(10);

        return $this->render('LibrecoresSiteBundle:Default:home.html.twig',
            $templateArgs
        );
    }

    /**
     * Grab all blog posts from the feed
     *
     * XXX: This function should fetch data from memcached or some other cache
     * and updated async to the HTTP request path through a cron job
     *
     * XXX: Also, put the blog URL into a config file
     */
    private function getBlogPosts()
    {
        $blogUrl = $this->get('kernel')->getRootDir().'/../web/planet/atom.xml';

        $feed = $this->get('fkr_simple_pie.rss');

        $feed->set_feed_url($blogUrl);
        $feed->enable_order_by_date(false);
        $feed->init();

        $blogPosts = array();
        foreach ($feed->get_items(0, 3) as $item) {
            $blogPosts[] = array(
                'author' => $item->get_author()->get_name(),
                'date' => $item->get_date('U'),
                'title' => $item->get_title(),
                'teaser' => $this->sanitizeContent($item->get_description(), 140),
                'url' => $item->get_link()
            );
        }
        return $blogPosts;
    }

    /**
     * Sanitize content to plaintext
     *
     * Strip HTML, convert HTML entities, etc.
     *
     * @param string $htmlContent input data
     * @param int $maxLength maximum length of the output string; -1 for unlimited
     * @return string sanitized content
     */
    private function sanitizeContent($text, $maxLength = -1)
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text);
        $text = trim($text);

        // If the text is longer than $maxLength, we try to cut it at the
        // closest white space character, and suffix it with ' ...'.
        if ($maxLength !== -1 && mb_strlen($text) > $maxLength) {
            $cutpos = strpos($text, ' ', $maxLength-4)+1;
            $text = mb_substr($text, 0, $cutpos).' ...';
        }

        return $text;
    }

    // XXX: add caching for static pages
    // see http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/cache.html
    public function pageAction($page)
    {
        $siteContentRoot = $this->get('kernel')->getRootDir().'/../sitecontent';

        // $page can contain anything, sanitize it by stripping all unknown
        // characters out
        $page = preg_replace('|[^a-zA-Z0-9_/-]|i', '', $page);

        // strip trailing slash
        if (substr($page, -1) == '/') {
            $page = substr($page, 0, -1);
        }

        // resolve directories and index pages
        if (is_dir($siteContentRoot.'/'.$page)) {
            $page .= '/index';
        }

        if (!file_exists("$siteContentRoot/$page.md")) {
            throw $this->createNotFoundException('Page not found.');
        }

        // show the page
        return $this->render(
            'LibrecoresSiteBundle:Default:contentwrapper.html.twig',
            array('page' => "@site_content/$page.md")
        );
    }
}
