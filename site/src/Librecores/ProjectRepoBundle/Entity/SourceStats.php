<?php
namespace Librecores\ProjectRepoBundle\Entity;

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
     * @ORM\OneToMany(targetEntity="SourceStatsAuthor", mappedBy="sourceStats")
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
        $this->authors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->commitHistogramEntries = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param \Librecores\ProjectRepoBundle\Entity\SourceStatsAuthor $authors
     * @return SourceStats
     */
    public function addAuthor(\Librecores\ProjectRepoBundle\Entity\SourceStatsAuthor $authors)
    {
        $this->authors[] = $authors;

        return $this;
    }

    /**
     * Remove authors
     *
     * @param \Librecores\ProjectRepoBundle\Entity\SourceStatsAuthor $authors
     */
    public function removeAuthor(\Librecores\ProjectRepoBundle\Entity\SourceStatsAuthor $authors)
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
     * @param \Librecores\ProjectRepoBundle\Entity\SourceStatsCommitHistogram $commitHistogramEntries
     * @return SourceStats
     */
    public function addCommitHistogramEntry(\Librecores\ProjectRepoBundle\Entity\SourceStatsCommitHistogram $commitHistogramEntries)
    {
        $this->commitHistogramEntries[] = $commitHistogramEntries;

        return $this;
    }

    /**
     * Remove commitHistogramEntries
     *
     * @param \Librecores\ProjectRepoBundle\Entity\SourceStatsCommitHistogram $commitHistogramEntries
     */
    public function removeCommitHistogramEntry(\Librecores\ProjectRepoBundle\Entity\SourceStatsCommitHistogram $commitHistogramEntries)
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
