<?php

namespace App\Util;
use \HTMLPurifier_URIFilter;
use \HTMLPurifier_URI;

/**
 * HTML Purifier Filter: produce absolute GitHub URLs
 *
 * This class is based on HTMLPurifier_URIFilter_MakeAbsolute.
 *
 * See http://htmlpurifier.org/docs/enduser-uri-filter.html for documentation
 * on this API.
 *
 * Does not support network paths.
 */
class GithubUrlPurifierFilter extends \HTMLPurifier_URIFilter
{
    /**
     * @type string
     */
    public $name = 'GithubUrl';

    /**
     * GitHub repository owner
     *
     * @var string
     */
    protected $ghOwner;

    /**
     * GitHub repository name
     *
     * @var string
     */
    protected $ghRepo;

    public function __construct($ghOwner, $ghRepo)
    {
        $this->ghOwner = $ghOwner;
        $this->ghRepo = $ghRepo;
    }

    /**
     * Get GitHub base URI for iamges
     */
    private function getImgBaseUri() : \HTMLPurifier_URI
    {
        return new \HTMLPurifier_URI('https', null, 'raw.github.com',
            null, '/'.$this->ghOwner.'/'.$this->ghRepo.'/HEAD/', null, null);
    }

    /**
     * Get GitHub base URI for non-image content
     */
    private function getContentBaseUri() : \HTMLPurifier_URI
    {
        return new \HTMLPurifier_URI('https', null, 'github.com', null,
            '/'.$this->ghOwner.'/'.$this->ghRepo.'/blob/HEAD/', null, null);
    }

    /**
     * Process an URI
     *
     * @param HTMLPurifier_URI $uri A reference to the URL to be filtered
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool true if the filtering was successful, or false if the
     *              URI is beyond repair and needs to be axed.
     */
    public function filter(&$uri, $config, $context)
    {
        // get base URI
        $token = $context->get('CurrentToken', true);
        if ($token && $token->name == 'img') {
            $base = $this->getImgBaseUri();
        } else {
            $base = $this->getContentBaseUri();
        }

        // prepare base path segments
        $stack = explode('/', $base->path);
        array_pop($stack); // discard last segment
        $basePathStack = $this->_collapseStack($stack);

        if ($uri->path === '' && is_null($uri->scheme) &&
            is_null($uri->host) && is_null($uri->query) && is_null($uri->fragment)) {
            // reference to current document
            $uri = clone $base;
            return true;
        }
        if (!is_null($uri->scheme)) {
            // absolute URI already: don't change
            if (!is_null($uri->host)) {
                return true;
            }
            $scheme_obj = $uri->getSchemeObj($config, $context);
            if (!$scheme_obj) {
                // scheme not recognized
                return false;
            }
            if (!$scheme_obj->hierarchical) {
                // non-hierarchal URI with explicit scheme, don't change
                return true;
            }
            // special case: had a scheme but always is hierarchical and had no authority
        }
        if (!is_null($uri->host)) {
            // network path, don't bother
            return true;
        }
        if ($uri->path === '') {
            $uri->path = $base->path;
        } else {
            // relative path, needs more complicated processing
            $stack = explode('/', $uri->path);
            $new_stack = array_merge($basePathStack, $stack);
            if ($new_stack[0] !== '' && !is_null($base->host)) {
                array_unshift($new_stack, '');
            }
            $new_stack = $this->_collapseStack($new_stack);
            $uri->path = implode('/', $new_stack);
        }
        // re-combine
        $uri->scheme = $base->scheme;
        if (is_null($uri->userinfo)) {
            $uri->userinfo = $base->userinfo;
        }
        if (is_null($uri->host)) {
            $uri->host = $base->host;
        }
        if (is_null($uri->port)) {
            $uri->port = $base->port;
        }

        // Fragment identifiers are prefixed with "user-content-" in GitHub.
        if ($uri->fragment !== null) {
            $uri->fragment = 'user-content-'.$uri->fragment;
        }
        return true;
    }

    /**
     * Resolve dots and double-dots in a path stack
     *
     * @param array $stack
     * @return array
     */
    private function _collapseStack($stack)
    {
        $result = array();
        $is_folder = false;
        for ($i = 0; isset($stack[$i]); $i++) {
            $is_folder = false;
            // absorb an internally duplicated slash
            if ($stack[$i] == '' && $i && isset($stack[$i + 1])) {
                continue;
            }
            if ($stack[$i] == '..') {
                if (!empty($result)) {
                    $segment = array_pop($result);
                    if ($segment === '' && empty($result)) {
                        // error case: attempted to back out too far:
                        // restore the leading slash
                        $result[] = '';
                    } elseif ($segment === '..') {
                        $result[] = '..'; // cannot remove .. with ..
                    }
                } else {
                    // relative path, preserve the double-dots
                    $result[] = '..';
                }
                $is_folder = true;
                continue;
            }
            if ($stack[$i] == '.') {
                // silently absorb
                $is_folder = true;
                continue;
            }
            $result[] = $stack[$i];
        }
        if ($is_folder) {
            $result[] = '';
        }
        return $result;
    }
}

// vim: et sw=4 sts=4
