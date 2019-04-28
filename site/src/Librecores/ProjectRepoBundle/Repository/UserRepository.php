<?php

namespace Librecores\ProjectRepoBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Librecores\ProjectRepoBundle\Entity\User;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * UserRepository
 *
 * @author Amitosh Swain Mahapatra <amitosh.swain@gmail.com>
 *
 * @method findOneByUsername(string $userOrOrganization)
 */
class UserRepository extends ServiceEntityRepository
{

    /**
     * {@inheritDoc}
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }
}
