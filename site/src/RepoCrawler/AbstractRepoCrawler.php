<?php

namespace App\RepoCrawler;

use App\Entity\Project;

/**
 * Repository crawler base class
 *
 * Get contents from a source code repository.
 */
abstract class AbstractRepoCrawler
{

    /**
     * Is the project processable by this crawler?
     *
     * @param Project $project
     *
     * @return boolean
     */
    abstract public function canProcess(Project $project): bool;

    /**
     * Update the project associated with the crawled repository with
     * information extracted from the repo
     *
     * @param Project $project
     *
     * @return bool operation successful?
     */
    abstract public function update(Project $project);
}
