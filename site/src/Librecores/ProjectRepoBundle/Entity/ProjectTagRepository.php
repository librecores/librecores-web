<?php

namespace Librecores\ProjectRepoBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Librecores\ProjectRepoBundle\Entity\Project;

/**
 * ProjectTagRepository
 *
 * Extends the default repository with custom functionality.
 */
class ProjectTagRepository extends EntityRepository
{

    public function findTags($search)
    {
        $t = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(array('t','c'))
            ->from('LibrecoresProjectRepoBundle:ProjectTag', 't')
            ->innerJoin('t.category', 'c')
            ->getQuery()
            ->getResult();
        return $t;
    }

    public function findTagsForProject(Project $p)
    {
        $tags = $this->getEntityManager()
            ->createQueryBuilder()
            ->select(array('t','c','tg'))
            ->from('LibrecoresProjectRepoBundle:ProjectTagging', 'tg')
            ->innerJoin('tg.tag', 't')
            ->innerJoin('t.category', 'c')
            ->where('tg.project = ?1')
            ->setParameter(1, $p->getId())
            ->getQuery()
            ->getResult();

        return $tags;
    }
}
