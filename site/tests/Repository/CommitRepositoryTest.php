<?php

namespace App\Repository;


use App\Entity\Commit;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CommitRepositoryTest extends TestCase
{
    public function testCommitRepositoryIsMappedToCommitEntity()
    {
        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $mockEntityManager->expects($this->once())
            ->method('getClassMetadata')
            ->with(Commit::class)
            ->willReturn(new ClassMetadata(Commit::class));

        $mockRegistry = $this->createMock(RegistryInterface::class);
        $mockRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(Commit::class)
            ->willReturn($mockEntityManager);

        /** @var RegistryInterface $mockRegistry */
        new CommitRepository($mockRegistry);
    }
}
