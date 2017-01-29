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

    /**
     * Find a project using a fragment/substring of its fully qualified name
     *
     * @param string $fqnameFragment
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
                'WHERE CONCAT(COALESCE(org.name, user.username), \'/\', p.name) LIKE :fqnameFragment')
            ->setParameter('fqnameFragment', $fqnameFragment);
        if ($limit != 0) {
            $p->setFirstResults(0)->setMaxResults($limit);
        }
        return $p->getResult();
    }

    /**
     * Find all projects ordered by recent activity (last modified)
     *
     * @param int $limit
     * @return array of projects
     */
    public function findByRecentActivity($limit = null)
    {
		$p = $this->getEntityManager()
			->createQuery(
				'SELECT p '.
				'FROM LibrecoresProjectRepoBundle:Project p '.
				'ORDER BY p.dateLastModified DESC');
		if ($limit != 0) {
			$p->setFirstResult(0)->setMaxResults($limit);
		}
		return $p->getResult();
    }
}
