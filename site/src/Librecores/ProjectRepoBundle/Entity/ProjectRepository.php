<?php
namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\EntityRepository;

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
     * @return NULL|Project
     */
    public function findProjectWithParent($parentName, $projectName)
    {
        $p = $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM LibrecoresProjectRepoBundle:Project p '.
                'LEFT JOIN p.parentOrganization org '.
                'LEFT JOIN p.parentUser user '.
                'WHERE p.name = :projectName '.
                '  AND (org.name = :parentName OR user.username = :parentName)')
            ->setParameter('parentName', $parentName)
            ->setParameter('projectName', $projectName)
            ->getOneOrNullResult();
        return $p;
    }
}

