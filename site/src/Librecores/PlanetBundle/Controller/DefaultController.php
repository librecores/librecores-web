<?php

namespace Librecores\PlanetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    private $blogposts = array();
    private $feedLastUpdate = null;

    public function indexAction()
    {
        $this->processAtomFeed();

        $templateArgs = array();
        $templateArgs['blogposts'] = $this->blogposts;
        $templateArgs['feedLastUpdate'] = $this->feedLastUpdate;

        return $this->render(
            'planet/index.html.twig',
            $templateArgs
        );
    }

    /**
     * Process all blog posts from the planet feed
     */
    private function processAtomFeed()
    {
        $atomFeed = $this->get('kernel')->getRootDir().'/../web/planet/atom.xml';

        $feed = new \SimplePie();
        $feed->set_cache_duration(3600);
        $feed->set_cache_location($this->get('kernel')->getCacheDir().'/rss');
        $feed->enable_cache(true);
        $feed->set_feed_url($atomFeed);
        $feed->enable_order_by_date(false);
        $feed->init();

        $feedTags = $feed->get_feed_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'updated');
        $updatedString = $feedTags[0]['data'];
        $this->feedLastUpdate = \DateTime::createFromFormat(
            \DateTime::ATOM,
            $updatedString,
            new \DateTimeZone('UTC')
        );

        $this->blogposts = array();
        foreach ($feed->get_items(0) as $item) {
            $authorName = $this->decodeHtmlEntities(
                $item->get_author()->get_name()
            );
            $blogDate = \DateTime::createFromFormat(
                'U',
                $item->get_gmdate('U'),
                new \DateTimeZone('UTC')
            );
            $sourceTitle = $this->decodeHtmlEntities(
                $item->get_source()->get_title()
            );
            $this->blogposts[] = array(
                'author' => $authorName,
                'date' => $blogDate,
                'title' => $this->decodeHtmlEntities($item->get_title()),
                'teaser' => $this->extractTeaser($item->get_content()),
                'teaser_img_url' => $this->extractTeaserImage($item->get_content()),
                'url' => $item->get_link(),
                'source_title' => $sourceTitle,
                'source_link' => $item->get_source()->get_link(),
            );
        }
    }

    /**
     * Decode HTML entities into an UTF-8 string
     *
     * Use this method instead of calling html_entity_decode() directly to benefit
     * from setting the necessary parameters only once.
     *
     * @param string $string
     *
     * @return string string in UTF-8 format with all XML entities removed
     */
    private function decodeHtmlEntities($string)
    {
        return html_entity_decode($string, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * Extract a good teaser image from the blog content
     *
     * Currently this method is rather dumb: it simply takes the first image
     * it finds, and the HTML parsing is not very evolved, either.
     *
     * @param string $content input data (HTML)
     *
     * @return string|null the image URL, or NULL if no image was found
     */
    private function extractTeaserImage($content)
    {
        $matches = array();
        $rv = preg_match('/<img[^>]+src=(["\'])([^">]+)\1/i', $content, $matches);
        if (!$rv) {
            return null;
        }

        return $matches[2];
    }

    /**
     * Extract a teaser from the blog content
     *
     * XXX: We should be much smarter here. End at a full sentence/paragraph
     *   (if possible), selectively allow HTML, etc.
     *
     * @param string $content input data (HTML)
     *
     * @return string a up to 500 characters long teaser for the content
     */
    private function extractTeaser($content)
    {
        $maxLength = 500;

        $content = strip_tags($content);
        $content = $this->decodeHtmlEntities($content);
        $content = trim($content);

        // If the text is longer than $maxLength, we try to cut it at the
        // closest white space character, and suffix it with ' ...'.
        if ($maxLength !== -1 && mb_strlen($content) > $maxLength) {
            // cut string to $maxLength - strlen(" ...")
            $content = mb_substr($content, 0, $maxLength - 4);

            // cut at whitespace
            $cutpos = mb_strrpos($content, ' ');
            if ($cutpos !== false) {
                $content = mb_substr($content, 0, $cutpos + 1);
            }
            $content .= ' ...';
        }

        return $content;
    }
}
