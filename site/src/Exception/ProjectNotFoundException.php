<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

/**
 * A project was not found.
 */
class ProjectNotFoundException extends RuntimeException
{
    /**
     * Create a new exception class for a given project ID.
     *
     * @param int $projectId
     *
     * @return self
     */
    public static function fromProjectId($projectId)
    {
        return new self("Project with ID $projectId was not found.");
    }
}
