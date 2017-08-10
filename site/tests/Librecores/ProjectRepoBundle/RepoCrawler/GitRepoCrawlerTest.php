<?php

namespace Tests\Librecores\ProjectRepoBundle\RepoCrawler;

use Doctrine\Common\Persistence\ObjectManager;
use Librecores\ProjectRepoBundle\Entity\Contributor;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;
use Librecores\ProjectRepoBundle\Entity\LanguageStat;
use Librecores\ProjectRepoBundle\Entity\Organization;
use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\RepoCrawler\GitRepoCrawler;
use Librecores\ProjectRepoBundle\Repository\ContributorRepository;
use Librecores\ProjectRepoBundle\Util\MarkupToHtmlConverter;
use Librecores\ProjectRepoBundle\Util\ProcessCreator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

/**
 * Tests for GitRepoCrawlerTest
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 *
 * @see GitOutputParser
 */
class GitRepoCrawlerTest extends TestCase
{
    public function testFetchCommits()
    {
        $contributors = [
            'janedoe@example.com' => new Contributor('Jane Doe', 'janedoe@example.com'),
            'johndoe@example.com' => new Contributor('John Doe', 'johndoe@example.com'),

        ];

        $repository = $this->getMockBuilder(ContributorRepository::class)
                           ->disableOriginalConstructor()
                           ->setMethods(['findOneBy', 'getEntityManager', 'getLatestCommit', 'removeAllCommits'])
                           ->getMock();

        $repository->expects($this->exactly(7))
                   ->method('findOneBy')
                   ->willReturnCallback(function (array $criteria) use ($contributors) {
                       return $contributors[$criteria['email']];
                   });

        /** @var LoggerInterface $mockLogger */
        $mockLogger = $this->createMock(LoggerInterface::class);

        /** @var MarkupToHtmlConverter $mockMarkupConverter */
        $mockMarkupConverter = $this->createMock(MarkupToHtmlConverter::class);

        /** @var ObjectManager $mockManager */
        $mockManager = $this->createMock(ObjectManager::class);
        $mockManager->method('getRepository')->willReturn($repository);

        $mockGitOutput  = file_get_contents(
            join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'output.txt']
            ));
        $mockClocOutput = file_get_contents(
            join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', 'cloc-mock-output.json']
            ));

        $mockGitProcess = $this->createMock(Process::class);
        $mockGitProcess->method('getOutput')
                       ->willReturn($mockGitOutput);
        $mockGitProcess->method('getExitCode')
                       ->willReturn(0);
        $mockClocProcess = $this->createMock(Process::class);
        $mockClocProcess->method('getOutput')
                        ->willReturn($mockClocOutput);
        $mockClocProcess->method('getExitCode')
                        ->willReturn(0);

        /** @var ProcessCreator $processCreator */
        $processCreator = $this->createMock(ProcessCreator::class);

        $processCreator->method('createProcess')
                       ->willReturnCallback(function ($cmd, $args)
                       use ($mockClocProcess, $mockGitProcess) {
                           if ('git' === $cmd) {
                               $output = $mockGitProcess;
                           } else {
                               $output = $mockClocProcess;
                           }

                           return $output;
                       });

        $org = new Organization();
        $org->setDisplayName('example')
            ->setName('example');

        $project = new Project();
        $project->setName('test')
                ->setDisplayName('test')
                ->setParentOrganization($org);

        $repo = new GitSourceRepo();
        $repo->setProject($project);

        $project->setSourceRepo($repo);

        $crawler = new GitRepoCrawler($repo, $mockMarkupConverter, $processCreator, $mockManager, $mockLogger);
        $crawler->updateSourceRepo();

        $commits = $repo->getCommits();

        $this->assertEquals($commits[0]->getCommitId(), 'e0889c6');
        $this->assertEquals($commits[1]->getCommitId(), '22b8123');
        $this->assertEquals($commits[2]->getCommitId(), '9e7dd1e');
        $this->assertEquals($commits[3]->getCommitId(), 'b611a9c');
        $this->assertEquals($commits[4]->getCommitId(), 'a4ecaae');
        $this->assertEquals($commits[5]->getCommitId(), '46f2311');
        $this->assertEquals($commits[6]->getCommitId(), '26f16e1');

        $this->assertEquals($commits[0]->getDateCommitted(), new \DateTime('Fri, 26 May 2017 18:32:10 +0530'));
        $this->assertEquals($commits[1]->getDateCommitted(), new \DateTime('Fri, 26 May 2017 20:08:57 +0530'));
        $this->assertEquals($commits[2]->getDateCommitted(), new \DateTime('Sat, 27 May 2017 16:13:23 +0200'));
        $this->assertEquals($commits[3]->getDateCommitted(), new \DateTime('Tue, 30 May 2017 00:33:18 +0300'));
        $this->assertEquals($commits[4]->getDateCommitted(), new \DateTime('Thu, 08 Jun 2017 01:04:02 +0530'));
        $this->assertEquals($commits[5]->getDateCommitted(), new \DateTime('Sun, 11 Jun 2017 14:04:03 +0200'));
        $this->assertEquals($commits[6]->getDateCommitted(), new \DateTime('Mon, 12 Jun 2017 16:24:21 +0200'));

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
    }
}
