<?php

namespace Librecores\ProjectRepoBundle\Service;

use App\Entity\Project;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Dispatch requests to a asynchronous worker queue
 *
 * This class contains convenience wrappers for publishing requests to the
 * queues used in LibreCores for asynchronous processing.
 */
class QueueDispatcherService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Update a project's information
     *
     * Calls the UpdateProjectInformation service for the given project.
     *
     * @param Project $project
     */
    public function updateProjectInfo(Project $project)
    {
        $this->container
            ->get('old_sound_rabbit_mq.update_project_info_producer')
            ->publish(serialize($project->getId()));
    }
}
