<?php


namespace App\Consumer;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\GitHub\GitHubApiService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Github\Client;
use Github\Exception\ErrorException;
use Psr\Log\LoggerInterface;

/**
 * Enrich metadata from GitHub
 *
 * This implementation uses GitHub GraphQL API to fetch data from GitHub API
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
class UpdateGitHubMetadataConsumer extends AbstractProjectUpdateConsumer
{

    /**
     * @var GitHubApiService
     */
    private $githubApiService;

    /**
     * UpdateGithubMetadataConsumer constructor.
     *
     * @param ProjectRepository      $repository
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface        $logger
     * @param GitHubApiService       $githubApiService
     */
    public function __construct(
        ProjectRepository $repository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        GitHubApiService $githubApiService
    ) {
        parent::__construct($repository, $entityManager, $logger);
        $this->githubApiService = $githubApiService;
    }

    /**
     * Extract and update Github specific metrics
     *
     * @return bool
     */
    protected function processProject() : bool
    {
        try {
            $this->logger->info("Enriching project: {$this->getProject()->getFqname()} with GitHub metadata");

            // GraphQL (GitHub APIv4) queries must use an authenticated client,
            // we cannot authenticate as application.
            // TODO: Implement fallback with GitHub v3 REST API.
            // https://github.com/librecores/librecores-web/issues/446
            $client = $this->getAuthenticatedGithubClient($this->getProject());
            if ($client === null) {
                $this->logger->warning("Unable to get authenticated GitHub client. Unable to ".
                                       "update GitHub meta data for {$this->getProject()->getFqname()}.");
                return self::PROCESSING_SUCCESSFUL;

            }

            $repoUrl = $this->getProject()->getSourceRepo()->getUrl();

            $isGithubRepo = GitHubApiService::isGitHubRepoUrl($repoUrl);

            if (!$isGithubRepo) {
                // skip processing
                $this->logger->info(
                    "Skipped project {$this->getProject()->getFqname()}"
                    ."as it is not a GitHub repo"
                );

                return self::PROCESSING_SUCCESSFUL;
            }

            $repoInfo = GitHubApiService::parseGitHubRepoUrl($repoUrl);
            $user = $repoInfo['user'];
            $repo = $repoInfo['repository'];
            $data = $this->fetchGithubMetadata($client, $user, $repo);

            $this->getProject()->setForks($data['forks']['totalCount']);

            if ($data['hasIssuesEnabled']) {
                $this->getProject()->setOpenIssues($data['issues']['totalCount']);
            }

            $this->getProject()->setOpenPullRequests($data['pullRequests']['totalCount']);
            $this->getProject()->setStars($data['stargazers']['totalCount']);
            $this->getProject()->setWatchers($data['watchers']['totalCount']);

            $dateLastActivity = new DateTime($data['updatedAt']);
            $this->getProject()->setDateLastActivityOccurred($dateLastActivity);

            $this->entityManager->persist($this->getProject());
            $this->entityManager->flush();
            $this->logger->info('Fetched GitHub metrics successfully');
        } catch (Exception $ex) {
            // Report this error and do not requeue
            $this->logger->error(
                'Unable to fetch data from Github: '.$ex->getMessage()
            );
        }

        return self::PROCESSING_SUCCESSFUL;
    }

    /**
     * Call GitHub API and fetch repository data
     *
     * @param Client $client
     * @param string $githubUser
     * @param string $githubRepoName
     *
     * @return array
     *
     * @throws ErrorException
     */
    private function fetchGithubMetadata(
        Client $client,
        string $githubUser,
        string $githubRepoName
    ) {
        $this->logger->debug("Fetching GitHub metadata for $githubUser/$githubRepoName");

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
            'owner' => $githubUser,
            'repository' => $githubRepoName,
        ];

        $res = $client->graphql()->execute($query, $variables);

        if (array_key_exists('errors', $res)) {
            $message = $res['errors'][0]['message'];
            throw new ErrorException($message);
        }

        return $res['data']['repository'];
    }

    /**
     * Get a GitHub client with permissions for the user associated with the
     * current SourceRepo
     *
     * @param Project $this->getProject()
     *
     * @return Client|null An authenticated Client, or null if authentication
     *                     failed.
     */
    private function getAuthenticatedGithubClient(Project $project): ?Client
    {

        $user = $this->getProject()->getParentUser();

        if (null === $user) {
            $user = $this->getProject()->getParentOrganization()->getCreator();
        }

        return $this->githubApiService->getAuthenticatedClientForUser($user);
    }
}
