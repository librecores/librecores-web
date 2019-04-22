<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Organization;

/**
 * Create the required minimum database entries
 *
 * The LibreCores sites assumes some database entries to be present in order
 * for the site to work. This fixtures creates these entries.
 *
 * Note: run this fixture first before any other fixture!
 */
class LoadBaseData extends AbstractFixture implements FixtureInterface
{
    /**
     * Load all minimum database entries
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // special organization "unassigned"
        $orgUnassigned = new Organization();
        $orgUnassigned->setName("unassigned");
        $orgUnassigned->setDisplayName("unassigned projects");
        $orgUnassigned->setDescription("Projects currently without an owner");
        $orgUnassigned->setCreatedAt(new \DateTime('2016-10-09')); // ORCONF 2016
        $manager->persist($orgUnassigned);

        $manager->flush();
    }
}
