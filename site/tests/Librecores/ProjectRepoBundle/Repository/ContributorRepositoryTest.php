<?php

namespace Librecores\ProjectRepoBundle\Repository;


use App\Entity\Contributor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ContributorRepositoryTest extends TestCase
{
    public function testContributorRepositoryIsMappedToContributorEntity()
    {
        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $mockEntityManager->expects($this->once())
            ->method('getClassMetadata')
            ->with(Contributor::class)
            ->willReturn(new ClassMetadata(Contributor::class));

        $mockRegistry = $this->createMock(RegistryInterface::class);
        $mockRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(Contributor::class)
            ->willReturn($mockEntityManager);

        /** @var RegistryInterface $mockRegistry */
        $repository = new ContributorRepository($mockRegistry);

        $this->assertEquals(Contributor::class, $repository->getClassName());
    }
}
