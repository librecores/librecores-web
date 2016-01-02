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

        return $this->render('LibrecoresPlanetBundle:Default:index.html.twig',
            $templateArgs
        );
    }

    /**
     * Process all blog posts from the planet feed
     */
    private function processAtomFeed()
    {
        $atomFeed = $this->get('kernel')->getRootDir().'/../web/planet/atom.xml';

        $feed = $this->get('fkr_simple_pie.rss');

        $feed->set_feed_url($atomFeed);
        $feed->enable_order_by_date(false);
        $feed->init();

        $updatedString = $feed->get_feed_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'updated')[0]['data'];
        $this->feedLastUpdate = \DateTime::createFromFormat(\DateTime::ATOM, $updatedString, new \DateTimeZone('UTC'));

        $this->blogposts = array();
        foreach ($feed->get_items(0) as $item) {
            $this->blogposts[] = array(
                'author' => $item->get_author()->get_name(),
                'date' => \DateTime::createFromFormat('U', $item->get_gmdate('U'), new \DateTimeZone('UTC')),
                'title' => $item->get_title(),
                'teaser' => $this->extractTeaser($item->get_content()),
                'teaser_img_url' => $this->extractTeaserImage($item->get_content()),
                'url' => $item->get_link(),
                'source_title' => $item->get_source()->get_title(),
                'source_link' => $item->get_source()->get_link(),
            );
        }
    }

    /**
     * Extract a good teaser image from the blog content
     *
     * Currently this method is rather dumb: it simply takes the first image
     * it finds, and the HTML parsing is not very evolved, either.
     *
     * @param string $content input data (HTML)
     * @return string|null the image URL, or NULL if no image was found
     */
    private function extractTeaserImage($content)
    {
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
     */
    private function extractTeaser($content)
    {
        $maxLength = 500;

        $content = strip_tags($content);
        $content = html_entity_decode($content);
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
