<?php

namespace Librecores\ProjectRepoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Crawl and update all source repositories
 *
 * Actually this command does not update the repositories directly, but instead
 * schedules them for being updated by inserting the repository into the RabbitMQ
 * queue.
 */
class UpdateReposCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('librecores:update-repos')
            ->setDescription('Update database by crawling the source repositories')
            ->setHelp("Crawl all registered source repositories and update the projects with it.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orm = $this->getContainer()->get('doctrine');
        $producerQueue = $this->getContainer()->get('old_sound_rabbit_mq.update_project_info_producer');

        $projects = $orm->getRepository('LibrecoresProjectRepoBundle:Project')
            ->findAll();
        $cnt = 0;
        foreach ($projects as $p) {
            if ($p->getSourceRepo() === null) {
                continue;
            }
            $producerQueue->publish(serialize($p->getId()));
            $cnt++;
        }

        $output->write("Scheduled $cnt projects for being updated.", true);

        return 0;
    }
}
