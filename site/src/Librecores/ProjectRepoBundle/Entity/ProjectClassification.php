<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A ProjectClassification represents a single classification for a Project
 *
 * A group of related categories can be used to classify the projects
 *
 * @author Sandip Kumar Bhuyan <sandipbhuyan@gmail.com>
 *
 * @ORM\Table(name="ProjectClassification")
 * @ORM\Entity
 */
class ProjectClassification implements NormalizableInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Classifiers for Project Classification
     *
     * This attribute holds the classifier for the classification of the
     * projects categories. The classification is a combination of categories.
     * Parent and child are separated by '::'.
     *
     * @var string
     *
     * @ORM\Column(name="classification", type="text")
     */
    private $classification;

    /**
     * Project entity object for a classification
     *
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="classifications")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $project;

    /**
     * ProjectClassification creation date/time
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * User who added this classification to the project
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Normalizes the object into an array of scalars|arrays.
     *
     * @param NormalizerInterface $serializer The serializer is given so that you
     *                                        can use it to serialize objects contained within this object
     * @param string|null         $format     The format is optionally given to be able to serialize differently
     *                                        based on different output formats
     * @param array               $context    Options for serializing this object
     *
     * @return array|string|int|float|bool
     */
    public function normalize(NormalizerInterface $serializer, $format = null, array $context = array())
    {
        return [
            'classifications' => $this->getClassification(),
            'projectName' => $this->getProject()->getName(),
            'projectDisplayName' => $this->getProject()->getDisplayName(),
            'projectTagName' => $this->getProject()->getTagline(),
            'projectDataAdded' => $this->getProject()->getDateAdded(),
            'projectDateLastActivityOccurred' => $this->getProject()->getDateLastActivityOccurred(),
            'projectmostUsedLanguage' => $this->getProject()->getSourceRepo()->getSourceStats()->getMostUsedLanguage(),
            'projectParentUserName' => $this->getProject()->getParentName(),
        ];
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set classification string for the Project
     *
     * @param string $classification
     *
     * @return ProjectClassification
     */
    public function setClassification($classification)
    {
        $this->classification = $classification;

        return $this;
    }

    /**
     * Get classification
     *
     * @return string
     */
    public function getClassification()
    {
        return $this->classification;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ProjectClassification
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the Project for the given ProjectClassification object
     *
     * @param \Librecores\ProjectRepoBundle\Entity\Project $project
     *
     * @return ProjectClassification
     */
    public function setProject(Project $project = null)
    {
        if ($this->project !== null) {
            $this->project->removeClassification($this);
        }

        if ($project !== null) {
            $project->addClassification($this);
        }

        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \Librecores\ProjectRepoBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set createdBy
     *
     * @param \Librecores\ProjectRepoBundle\Entity\User $createdBy
     *
     * @return ProjectClassification
     */
    public function setCreatedBy(\Librecores\ProjectRepoBundle\Entity\User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Librecores\ProjectRepoBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
}
