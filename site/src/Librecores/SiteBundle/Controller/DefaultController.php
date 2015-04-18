<?php

namespace Librecores\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function homeAction()
    {
        $templateArgs = array();
        $templateArgs['blogposts'] = $this->getBlogPosts();


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
        $blogUrl = 'http://blog.'.$_SERVER['HTTP_HOST'].'/?feed=rss2';

        $feed = $this->get('fkr_simple_pie.rss');

        $feed->set_feed_url($blogUrl);
        $feed->enable_order_by_date(false);
        $feed->init();

        $blogPosts = array();
        foreach ($feed->get_items(0, 5) as $item) {
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
        // $page can contain anything, sanitize it by stripping all unknown
        // characters out
        $page = preg_replace('|[^a-zA-Z0-9_/-]|i', '', $page);

        // everything else goes through markdown and the default content template
        return $this->render(
            'LibrecoresSiteBundle:Default:contentwrapper.html.twig',
            array('page' => '@site_content/'.$page.'.md')
        );
    }
}
