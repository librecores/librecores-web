<?php
namespace Librecores\ProjectRepoBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Process\Process;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Knp\Bundle\MarkdownBundle\Parser\MarkdownParser;

use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;

/**
 * Update the crawled information associated with a project
 *
 * @author Philipp Wagner <mail@philipp-wagner.com>
 */
class UpdateProjectInformation implements ConsumerInterface
{
    private $logger;
    /** @var Doctrine\Bundle\DoctrineBundle\Registry */
    private $orm;

    private $mdparser;

    private $stats;


    const TYPE_MARKDOWN = 'md';
    const TYPE_PLAINTEXT = 'txt';
    const TYPE_POD = 'pod';

    public function __construct(LoggerInterface $logger, Registry $doctrine,
                                MarkdownParser $markdownParser)
    {
        $this->logger = $logger;
        $this->orm = $doctrine;
        $this->mdparser = $markdownParser;
    }

    public function execute(AMQPMessage $msg)
    {
        $projectId = (int)unserialize($msg->body);

        $project = $this->orm->getRepository('LibrecoresProjectRepoBundle:Project')
            ->find($projectId);

        // check if this project is associated with a source repository
        if ($project->getSourceRepo() === null ||
            $project->getSourceRepo()->getType() != SourceRepo::REPO_TYPE_GIT) {
            $this->logger->error("Unable to update project with ID $project: ".
                "no valid source repository associated.");

            $this->markInProcessing($project, false);
            return true; /* don't requeue */
        }
        $sourceRepo = $project->getSourceRepo();

        // create temporary directory
        $cmd = 'mktemp -d --tmpdir lc.updateprojectinfo.git.XXXXXXXXXX';
        $process = new Process($cmd);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException("Unable to create temporary directory: ".$process->getErrorOutput());
        }
        $clonedir = trim($process->getOutput());

