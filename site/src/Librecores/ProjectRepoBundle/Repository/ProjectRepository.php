<?php

namespace Librecores\ProjectRepoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Librecores\ProjectRepoBundle\Entity\Project;

/**
 * ProjectRepository
 *
 * Extends the default repository with custom functionality.
 */
class ProjectRepository extends EntityRepository
{
    /**
     * Find a project based using the parent/name scheme.
     *
     * @param string $parentName
     * @param string $projectName
     *
     * @return NULL|Project
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findProjectWithParent($parentName, $projectName)
    {
        $p = $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM LibrecoresProjectRepoBundle:Project p '.
                'LEFT JOIN p.parentOrganization org '.
                'LEFT JOIN p.parentUser user '.
                'WHERE p.name = :projectName '.
                '  AND (org.name = :parentName OR user.username = :parentName)'
            )
            ->setParameter('parentName', $parentName)
            ->setParameter('projectName', $projectName)
            ->getOneOrNullResult();

        return $p;
    }

    /**
     * Find a project using a fragment/substring of its fully qualified name
     *
     * @param string $fqnameFragment
     * @param int    $limit
     *
     * @return NULL|Project
     */
    public function findByFqnameFragment($fqnameFragment, $limit = null)
    {
        if (empty($fqnameFragment)) {
            return array();
        }

        $fqnameFragment = "%$fqnameFragment%";

        $p = $this->getEntityManager()
            ->createQuery(
                'SELECT p '.
                'FROM LibrecoresProjectRepoBundle:Project p '.
                'LEFT JOIN p.parentOrganization org '.
                'LEFT JOIN p.parentUser user '.
                'WHERE CONCAT(COALESCE(org.name, user.username), \'/\', p.name) LIKE :fqnameFragment'
            )
            ->setParameter('fqnameFragment', $fqnameFragment);
        if ($limit !== 0) {
            $p->setFirstResults(0)->setMaxResults($limit);
        }

        return $p->getResult();
    }
}
