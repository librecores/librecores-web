<?php

namespace Tests\Librecores\ProjectRepoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test for DefaultController
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
class DefaultControllerTest extends WebTestCase
{
    public function testUserOrOrgViewAction()
    {
        // We use separate environments for testing in local and CI
        // if env var SYMFONY_ENV exists, then pass it as options to kernel builder
        // else fallback to default. This is required for all tests

        $env = getenv('SYMFONY_ENV');
        $client = static::createClient($env ? ['environment' => $env] : []);

        // test for a user

        $crawler = $client->request('GET', '/test');

        $this->assertEquals($client->getResponse()->getStatusCode(), 200);

        // ensure header contains user's name
        $this->assertEquals($crawler->filter('.librecores-user-page-header > h1')->eq(0)->text(), 'test');

        $crawler = $client->request('GET', '/openrisc');
        $this->assertEquals($crawler->filter('html body div#maincontent.container h1')->text(), 'openrisc : OpenRISC');
    }
}
