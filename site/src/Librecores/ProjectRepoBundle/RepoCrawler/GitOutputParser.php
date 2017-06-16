<?php


namespace Librecores\ProjectRepoBundle\RepoCrawler;

use InvalidArgumentException;
use Librecores\ProjectRepoBundle\Entity\Commit;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Librecores\ProjectRepoBundle\Repository\ContributorRepository;

class GitOutputParser implements OutputParserInterface
{
    /**
     * @var ContributorRepository
     */
    private $contributors;

    public function __construct(ContributorRepository $contributors)
    {
        $this->contributors = $contributors;
    }

    /**
     * {@inheritdoc}
     * @see OutputParserInterface
     */
    public function parseCommits(SourceRepo $repo, string $outputString): array
    {
        $outputString = preg_replace('/^\h*\v+/m', '', trim($outputString));    // remove blank lines
        $output = explode("\n",$outputString);  // explode lines into array

        // every commit takes 2 lines
        //
        // commit info
        // files modified
        //
        // thus length of $output must be a multiple of 2
        $len = count($output);
        if ($len % 2 !== 0) {
            throw new InvalidArgumentException('Insufficient lines. Possibly corrupt output');
        }

        $commits = []; // stores the array of commits
        for ($i = 0; $i < $len; $i += 2) {

            // Every commit has 4 parts, id, author name, email, commit timestamp
            // in the format id|name|email|timestamp
            // explode to get 4 parts of the output line
            $parts = explode('|', trim($output[$i]), 4);
            $contributor = $this->contributors->getContributorForRepository($repo, $parts[2], $parts[1]);

            $commit = new Commit();
            $commit->setCommitId($parts[0])
                    ->setDateCommitted(new \DateTime($parts[3]))
                    ->setContributor($contributor);

            $commits[] = $commit;
        }

        return $commits;
    }
}