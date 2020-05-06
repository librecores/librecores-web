<?php

namespace App\Controller;

use App\Form\Model\SearchQuery;
use App\Form\Type\SearchQueryType;
use SimplePie;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends Controller
{
    /**
     * Render the home page
     *
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/", name = "librecores_site_home")
     */
    public function homeAction(Request $request)
    {
        $templateArgs = array();

        // search query form
        $searchQueryForm = $this->createForm(
            SearchQueryType::class,
            new SearchQuery(),
            ['action' => $this->generateUrl('librecores_project_repo_project_search')]
        );
        $templateArgs['search_query_form'] = $searchQueryForm->createView();

        // blog posts on planet
        $templateArgs['blogposts'] = $this->getBlogPosts();

        return $this->render(
            'default/home.html.twig',
            $templateArgs
        );
    }

    /**
     * XXX: add caching for static pages
     * see http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/cache.html
     *
     * Route requirements: allow / inside path
     *
     * @Route("/static/{page}", name = "librecores_site_page", requirements = {"page" = ".+"})
     *
     * @param string $page
     *
     * @return Response
     */
    public function pageAction($page = "home")
    {
        $siteContentRoot = $this->get('kernel')->getProjectDir().'/sitecontent';

        // $page can contain anything, sanitize it by stripping all unknown
        // characters out
        $page = preg_replace('|[^a-zA-Z0-9_/-]|i', '', $page);

        // strip trailing slash
        if (substr($page, -1) === '/') {
            $page = substr($page, 0, -1);
        }

        // resolve directories and index pages
        if (is_dir($siteContentRoot.'/'.$page)) {
            $page .= '/index';
        }

        $file = "$siteContentRoot/$page.md";
        if (!file_exists($file)) {
            throw $this->createNotFoundException('Page not found.');
        }

        $document = YamlFrontMatter::parseFile($file);

        // show the page
        return $this->render(
            'default/content_wrapper.html.twig',
            [
                'title' => $document->matter('title', 'LibreCores'),
                'content' => $document->body(),
            ]
        );
    }

    /**
     * Grab all blog posts from the feed
     *
     * XXX: This function should fetch data from memcached or some other cache
     * and updated async to the HTTP request path through a cron job
     *
     * XXX: Also, put the blog URL into a config file
     *
     * @return array
     */
    private function getBlogPosts()
    {
        $blogUrl = $this->get('kernel')->getProjectDir().'/web/planet/atom.xml';

        $feed = new SimplePie();
        $feed->set_cache_duration(3600);
        $feed->set_cache_location($this->get('kernel')->getCacheDir().'/rss');
        $feed->enable_cache(true);
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
                'url' => $item->get_link(),
            );
        }

        return $blogPosts;
    }

    /**
     * Sanitize content to plaintext
     *
     * Strip HTML, convert HTML entities, etc.
     *
     * @param string $text      input data
     * @param int    $maxLength maximum length of the output string; -1 for unlimited
     *
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
            $cutpos = strpos($text, ' ', $maxLength - 4) + 1;
            $text = mb_substr($text, 0, $cutpos).' ...';
        }

        return $text;
    }
}
