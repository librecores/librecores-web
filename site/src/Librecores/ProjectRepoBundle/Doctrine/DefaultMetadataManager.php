<?php
/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 12/6/17
 * Time: 9:46 PM
 */

namespace Librecores\ProjectRepoBundle\RepoCrawler;


use Librecores\ProjectRepoBundle\Entity\Commit;
use Librecores\ProjectRepoBundle\Entity\Contributor;
use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Repository\CommitRepository;
use Librecores\ProjectRepoBundle\Repository\ContributorRepository;

/**
 * Implementation of MetadataManagerInterface
 */
class DefaultMetadataManager implements MetadataManagerInterface
{
    // TODO: Implement caching

    /**
     * @var CommitRepository
     */
    private $commitRepository;

    /**
     * @var ContributorRepository
     */
    private $contributorRepository;

    /**
     * DefaultMetadataManager constructor.
     * @param CommitRepository $commitRepository
     * @param ContributorRepository $contributorRepository
     */
    public function __construct(
        CommitRepository $commitRepository,
        ContributorRepository $contributorRepository
    ) {
        $this->commitRepository = $commitRepository;
        $this->contributorRepository = $contributorRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshMetadata(Project $project): bool
    {
        // Currently noop, as no caching is performed
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommits(Project $project): array
    {
        return $this->commitRepository->get(
            $project->getSourceRepo()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCommitCount(Project $project): int
    {
        return $this->commitRepository->count(
            $project->getSourceRepo()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestCommit(Project $project): Commit
    {
        return $this->commitRepository->latest(
            $project->getSourceRepo()
        );
    }

    /**
     * {@inheritdoc}
     */
    function getFirstCommit(Project $project): Commit
    {
        return $this->commitRepository->first(
            $project->getSourceRepo()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getContributors(Project $project): array
    {
        return $this->contributorRepository->getContributorsForRepository(
            $project->getSourceRepo()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getContributorsCount(Project $project): int
    {
        return $this->contributorRepository->getContributorCountForRepository(
            $project->getSourceRepo()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTopContributors(Project $project, int $count = 5): array
    {
        return $this->contributorRepository->getTopContributorsForRepository(
            $project->getSourceRepo(),
            5
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCommitCountForContributor(Contributor $contributor): int
    {
        return $this->commitRepository->commitsByContributor(
            $contributor
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getContributorAvatar(Contributor $contributor): string
    {
        // https://en.gravatar.com/site/implement/images/php/
        // We use 32x32 px images and a 8 bit retro image as fallback
        // similar to Github
        return 'https://www.gravatar.com/avatar/'
            .md5(strtolower(trim($contributor->getEmail())))
            .'?s=32&d=retro';
    }

    /**
     * {@inheritdoc}
     */
    public function getCommitHistogram(
        Project $project,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        int $bucket
    ): array {
        // TODO: This function badly needs some form of caching
        // aggregation queries in mysql are very expensive, this function
        // will never use an index and always perform a full table scan
        return $this->commitRepository->histogram(
            $project->getSourceRepo(),
            $start,
            $end,
            $bucket
        );
    }
}