        // get the code from git
        $cmd = 'git clone '.escapeshellarg($sourceRepo->getUrl()).' '.escapeshellarg($clonedir);
        $this->logger->info('Cloning repository: '.$cmd);
        $process = new Process($cmd);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException("Unable to clone git repository: ".$process->getErrorOutput());
        }
        $this->logger->debug('Repository cloned');

        // extract LICENSE file contents
        if ($project->getLicenseTextAutoUpdate()) {
            $licenseFiles = array('LICENSE', 'COPYING');
            $licenseFile = $this->findFile($clonedir, $licenseFiles);
            if ($licenseFile === false) {
                $this->logger->debug('Found no LICENSE file');
                $project->setLicenseText(null);
            } else {
                $this->logger->debug('Using file '.$licenseFile['file'].' as LICENSE');
                $md = $this->convertToMarkdown($licenseFile['file'],
                    $licenseFile['type']);
                $project->setLicenseText($md);
            }
        }

        // extract README file contents
        if ($project->getDescriptionTextAutoUpdate()) {
            $readmeFiles = array('README');
            $readmeFile = $this->findFile($clonedir, $readmeFiles);
            if ($readmeFile === false) {
                $this->logger->debug('Found no README file');
                $project->setReadmeFileContent(null);
            } else {
                $this->logger->debug('Using file '.$readmeFile['file'].' as README');
                $md = $this->convertToMarkdown($readmeFile['file'],
                    $readmeFile['type']);
                $project->setDescriptionText($md);
            }
        }

        // get git statistics
        $this->collectGitStatistics($clonedir);

        // mark project as "done processing"
        // we don't use markInProcessing() to avoid the double DB flush
        $project->setInProcessing(false);

        // persist to DB
        $this->orm->getManager()->flush();

        // remove event from queue
        return true;
    }

    private function markInProcessing($project, $isInProcessing = true)
    {
        $project->setInProcessing($isInProcessing);
        $this->orm->getManager()->flush();
    }

    protected function findFile($basedir, $basenames)
    {
        // extensions to the file name that we are looking for
        // Order matters here! Put the highest priority file types at the top.
        $extensions = array(
            '.md' => self::TYPE_MARKDOWN,
            '.markdown' => self::TYPE_MARKDOWN,
            '.pod' => self::TYPE_POD,
            '.txt' => self::TYPE_PLAINTEXT,
            '' => self::TYPE_PLAINTEXT,
        );

        foreach ($basenames as $basename) {
            foreach ($extensions as $ext => $type) {
                $filename = $basedir.'/'.$basename.$ext;
                if (is_file($filename)) {
                    return array('file' => $filename, 'type' => $type);
                }
            }
        }

        return false;
    }

    /**
     * Convert a file's contents to Markdown
     *
     * @param string $filename
     * @param string $type one of the TYPE_* constants
     * @throws \RuntimeException
     */
    protected function convertToMarkdown($filename, $type)
    {
        $raw = file_get_contents($filename);

        switch ($type) {
            case self::TYPE_MARKDOWN:
                return $raw;
                break;
            case self::TYPE_PLAINTEXT:
                return $this->convertTextToMarkdown($raw);
                break;
            case self::TYPE_POD:
                return $this->convertPodToMarkdown($raw);
                break;
            default:
                throw new \RuntimeException("Invalid type: $type");
        }
    }

    /**
     * Convert Plain Old Documentation format (POD) to Markdown
     *
     * @param string $pod
     */
    protected function convertPodToMarkdown($pod)
    {
        $cmd = 'pod2markdown';
        $process = new Process($cmd);
        $process->setInput($pod);
        $process->mustRun();
        return $process->getOutput();
    }

    /**
     * Convert plaintext to Markdown
     *
     * @param string $text
     */
    protected function convertTextToMarkdown($text)
    {
        return "~~~\n".$text."\n~~~";
    }

    /**
     * Get statistics from a git repository
     *
     * @param string $repoDir root directory of the git checkout
     */
    protected function collectGitStatistics($repoDir)
    {
        $this->logger->info("Getting git repository statistics");
        $cmd = 'git --no-pager log --reverse '.
            '--pretty="%cd|%H|%aN|%aE" --no-merges --date=iso --shortstat';
        $process = new Process($cmd);
        // XXX: Switch this to |git -C| as soon as a new enough version of git
        // is available on Debian.
        $process->setWorkingDirectory($repoDir);

        $process->run(function ($type, $buffer) {
            static $commitBuf = '';
            static $nlcnt = 0;

            if (Process::ERR === $type) {
                $this->logger->warning("Git error: ".$buffer);
                return;
            }

            for ($c = 0; $c < strlen($buffer); $c++) {
                $commitBuf .= $buffer[$c];

                // Three lines always make up one commit. Split the incoming
                // data stream on newlines and pass the string on to
                // |parseGitCommit()| if we got three lines.
                if ($buffer[$c] === "\n") {
                    $nlcnt++;
                    if ($nlcnt === 3) {
                        $this->parseGitCommit($commitBuf);
                        $nlcnt = 0;
                        $commitBuf = '';
                    }
                }
            }
        });

        var_dump($this->stats);
    }

    /**
     * Parse the output belonging to a single commit in "git log"
     *
     * We extract:
     * - statistics about the authors: names, email addresses, insertations and
     *   deletions
     * - a commit histogram (added/deleted lines, grouped by month)
     *
     * @param string $logEntry
     */
    private function parseGitCommit($logEntry)
    {
        $logEntryL = explode("\n", $logEntry, 3);

        // extract data out of log entry string
        list($cDate, $cHash, $authorName, $email) = explode('|', $logEntryL[0], 4);
        $email = strtolower($email);

        $filesChanged = $insertations = $deletions = 0;
        foreach (explode(',', $logEntryL[2]) as $change) {
            if ($pos = strpos($change, 'file')) {
                $filesChanged = (int)trim(substr($change, 0, $pos));
            } elseif ($pos = strpos($change, 'ins')) {
                $insertations = (int)trim(substr($change, 0, $pos));
            } elseif ($pos = strpos($change, 'del')) {
                $deletions = (int)trim(substr($change, 0, $pos));
            } else {
                $this->logger->warning("Unknown entry in git log message found: '$change'");
            }
        }

        // author statistics
        if (!isset($this->stats['authors'][$email])) {
            $this->stats['authors'][$email] = array(
                'commits' => 0,
                'insertations' => 0,
                'deletions' => 0,
                'filesChanged' => 0,
            );
        }

        // we update the author name as well to make sure to match the
        // last commit in history
        $this->stats['authors'][$email]['name'] = $authorName;

        $this->stats['authors'][$email]['commits']++;
        $this->stats['authors'][$email]['insertations'] += $insertations;
        $this->stats['authors'][$email]['deletions'] += $deletions;
        $this->stats['authors'][$email]['filesChanged'] += $filesChanged;

        // commit frequency histogram (month buckets)
        $d = date('Ym', strtotime($cDate));
        if (!isset($this->stats['commit_histogram'][$d])) {
            $this->stats['commit_histogram'][$d] = 0;
        }
        $this->stats['commit_histogram'][$d]++;
    }
}
