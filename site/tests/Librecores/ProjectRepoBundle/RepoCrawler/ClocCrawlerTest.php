<?php

namespace Test\Librecores\ProjectRepoBundle\RepoCrawler;

use Doctrine\Common\Persistence\ObjectManager;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;
use Librecores\ProjectRepoBundle\Entity\LanguageStat;
use Librecores\ProjectRepoBundle\RepoCrawler\ClocCrawler;
use Librecores\ProjectRepoBundle\Util\ExecutorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ClocCrawler
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 *
 * @see ClocCrawler
 */
class ClocCrawlerTest extends TestCase
{
    public function testCrawlWithMockData()
    {


        $mockOutput = file_get_contents(
            join(
                DIRECTORY_SEPARATOR,
                [__DIR__, '..', 'Resources', 'cloc-mock-output.json']
            )
        );

        /** @var ExecutorInterface $mockExecutor */
        $mockExecutor = $this->createMock(ExecutorInterface::class);

        // chaining the call breaks PHPUnit for some reason, so we are forced to
        // deal with a PHPCS warning
        $mockExecutor->expects($this->once())
            ->method('exec')
            ->willReturn($mockOutput);

        /** @var ObjectManager $managerMock */
        $managerMock = $this->createMock(ObjectManager::class);

        $crawler = new ClocCrawler($mockExecutor, $managerMock);
        $repo = new GitSourceRepo();
        $crawler->crawl($repo, 'dummy');

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

    // TODO: Testing with real CLOC
}
