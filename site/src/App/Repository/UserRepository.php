<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
