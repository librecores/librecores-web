<?php

namespace App\Tests\RepoCrawler;

use App\RepoCrawler\GitRepoCrawler;
use App\Entity\Contributor;
use App\Entity\GitSourceRepo;
use App\Entity\LanguageStat;
use App\Entity\Organization;
use App\Entity\Project;
use App\Util\MarkupToHtmlConverter;
use App\Util\ProcessCreator;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use App\Service\ProjectMetricsProvider;
use App\Repository\CommitRepository;
use App\Repository\ContributorRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Tests for GitRepoCrawler
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 *
 * @see    GitOutputParser
 */
class GitRepoCrawlerTest extends TestCase
{
    public function testFetchCommits()
    {
        $contributors = [
            'janedoe@example.com' => new Contributor('Jane Doe', 'janedoe@example.com'),
            'johndoe@example.com' => new Contributor('John Doe', 'johndoe@example.com'),

        ];

        /** @var ContributorRepository $mockContributorRepository */
        $mockContributorRepository = $this->getMockBuilder(ContributorRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOneBy', 'getEntityManager', 'getLatestCommit', 'removeAllCommits'])
            ->getMock();

        $mockContributorRepository
            ->expects($this->exactly(7))
            ->method('findOneBy')
            ->willReturnCallback(
                function (array $criteria) use ($contributors) {
                    return $contributors[$criteria['email']];
                }
            );

        /** @var LoggerInterface $mockLogger */
        $mockLogger = $this->createMock(LoggerInterface::class);

        // create a debug channel, uncomment for debug purposes
//        $mockLogger = new \Monolog\Logger('test');
//        $mockLogger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout'));

        /** @var MarkupToHtmlConverter $mockMarkupConverter */
        $mockMarkupConverter = $this->createMock(MarkupToHtmlConverter::class);

        /** @var ObjectManager $mockManager */
        $mockManager = $this->createMock(ObjectManager::class);

        $mockGitCloneOutput = $this->getTestDataFileContents('git-clone.txt');
        $mockGitTagOutput = $this->getTestDataFileContents('git-tag.txt');
        $mockClocOutput = $this->getTestDataFileContents('cloc-mock-output.json');

        $mockGitLogProcess = $this->createMock(Process::class);
        $mockGitLogProcess->method('getOutput')
            ->willReturn($mockGitCloneOutput);
        $mockGitLogProcess->method('getExitCode')
            ->willReturn(0);

        $mockGitTagProcess = $this->createMock(Process::class);
        $mockGitTagProcess->method('getOutput')
            ->willReturn($mockGitTagOutput);
        $mockGitTagProcess->method('getExitCode')
            ->willReturn(0);

        $mockClocProcess = $this->createMock(Process::class);
        $mockClocProcess->method('getOutput')
            ->willReturn($mockClocOutput);
        $mockClocProcess->method('getExitCode')
            ->willReturn(0);

        /** @var ProcessCreator $processCreator */
        $processCreator = $this->createMock(ProcessCreator::class);

        $processCreator
            ->method('createProcess')
            ->willReturnCallback(
                function ($commandLine, $cwd) use ($mockGitTagProcess, $mockClocProcess, $mockGitLogProcess) {
                    if ($commandLine[0] === 'git') {
                        $process = ('log' === $commandLine[1] ? $mockGitLogProcess : $mockGitTagProcess);
                    } else {
                        $process = $mockClocProcess;
                    }

                    return $process;
                }
            );

        $org = new Organization();
        $org->setDisplayName('example')
            ->setName('example');

        $project = new Project();
        $project->setName('test')
            ->setDisplayName('test')
            ->setParentOrganization($org)
            ->setDescriptionTextAutoUpdate(false)
            ->setLicenseTextAutoUpdate(false);

        /** @var CommitRepository $mockCommitRepository */
        $mockCommitRepository = $this->createMock(CommitRepository::class);

        /** @var ProjectMetricsProvider $mockProjectMetricsProvider */
        $mockProjectMetricsProvider = $this->createMock(ProjectMetricsProvider::class);

        $mockProjectMetricsProvider->method('getCodeQualityScore')->willReturn(4.5);

        $mockFilesystem = $this->createMock(Filesystem::class);
        $mockFilesystem->method('tempnam')->willReturn('/tmp/directory');

        $repo = new GitSourceRepo();
        $repo->setProject($project);

        $project->setSourceRepo($repo);

        $crawler = new GitRepoCrawler(
            $mockMarkupConverter,
            $processCreator,
            $mockCommitRepository,
            $mockContributorRepository,
            $mockManager,
            $mockLogger,
            $mockProjectMetricsProvider,
            /** @var Filesystem $mockFilesystem */
            $mockFilesystem
        );
        $crawler->update($project);

        $commits = $repo->getCommits();

        $this->assertEquals('e0889c6', $commits[0]->getCommitId());
        $this->assertEquals('22b8123', $commits[1]->getCommitId());
        $this->assertEquals('9e7dd1e', $commits[2]->getCommitId());
        $this->assertEquals('b611a9c', $commits[3]->getCommitId());
        $this->assertEquals('a4ecaae', $commits[4]->getCommitId());
        $this->assertEquals('46f2311', $commits[5]->getCommitId());
        $this->assertEquals('26f16e1', $commits[6]->getCommitId());

        $this->assertEquals(new DateTime('Fri, 26 May 2017 18:32:10 +0530'), $commits[0]->getDateCommitted());
        $this->assertEquals(new DateTime('Fri, 26 May 2017 20:08:57 +0530'), $commits[1]->getDateCommitted());
        $this->assertEquals(new DateTime('Sat, 27 May 2017 16:13:23 +0200'), $commits[2]->getDateCommitted());
        $this->assertEquals(new DateTime('Tue, 30 May 2017 00:33:18 +0300'), $commits[3]->getDateCommitted());
        $this->assertEquals(new DateTime('Thu, 08 Jun 2017 01:04:02 +0530'), $commits[4]->getDateCommitted());
        $this->assertEquals(new DateTime('Sun, 11 Jun 2017 14:04:03 +0200'), $commits[5]->getDateCommitted());
        $this->assertEquals(new DateTime('Mon, 12 Jun 2017 16:24:21 +0200'), $commits[6]->getDateCommitted());

        $this->assertEquals('johndoe@example.com', $commits[0]->getContributor()->getEmail());
        $this->assertEquals('johndoe@example.com', $commits[1]->getContributor()->getEmail());
        $this->assertEquals('johndoe@example.com', $commits[2]->getContributor()->getEmail());
        $this->assertEquals('janedoe@example.com', $commits[3]->getContributor()->getEmail());
        $this->assertEquals('janedoe@example.com', $commits[4]->getContributor()->getEmail());
        $this->assertEquals('johndoe@example.com', $commits[5]->getContributor()->getEmail());
        $this->assertEquals('janedoe@example.com', $commits[6]->getContributor()->getEmail());

        $this->assertEquals(0, $commits[0]->getFilesModified());
        $this->assertEquals(1, $commits[1]->getFilesModified());
        $this->assertEquals(1, $commits[2]->getFilesModified());
        $this->assertEquals(7, $commits[3]->getFilesModified());
        $this->assertEquals(4, $commits[4]->getFilesModified());
        $this->assertEquals(1, $commits[5]->getFilesModified());
        $this->assertEquals(4, $commits[6]->getFilesModified());

        $this->assertEquals(0, $commits[0]->getLinesAdded());
        $this->assertEquals(4, $commits[1]->getLinesAdded());
        $this->assertEquals(5, $commits[2]->getLinesAdded());
        $this->assertEquals(183, $commits[3]->getLinesAdded());
        $this->assertEquals(37, $commits[4]->getLinesAdded());
        $this->assertEquals(3, $commits[5]->getLinesAdded());
        $this->assertEquals(0, $commits[6]->getLinesAdded());

        $this->assertEquals(0, $commits[0]->getLinesRemoved());
        $this->assertEquals(0, $commits[1]->getLinesRemoved());
        $this->assertEquals(0, $commits[2]->getLinesRemoved());
        $this->assertEquals(3, $commits[3]->getLinesRemoved());
        $this->assertEquals(0, $commits[4]->getLinesRemoved());
        $this->assertEquals(0, $commits[5]->getLinesRemoved());
        $this->assertEquals(8, $commits[6]->getLinesRemoved());

        $stats = $repo->getSourceStats();
        // conditions
        $this->assertEquals(102, $stats->getTotalFiles());
        $this->assertEquals(5995, $stats->getTotalLinesOfCode());
        $this->assertEquals(3590, $stats->getTotalLinesOfComments());
        $this->assertEquals(1378, $stats->getTotalBlankLines());

        foreach ($stats->getLanguageStats() as $languageStat) {
            /** @var LanguageStat $languageStat */
            switch ($languageStat->getLanguage()) {
                case 'PHP':
                    $this->assertEquals(68, $languageStat->getFileCount());
                    $this->assertEquals(
                        1089,
                        $languageStat->getBlankLineCount()
                    );
                    $this->assertEquals(
                        3498,
                        $languageStat->getCommentLineCount()
                    );
                    $this->assertEquals(4295, $languageStat->getLinesOfCode());
                    break;
                case 'Twig':
                    $this->assertEquals(27, $languageStat->getFileCount());
                    $this->assertEquals(
                        216,
                        $languageStat->getBlankLineCount()
                    );
                    $this->assertEquals(
                        48,
                        $languageStat->getCommentLineCount()
                    );
                    $this->assertEquals(1335, $languageStat->getLinesOfCode());
                    break;
                case 'YAML':
                    $this->assertEquals(6, $languageStat->getFileCount());
                    $this->assertEquals(61, $languageStat->getBlankLineCount());
                    $this->assertEquals(
                        43,
                        $languageStat->getCommentLineCount()
                    );
                    $this->assertEquals(184, $languageStat->getLinesOfCode());
                    break;
                case 'XML':
                    $this->assertEquals(1, $languageStat->getFileCount());
                    $this->assertEquals(12, $languageStat->getBlankLineCount());
                    $this->assertEquals(
                        1,
                        $languageStat->getCommentLineCount()
                    );
                    $this->assertEquals(181, $languageStat->getLinesOfCode());
                    break;
                default:
                    $this->fail('Unknown language encountered');
                    break;
            }
        }

        $releases = $project->getReleases();

        // there are 10 valid release objects
        $this->assertEquals(count($releases), 10);

        $this->assertEquals('v1.1.7', $releases[0]->getName());
        $this->assertEquals('b645cc95', $releases[0]->getCommitID());
        $this->assertEquals(
            new DateTime('Fri Sep 23 02:02:24 2016 +0530'),
            $releases[0]->getPublishedAt()
        );

        $this->assertEquals('v1.1', $releases[1]->getName());
        $this->assertEquals('e5b62acb', $releases[1]->getCommitID());
        $this->assertEquals(
            new DateTime('Tue Sep 6 15:29:04 2016 +0530'),
            $releases[1]->getPublishedAt()
        );

        $this->assertEquals('1.1.6', $releases[2]->getName());
        $this->assertEquals('e5b62acb', $releases[2]->getCommitID());
        $this->assertEquals(
            new DateTime('Tue Sep 6 15:29:04 2016 +0530'),
            $releases[2]->getPublishedAt()
        );

        $this->assertEquals('1.1', $releases[3]->getName());
        $this->assertEquals('df02e241', $releases[3]->getCommitID());
        $this->assertEquals(
            new DateTime('Mon Sep 5 03:25:52 2016 +0530'),
            $releases[3]->getPublishedAt()
        );

        $this->assertEquals('v4.1.5-android', $releases[4]->getName());
        $this->assertEquals('df02e241', $releases[4]->getCommitID());
        $this->assertEquals(
            new DateTime('Mon Sep 5 03:25:52 2016 +0530'),
            $releases[4]->getPublishedAt()
        );

        $this->assertEquals('v4.2.10-RC-2', $releases[5]->getName());
        $this->assertEquals('6d5b3930', $releases[5]->getCommitID());
        $this->assertEquals(
            new DateTime('Thu Sep 1 11:45:17 2016 +0530'),
            $releases[5]->getPublishedAt()
        );

        $this->assertEquals('v1.1.4-alpha24', $releases[6]->getName());
        $this->assertEquals('6d5b3930', $releases[6]->getCommitID());
        $this->assertEquals(
            new DateTime('Thu Sep 1 11:45:17 2016 +0530'),
            $releases[6]->getPublishedAt()
        );

        $this->assertEquals('android-v1.1.4-beta24', $releases[7]->getName());
        $this->assertEquals('5452c021', $releases[7]->getCommitID());
        $this->assertEquals(
            new DateTime('Thu Sep 1 11:30:05 2016 +0530'),
            $releases[7]->getPublishedAt()
        );

        $this->assertEquals('android-1.1.3', $releases[8]->getName());
        $this->assertEquals('4ef6195b', $releases[8]->getCommitID());
        $this->assertEquals(
            new DateTime('Wed Aug 31 00:50:37 2016 +0530'),
            $releases[8]->getPublishedAt()
        );

        $this->assertEquals('v1.1.1', $releases[9]->getName());
        $this->assertEquals('9daa1', $releases[9]->getCommitID());
        $this->assertEquals(
            new DateTime('Tue Aug 23 04:11:11 2016 +0530'),
            $releases[9]->getPublishedAt()
        );

        // notavalidrelease|f17341b4|Sun Aug 28 11:39:38 2016 +0530
        // 11invalidtag|f17341b4|Sun Aug 28 11:39:38 2016 +0530
    }

    private function getTestDataFileContents($testDataFilename)
    {
        $testDataPath = join(
            DIRECTORY_SEPARATOR,
            [__DIR__, 'Resources', $testDataFilename]
        );

        return file_get_contents($testDataPath);
    }
}
