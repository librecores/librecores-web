<?php

namespace Tests\Librecores\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test for default controller.
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * Test home page (/)
     */
    public function testHomeAction()
    {
        // We use separate environments for testing in local and CI
        // if env var SYMFONY_ENV exists, then pass it as options to kernel builder
        // else fallback to default. This is required for all tests

        $env = getenv('SYMFONY_ENV');
        $client = static::createClient($env ? [ 'environment' => $env ] : []);

        $crawler = $client->request('GET', '/');

        $this->assertEquals($client->getResponse()->getStatusCode(), 200);  // must be a 200 OK response

        // since most of the content is static, extensive testing is not required, asserting presence of certain key elements
        $this->assertEquals($crawler->filter('.librecores-home-banner-wordmark')->html(), 'LibreCores');
        $this->assertEquals($crawler->filter('.librecores-home-banner-claim')->html(), 'We drive Free and Open Digital Hardware.');   //expect certain values
    }

    // pageAction is not testable
}
