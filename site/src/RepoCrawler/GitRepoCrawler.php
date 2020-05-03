<?php

namespace App\RepoCrawler;

use App\Entity\Project;
use App\Util\FileUtil;
use App\Util\MarkupToHtmlConverter;
use App\Util\ProcessCreator;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use App\Service\ProjectMetricsProvider;
use App\Entity\Commit;
use App\Entity\LanguageStat;
use App\Entity\ProjectRelease;
use App\Entity\SourceRepo;
use App\Repository\CommitRepository;
use App\Repository\ContributorRepository;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Crawl and extract metadata from a remote git repository
 *
 * This implementation performs a clone of the git repository
 * and uses ordinary git commands to fetch metadata
 */
class GitRepoCrawler extends AbstractRepoCrawler
{
    /**
     * Git clone timeout in seconds
     *
     * @internal
     *
     * @var int
     */
    private const TIMEOUT_GIT_CLONE = 3 * 60;

    /**
     * Git log timeout in seconds
     *
     * @internal
     *
     * @var int
     */
    private const TIMEOUT_GIT_LOG = 5 * 60;

    /**
     * Case-insensitive basenames without file extensions of files used for the
     * full-text of the license in a repository.
     *
     * @var array
     */
    private const FILES_LICENSE = ['LICENSE', 'COPYING'];

    /**
     * Case-insensitive basenames without file extensions of files used for
     * the full-text of the description in a repository.
     *
     * @var array
     */
    private const FILES_DESCRIPTION = ['README'];

    /**
     * File extensions we recognize as valid content for license and description
     * texts.
     *
     * Order matters! Put the highest priority file types at the top.
     * List from https://github.com/github/markup#markups
     *
     * @var array
     *
     * @see self::FILES_LICENSE
     * @see self::FILES_DESCRIPTION
     */
    const FILE_EXTENSIONS = [
        '.markdown',
        '.mdown',
        '.mkdn',
        '.md',
        '.textile',
        '.rdoc',
        '.org',
        '.creole',
        '.mediawiki',
        '.wiki',
        '.rst',
        '.asciidoc',
        '.adoc',
        '.asc',
        '.pod',
        '.pod6',
        '.txt',
        '',
    ];

    /**
     * @var MarkupToHtmlConverter
     */
    private $markupConverter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProcessCreator
     */
    private $processCreator;

    /**
     * @var CommitRepository
     */
    private $commitRepository;

