<?php

namespace App\Service;

use App\Entity\Project;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Dispatch requests to a asynchronous worker queue
 *
 * This class contains convenience wrappers for publishing requests to the
 * queues used in LibreCores for asynchronous processing.
 */
class QueueDispatcherService
{

    /**
     * @var ProducerInterface
     */
    private $producer;

    public function __construct(ProducerInterface $updateProjectInfoProducer)
    {

        $this->producer = $updateProjectInfoProducer;
    }

    /**
     * Update a project's information
     *
     * Calls the UpdateProjectInformation service for the given project.
     *
     * @param Project $project
     */
    public function updateProjectInfo(Project $project)
    {
        $this->producer->publish(serialize($project->getId()));
    }
}
