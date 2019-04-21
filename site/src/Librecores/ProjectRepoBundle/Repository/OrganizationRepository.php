<?php

namespace Librecores\ProjectRepoBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Librecores\ProjectRepoBundle\Entity\Organization;
use Librecores\ProjectRepoBundle\Entity\User;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * OrganizationRepository
 *
 * Extends the default repository with custom functionality.
 */
class OrganizationRepository extends ServiceEntityRepository
{
    /**
     * {@inheritDoc}
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    /**
     * Find all the organizations that a user is a member of.
     *
     * @param User $user
     *
     * @return Organization[]
     */
    public function findAllByMemberOrderedByName(User $user)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('o.name', 'o.displayName', 'o.description', 'm.permission')
            ->from('LibrecoresProjectRepoBundle:Organization', 'o')
            ->innerJoin('o.members', 'm')
            ->where('m.user = :member')
            ->setParameter('member', $user->getId())
            ->orderBy('o.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find an organization by its name.
     *
     * @param string $organizationName
     *
     * @return NULL|Organization
     *
     * @throws NonUniqueResultException
     */
    public function findOneByName($organizationName)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('o')
            ->from('LibrecoresProjectRepoBundle:Organization', 'o')
            ->where('o.name = :name')
            ->setParameter('name', $organizationName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find an organization by a part of its name
     *
     * @param string $fragment
     *
     * @return NULL|Organization
     */
    public function findByFragment($fragment)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('o')
            ->from('LibrecoresProjectRepoBundle:Organization', 'o')
            ->where('o.name LIKE :name')
            ->orWhere('o.displayName LIKE :name')
            ->orWhere('o.description LIKE :name')
            ->setParameter('name', "%$fragment%")
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $name
     *
     * @return int
     *
     * @throws NonUniqueResultException
     */
    public function countByNameIgnoreCase(string $name): int
    {
        $name = strtolower($name);

        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('LOWER(o.name) = :name')
            ->getQuery()
            ->setParameter('name', $name)
            ->getSingleScalarResult();
    }
}
