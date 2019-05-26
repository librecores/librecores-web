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

        $this->assertEquals($commits[0]->getCommitId(), 'e0889c6');
        $this->assertEquals($commits[1]->getCommitId(), '22b8123');
        $this->assertEquals($commits[2]->getCommitId(), '9e7dd1e');
        $this->assertEquals($commits[3]->getCommitId(), 'b611a9c');
        $this->assertEquals($commits[4]->getCommitId(), 'a4ecaae');
        $this->assertEquals($commits[5]->getCommitId(), '46f2311');
        $this->assertEquals($commits[6]->getCommitId(), '26f16e1');

        $this->assertEquals($commits[0]->getDateCommitted(), new DateTime('Fri, 26 May 2017 18:32:10 +0530'));
        $this->assertEquals($commits[1]->getDateCommitted(), new DateTime('Fri, 26 May 2017 20:08:57 +0530'));
        $this->assertEquals($commits[2]->getDateCommitted(), new DateTime('Sat, 27 May 2017 16:13:23 +0200'));
        $this->assertEquals($commits[3]->getDateCommitted(), new DateTime('Tue, 30 May 2017 00:33:18 +0300'));
        $this->assertEquals($commits[4]->getDateCommitted(), new DateTime('Thu, 08 Jun 2017 01:04:02 +0530'));
        $this->assertEquals($commits[5]->getDateCommitted(), new DateTime('Sun, 11 Jun 2017 14:04:03 +0200'));
        $this->assertEquals($commits[6]->getDateCommitted(), new DateTime('Mon, 12 Jun 2017 16:24:21 +0200'));

        $this->assertEquals($commits[0]->getContributor()->getEmail(), 'johndoe@example.com');
        $this->assertEquals($commits[1]->getContributor()->getEmail(), 'johndoe@example.com');
        $this->assertEquals($commits[2]->getContributor()->getEmail(), 'johndoe@example.com');
        $this->assertEquals($commits[3]->getContributor()->getEmail(), 'janedoe@example.com');
        $this->assertEquals($commits[4]->getContributor()->getEmail(), 'janedoe@example.com');
        $this->assertEquals($commits[5]->getContributor()->getEmail(), 'johndoe@example.com');
        $this->assertEquals($commits[6]->getContributor()->getEmail(), 'janedoe@example.com');

        $this->assertEquals($commits[0]->getFilesModified(), 0);
        $this->assertEquals($commits[1]->getFilesModified(), 1);
        $this->assertEquals($commits[2]->getFilesModified(), 1);
        $this->assertEquals($commits[3]->getFilesModified(), 7);
        $this->assertEquals($commits[4]->getFilesModified(), 4);
        $this->assertEquals($commits[5]->getFilesModified(), 1);
        $this->assertEquals($commits[6]->getFilesModified(), 4);

        $this->assertEquals($commits[0]->getLinesAdded(), 0);
        $this->assertEquals($commits[1]->getLinesAdded(), 4);
        $this->assertEquals($commits[2]->getLinesAdded(), 5);
        $this->assertEquals($commits[3]->getLinesAdded(), 183);
        $this->assertEquals($commits[4]->getLinesAdded(), 37);
        $this->assertEquals($commits[5]->getLinesAdded(), 3);
        $this->assertEquals($commits[6]->getLinesAdded(), 0);

        $this->assertEquals($commits[0]->getLinesRemoved(), 0);
        $this->assertEquals($commits[1]->getLinesRemoved(), 0);
        $this->assertEquals($commits[2]->getLinesRemoved(), 0);
        $this->assertEquals($commits[3]->getLinesRemoved(), 3);
        $this->assertEquals($commits[4]->getLinesRemoved(), 0);
        $this->assertEquals($commits[5]->getLinesRemoved(), 0);
        $this->assertEquals($commits[6]->getLinesRemoved(), 8);

        $stats = $repo->getSourceStats();
        // conditions
        $this->assertEquals($stats->getTotalFiles(), 102);
        $this->assertEquals($stats->getTotalLinesOfCode(), 5995);
        $this->assertEquals($stats->getTotalLinesOfComments(), 3590);
        $this->assertEquals($stats->getTotalBlankLines(), 1378);

        foreach ($stats->getLanguageStats() as $languageStat) {
            /** @var LanguageStat $languageStat */
            switch ($languageStat->getLanguage()) {
                case 'PHP':
                    $this->assertEquals($languageStat->getFileCount(), 68);
                    $this->assertEquals(
                        $languageStat->getBlankLineCount(),
                        1089
                    );
                    $this->assertEquals(
                        $languageStat->getCommentLineCount(),
                        3498
                    );
                    $this->assertEquals($languageStat->getLinesOfCode(), 4295);
                    break;
                case 'Twig':
                    $this->assertEquals($languageStat->getFileCount(), 27);
                    $this->assertEquals(
                        $languageStat->getBlankLineCount(),
                        216
                    );
                    $this->assertEquals(
                        $languageStat->getCommentLineCount(),
                        48
                    );
                    $this->assertEquals($languageStat->getLinesOfCode(), 1335);
                    break;
                case 'YAML':
                    $this->assertEquals($languageStat->getFileCount(), 6);
                    $this->assertEquals($languageStat->getBlankLineCount(), 61);
                    $this->assertEquals(
                        $languageStat->getCommentLineCount(),
                        43
                    );
                    $this->assertEquals($languageStat->getLinesOfCode(), 184);
                    break;
                case 'XML':
                    $this->assertEquals($languageStat->getFileCount(), 1);
                    $this->assertEquals($languageStat->getBlankLineCount(), 12);
                    $this->assertEquals(
                        $languageStat->getCommentLineCount(),
                        1
                    );
                    $this->assertEquals($languageStat->getLinesOfCode(), 181);
                    break;
                default:
                    $this->fail('Unknown language encountered');
                    break;
            }
        }

        $releases = $project->getReleases();

        // there are 10 valid release objects
        $this->assertEquals(count($releases), 10);

        $this->assertEquals($releases[0]->getName(), 'v1.1.7');
        $this->assertEquals($releases[0]->getCommitID(), 'b645cc95');
        $this->assertEquals(
            $releases[0]->getPublishedAt(),
            new DateTime('Fri Sep 23 02:02:24 2016 +0530')
        );

        $this->assertEquals($releases[1]->getName(), 'v1.1');
        $this->assertEquals($releases[1]->getCommitID(), 'e5b62acb');
        $this->assertEquals(
            $releases[1]->getPublishedAt(),
            new DateTime('Tue Sep 6 15:29:04 2016 +0530')
        );

        $this->assertEquals($releases[2]->getName(), '1.1.6');
        $this->assertEquals($releases[2]->getCommitID(), 'e5b62acb');
        $this->assertEquals(
            $releases[2]->getPublishedAt(),
            new DateTime('Tue Sep 6 15:29:04 2016 +0530')
        );

        $this->assertEquals($releases[3]->getName(), '1.1');
        $this->assertEquals($releases[3]->getCommitID(), 'df02e241');
        $this->assertEquals(
            $releases[3]->getPublishedAt(),
            new DateTime('Mon Sep 5 03:25:52 2016 +0530')
        );

        $this->assertEquals($releases[4]->getName(), 'v4.1.5-android');
        $this->assertEquals($releases[4]->getCommitID(), 'df02e241');
        $this->assertEquals(
            $releases[4]->getPublishedAt(),
            new DateTime('Mon Sep 5 03:25:52 2016 +0530')
        );

        $this->assertEquals($releases[5]->getName(), 'v4.2.10-RC-2');
        $this->assertEquals($releases[5]->getCommitID(), '6d5b3930');
        $this->assertEquals(
            $releases[5]->getPublishedAt(),
            new DateTime('Thu Sep 1 11:45:17 2016 +0530')
        );

        $this->assertEquals($releases[6]->getName(), 'v1.1.4-alpha24');
        $this->assertEquals($releases[6]->getCommitID(), '6d5b3930');
        $this->assertEquals(
            $releases[6]->getPublishedAt(),
            new DateTime('Thu Sep 1 11:45:17 2016 +0530')
        );

        $this->assertEquals($releases[7]->getName(), 'android-v1.1.4-beta24');
        $this->assertEquals($releases[7]->getCommitID(), '5452c021');
        $this->assertEquals(
            $releases[7]->getPublishedAt(),
            new DateTime('Thu Sep 1 11:30:05 2016 +0530')
        );

        $this->assertEquals($releases[8]->getName(), 'android-1.1.3');
        $this->assertEquals($releases[8]->getCommitID(), '4ef6195b');
        $this->assertEquals(
            $releases[8]->getPublishedAt(),
            new DateTime('Wed Aug 31 00:50:37 2016 +0530')
        );

        $this->assertEquals($releases[9]->getName(), 'v1.1.1');
        $this->assertEquals($releases[9]->getCommitID(), '9daa1');
        $this->assertEquals(
            $releases[9]->getPublishedAt(),
            new DateTime('Tue Aug 23 04:11:11 2016 +0530')
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
