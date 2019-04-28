<?php

namespace Librecores\ProjectRepoBundle\Command;


use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;
use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Repository\ProjectRepository;
use LibreCores\TestUtils\Mocks\MockProducer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateReposCommandTest extends TestCase
{
    public function testPublishesAllProjectsWhenInvoked()
    {
        $projects = [
            $this->createMockProject(1, new GitSourceRepo()),
            $this->createMockProject(2, new GitSourceRepo()),
            $this->createMockProject(3, new GitSourceRepo()),
        ];

        $mockProjectRepository = $this->createMock(ProjectRepository::class);
        $mockProjectRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($projects);

        $producer = new MockProducer();

        $mockOutputInterface = $this->createMock(OutputInterface::class);
        $mockOutputInterface->expects($this->once())
            ->method('write')
            ->with("Scheduled 3 projects for being updated.", true);

        $mockInputInterface = $this->createMock(InputInterface::class);

        $command = new UpdateReposCommand($mockProjectRepository, $producer);
        $exitCode = $command->run($mockInputInterface, $mockOutputInterface);

        $this->assertEquals(0, $exitCode);

        $publishedIds = array_map(
            function ($x) { return unserialize($x); },
            array_column($producer->getPublishedMessages(), 'body')
        );
        $this->assertEquals([1, 2, 3], $publishedIds);
    }

    public function testDoesNotPublisheProjectsWithoutSourceRepo()
    {
        $projects = [
            $this->createMockProject(1, new GitSourceRepo()),
            $this->createMockProject(2,),
            $this->createMockProject(3, new GitSourceRepo()),
        ];

        $mockProjectRepository = $this->createMock(ProjectRepository::class);
        $mockProjectRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($projects);

        $producer = new MockProducer();

        $mockOutputInterface = $this->createMock(OutputInterface::class);
        $mockOutputInterface->expects($this->once())
            ->method('write')
            ->with("Scheduled 2 projects for being updated.", true);

        $mockInputInterface = $this->createMock(InputInterface::class);

        $command = new UpdateReposCommand($mockProjectRepository, $producer);
        $exitCode = $command->run($mockInputInterface, $mockOutputInterface);

        $this->assertEquals(0, $exitCode);

        $publishedIds = array_map(
            function ($x) { return unserialize($x); },
            array_column($producer->getPublishedMessages(), 'body')
        );
        $this->assertEquals([1, 3], $publishedIds);
    }

    private function createMockProject($id, $sourceRepo = null)
    {
        $mockProject = $this->createMock(Project::class);
        $mockProject->method('getId')->willReturn($id);
        $mockProject->method('getSourceRepo')->willReturn($sourceRepo);

        return $mockProject;
    }

}
