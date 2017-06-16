<?php
namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * SourceStats
 *
 * Statistics about a source repository
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class SourceStats
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\OneToMany(targetEntity="Librecores\ProjectRepoBundle\Entity\Contributor", mappedBy="sourceStats")
     */
    private $authors;

    /**
     * @ORM\OneToMany(targetEntity="SourceStatsCommitHistogram", mappedBy="sourceStats")
     */
    private $commitHistogramEntries;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->commitHistogramEntries = new ArrayCollection();
    }

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
     * Add authors
     *
     * @param Contributor $authors
     * @return SourceStats
     */
    public function addAuthor(Contributor $authors)
    {
        $this->authors[] = $authors;

        return $this;
    }

    /**
     * Remove authors
     *
     * @param Contributor $authors
     */
    public function removeAuthor(Contributor $authors)
    {
        $this->authors->removeElement($authors);
    }

    /**
     * Get authors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * Add commitHistogramEntries
     *
     * @param SourceStatsCommitHistogram $commitHistogramEntries
     * @return SourceStats
     */
    public function addCommitHistogramEntry(SourceStatsCommitHistogram $commitHistogramEntries)
    {
        $this->commitHistogramEntries[] = $commitHistogramEntries;

        return $this;
    }

    /**
     * Remove commitHistogramEntries
     *
     * @param SourceStatsCommitHistogram $commitHistogramEntries
     */
    public function removeCommitHistogramEntry(SourceStatsCommitHistogram $commitHistogramEntries)
    {
        $this->commitHistogramEntries->removeElement($commitHistogramEntries);
    }

    /**
     * Get commitHistogramEntries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommitHistogramEntries()
    {
        return $this->commitHistogramEntries;
    }
}
