<?php
namespace Librecores\ProjectRepoBundle\Doctrine;

use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;

/**
 * Extended user manager
 *
 * @package Librecores\ProjectRepoBundle\Doctrine
 *
 * @author Philipp Wagner <mail@philipp-wagner.com>
 */
class UserManager extends BaseUserManager
{
    /**
     * Find user by given username or email
     *
     * @param string $username
     * @param string $email
     * @return mixed
     */
    public function findUserByUsernameOrEmail2($username, $email)
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.usernameCanonical = :username')
            ->orWhere('u.emailCanonical = :email')
            ->setParameter('username', $this->canonicalizeUsername($username))
            ->setParameter('email', $this->canonicalizeEmail($email))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Find user by OAuth user ID
     *
     * @param string $oAuthService name of the OAuth service
     * @param string $oAuthUserId user ID on the OAuth service
     * @return mixed
     */
    public function findUserByOAuth($oAuthService, $oAuthUserId)
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.oAuthUserId = :oAuthUserId')
            ->andWhere('u.oAuthService = :oAuthService')
            ->setParameter('oAuthService', $oAuthService)
            ->setParameter('oAuthUserId', $oAuthUserId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
