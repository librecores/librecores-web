<?php

namespace Librecores\ProjectRepoBundle\Util;

/**
 * Helper utility service, which provides methods for operating with GitHub projects.
 */

class GitHubUtil
{

    private $githubUrl;
    private $shieldsUrl;
    private $shieldsExt;
    private $sourceRepoUrl;
    private $issueTrackerUrl;

    public function __construct($githubUrl, $shieldsUrl, $shieldsExt)
    {
        $this->githubUrl       = $githubUrl;
        $this->shieldsUrl      = $shieldsUrl;
        $this->shieldsExt      = $shieldsExt;
        $this->sourceRepoUrl   = null;
        $this->issueTrackerUrl = null;
    }

    public function setUrls($sourceRepoUrl, $issueTrackerUrl)
    {
        $this->setSourceRepoUrl($sourceRepoUrl);
        $this->setIssueTrackerUrl($issueTrackerUrl);
    }

    public function getSourceRepoUrl()
    {
        return $this->sourceRepoUrl;
    }

    public function setSourceRepoUrl($sourceRepoUrl)
    {
        $this->sourceRepoUrl= $sourceRepoUrl;
    }

    public function getIssueTrackerUrl()
    {
        return $this->issueTrackerUrl;
    }

    public function setIssueTrackerUrl($issueTrackerUrl)
    {
        $this->issueTrackerUrl = $issueTrackerUrl;
    }

    public function getPullRequestsUrl()
    {
        if ($this->isSourceRepoOnGitHub())
        {
            return $this->githubUrl . $this->getGitHubPath($this->sourceRepoUrl) . '/pulls';
        }
        else
        {
            return null;
        }
    }

    public function isSourceRepoOnGitHub()
    {
        return (strpos($this->sourceRepoUrl, 'github') !== false);
    }

    public function isIssueTrackerOnGitHub()
    {
        return (strpos($this->issueTrackerUrl, 'github') !== false);
    }

    public function getStarsBadgeImage()
    {
        if ($this->isSourceRepoOnGitHub())
        {
            return $this->getShieldsUrl('stars') .
                   $this->getGitHubPath($this->sourceRepoUrl) .
                   $this->getShieldsExt();
        }
        else
        {
            return null;
        }
    }

    public function getOpenPRsBadgeImage()
    {
        if ($this->isSourceRepoOnGitHub())
        {
            return $this->getShieldsUrl('issues-pr') .
                   $this->getGitHubPath($this->sourceRepoUrl) .
                   $this->getShieldsExt();
        }
        else
        {
            return null;
        }
    }

    public function getIssuesBadgeImage()
    {
        if ($this->isSourceRepoOnGitHub())
        {
            return $this->getShieldsUrl('issues') .
                   $this->getGitHubPath($this->sourceRepoUrl) .
                   $this->getShieldsExt();
        }
        else
        {
            return null;
        }
    }

    private function getShieldsUrl($type)
    {
        return $this->shieldsUrl . $type . '/';
    }

    private function getShieldsExt()
    {
        return '.' . $this->shieldsExt;
    }

    private function getGitHubPath($url)
    {
        return $this->getBetween($url, $this->githubUrl, '.git');
    }

    private function getBetween($input, $start, $end)
    {
        return substr($input, strlen($start)+strpos($input, $start), (strlen($input) - strpos($input, $end))*(-1));
    }
}
