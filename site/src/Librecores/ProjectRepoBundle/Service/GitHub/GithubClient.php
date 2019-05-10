<?php


namespace Librecores\ProjectRepoBundle\Service\GitHub;


use Github\Client;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;

class GithubClient extends Client
{
    private const USER_AGENT = 'LibreCores.org (https://www.librecores.org)';

    public function __construct()
    {
        parent::__construct();
        $builder = $this->getHttpClientBuilder();
        $builder->addPlugin(new AuthenticationRequiredCheckPlugin());
        $builder->removePlugin(HeaderDefaultsPlugin::class);
        $builder->addPlugin(
            new HeaderDefaultsPlugin(['User-Agent' => static::USER_AGENT])
        );
    }

}
