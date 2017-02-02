<?php

namespace Librecores\ProjectRepoBundle\Util;

/**
 * Helper class, which provides methods for operating with GitHub projects.
 */
class GitHubUtil {
  
  private $url;
  
  public function __construct($url) {
    $this->url = $url;
  }
  
  public function isOnGitHub()
  {
    if (strpos($this->url, 'github') !== false) {
      return true;
    }
    return false;
  }
  
  public function getStarsBadgeImage()
  {
    return 'https://img.shields.io/github/stars/' . $this->getGitHubPath($this->url) . '.svg';
  }
  
  public function getIssuesBadgeImage()
  {
    return 'https://img.shields.io/github/issues/' . $this->getGitHubPath($this->url) . '.svg';
  }
  
  public function getOpenPRsBadgeImage()
  {
    return 'https://img.shields.io/github/issues-pr/' . $this->getGitHubPath($this->url) . '.svg';
  }
  
  public function getGitHubPath()
  {
    return $this->getBetween($this->url, 'github.com/','.git');
  }
  
  private function getBetween($input, $start, $end)
  {
    $substr = substr($input, strlen($start)+strpos($input, $start), (strlen($input) - strpos($input, $end))*(-1));
    return $substr;
  }
}