<?php
namespace Librecores\ProjectRepoBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Librecores\ProjectRepoBundle\Entity\ClassificationHierarchy;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Librecores\ProjectRepoBundle\Entity\User;
use Librecores\ProjectRepoBundle\Entity\Organization;
use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Entity\OrganizationMember;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;

/**
 * Create a basic test environment
 */
class LoadDevTestData extends AbstractFixture
    implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Load some users, projects and organizations for testing
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // user test
        $userTest = $this->createUser('test', 'test');
        $manager->persist($userTest);

        // user test2
        $userTest2 = $this->createUser('test2', 'test2');
        $manager->persist($userTest2);

        // organization openrisc
        $orgOpenrisc = new Organization();
        $orgOpenrisc->setName("openrisc");
        $orgOpenrisc->setDisplayName("OpenRISC");
        $orgOpenrisc->setDescription("The OpenRISC community");
        $orgOpenrisc->setCreator($userTest);
        $orgOpenrisc->setCreatedAt(new \DateTime('2017-01-01'));
        $manager->persist($orgOpenrisc);

        // make user test member of organization openrisc
        $orgOpenriscMemberTest = new OrganizationMember();
        $orgOpenriscMemberTest->setUser($userTest);
        $orgOpenriscMemberTest->setPermission(OrganizationMember::PERMISSION_ADMIN);
        $orgOpenriscMemberTest->setUpdatedAt($userTest);
        $orgOpenriscMemberTest->setOrganization($orgOpenrisc);
        $manager->persist($orgOpenriscMemberTest);

        // source repository for openrisc/mor1kx
        $sourcerepoMor1kx = new GitSourceRepo();
        $sourcerepoMor1kx->setUrl('https://github.com/openrisc/mor1kx.git');
        $manager->persist($sourcerepoMor1kx);

        // project openrisc/mor1kx
        $projectMor1kx = new Project();
        $projectMor1kx->setName('mor1kx');
        $projectMor1kx->setDisplayName('mor1kx: the successor of OR1200');
        $projectMor1kx->setParentOrganization($orgOpenrisc);
        $projectMor1kx->setDateAdded(new \DateTime('2017-01-01'));
        $projectMor1kx->setDateLastModified(new \DateTime('2017-01-01'));
        $projectMor1kx->setDescriptionTextAutoUpdate(true);
        $projectMor1kx->setLicenseTextAutoUpdate(true);
        $projectMor1kx->setTagline("The greatest OpenRISC 1000 implementation on earth");
        $projectMor1kx->setIssueTracker('https://github.com/openrisc/mor1kx/issues');
        $projectMor1kx->setProjectUrl('http://openrisc.io/implementations');
        $projectMor1kx->setLicenseName('OHDL');
        $projectMor1kx->setSourceRepo($sourcerepoMor1kx);
        $manager->persist($projectMor1kx);

        // source repository for test2/optimsoc
        $sourcerepoOptimsoc = new GitSourceRepo();
        $sourcerepoOptimsoc->setUrl('https://github.com/optimsoc/sources.git');
        $manager->persist($sourcerepoOptimsoc);

        // project test2/optimsoc
        $projectOptimsoc = new Project();
        $projectOptimsoc->setName('optimsoc');
        $projectOptimsoc->setDisplayName('OpTiMSoC');
        $projectOptimsoc->setParentUser($userTest2);
        $projectOptimsoc->setDateAdded(new \DateTime('2017-01-01'));
        $projectOptimsoc->setDateLastModified(new \DateTime('2017-01-01'));
        $projectOptimsoc->setDescriptionTextAutoUpdate(true);
        $projectOptimsoc->setLicenseTextAutoUpdate(true);
        $projectOptimsoc->setTagline("Open Tiled Manycores System-on-Chip");
        $projectOptimsoc->setIssueTracker('https://github.com/optimsoc/sources/issues');
        $projectOptimsoc->setProjectUrl('https://www.optimsoc.org');
        $projectOptimsoc->setLicenseName('MIT');
        $projectOptimsoc->setSourceRepo($sourcerepoOptimsoc);
        $manager->persist($projectOptimsoc);

        /**
         *Populating classificatioinHierarchy object for development environment
         *
         * These are the test data for classification hierarchy. For the complete
         * classification hierarchy run migration version 20180530072940 again
         */
        //ClassificationHierarchy License
        $license = new ClassificationHierarchy();
        $license->setName('License');
        $license->setParent(NULL);
        $manager->persist($license);

        //ClassificationHierarchy License::Free and Open
        $freeAndOpen = new ClassificationHierarchy();
        $freeAndOpen->setName('Free and Open');
        $freeAndOpen->setParent($license);
        $manager->persist($freeAndOpen);

        //ClassificationHierarchy License::Other/Proprietary
        $Other = new ClassificationHierarchy();
        $Other->setName('Other/Proprietary License');
        $Other->setParent($license);
        $manager->persist($Other);

        //ClassificationHierarchy License::Free and Open::Premissive
        $permissive = new ClassificationHierarchy();
        $permissive->setName('Permissive');
        $permissive->setParent($freeAndOpen);
        $manager->persist($permissive);

        //ClassificationHierarchy License::Free and Open::Weak Copyleft
        $weakCopyleft = new ClassificationHierarchy();
        $weakCopyleft->setName('Weak Copyleft');
        $weakCopyleft->setParent($freeAndOpen);
        $manager->persist($weakCopyleft);

        //ClassificationHierarchy License::Premisive::BSD
        $BSD = new ClassificationHierarchy();
        $BSD->setName('BSD');
        $BSD->setParent($permissive);
        $manager->persist($BSD);

        //ClassificationHierarchy License::Premisive::MIT
        $MIT = new ClassificationHierarchy();
        $MIT->setName('MIT');
        $MIT->setParent($permissive);
        $manager->persist($MIT);

        //ClassificationHierarchy Tool
        $tool = new ClassificationHierarchy();
        $tool->setName('Tool');
        $tool->setParent(NULL);
        $manager->persist($tool);

        //ClassificationHierarchy Support
        $support = new ClassificationHierarchy();
        $support->setName('Support');
        $support->setParent(NULL);
        $manager->persist($support);

        //ClassificationHierarchy Support::Commercially Supported
        $commercially = new ClassificationHierarchy();
        $commercially->setName('Commercially supported');
        $commercially->setParent($support);
        $manager->persist($commercially);

        //ClassificationHierarchy Support::Community Supported
        $community = new ClassificationHierarchy();
        $community->setName('Community supported');
        $community->setParent($support);
        $manager->persist($community);

        //ClassificationHierarchy Topic
        $topic = new ClassificationHierarchy();
        $topic->setName('Topic');
        $topic->setParent(NULL);
        $manager->persist($topic);

        //ClassificationHierarchy Topic::Hardware
        $hardware = new ClassificationHierarchy();
        $hardware->setName('Hardware');
        $hardware->setParent($topic);
        $manager->persist($hardware);

        //ClassificationHierarchy Topic::Software
        $software = new ClassificationHierarchy();
        $software->setName('Software');
        $software->setParent($topic);
        $manager->persist($software);

        //ClassificationHierarchy Target
        $target = new ClassificationHierarchy();
        $target->setName('Target');
        $target->setParent(NULL);
        $manager->persist($target);

        //ClassificationHierarchy Proven On
        $proven = new ClassificationHierarchy();
        $proven->setName('Proven On');
        $proven->setParent(NULL);
        $manager->persist($proven);

        //ClassificationHierarchy LibreCores
        $librecores = new ClassificationHierarchy();
        $librecores->setName('LibreCores');
        $librecores->setParent(NULL);
        $manager->persist($librecores);

        //ClassificationHierarchy Programming Language
        $programing = new ClassificationHierarchy();
        $programing->setName('Programming Language');
        $programing->setParent(NULL);
        $manager->persist($programing);

        //ClassificationHierarchy Programming Language::Verilog
        $verilog = new ClassificationHierarchy();
        $verilog->setName('Verilog');
        $verilog->setParent($programing);
        $manager->persist($verilog);

        //ClassificationHierarchy Programming Language::Verilog::Verilog 95
        $verilog_95 = new ClassificationHierarchy();
        $verilog_95->setName('Verilog 95');
        $verilog_95->setParent($verilog);
        $manager->persist($verilog_95);

        //ClassificationHierarchy Programming Language::Verilog::Verilog 2001
        $verilog_01 = new ClassificationHierarchy();
        $verilog_01->setName('Verilog 2001');
        $verilog_01->setParent($verilog);
        $manager->persist($verilog_01);

        //ClassificationHierarchy Programming Language::C
        $c = new ClassificationHierarchy();
        $c->setName('C');
        $c->setParent($programing);
        $manager->persist($c);

        //ClassificationHierarchy Programming Language::C++
        $cPlus = new ClassificationHierarchy();
        $cPlus->setName('C++');
        $cPlus->setParent($programing);
        $manager->persist($cPlus);

        //ClassificationHierarchy Programming Language::JAVA
        $java = new ClassificationHierarchy();
        $java->setName('JAVA');
        $java->setParent($programing);
        $manager->persist($java);

        $manager->flush();
    }

    /**
     * Populate an User object
     *
     * @param string $username
     * @param string $password
     * @return \Librecores\ProjectRepoBundle\Entity\User
     */
    private function createUser($username, $password)
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail("$username@example.com");
        $user->setSalt(md5(uniqid()));
        $encoder = $this->container->get('security.password_encoder');
        $password = $encoder->encodePassword($user, $password);
        $user->setPassword($password);
        $user->setEnabled(true);

        return $user;
    }
}
