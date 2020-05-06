<?php

namespace App\Util;

use App\Util\MarkupToHtmlConverter;
use App\Util\GithubUrlPurifierFilter;
use \HTMLPurifier;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Convert GitHub markup to HTML
 *
 * GitHub-hosted markup files can contain relative links to content and
 * images, which are interpreted as relative to the repository. These links
 * are converted into absolute links.
 * See
 * https://help.github.com/en/github/creating-cloning-and-archiving-repositories/about-readmes#relative-links-and-image-paths-in-readme-files
 */
class GithubMarkupToHtmlConverter extends MarkupToHtmlConverter
{
    protected $ghOwner;
    protected $ghRepo;

    /**
     * Default constructor
     *
     * @param string $ghOwner owner (user or organization) of the GH repository
     * @param string $ghRepo GitHub repository name
     * @param string $cacheDir cache directory to be used by the converter.
     *                         The directory must exist.
     * @param LoggerInterface $logger
     */
    public function __construct($ghOwner, $ghRepo, $cacheDir, LoggerInterface $logger)
    {
        parent::__construct($cacheDir, $logger);
        $this->ghOwner = $ghOwner;
        $this->ghRepo = $ghRepo;
    }

    /**
     * {@inheritDoc}
     */
    protected function getHtmlPurifierConfig() : \HTMLPurifier_Config
    {
        $config = parent::getHtmlPurifierConfig();

        $ghUrlFilter = new GithubUrlPurifierFilter($this->ghOwner, $this->ghRepo);
        $uri = $config->getDefinition('URI');
        $uri->addFilter($ghUrlFilter, $config);

        return $config;
    }
}
