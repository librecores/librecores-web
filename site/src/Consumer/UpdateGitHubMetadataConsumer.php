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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * UpdateGithubMetadataConsumer constructor.
     *
     * @param ProjectRepository      $repository
     * @param GitHubApiService       $githubApiService
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface        $logger
     */
    public function __construct(
        ProjectRepository $repository,
        GitHubApiService $githubApiService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        parent::__construct($repository, $logger);
        $this->githubApiService = $githubApiService;
        $this->entityManager = $entityManager;
    }

    /**
     * Extract and update Github specific metrics
     *
     * @param Project $project
     *
     * @return bool
     */
    protected function processProject(Project $project) : bool
    {
        try {
            $this->logger->info("Enriching project: {$project->getFqname()} with GitHub metadata");

            // GraphQL (GitHub APIv4) queries must use an authenticated client,
            // we cannot authenticate as application.
            // TODO: Implement fallback with GitHub v3 REST API.
            // https://github.com/librecores/librecores-web/issues/446
            $client = $this->getAuthenticatedGithubClient($project);
            if ($client === null) {
                $this->logger->warning("Unable to get authenticated GitHub client. Unable to ".
                                       "update GitHub meta data for {$project->getFqname()}.");
                return true;

            }

            $repoUrl = $project->getSourceRepo()->getUrl();

            $isGithubRepo = GitHubApiService::isGitHubRepoUrl($repoUrl);

            if (!$isGithubRepo) {
                // skip processing
                $this->logger->info(
                    "Skipped project {$project->getFqname()}"
                    ."as it is not a GitHub repo"
                );

                return true;
            }

            $repoInfo = GitHubApiService::parseGitHubRepoUrl($repoUrl);
            $user = $repoInfo['user'];
            $repo = $repoInfo['repository'];
            $data = $this->fetchGithubMetadata($client, $user, $repo);

            $project->setForks($data['forks']['totalCount']);

            if ($data['hasIssuesEnabled']) {
                $project->setOpenIssues($data['issues']['totalCount']);
            }

            $project->setOpenPullRequests($data['pullRequests']['totalCount']);
            $project->setStars($data['stargazers']['totalCount']);
            $project->setWatchers($data['watchers']['totalCount']);

            $dateLastActivity = new DateTime($data['updatedAt']);
            $project->setDateLastActivityOccurred($dateLastActivity);

            $this->entityManager->persist($project);
            $this->entityManager->flush();
            $this->logger->info('Fetched GitHub metrics successfully');
        } catch (Exception $ex) {
            // Report this error and do not requeue
            $this->logger->error(
                'Unable to fetch data from Github: '
                .$ex->getMessage()
            );
        }

        return true;
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
     * @param Project $project
     *
     * @return Client|null An authenticated Client, or null if authentication
     *                     failed.
     */
    private function getAuthenticatedGithubClient(Project $project): ?Client
    {

        $user = $project->getParentUser();

        if (null === $user) {
            $user = $project->getParentOrganization()->getCreator();
        }

        return $this->githubApiService->getAuthenticatedClientForUser($user);
    }
}
