<?php

namespace App\RepoCrawler;

use App\Entity\Project;
use InvalidArgumentException;

/**
 * Repository crawler factory: get an appropriate repository crawler instance
 */
class RepoCrawlerRegistry
{
    /**
     * @var AbstractRepoCrawler[]
     */
    private $repoCrawlers;

    public function __construct(iterable $repoCrawlers)
    {
        $this->repoCrawlers = $repoCrawlers;
    }

    /**
     * Get a RepoCrawler subclass for the project
     *
     * @param Project $project
     *
     * @return AbstractRepoCrawler
     *
     */
    public function getCrawlerForProject(Project $project): AbstractRepoCrawler
    {
        foreach ($this->repoCrawlers as $key => $crawler) {
            if ($crawler->canProcess($project)) {
                return $crawler;
            }
        }

        throw new InvalidArgumentException(
            sprintf(
                "No crawler for source repository of type %s found.",
                get_class($project->getSourceRepo())
            )
        );
    }
}