    /**
     * @var ProjectMetricsProvider
     */
    private $projectMetricsProvider;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var ContributorRepository
     */
    private $contributorRepository;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @inheritDoc
     */
    public function __construct(
        MarkupToHtmlConverter $markupConverter,
        ProcessCreator $processCreator,
        CommitRepository $commitRepository,
        ContributorRepository $contributorRepository,
        ObjectManager $manager,
        LoggerInterface $logger,
        ProjectMetricsProvider $projectMetricsProvider,
        Filesystem $filesystem
    ) {
        $this->markupConverter = $markupConverter;
        $this->processCreator = $processCreator;
        $this->commitRepository = $commitRepository;
        $this->contributorRepository = $contributorRepository;
        $this->manager = $manager;
        $this->logger = $logger;
        $this->projectMetricsProvider = $projectMetricsProvider;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritDoc}
     * @see AbstractRepoCrawler::isValidRepoType()
     */
    public function canProcess(Project $project): bool
    {
        return SourceRepo::REPO_TYPE_GIT === $project->getSourceRepo()->getType();
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function update(Project $project)
    {
        $repo = $project->getSourceRepo();

        $lastCommit = $this->commitRepository->getLatestCommit($repo);

        $repoDir = $this->cloneRepo($repo);

        // determine if our latest commit exists and fetch new commits since
        // what we have on DB
        if ($lastCommit && $this->commitExists($repoDir, $lastCommit->getCommitId())) {
            $commitCount = $this->updateCommits($repoDir, $repo, $lastCommit->getCommitId());
        } else {
            // there has been a history rewrite
            // we drop everything and persist all commits to the DB
            // XXX: Find a way to find the common ancestor and do partial rewrites
            $this->commitRepository->removeAllCommits($repo);
            $repo->getCommits()->clear();
            $commitCount = $this->updateCommits($repoDir, $repo);
        }

        if ($commitCount > 0) {
            $this->countLinesOfCode($repoDir, $repo);
        }

        if ($project->getDescriptionTextAutoUpdate()) {
            $project->setDescriptionText($this->getDescriptionSafeHtml($repoDir));
        }
        if ($project->getLicenseTextAutoUpdate()) {
            $project->setLicenseText($this->getLicenseTextSafeHtml($repoDir));
        }

        $this->updateReleases($repoDir, $project);


        $this->manager->persist($repo);

        // we need a explicit flush here because we query commit data later
        $this->manager->flush();

        $latestCommit = $this->commitRepository->getLatestCommit($project->getSourceRepo());

        if ($latestCommit) {
            $project->setDateLastActivityOccurred($latestCommit->getDateCommitted());
        }

        // Retrieve the code quality score for the project and persist it in the database
        $projectMetrics = $this->projectMetricsProvider->getCodeQualityScore($project);
        $qualityScore = $projectMetrics * 100;
        $project->setQualityScore($qualityScore);

        $this->manager->persist($project);

        $this->logger->debug('Cleaning up repo clone directory '. $repoDir);

        $this->filesystem->remove($repoDir);

        return true;
    }

    /**
     * Checks whether the given commit ID exists on the default tree of the
     * repository
     *
     * @param string $repoDir
     * @param string $commitId ID of the commit to search
     *
     * @return bool commit exists in the tree ?
     */
    private function commitExists(string $repoDir, string $commitId): bool
    {
        // Stolen from https://stackoverflow.com/a/13526591
        $this->logger->debug('Checking commits in '.$repoDir);

        $cmd = ['git', 'merge-base', '--is-ancestor', $commitId, 'HEAD'];
        $process = $this->processCreator->createProcess($cmd, $repoDir);
        $this->executeProcess($process);
        $code = $process->getExitCode();

        if (0 === $code) {
            $this->logger->debug("Commit $commitId exists in the default branch of $repoDir");
            return true;
        } else if (1 === $code || 128 === $code) {
            $this->logger->debug("Commit $commitId does not exist in $repoDir");
            return false;
        } else {
            throw new RuntimeException(
                sprintf(
                    "Unable to fetch commits from %s: %s",
                    $repoDir,
                    $process->getErrorOutput()
                )
            );
        }
    }

    /**
     * Get all commits in the repository since a specified commit ID or all if
     * not specified.
     *
     * @param string      $repoDir
     * @param SourceRepo  $repo
     * @param string|null $sinceCommitId ID of commit after which the commits are to be
     *                                   returned
     *
     * @return int Commits updated
     *
     * @throws Exception
     */
    protected function updateCommits(string $repoDir, SourceRepo $repo, ?string $sinceCommitId = null): int
    {
        $this->logger->debug("Fetching commits for the repository {$repo->getId()} of project {$repo->getProject()->getFqname()}");

        $cmd = [
            'git',
            'log',
            '--reverse',
            '--format=%H|%aN|%aE|%aD',
            '--shortstat',
        ];
        if (null !== $sinceCommitId) {
            $cmd[] = $sinceCommitId;
            $cmd[] = '...';
        }

        $this->logger->debug("Fetching commits in $repoDir");

        $process = $this->processCreator->createProcess($cmd, $repoDir);
        $process->setTimeout(static::TIMEOUT_GIT_LOG);
        $this->mustExecuteProcess($process);
        $output = $process->getOutput();
        $this->logger->debug("Fetched commits from $repoDir");

        return $this->parseCommits($output, $repo);
    }

    /**
     * Crawl a repositories' source code and count lines of code in each language
     *
     * Implementation uses Cloc: https://github.com/AlDanial/cloc
     *
     * @param string     $repoDir
     * @param SourceRepo $repo
     */
    protected function countLinesOfCode(string $repoDir, SourceRepo $repo)
    {
        $cmd = [
            'cloc',
            '--json',
            '--skip-uniqueness',
            $repoDir,
        ];
        $process = $this->processCreator->createProcess($cmd);

        $this->mustExecuteProcess($process);
        $result = $process->getOutput();

        $cloc = json_decode($result, true);

        $sourceStats = $repo->getSourceStats();
        $sourceStats->setAvailable(true)
            ->setTotalFiles($cloc['header']['n_files'])
            ->setTotalLinesOfCode($cloc['SUM']['code'])
            ->setTotalBlankLines($cloc['SUM']['blank'])
            ->setTotalLinesOfComments($cloc['SUM']['comment']);

        unset($cloc['header'], $cloc['SUM']);

        foreach ($cloc as $lang => $value) {
            $languageStat = new LanguageStat();

            $languageStat->setLanguage($lang)
                ->setFileCount($value['nFiles'])
                ->setLinesOfCode($value['code'])
                ->setCommentLineCount($value['comment'])
                ->setBlankLineCount($value['blank']);
            $sourceStats->addLanguageStat($languageStat);
        }

        $repo->setSourceStats($sourceStats);
        $this->manager->persist($repo);
    }

    /**
     * Get the description of the repository as safe HTML
     *
     * Usually this is the content of the README file.
     *
     * "Safe" HTML is stripped from all possibly malicious content, such as
     * script tags, etc.
     *
     * @param string $repoDir
     *
     * @return string|null the repository description, or null if none was found
     */
    protected function getDescriptionSafeHtml(string $repoDir): ?string
    {
        $descriptionFile = FileUtil::findFile(
            $repoDir,
            self::FILES_DESCRIPTION,
            self::FILE_EXTENSIONS,
            false // case insensitive
        );

        if ($descriptionFile === false) {
            $this->logger->debug('No description file found in the repository.');

            return null;
        }

        $this->logger->debug('Using file '.$descriptionFile.' as description.');

        try {
            $sanitizedHtml = $this->markupConverter->convertFile($descriptionFile);
        } catch (Exception $e) {
            $this->logger->error(
                "Unable to convert $descriptionFile to HTML ".
                "for license text."
            );

            return null;
        }

        return $sanitizedHtml;
    }

    /**
     * Get the license text of the repository as safe HTML
     *
     * Usually this license text is taken from the LICENSE or COPYING files.
     *
     * "Safe" HTML is stripped from all possibly malicious content, such as
     * script tags, etc.
     *
     * @param string $repoDir
     *
     * @return string|null the license text, or null if none was found
     */
    protected function getLicenseTextSafeHtml(string $repoDir): ?string
    {
        $licenseFile = FileUtil::findFile(
            $repoDir,
            self::FILES_LICENSE,
            self::FILE_EXTENSIONS,
            false // case insensitive
        );

        if ($licenseFile === false) {
            $this->logger->debug('Found no file containing the license text.');

            return null;
        }

        $this->logger->debug("Using file $licenseFile as license text.");

        try {
            $sanitizedHtml = $this->markupConverter->convertFile($licenseFile);
        } catch (Exception $e) {
            $this->logger->error(
                "Unable to convert $licenseFile to HTML for license text."
            );

            return null;
        }

        return $sanitizedHtml;
    }

    /**
     * Update project releases
     *
     * @param Project $project
     * @param         $repoDir
     *
     * @return bool if successful
     */
    private function updateReleases($repoDir, Project $project)
    {
        $this->logger->debug("Updating releases in $repoDir for {$project->getFqname()}");

        $cmd = [
            'git',
            'tag',
            '--sort=-creatordate',
            '--format=%(refname:strip=2)|%(objectname:short)|%(creatordate)',
        ];
        $process = $this->processCreator->createProcess($cmd, $repoDir);
        $process->setTimeout(static::TIMEOUT_GIT_LOG);

        try {
            $this->mustExecuteProcess($process);
            $output = $process->getOutput();
        } catch (Exception $e) {
            $this->logger->error(
                "Unable to fetch releases from $repoDir. Process execution "
                ."failed: ".$e->getMessage()
            );

            return false;
        }

        $tagRegex = '/^([\w-]*v?.*\d+\.\d+.*)\|([[:xdigit:]]+)\|(.+)$/i';
        $preReleaseRegex = '/.*[-_\.](alpha|beta|RC-?\d+).*/i';

        $lines = explode("\n", trim($output));
        $releases = [];

        foreach ($lines as $line) {
            try {
                if (preg_match($tagRegex, $line, $matches)) {
                    $release = new ProjectRelease();
                    $release->setName($matches[1])
                        ->setPublishedAt(new DateTime($matches[3]))
                        ->setCommitID($matches[2])
                        ->setIsPrerelease(preg_match($preReleaseRegex, $matches[1]));
                    $releases[] = $release;
                }
            } catch (Exception $e) {
                $this->logger->warning("Skipped release tag due to parse error: $line");
            }
        }

        $project->setReleases($releases);
        $this->manager->persist($project);

        $count = count($releases);
        $this->logger->debug("Fetched $count releases from $repoDir");

        return true;
    }

    /**
     * Clone a repository
     *
     * @param SourceRepo $repo
     *
     * @return string path to the directory where we clone the repo
     *
     */
    private function cloneRepo(SourceRepo $repo)
    {
        $repoUrl = $repo->getUrl();
        $repoClonePath = FileUtil::createTemporaryDirectory('lc-gitrepocrawler-');

        $this->logger->debug("Cloning repository $repoUrl for {$repo->getProject()->getFqname()}");

        $cmd = ['git', 'clone', $repoUrl, $repoClonePath];
        $process = $this->processCreator->createProcess($cmd);
        $process->setTimeout(static::TIMEOUT_GIT_CLONE);
        $this->mustExecuteProcess($process);
        $this->logger->debug("Cloned repository $repoUrl to $repoClonePath");

        return $repoClonePath;
    }

    /**
     * Parse commits from the output from git
     *
     * @param string     $outputString raw output from git
     *
     * @param SourceRepo $repo
     *
     * @return int
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function parseCommits(string $outputString, SourceRepo $repo): int
    {
        $outputString = preg_replace('/^\h*\v+/m', '', trim($outputString));    // remove blank lines
        $output = explode("\n", $outputString);  // explode lines into array

        $commits = []; // stores the array of commits
        $len = count($output);
        for ($i = 0; $i < $len; $i++) {
            // Every commit has 4 parts, id, author name, email, commit timestamp
            // in the format id|name|email|timestamp
            // followed by an optional line for modifications
            $commitMatches = [];
            if (preg_match('/^([\da-f]+)\|(.+)\|(.+@.+)\|(.+)$/', $output[$i], $commitMatches)) {
                $contributor = $this->contributorRepository
                    ->getContributorForRepository(
                        $repo,
                        $commitMatches[3],
                        $commitMatches[2]
                    );
                $date = new DateTime($commitMatches[4]);
                $date->setTimezone(new DateTimeZone('UTC'));
                $commit = new Commit();
                $commit
                    ->setCommitId($commitMatches[1])
                    ->setSourceRepo($repo)
                    ->setDateCommitted($date)
                    ->setContributor($contributor);

                $modificationMatches = [];
                if ($i < $len - 1 &&
                    preg_match(
                        '/(\d+) files? changed(?:, (\d+) insertions?\(\+\))?(?:, (\d+) deletions?\(-\))?/',
                        $output[$i + 1],
                        $modificationMatches
                    )) {
                    $commit->setFilesModified($modificationMatches[1]);

                    if (array_key_exists(2, $modificationMatches) && strlen($modificationMatches[2])) {
                        $commit->setLinesAdded($modificationMatches[2]);
                    }
                    if (array_key_exists(3, $modificationMatches) && strlen($modificationMatches[3])) {
                        $commit->setLinesRemoved($modificationMatches[3]);
                    }
                    $i++;   // skip the next line
                }
                $this->manager->persist($commit);
                $commits[] = $commit;
            }
        }
        $commitsUpdated = count($commits);

        $this->logger->debug("Updated $commitsUpdated commits");

        return $commitsUpdated;
    }

    /**
     * Helper for executing a process
     *
     * @param Process $process
     */
    private function executeProcess(Process $process)
    {
        $this->logger->debug('Executing '.$process->getCommandLine().' in '.$process->getWorkingDirectory());
        $process->run();
        $this->logger->debug('Process exited with status '.$process->getExitCode());
    }

    /**
     * Helper to execute a process and throw exception if any error occurs.
     *
     * @param Process $process
     */
    private function mustExecuteProcess(Process $process)
    {
        $this->logger->debug('Executing '.$process->getCommandLine().' in '.$process->getWorkingDirectory());
        $process->mustRun();
        $this->logger->debug('Process exited with status '.$process->getExitCode());
    }
}
