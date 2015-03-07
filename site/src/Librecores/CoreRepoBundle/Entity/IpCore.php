<?php

namespace Librecores\CoreRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IpCore
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class IpCore
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="vendor", type="string", length=255)
     */
    private $vendor;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="projectUrl", type="string", length=255)
     */
    private $projectUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="issueTracker", type="string", length=255)
     */
    private $issueTracker;
    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set vendor
     *
     * @param string $vendor
     * @return IpCore
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;

        return $this;
    }

    /**
     * Get vendor
     *
     * @return string 
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return IpCore
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set projectUrl
     *
     * @param string $projectUrl
     * @return IpCore
     */
    public function setProjectUrl($projectUrl)
    {
        $this->projectUrl = $projectUrl;

        return $this;
    }

    /**
     * Get projectUrl
     *
     * @return string 
     */
    public function getProjectUrl()
    {
        return $this->projectUrl;
    }

    /**
     * Set issueTracker
     *
     * @param string $issueTracker
     * @return IpCore
     */
    public function setIssueTracker($issueTracker)
    {
        $this->issueTracker = $issueTracker;

        return $this;
    }

    /**
     * Get issueTracker
     *
     * @return string 
     */
    public function getIssueTracker()
    {
        return $this->issueTracker;
    }
}
