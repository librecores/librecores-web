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
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * Enrich metadata from GitHub
 *
 * This implementation uses GitHub GraphQL API to fetch data from GitHub API
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
class UpdateGitHubMetadataConsumer implements ConsumerInterface
{

    private const GH_REGEX =
        '/^https:\/\/github\.com\/([^\/]+)\/([^\/]+?)(?:\.git)?$/';

    /**
     * @var ProjectRepository
     */
    private $repository;

    /**
     * @var GitHubApiService
     */
    private $githubApiService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

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

        $this->repository = $repository;
        $this->githubApiService = $githubApiService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @param AMQPMessage $msg The message
     *
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        try {
            $projectId = (int) unserialize($msg->body);
            /** @var Project $project */
            $project = $this->repository->find($projectId);

            if (!$project) {
                // this should not happen in production
                // but happens in dev if for some reason we clear the projects
                // table
                $this->logger->error(
                    "Unable to update project with ID $projectId: project does "
                    ."not exist."
                );

                return true; // don't requeue
            }

            $this->updateGithubMetadata($project);

            return true;
        } catch (Exception $e) {
            // We got an unexpected Exception. We assume this is a one-off event
            // and just log it, but otherwise keep the consumer running for the
            // next requests.
            $this->logger->error(
                "Processing of repository resulted in an ".get_class($e)
            );
            $this->logger->error('Message: '.$e->getMessage());
            $this->logger->error('Trace: '.$e->getTraceAsString());

            return false;
        }
    }

    /**
     * Extract and update Github specific metrics
     *
     * @param Project $project
     */
    private function updateGithubMetadata(Project $project)
    {
        try {
            $this->logger->info("Enriching project: {$project->getFqname()} with GitHub metadata");
            $client = $this->getGithubClient($project);

            $repoUrl = $project->getSourceRepo()->getUrl();

            $isGithubRepo = preg_match(static::GH_REGEX, $repoUrl, $matches);

            if (!$isGithubRepo) {
                // skip processing
                $this->logger->info(
                    "Skipped project {$project->getFqname()}"
                    ."as it is not a GitHub repo"
                );

                return;
            }

            list($user, $repo) = array_slice($matches, 1);
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
            $this->logger->error(
                'Unable to fetch data from Github: '
                .$ex->getMessage()
            );
        }
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
     * @return Client
     *
     */
    private function getGithubClient(Project $project): Client
    {

        $user = $project->getParentUser();

        if (null === $user) {
            $user = $project->getParentOrganization()->getCreator();
        }

        return $this->githubApiService->getAuthenticatedClientForUser($user);
    }
}
