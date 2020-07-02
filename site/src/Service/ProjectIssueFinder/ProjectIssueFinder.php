<?php


namespace App\Service\ProjectIssueFinder;

use App\Entity\Project;

class ProjectIssueFinder
{
    public function findProjectIssues(Project $p): ProjectIssues
    {
        $issues = new ProjectIssues($p);

        $issues->setLicenseMissing(!$p->getLicenseText());
        $issues->setIssueTrackerMissing(!$p->getIssueTracker());
        $issues->setHomePageMissing(!$p->getProjectUrl());
        $issues->setReadmeMissing(!$p->getDescriptionText());

        return $issues;
    }
}
