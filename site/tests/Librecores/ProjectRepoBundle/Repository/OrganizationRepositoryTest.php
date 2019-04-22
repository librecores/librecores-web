<?php

namespace Librecores\ProjectRepoBundle\Repository;


use App\Entity\Organization;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\RegistryInterface;

class OrganizationRepositoryTest extends TestCase
{
    public function testOrganizationRepositoryIsMappedToOrganizationEntity()
    {
        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $mockEntityManager->expects($this->once())
            ->method('getClassMetadata')
            ->with(Organization::class)
            ->willReturn(new ClassMetadata(Organization::class));

        $mockRegistry = $this->createMock(RegistryInterface::class);
        $mockRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(Organization::class)
            ->willReturn($mockEntityManager);

        /** @var RegistryInterface $mockRegistry */
        $repository = new OrganizationRepository($mockRegistry);

        $this->assertEquals(Organization::class, $repository->getClassName());
    }
}
