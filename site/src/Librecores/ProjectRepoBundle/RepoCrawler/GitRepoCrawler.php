<?php
namespace Librecores\ProjectRepoBundle\RepoCrawler;

use Librecores\ProjectRepoBundle\Entity\Commit;
use Librecores\ProjectRepoBundle\Util\FileUtil;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;

/**
 * Crawls and extracts metadata from a remote git repository
 *
 * This implementation performs a clone of the git repository
 * and uses ordinary git commands to fetch metadata
 */
class GitRepoCrawler extends RepoCrawler
{
    /**
     * Git clone timeout in seconds
     *
     * @internal
     * @var int
     */
    const TIMEOUT_GIT_CLONE = 3*60;

    /**
     * Git log timeout in seconds
     *
     * @internal
     * @var int
     */
    const TIMEOUT_GIT_LOG = 60;

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

        $this->logger->info('Cloning repository: ' . $repoUrl);

        $args = ['clone', $repoUrl, $this->repoClonePath];
        $this->executor->exec('git', $args, ['timeout' => self::TIMEOUT_GIT_CLONE]);
        $this->logger->debug('Cloned repository ' . $repoUrl);
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

    /**
     * {@inheritDoc}
     * @see RepoCrawler::fetchCommits()
     */
    public function fetchCommits(?string $sinceId = null)
    {

        $args = ['log', '--reverse', '--format=%h|%aN|%aE|%aD', '--shortstat'];

        if (null !== $sinceId) {
            // we don't need escapeshellargs here
            // it is performed internally by ProcessBuilder
            $args[] = $sinceId;
            $args[] = '...';
        }

        $cwd = $this->getRepoClonePath();

        $this->logger->info("Fetching commits in $cwd");

        $output = $this->executor->exec('git', $args, [
            'cwd' => $cwd,
            'timeout' => self::TIMEOUT_GIT_LOG
        ]);

        $this->logger->debug("Fetched commits from $cwd");
        $this->parseCommits($output);
    }

    /**
     * {@inheritDoc}
     * @see RepoCrawler::commitExists()
     */
    public function commitExists(string $id) : bool
    {
        // Stolen from https://stackoverflow.com/a/13526591
        $args = [ 'merge-base', '--is-ancestor', $id, 'HEAD' ];
        $cwd = $this->getRepoClonePath();

        $this->logger->info('Checking commits in ' . $cwd);

        $this->executor->exec('git', $args, [
            'timeout' => self::TIMEOUT_GIT_LOG,
            'cwd' => $cwd,
            'errors' => true
        ], $code, $errors);
        switch ($code)
        {
            case 0:     // successful, commit exists
            case 1:     // commit does not exist in branch
            case 128:   // commit does not exist in repository
                $this->logger->debug("Checked commits from $cwd");
                return $code === 0;
            default:    // anything other than 0 or 1 is error
                //die($code);
                throw new \RuntimeException("Unable to fetch commits from $cwd : ".$errors);
        }
    }

    /**
     * Run configured source crawlers on the repository.
     */
    public function runCrawlers()
    {
        foreach ($this->sourceCrawlers as $crawler) {
            $crawler->crawl($this->repo, $this->getRepoClonePath());
        }
    }

    /**
     * Parses commits from the output from git
     *
     * @param string $outputString raw output from git
     */
    private function parseCommits(string $outputString)
    {
        $this->logger->info('Parsing commits for repo ' . $this->getRepoClonePath());

        $outputString = preg_replace('/^\h*\v+/m', '', trim($outputString));    // remove blank lines
        $output = explode("\n",$outputString);  // explode lines into array

        $commits = []; // stores the array of commits
        $len = count($output);
        for ($i = 0; $i < $len; $i++) {

            // Every commit has 4 parts, id, author name, email, commit timestamp
            // in the format id|name|email|timestamp
            // followed by an optional line for modifications
            $commitMatches = [];
            if(preg_match('/^([\da-f]+)\|(.+)\|(.+@.+)\|(.+)$/', $output[$i], $commitMatches)) {
                $contributor = $this->manager->getRepository('LibrecoresProjectRepoBundle:Contributor')->getContributorForRepository($this->repo, $commitMatches[3], $commitMatches[2]);
                $date = new \DateTime($commitMatches[4]);
                $date->setTimezone(new \DateTimeZone('UTC'));
                $commit = new Commit();
                $commit->setCommitId($commitMatches[1])
                    ->setRepository($this->repo)
                    ->setDateCommitted($date)
                    ->setContributor($contributor);

                $modificationMatches = [];
                if ($i < $len - 1 && preg_match('/(\d+) files? changed(?:, (\d+) insertions?\(\+\))?(?:, (\d+) deletions?\(-\))?/',
                        $output[$i + 1], $modificationMatches)) {
                    $commit->setFilesModified($modificationMatches[1]);

                    if(array_key_exists(2, $modificationMatches) && strlen($modificationMatches[2])) {
                        $commit->setLinesAdded($modificationMatches[2]);
                    }
                    if(array_key_exists(3, $modificationMatches) && strlen($modificationMatches[3])) {
                        $commit->setLinesRemoved($modificationMatches[3]);
                    }
                    $i++;   // skip the next line
                }
                $this->manager->persist($commit);
                $commits[] = $commit;
            }
        }

        $this->logger->debug('Parsed ' . count($commits) . ' commits for repo ' . $this->getRepoClonePath());
    }
}
