<?php
/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 27/7/17
 * Time: 3:52 PM
 */

namespace Librecores\ProjectRepoBundle\RepoCrawler;

use Doctrine\Common\Persistence\ObjectManager;
use Github\Client;
use Github\Exception\RuntimeException;
use Github\HttpClient\Message\ResponseMediator;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;
use Librecores\ProjectRepoBundle\Util\GithubApiService;
use Librecores\ProjectRepoBundle\Util\MarkupToHtmlConverter;
use Librecores\ProjectRepoBundle\Util\ProcessCreator;
use Psr\Log\LoggerInterface;

class GithubRepoCrawler extends GitRepoCrawler
{

    /**
     * Github URL regex
     * @var string
     */
    private const GH_REGEX = '/^https:\/\/github\.com\/(.+)\/(.+?)(?:\.git)?$/';

    /**
     * @var Client
     */
    private $githubClient;

    /**
     * @var GithubApiService
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

    public function __construct(SourceRepo $repo, MarkupToHtmlConverter $markupConverter,
                                ProcessCreator $processCreator, ObjectManager $manager,
                                LoggerInterface $logger, GithubApiService $ghApi)
    {
        parent::__construct($repo, $markupConverter, $processCreator, $manager, $logger);
        $this->githubApi = $ghApi;
        preg_match(static::GH_REGEX, $this->repo->getUrl(), $matches);
        $this->githubUser = $matches[1];
        $this->githubRepoName = $matches[2];
    }

    /**
     * Check whether the given URL is a valid Github repository URL
     * @param $repoUrl
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
    public function updateSourceRepo()
    {
        $this->updateGithubMetrics();
        parent::updateSourceRepo();
    }

    private function getGithubClient()
    {

        if (null === $this->githubClient) {
            $user = $this->repo->getProject()->getParentUser();

            if (null === $user) {
                $user = $this->repo->getProject()->getParentOrganization()->getCreator();
            }

            $this->githubClient = $this->githubApi->getAuthenticatedClientForUser($user);

            // TODO: Fallback to personal token if an account is still not available
        }

        return $this->githubClient;
    }

    /**
     * Extract and update Github specific metrics
     */
    private function updateGithubMetrics()
    {

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
    issues(states:OPEN) {
      totalCount
    }
    pullRequests(states:OPEN) {
      totalCount
    }
    stargazers {
      totalCount
    }
  }
}
QUERY;

        $variables = [
            'owner' => $this->githubUser,
            'repository' => $this->githubRepoName,
        ];

        $client = $this->getGithubClient();

        if (null === $client) {
            // we cannot extract any data from github in this case
            $this->logger->error('Unable to fetch data from Github: No access token');
            return;
        }
        try {
            $res = $client->graphql()->execute($query, $variables);

            $data = $res['data']['repository'];

            $this->repo->getSourceStats()->setForks($data['forks']['totalCount']);
            $this->repo->getSourceStats()->setOpenIssues($data['issues']['totalCount']);
            $this->repo->getSourceStats()->setOpenPullRequests($data['pullRequests']['totalCount']);
            $this->repo->getSourceStats()->setStars($data['stargazers']['totalCount']);
            $this->logger->info('Fetched GitHub metrics successfully');
        } catch (RuntimeException $ex) {
            $this->logger->error('Unable to fetch data from Github: '
                                   .$ex->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     * @return string|null the license text, or null if none was found
     */
    public function getLicenseTextSafeHtml(): ?string
    {
        try {
            $client = $this->getGithubClient();

            if (null === $client) {
                // we cannot extract any data from github in this case
                return parent::getLicenseTextSafeHtml();
            }
            $response = $client->getHttpClient()
                               ->get("licenses/repos/$this->githubUser/$this->githubRepoName/license",
                                     ['Accept' => 'application/json, application/vnd.github.drax-preview+json, application/vnd.github.v3.raw']);
            $license  = ResponseMediator::getContent($response);

            return $this->markupConverter->convert($license);
        } catch (RuntimeException $e) {
            $this->logger->warning('Falling back to repository scanning, unable to fetch license from Github: '
                                   .$e->getMessage());
            return parent::getLicenseTextSafeHtml();
        }
    }

    /**
     * {@inheritdoc}
     * @return string|null the repository description, or null if none was found
     */
    public function getDescriptionSafeHtml(): ?string
    {
        try {
            $client = $this->getGithubClient();

            if (null === $client) {
                // we cannot extract any data from github in this case
                return parent::getDescriptionSafeHtml();
            }
            /** @var string $contents */
            $contents = $client->repository()->contents()
                               ->configure('raw')->readme($this->githubUser, $this->githubRepoName);

            return $this->markupConverter->convert($contents);
        } catch (RuntimeException $e) {
            $this->logger->warning('Falling back to repository scanning, unable to fetch description from Github: '
                                   .$e->getMessage());
            return parent::getDescriptionSafeHtml();
        }
    }
}
