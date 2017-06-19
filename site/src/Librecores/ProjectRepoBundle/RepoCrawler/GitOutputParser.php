<?php


namespace Librecores\ProjectRepoBundle\RepoCrawler;

use InvalidArgumentException;
use Librecores\ProjectRepoBundle\Entity\Commit;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Librecores\ProjectRepoBundle\Repository\ContributorRepository;
use Psr\Log\LoggerInterface;

class GitOutputParser implements OutputParserInterface
{
    /**
     * @var ContributorRepository
     */
    private $contributors;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GitOutputParser constructor.
     * @param ContributorRepository $contributors
     * @param LoggerInterface $logger
     */

    public function __construct(ContributorRepository $contributors, LoggerInterface $logger)
    {
        $this->contributors = $contributors;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     * @see OutputParserInterface
     */
    public function parseCommits(SourceRepo $repo, string $outputString): array
    {
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
                $contributor = $this->contributors->getContributorForRepository($repo, $commitMatches[3], $commitMatches[2]);
                $commit = new Commit();
                $commit->setCommitId($commitMatches[1])
                    ->setRepository($repo)
                    ->setDateCommitted(new \DateTime($commitMatches[4]))
                    ->setContributor($contributor);

                //XXX: Find a better way to find file modifications in each commit
                $modificationMatches = [];
                if ($i < $len - 1 && preg_match('/(\d+) files? changed(?:, (\d+) insertions?\(\+\))?(?:, (\d+) deletions?\(-\))?/',
                    $output[$i + 1], $modificationMatches)) {
                    $commit->setFilesModified($modificationMatches[1]);

                    if(array_key_exists(2, $modificationMatches) && count($modificationMatches[2])) {
                        $commit->setLinesAdded($modificationMatches[2]);
                    }
                    if(array_key_exists(3, $modificationMatches) && count($modificationMatches[3])) {
                        $commit->setLinesRemoved($modificationMatches[3]);
                    }
                    $i++;   // skip the next line
                }

                $commits[] = $commit;
            }
        }

        // handle root commit without modification


        return $commits;
    }
}