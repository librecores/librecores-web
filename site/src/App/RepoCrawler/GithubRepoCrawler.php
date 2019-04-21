<?php

namespace App\RepoCrawler;

use App\Util\MarkupToHtmlConverter;
use App\Util\ProcessCreator;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Github\Client;
use Librecores\ProjectRepoBundle\Doctrine\ProjectMetricsProvider;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Librecores\ProjectRepoBundle\Repository\CommitRepository;
use Librecores\ProjectRepoBundle\Repository\ContributorRepository;
use Librecores\ProjectRepoBundle\Service\GitHub\GitHubApiService;
use Psr\Log\LoggerInterface;

/**
 * Crawl and extract metadata from a remote git repository
 *
 * This implementation uses GitHub GraphQL API to fetch data from GitHub API
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
class GithubRepoCrawler extends GitRepoCrawler
{

    /**
     * Github URL regex
     *
     * @var string
     */
    private const GH_REGEX = '/^https:\/\/github\.com\/([^\/]+)\/([^\/]+?)(?:\.git)?$/';

    /**
     * @var Client
     */
    private $githubClient;

    /**
     * @var GitHubApiService
     */
    private $githubApi;

    /**
     * @var string
     */
    private $githubUser;

    /**
     * @var string
     */
    private $githubRepoName;

    private $githubData;

    /**
     * @inheritDoc
     */
    public function __construct(
        SourceRepo $repo,
        MarkupToHtmlConverter $markupConverter,
        ProcessCreator $processCreator,
        CommitRepository $commitRepository,
        ContributorRepository $contributorRepository,
        ObjectManager $manager,
        LoggerInterface $logger,
        GitHubApiService $ghApi,
        ProjectMetricsProvider $projectMetricsProvider
    ) {
        parent::__construct(
            $repo,
            $markupConverter,
            $processCreator,
            $commitRepository,
            $contributorRepository,
            $manager,
            $logger,
            $projectMetricsProvider
        );
        $this->githubApi = $ghApi;
        preg_match(static::GH_REGEX, $this->repo->getUrl(), $matches);
        $this->githubUser = $matches[1];
        $this->githubRepoName = $matches[2];
    }

    /**
     * Check whether the given URL is a valid Github repository URL
     *
     * @param string $repoUrl
     *
     * @return bool
     */
    public static function isGithubRepoUrl($repoUrl)
    {
        return preg_match(static::GH_REGEX, $repoUrl);
    }

    /**
     * {@inheritDoc}
     * @see RepoCrawler::isValidRepoType()
     */
    public function isValidRepoType(): bool
    {
        return static::isGithubRepoUrl($this->repo->getUrl());
    }

    /**
     * {@inheritdoc}
     */
    public function updateProject()
    {
        $success = parent::updateProject();
        $this->updateGithubMetrics();

        return $success;
    }

    /**
     * Get a GitHub client with permissions for the user associated with the
     * current SourceRepo
     *
     * @return Client
     *
     * @throws Exception if GitHub credentials are not found
     */
    protected function getGithubClient()
    {

        $user = $this->repo->getProject()->getParentUser();

        if (null === $user) {
            $user = $this->repo->getProject()
                ->getParentOrganization()->getCreator();
        }

        $this->githubClient = $this->githubApi
            ->getAuthenticatedClientForUser($user);

        if (null === $this->githubClient) {
            throw new Exception('GitHub access token not available');
        }

        return $this->githubClient;
    }

    /**
     * Call GitHub API and fetch repository data
     *
     * @return array
     *
     * @throws Exception
     */
    protected function getGithubData()
    {
        if (null !== $this->githubData) {
            return $this->githubData;
        }
        $client = $this->getGithubClient();

        $query =
            <<<'QUERY'
query getRepoData (
  $owner: String!
  $repository: String!
 ) {
  repository(owner: $owner, name: $repository) {
    forks {
      totalCount
    }
    hasIssuesEnabled
    updatedAt
    pushedAt
    issues(states:OPEN) {
      totalCount
    }
    pullRequests(states:OPEN) {
      totalCount
    }
    stargazers {
      totalCount
    }
    watchers {
      totalCount
    }
  }
}
QUERY;

        $variables = [
            'owner' => $this->githubUser,
            'repository' => $this->githubRepoName,
        ];

        $res = $client->graphql()->execute($query, $variables);

        if (array_key_exists('errors', $res)) {
            $message = $res['errors'][0]['message'];
            throw new CrawlerException($this->repo, $message);
        }

        $this->githubData = $res['data']['repository'];

        return $this->githubData;
    }

    /**
     * Extract and update Github specific metrics
     */
    protected function updateGithubMetrics()
    {
        try {
            $data = $this->getGithubData();
            $project = $this->repo->getProject();

            $project->setForks($data['forks']['totalCount']);

            if ($data['hasIssuesEnabled']) {
                $project->setOpenIssues($data['issues']['totalCount']);
            }

            $project->setOpenPullRequests($data['pullRequests']['totalCount']);
            $project->setStars($data['stargazers']['totalCount']);
            $project->setWatchers($data['watchers']['totalCount']);

            $dateLastActivity = new DateTime($data['updatedAt']);
            $project->setDateLastActivityOccurred($dateLastActivity);

            $this->manager->persist($project);
            $this->logger->info('Fetched GitHub metrics successfully');
        } catch (Exception $ex) {
            $this->logger->error(
                'Unable to fetch data from Github: '
                .$ex->getMessage()
            );
        }
    }
}
