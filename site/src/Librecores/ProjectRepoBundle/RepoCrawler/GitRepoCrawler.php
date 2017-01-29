<?php
namespace Librecores\ProjectRepoBundle\RepoCrawler;

use Librecores\ProjectRepoBundle\Util\FileUtil;
use Symfony\Component\Process\Process;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;

class GitRepoCrawler extends RepoCrawler
{
    /** git clone timeout in seconds */
    const TIMEOUT_GIT_CLONE = 3*60;

    /**
     * Case-insensitive basenames without file extensions of files used for the
     * full-text of the license in a repository.
     *
     * @var array
     */
    const FILES_LICENSE = [ 'LICENSE', 'COPYING' ];

    /**
     * Case-insensitive basenames without file extensions of files used for
     * the full-text of the description in a repository.
     *
     * @var array
     */
    const FILES_DESCRIPTION = [ 'README' ];

    /**
     * File extensions we recognize as valid content for license and description
     * texts.
     *
     * Order matters! Put the highest priority file types at the top.
     * List from https://github.com/github/markup#markups
     *
     * @var array
     * @see self::FILES_LICENSE
     * @see self::FILES_DESCRIPTION
     */
    const FILE_EXTENSIONS = [
        '.markdown', '.mdown', '.mkdn', '.md',
        '.textile',
        '.rdoc',
        '.org',
        '.creole',
        '.mediawiki', '.wiki',
        '.rst',
        '.asciidoc', '.adoc', '.asc',
        '.pod',
        '.txt',
        ''];

    private $repoClonePath = null;

    /**
     * Destructor: clean up
     */
    public function __destruct()
    {
        if ($this->repoClonePath !== null) {
            $this->logger->debug('Cleaning up repo clone directory '.$this->repoClonePath);
            FileUtil::recursiveRmdir($this->repoClonePath);
        }
    }

    /**
     * {@inheritDoc}
     * @see RepoCrawler::isValidRepoType()
     */
    protected function isValidRepoType(): bool
    {
        return $this->repo instanceof GitSourceRepo;
    }

    /**
     * Clone a repository
     *
     * @throws \RuntimeException
     */
    private function cloneRepo()
    {
        $repoUrl = $this->repo->getUrl();
        $this->repoClonePath = FileUtil::createTemporaryDirectory('lc-gitrepocrawler-');

        $cmd = 'git clone '.escapeshellarg($repoUrl).' '.escapeshellarg($this->repoClonePath);
        $this->logger->info('Cloning repository: '.$cmd);
        $process = new Process($cmd);
        $process->setTimeout(self::TIMEOUT_GIT_CLONE);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException("Unable to clone git repository: ".$process->getErrorOutput());
        }
        $this->logger->debug("Cloned repository $repoUrl");
    }

    /**
     * Get the path to the cloned repository
     *
     * If not yet available the repository will be cloned first.
     *
     * @return string
     */
    protected function getRepoClonePath()
    {
        if ($this->repoClonePath === null) {
            $this->cloneRepo();
        }
        return $this->repoClonePath;
    }

    /**
     * {@inheritDoc}
     * @see RepoCrawler::getDescriptionSafeHtml()
     */
    public function getDescriptionSafeHtml(): ?string
    {
        $descriptionFile = FileUtil::findFile($this->getRepoClonePath(),
                                              self::FILES_DESCRIPTION,
                                              self::FILE_EXTENSIONS);

        if ($descriptionFile === false) {
            $this->logger->debug('No description file found in the repository.');
            return null;
        }

        $this->logger->debug('Using file '.$descriptionFile.' as description.');

        try {
            $sanitizedHtml = $this->markupConverter->convertFile($descriptionFile);
        } catch (\Exception $e) {
            $this->logger->error("Unable to convert $descriptionFile to HTML ".
                                 "for license text.");
            return null;
        }

        return $sanitizedHtml;
    }

    /**
     *
     * {@inheritDoc}
     * @see RepoCrawler::getLicenseTextSafeHtml()
     */
    public function getLicenseTextSafeHtml(): ?string
    {
        $licenseFile = FileUtil::findFile($this->getRepoClonePath(),
            self::FILES_LICENSE,
            self::FILE_EXTENSIONS);

        if ($licenseFile === false) {
            $this->logger->debug('Found no file containing the license text.');
            return null;
        }

        $this->logger->debug("Using file $licenseFile as license text.");

        try {
            $sanitizedHtml = $this->markupConverter->convertFile($licenseFile);
        } catch (\Exception $e) {
            $this->logger->error("Unable to convert $licenseFile.' to HTML ".
                                 "for license text.");
            return null;
        }

        return $sanitizedHtml;
    }
}
