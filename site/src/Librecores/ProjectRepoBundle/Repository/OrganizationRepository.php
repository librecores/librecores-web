<?php
namespace Librecores\ProjectRepoBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Librecores\ProjectRepoBundle\Entity\User;

/**
 * OrganizationRepository
 *
 * Extends the default repository with custom functionality.
 */
class OrganizationRepository extends EntityRepository
{
    /**
     * Find all the organizations that a user is a member of.
     *
     * @param User $user
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
     * @return NULL|Organization
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
}
