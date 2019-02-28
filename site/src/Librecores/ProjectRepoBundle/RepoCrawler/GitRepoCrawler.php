<?php

namespace Librecores\ProjectRepoBundle\RepoCrawler;

use Librecores\ProjectRepoBundle\Entity\Commit;
use Librecores\ProjectRepoBundle\Entity\Contributor;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;
use Librecores\ProjectRepoBundle\Entity\LanguageStat;
use Librecores\ProjectRepoBundle\Entity\ProjectRelease;
use Librecores\ProjectRepoBundle\Util\FileUtil;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Util\StatsUtil;
use Librecores\ProjectRepoBundle\Util\Dates;

/**
 * Crawl and extract metadata from a remote git repository
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
     *
     * @var int
     */
    const TIMEOUT_GIT_CLONE = 3 * 60;

    /**
     * Git log timeout in seconds
     *
     * @internal
     *
     * @var int
     */
    const TIMEOUT_GIT_LOG = 60;

    /**
     * Case-insensitive basenames without file extensions of files used for the
     * full-text of the license in a repository.
     *
     * @var array
     */
    const FILES_LICENSE = ['LICENSE', 'COPYING'];

    /**
     * Case-insensitive basenames without file extensions of files used for
     * the full-text of the description in a repository.
     *
     * @var array
     */
    const FILES_DESCRIPTION = ['README'];

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

    private $repoClonePath = null;

    /**
     * Clean up the resources used by this repository
     */
    public function __destruct()
    {
        if ($this->repoClonePath === null) {
            return;
        }
        $this->logger->debug('Cleaning up repo clone directory '.$this->repoClonePath);

        $fileSystem = new Filesystem();
        $fileSystem->remove($this->repoClonePath);
    }

    /**
     * {@inheritDoc}
     * @see RepoCrawler::isValidRepoType()
     */
    public function isValidRepoType(): bool
    {
        return $this->repo instanceof GitSourceRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function updateSourceRepo()
    {
        $this->logger->info('Fetching commits for the repository '.$this->repo->getId().' of project '.
        $this->repo->getProject()->getFqname());
        $commitRepository = $this->manager->getRepository(Commit::class);
        $lastCommit       = $commitRepository->getLatestCommit($this->repo);

        // determine if our latest commit exists and fetch new commits since
        // what we have on DB
        if ($lastCommit && $this->commitExists($lastCommit->getCommitId())) {
            $commitCount = $this->updateCommits($lastCommit->getCommitId());
        } else {
            // there has been a history rewrite
            // we drop everything and persist all commits to the DB
            // XXX: Find a way to find the common ancestor and do partial rewrites
            $commitRepository->removeAllCommits($this->repo);
            $this->repo->getCommits()->clear();
            $commitCount = $this->updateCommits();
        }

        if ($commitCount > 0) {
            $this->countLinesOfCode();
        }

        $this->manager->persist($this->repo);

        // we need a explicit flush here because we query commit data later
        $this->manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function updateProject()
    {
        $project = $this->repo->getProject();
        if ($project === null) {
            $this->logger->debug('No project associated with source '.
            'repository '.$this->repo->getId());

            return false;
        }

        if ($project->getDescriptionTextAutoUpdate()) {
            $project->setDescriptionText($this->getDescriptionSafeHtml());
        }
        if ($project->getLicenseTextAutoUpdate()) {
            $project->setLicenseText($this->getLicenseTextSafeHtml());
        }

        $this->updateReleases();

        /** @var Commit $latestCommit */
        $latestCommit = $this->manager
            ->getRepository('LibrecoresProjectRepoBundle:Commit')
            ->getLatestCommit($this->repo);

        if ($latestCommit) {
            $project->setDateLastActivityOccurred($latestCommit->getDateCommitted());
        }

        // Retrieve the code quality score for the project and persist it in the database
        $projectMetrics = $this->getCodeQualityScore($project);

        $project->setScore($projectMetrics);

        $this->manager->persist($project);

        return true;
    }

    /**
     * Get the code quality score calculated from various metrics.
     *
     * The formula is explained here:
     * http://www.librecores.org/static/docs/code-quality.
     * This logic has been moved from @LibreCores\ProjectRepoBundle\Doctrine\ProjectMetricsProvider.php
     * because we want to persist the projectMetrics as and when we crawl for the details of the project
     * and push it to Algolia for custom ranking
     *
     * @param Project $project
     *
     * @return float
     */
    public function getCodeQualityScore(Project $project): float
    {

        // TODO: Some day, we should use a DecisionTreeRegressor here
        // trained on real world projects

        $score = 0;

        // +2 for issue tracker
        if (null !== $project->getIssueTracker()) {
            $score += 2;
        } else {
            $score -= 1;
        }

        // activity max = +2
        $lastActivity = $project->getDateLastActivityOccurred();

        if ($lastActivity) {
            $now        = new \DateTimeImmutable();
            $difference = $now->diff($lastActivity);

            if ($difference->days < 30) {
                $score += 2;
            } elseif ($difference->y < 1) {
                $score += 1;
            } elseif ($difference->y < 3) {
                $score += 0.25;
            } else {
                $score -= -0.25;
            }
        }

        // +0.5 for some activity in issue trackers
        if ($project->getOpenIssues()) {
            $score += 0.5;
        }

        // +0.25 for some activity in PRs.
        // Low weight since this feature is GitHub specific
        if ($project->getOpenPullRequests()) {
            $score += 0.25;
        }

        // TODO: +0.5 if issues/PRs were closed within last month

        // all time contributors, max +3
        $contributors = $this->getContributorsCount($project);
        if ($contributors > 20) {
            $score += 3;
        } elseif ($contributors > 8) {
            $score += 1;
        } elseif ($contributors > 3) {
            $score += 0.5;
        } else {
            $score -= 1;
        }

        // Comment to code ratio +2
        if ($project->getSourceRepo()->getSourceStats()->getCommentToCodeRatio() > 0.2) {
            $score += 2;
        } else {
            $score -= 1;
        }

        // Max +2.5
        // Even though it is GitHub specific, it is user feedback and hence
        // a real indicator of project quality
        $stars = $project->getStars();

        if ($stars > 10000) {
            $score += 2.5;
        } elseif ($stars > 1000) {
            $score += 1;
        } elseif ($stars > 100) {
            $score += 0.5;
        }

        // +0.5 for release tags
        if (!empty($project->getReleases())) {
            $score += 0.5;

            // +0.5 for recent release
            if ($project->getReleases()[0]->getPublishedAt()->diff(new \DateTime())->y < 3) {
                $score += 0.5;
            }
        }

        // +0.25 for changelog
        if (preg_match('/change\s?log|release\s?(notes|history)?/i', $project->getDescriptionText())) {
            $score += 0.25;
        }

        // TODO: Handle repositories with CHANGELOG in a file

        // +1 for description
        // It is important for a project to have a description
        if ($project->getDescriptionText()) {
            $score += 1;
        } else {
            $score -= 2;
        }

        // +1 for a license file
        // -3 since this is very important for open source projects
        if ($project->getLicenseText()) {
            $score += 1;
        } else {
            $score -= 3;
        }

        // TODO: This section needs improvement

        // commit activity
        $commitActivity = $this->getPhaseWiseAverageRateOfChangeOfCommits($project);
        $averageCommits = StatsUtil::normalize($this->getPhaseWiseAverageCommitCount($project));

        // +0.5 for stable project with mid term > 0.2
        if ($averageCommits['mid'] > 0.2) {
            $score += 0.5;
        } else {
            $score -= 0.5;
        }

        // +0.5 for constant commits
        if ($averageCommits['end'] > 0.05) {
            $score += 0.5;
        } else {
            $score -= 0.5;
        }

        // +0.5 if commit activity is not decreasing

        if ($commitActivity['mid'] >= 0) {
            $score += 0.25;
        } else {
            $score -= 0.25;
        }

        if ($commitActivity['end'] >= 0) {
            $score += 0.25;
        } else {
            $score -= 0.25;
        }

        // +0.5 if project is still interesting

        if ($this->getAverageRateOfChangeOfYearlyContributors($project) > 0.3) {
            $score += 0.5;
        }

        return max(ceil($score * 5 / 17.5), 0);
    }

    /**
     * Get the total number of contributors to a project
     *
     * @param Project $project
     *
     * @return int
     */
    public function getContributorsCount(Project $project): int
    {
        return $this->manager->getRepository(Contributor::class)->getContributorCountForRepository(
            $project->getSourceRepo()
        );
    }

    /**
     * Get the the average rate of change of yearly unique contributors
     * through out the project lifetime.
     *
     * @param Project $project
     *
     * @return float average rate of change of yearly contributors
     */
    public function getAverageRateOfChangeOfYearlyContributors(Project $project)
    {
        $contributorsPerYear = array_values($this->getContributorHistogram(
            $project,
            Dates::INTERVAL_YEAR
        ));

        return StatsUtil::averageRateOfChange($contributorsPerYear);
    }

    /**
     * Get the the average commit count per phase.
     *
     * The average number of commits per year in 3 phases, which are
     * roughly 1/3 of project's lifetime. For projects < 6 years and
     * > 2years 2 phases - start and mid, are considered for averaging,
     * each consisting of roughly half the time. For projects < 2,
     * average of the entire time is taken as returned as the value
     * for start.
     *
     * @param Project $project
     *
     * @return array averages in three phases - start,
     *          mid, end. Value of the phases that have not been
     *          calculated is 0
     */
    public function getPhaseWiseAverageCommitCount(Project $project)
    {
        $yearlyCommitCount =
            array_values($this->manager->getRepository(Commit::class)->getCommitHistogram(
                $project->getSourceRepo(),
                Dates::INTERVAL_YEAR
            ));

        if (empty($yearlyCommitCount)) {
            return [
                'start' => 0,
                'mid' => 0,
                'end' => 0,
            ];
        }

        $yearsWithCommits = count($yearlyCommitCount);

        // It makes sense to divide a 6 year old project not a young project
        if ($yearsWithCommits >= 6) {
            list($start, $mid, $end) = array_chunk(
                $yearlyCommitCount,
                ceil(count($yearlyCommitCount) / 3)
            );

            return [
                'start' => StatsUtil::average($start),
                'mid' => StatsUtil::average($mid),
                'end' => StatsUtil::average($end),
            ];
        }

        if ($yearsWithCommits > 2) {
            list($start, $mid) = array_chunk(
                $yearlyCommitCount,
                ceil(count($yearlyCommitCount) / 2)
            );

            return [
                'start' => StatsUtil::average($start),
                'mid' => StatsUtil::average($mid),
                'end' => 0,
            ];
        }

        return [
            'start' => StatsUtil::average($yearlyCommitCount),
            'mid' => -1,
            'end' => -1,
        ];
    }

    /**
     * Get the average rate of change of commits.
     *
     * It is calculated in 3 phases, which are roughly 1/3 of project's lifetime.
     * For projects< 6 years and > 2years 2 phases - start and mid, are
     * considered for averaging, each consisting of roughly half the
     * time. For projects < 2, average of the entire time is taken as
     * returned as the value for start.
     *
     * @param Project $project
     *
     * @return array average rate of change of number of commits
     *          in three phases - start, mid, end. Value of the
     *          phases that have not been calculated is 0
     */
    public function getPhaseWiseAverageRateOfChangeOfCommits(Project $project)
    {
        $yearlyCommitCount =
            array_values($this->manager->getRepository(Commit::class)->getCommitHistogram(
                $project->getSourceRepo(),
                Dates::INTERVAL_YEAR
            ));

        if (empty($yearlyCommitCount)) {
            return [
                'start' => -1,
                'mid' => -1,
                'end' => -1,
            ];
        }

        $yearsWithCommits = count($yearlyCommitCount);

        // It makes sense to divide a 6 year old project not a young project
        if ($yearsWithCommits >= 6) {
            list($start, $mid, $end) = array_chunk(
                $yearlyCommitCount,
                ceil(count($yearlyCommitCount) / 3)
            );

            return [
                'start' => StatsUtil::averageRateOfChange($start),
                'mid' => StatsUtil::averageRateOfChange($mid),
                'end' => StatsUtil::averageRateOfChange($end),
            ];
        }

        if ($yearsWithCommits > 2) {
            list($start, $mid) = array_chunk(
                $yearlyCommitCount,
                ceil(count($yearlyCommitCount) / 2)
            );

            return [
                'start' => StatsUtil::averageRateOfChange($start),
                'mid' => StatsUtil::averageRateOfChange($mid),
                'end' => -1,
            ];
        }

        return [
            'start' => StatsUtil::averageRateOfChange($yearlyCommitCount),
            'mid' => -1,
            'end' => -1,
        ];
    }

    /**
     * Get a histogram of contributors over a range of dates
     *
     * @param Project            $project
     * @param int                $bucket  one of the constants 'INTERVAL_DAY', 'INTERVAL_WEEK'
     *                                    'INTERVAL_MONTH', 'INTERVAL_YEAR', which specifies
     *                                    the histogram bucket size
     * @param \DateTimeImmutable $start   start date of commits
     * @param \DateTimeImmutable $end     end date of commits
     *
     * @return array associative array of a time span index and commits in that
     *               time span
     */
    public function getContributorHistogram(
        Project $project,
        int $bucket,
        \DateTimeImmutable $start = null,
        \DateTimeImmutable $end = null
    ): array {
        // TODO: This function needs some form of caching
        // aggregation queries in mysql are very expensive, this function
        // will never use an index and always perform a full table scan
        return $this->manager->getRepository(Commit::class)->getCommitContributorHistogram(
            $project->getSourceRepo(),
            $bucket,
            $start,
            $end
        );
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
     * Checks whether the given commit ID exists on the default tree of the
     * repository
     *
     * @param string $commitId ID of the commit to search
     *
     * @return bool commit exists in the tree ?
     */
    protected function commitExists(string $commitId): bool
    {
        // Stolen from https://stackoverflow.com/a/13526591

        $cwd = $this->getRepoClonePath();

        $this->logger->info('Checking commits in '.$cwd);

        $cmd = [ 'git', 'merge-base', '--is-ancestor', $commitId, 'HEAD' ];
        $process = $this->processCreator->createProcess($cmd, $cwd);
        $this->executeProcess($process);
        $code = $process->getExitCode();

        if (0 === $code) {
            $value = true;    // commit exists in default branch
        } else {
            if (1 === $code || 128 === $code) {
                $value = false;    // commit does not exist in repository or branch
            } else {
                throw new \RuntimeException(
                    sprintf(
                        "Unable to fetch commits from %s: %s",
                        $cwd,
                        $process->getErrorOutput()
                    )
                );
            }
        }

        $this->logger->debug("Checked commits in $cwd");

        return $value;
    }

    /**
     * Get all commits in the repository since a specified commit ID or all if
     * not specified.
     *
     * @param string|null $sinceCommitId ID of commit after which the commits are to be
     *                                   returned
     * @return int Commits updated
     */
    protected function updateCommits(?string $sinceCommitId = null) : int
    {
        $this->logger->info(
            'Fetching commits for the repository '.$this->repo->getId()
            .' of project '.$this->repo->getProject()->getFqname()
        );

        $cmd = ['git', 'log', '--reverse', '--format=%H|%aN|%aE|%aD',
                '--shortstat', ];
        if (null !== $sinceCommitId) {
            $cmd[] = $sinceCommitId;
            $cmd[] = '...';
        }

        $cwd = $this->getRepoClonePath();

        $this->logger->info("Fetching commits in $cwd");

        $process = $this->processCreator->createProcess($cmd, $cwd);
        $process->setTimeout(static::TIMEOUT_GIT_LOG);
        $this->mustExecuteProcess($process);
        $output = $process->getOutput();
        $this->logger->debug("Fetched commits from $cwd");

        return $this->parseCommits($output);
    }

    /**
     * Crawl a repositories' source code and count lines of code in each language
     *
     * Implementation uses Cloc: https://github.com/AlDanial/cloc
     *
     */
    protected function countLinesOfCode()
    {
        $cmd = ['cloc', '--json', '--skip-uniqueness',
                $this->getRepoClonePath()];
        $process = $this->processCreator->createProcess($cmd);

        $this->mustExecuteProcess($process);
        $result = $process->getOutput();

        $cloc = json_decode($result, true);

        $sourceStats = $this->repo->getSourceStats();
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

        $this->repo->setSourceStats($sourceStats);
        $this->manager->persist($this->repo);
    }

    /**
     * Get the description of the repository as safe HTML
     *
     * Usually this is the content of the README file.
     *
     * "Safe" HTML is stripped from all possibly malicious content, such as
     * script tags, etc.
     *
     * @return string|null the repository description, or null if none was found
     */
    protected function getDescriptionSafeHtml(): ?string
    {
        $descriptionFile = FileUtil::findFile(
            $this->getRepoClonePath(),
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
        } catch (\Exception $e) {
            $this->logger->error("Unable to convert $descriptionFile to HTML ".
            "for license text.");

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
     * @return string|null the license text, or null if none was found
     */
    protected function getLicenseTextSafeHtml(): ?string
    {
        $licenseFile = FileUtil::findFile(
            $this->getRepoClonePath(),
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
        } catch (\Exception $e) {
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
     * @return bool if successful
     */
    protected function updateReleases()
    {
        $project = $this->repo->getProject();
        $cwd = $this->getRepoClonePath();

        $this->logger->info("Updating releases in $cwd");

        $cmd = ['git', 'tag', '--sort=-creatordate',
            '--format=%(refname:strip=2)|%(objectname:short)|%(creatordate)'];
        $process = $this->processCreator->createProcess($cmd, $cwd);
        $process->setTimeout(static::TIMEOUT_GIT_LOG);

        try {
            $this->mustExecuteProcess($process);
            $output = $process->getOutput();
        } catch (\Exception $e) {
            $this->logger->error(
                "Unable to fetch releases from $cwd. Process execution "
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
                            ->setPublishedAt(new \DateTime($matches[3]))
                            ->setCommitID($matches[2])
                            ->setIsPrerelease(preg_match($preReleaseRegex, $matches[1]));
                    $releases[] = $release;
                }
            } catch (\Exception $e) {
                $this->logger->warning("Skipped release tag due to parse error: $line");
            }
        }

        $project->setReleases($releases);
        $this->manager->persist($project);

        $count = count($releases);
        $this->logger->debug("Fetched $count releases from $cwd");

        return true;
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

        $this->logger->info('Cloning repository: '.$repoUrl);

        $cmd = ['git', 'clone', $repoUrl, $this->repoClonePath];
        $process = $this->processCreator->createProcess($cmd);
        $process->setTimeout(static::TIMEOUT_GIT_CLONE);
        $this->mustExecuteProcess($process);
        $this->logger->debug('Cloned repository '.$repoUrl);
    }

    /**
     * Parse commits from the output from git
     *
     * @param string $outputString raw output from git
     *
     * @return int
     */
    private function parseCommits(string $outputString) : int
    {
        $this->logger->info('Parsing commits for repo '.$this->getRepoClonePath());

        $outputString = preg_replace('/^\h*\v+/m', '', trim($outputString));    // remove blank lines
        $output       = explode("\n", $outputString);  // explode lines into array

        $commits = []; // stores the array of commits
        $len     = count($output);
        for ($i = 0; $i < $len; $i++) {
            // Every commit has 4 parts, id, author name, email, commit timestamp
            // in the format id|name|email|timestamp
            // followed by an optional line for modifications
            $commitMatches = [];
            if (preg_match('/^([\da-f]+)\|(.+)\|(.+@.+)\|(.+)$/', $output[$i], $commitMatches)) {
                $contributor = $this->manager->getRepository('LibrecoresProjectRepoBundle:Contributor')
                ->getContributorForRepository(
                    $this->repo,
                    $commitMatches[3],
                    $commitMatches[2]
                );
                $date        = new \DateTime($commitMatches[4]);
                $date->setTimezone(new \DateTimeZone('UTC'));
                $commit = new Commit();
                $commit
                    ->setCommitId($commitMatches[1])
                    ->setSourceRepo($this->repo)
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
        $count = count($commits);
        $this->logger->debug('Parsed '.$count.' commits for repo '.$this->getRepoClonePath());

        return $count;
    }

    /**
     * Helper for executing a process
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
