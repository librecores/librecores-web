<?php

namespace Librecores\ProjectRepoBundle\RepoCrawler;


use Doctrine\Common\Persistence\ObjectManager;
use Librecores\ProjectRepoBundle\Entity\LanguageStat;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Librecores\ProjectRepoBundle\Entity\SourceStats;
use Librecores\ProjectRepoBundle\Util\ExecutorInterface;

/**
 * SourceCrawler that extracts line of code information
 *
 * Implementation uses Cloc: https://github.com/AlDanial/cloc
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
class ClocCrawler implements SourceCrawlerInterface
{

    /**
     * @var ExecutorInterface
     */
    private $executor;

    /**
     * @var ObjectManager
     */
    private $manager;

    public function __construct(
        ExecutorInterface $executor,
        ObjectManager $manager
    ) {
        $this->executor = $executor;
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function crawl(SourceRepo $repository, string $srcDir)
    {
        $result = $this->executor->exec(
            'cloc',
            ['--json', '--skip-uniqueness', $srcDir]
        );
        $cloc = json_decode($result, true);

        $sourceStats = $repository->getSourceStats();
        $sourceStats->setAvailable(true)
            ->setTotalFiles($cloc['header']['n_files'])
            ->setTotalLinesOfCode($cloc['SUM']['code'])
            ->setTotalBlankLines($cloc['SUM']['blank'])
            ->setTotalLinesOfComments($cloc['SUM']['comment']);

        unset($cloc['header'], $cloc['SUM']);

        foreach ($cloc as $lang => $value) {
            $languageStat = $this->parseLangStat($lang, $value);
            $sourceStats->addLanguageStat($languageStat);
        }

        $repository->setSourceStats($sourceStats);

        $this->manager->persist($repository);
    }

    private function parseLangStat($lang, $value): LanguageStat
    {
        $stat = new LanguageStat();

        return $stat->setLanguage($lang)
            ->setFileCount($value['nFiles'])
            ->setLinesOfCode($value['code'])
            ->setCommentLineCount($value['comment'])
            ->setBlankLineCount($value['blank']);
    }
}