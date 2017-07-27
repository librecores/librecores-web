<?php
/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 27/7/17
 * Time: 3:52 PM
 */

namespace Librecores\ProjectRepoBundle\RepoCrawler;

use Doctrine\Common\Persistence\ObjectManager;
use Github\HttpClient\Message\ResponseMediator;
use GuzzleHttp\Client as HttpClient;
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
    private const GH_REGEX = '/https:\/\/github\.com\/(.+)\/(.+)(?:\.git)?';

    private const GH_API_ENDPOINT = 'https://api.github.com/graphql';

    /**
     * @var GithubApiService
     */
    private $ghApi;

    /**
     * @var string
     */
    private $ghUser;

    /**
     * @var string
     */
    private $ghRepo;

    public function __construct(SourceRepo $repo, MarkupToHtmlConverter $markupConverter,
                                ProcessCreator $processCreator, ObjectManager $manager,
                                LoggerInterface $logger, GithubApiService $ghApi)
    {
        parent::__construct($repo, $markupConverter, $processCreator, $manager, $logger);
        $this->ghApi = $ghApi;
    }

    public function isValidRepoType(): bool
    {
        return preg_match(static::GH_REGEX, $this->repo->getUrl());
    }

    public function updateSourceRepo()
    {
        $this->updateGithubMetrics();
        parent::updateCommits();
    }

    private function updateGithubMetrics()
    {
        preg_match(static::GH_REGEX, $this->repo->getUrl(), $matches);

        $data = json_encode([
                                'query' =>
                                    <<<'QUERY'
query {
  repository(owner:$owner, name:$repository) { 
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
QUERY
                                ,
                                'variables' => [
                                    'owner' => $this->ghUser,
                                    'repository' => $this->ghRepo,
                                ],
                            ]);

        //TODO: Replace with API method when KnpLabs/php-github-api v2.6 is released


        $client = new HttpClient();

        $response = $client->post(static::GH_API_ENDPOINT, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $data,
        ]);

        $res = json_decode($response->getBody()->getContents(), true);
        if (200 === $response->getStatusCode()) {

            $this->repo->getSourceStats()->setForks($res['forks']['totalCount']);
            $this->repo->getSourceStats()->setOpenIssues($res['issues']['totalCount']);
            $this->repo->getSourceStats()->setOpenPullRequests($res['pullRequests']['totalCount']);
            $this->repo->getSourceStats()->setStars($res['stargazers']['totalCount']);
            $this->logger->info('Fetched GitHub metrics successfully');

        } else {
            $this->logger->error('Error fetching repository data:'.$response->getStatusCode()
                                 .' - '.$response->getBody()->getContents());
            throw new \RuntimeException("Error response from GitHub");
        }

    }

    /**
     * {@inheritdoc}
     * @return string|null the license text, or null if none was found
     */
    public function getLicenseTextSafeHtml(): ?string
    {
        $response = $this->ghApi->getClient()->getHttpClient()
                                ->get("licenses/repos/$this->ghUser/$this->ghRepo/license",
                                      ['Accept' => 'application/json, application/vnd.github.drax-preview+json, application/vnd.github.v3.raw']);
        $license  = ResponseMediator::getContent($response);

        return $this->markupConverter->convert($license['content']);
    }

    /**
     * Get the description of the repository as safe HTML
     *
     * Usually this is the content of the README file.
     *
     * "Safe" HTML is stripped from all possibly malicious content, such as
     * script tags, etc.
     *
     * @return string|null the repository description, or null if none was found
     */
    public function getDescriptionSafeHtml(): ?string
    {
        $contents = $this->ghApi->getClient()->repository()->contents()
                                ->configure('raw')->readme($this->ghUser, $this->ghRepo);

        return $this->markupConverter->convert($contents['content']);
    }
}
