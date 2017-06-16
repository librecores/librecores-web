<?php

namespace Librecores\ProjectRepoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Librecores\ProjectRepoBundle\Entity\Contributor;
use Librecores\ProjectRepoBundle\Entity\SourceRepo;

/**
 * ContributorRepository
 *
 * Extends the default repository with custom functionality.
 */
class ContributorRepository extends EntityRepository
{
    /**
     * Get contributor for a source repository from an email.
     *
     * Creates and returns a new entity, if a name is supplied and the email does not exist.
     *
     * @param SourceRepo $repo repository to search against
     * @param string $email email of the contributor to search for
     * @param null|string $name name of the contributor
     * @return Contributor|null
     */
    public function getContributorForRepository(SourceRepo $repo, string $email, ?string $name = null): ?Contributor
    {
        $contributor = $this->findOneBy(['sourceRepo' => $repo]);

        // create and return and entity only when the name is specified
        if (null === $contributor && $name !== null) {
            $contributor = new Contributor();
            $contributor->setName($name)
                        ->setEmail($email)
                        ->setSourceRepo($repo);

            // we mark the entity for persisting but do not perform the flush
            // as this entity is inevitably going to be used as a part of a
            // UnitOfWork on which flush will be ultimately called
            $this->getEntityManager()->persist($contributor);
        }

        return $contributor;
    }
}
