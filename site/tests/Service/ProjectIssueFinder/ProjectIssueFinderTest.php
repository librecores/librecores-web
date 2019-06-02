<?php

namespace App\Tests\Service\ProjectIssueFinder;

use App\Entity\Project;
use App\Service\ProjectIssueFinder\ProjectIssueFinder;
use PHPUnit\Framework\TestCase;

class ProjectIssueFinderTest extends TestCase
{
    public function testGetAlertsNotifiesMissingLicense()
    {
        $p = new Project();

        $projectIssueFinder = new ProjectIssueFinder();
        $issues = $projectIssueFinder->findProjectIssues($p);

        $this->assertTrue($issues->isLicenseMissing());
    }

    public function testFindProjectIssuesDoesNotNotifyWhenLicenseIsPresent()
    {
        $p = new Project();
        $p->setLicenseName('BSD-2-CLAUSE');
        $p->setLicenseText('BSD License Text');

        $projectIssueFinder = new ProjectIssueFinder();
        $issues = $projectIssueFinder->findProjectIssues($p);

        $this->assertFalse($issues->isLicenseMissing());
    }

    public function testFindProjectIssuesNotifiesMissingReadme()
    {
        $p = new Project();

        $projectIssueFinder = new ProjectIssueFinder();
        $issues = $projectIssueFinder->findProjectIssues($p);

        $this->assertTrue($issues->isReadmeMissing());
    }

    public function testFindProjectIssuesDoesNotNotifyWhenReadmeIsPresent()
    {
        $p = new Project();
        $p->setDescriptionText('readme');

        $projectIssueFinder = new ProjectIssueFinder();
        $issues = $projectIssueFinder->findProjectIssues($p);

        $this->assertFalse($issues->isReadmeMissing());
    }

    public function testFindProjectIssuesNotifiesMissingIssueTracker()
    {
        $p = new Project();

        $projectIssueFinder = new ProjectIssueFinder();
        $issues = $projectIssueFinder->findProjectIssues($p);

        $this->assertTrue($issues->isReadmeMissing());
    }

    public function testFindProjectIssuesDoesNotNotifyWhenIssueTrackerIsPresent()
    {
        $p = new Project();
        $p->setIssueTracker('http://example.com/issues.php');

        $projectIssueFinder = new ProjectIssueFinder();
        $issues = $projectIssueFinder->findProjectIssues($p);

        $this->assertFalse($issues->isIssueTrackerMissing());
    }

    public function testFindProjectIssuesNotifiesMissingHomePage()
    {
        $p = new Project();

        $projectIssueFinder = new ProjectIssueFinder();
        $issues = $projectIssueFinder->findProjectIssues($p);

        $this->assertTrue($issues->isHomePageMissing());
    }

    public function testFindProjectIssuesDoesNotNotifyWhenHomePageIsPresent()
    {
        $p = new Project();
        $p->setProjectUrl('http://example.com/project');

        $projectIssueFinder = new ProjectIssueFinder();
        $issues = $projectIssueFinder->findProjectIssues($p);

        $this->assertFalse($issues->isHomePageMissing());
    }
}
