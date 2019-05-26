<?php

namespace App\Command;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Crawl and update all source repositories
 *
 * Actually this command does not update the repositories directly, but instead
 * schedules them for being updated by inserting the repository into the
 * RabbitMQ queue.
 */
class UpdateReposCommand extends Command
{
    const COMMAND_NAME = 'librecores:update-repos';

    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    /**
     * @var ProducerInterface
     */
    private $producer;

    public function __construct(
        ProjectRepository $projectRepository,
        ProducerInterface $updateProjectInfoProducer
    ) {
        parent::__construct(self::COMMAND_NAME);
        $this->projectRepository = $projectRepository;
        $this->producer = $updateProjectInfoProducer;
    }

    protected function configure()
    {
        $this->setDescription('Update database by crawling the source repositories')
            ->setHelp("Crawl all registered source repositories and update the projects with it.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Project[] $projects */
        $projects = $this->projectRepository->findAll();
        $cnt = 0;
        foreach ($projects as $p) {
            if ($p->getSourceRepo() === null) {
                continue;
            }
            $this->producer->publish(serialize($p->getId()));
            $cnt++;
        }

        $output->write("Scheduled $cnt projects for being updated.", true);

        return 0;
    }
}
