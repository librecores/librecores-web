<?php
namespace Librecores\ProjectRepoBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Librecores\ProjectRepoBundle\Entity\User;
use Librecores\ProjectRepoBundle\Entity\Organization;
use Librecores\ProjectRepoBundle\Entity\Project;
use Librecores\ProjectRepoBundle\Entity\OrganizationMember;
use Librecores\ProjectRepoBundle\Entity\GitSourceRepo;
use Librecores\ProjectRepoBundle\Entity\ClassificationHierarchy;

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

        //classificatioin hierarchy
        foreach ($this->classifiers as $arr) {
            $classifier =  $this->createClassifier($arr[0], $arr[1]);
            $manager->persist($classifier);
        }

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

    /**
     * Populate an ClassificationHierarchy object
     *
     * @param integer $parent_id
     * @param string $name
     * @return \Librecores\ProjectRepoBundle\Entity\ClassificationHierarchy
     */
    private function createClassifier($parent_id,$name) {
        $classifier = new ClassificationHierarchy();
        $classifier->setParentId($parent_id);
        $classifier->setName($name);
        $classifier->prePersist();

        return $classifier;
    }

   private $classifiers = array(
        array(0,'Language'),
        array(1,'web'),
        array(1,'hdl'),
        array(2,'php'),
        array(2,'nodejs'),
        array(2,'python'),
        array(3,'verilog'),
        array(3,'vhdl'),
        array(0,'proven on'),
        array(9,'asic'),
        array(9,'fpga'),
        array(11,'xilinx'),
        array(12,'7series'),
        array(11,'altera'),
        array(0,'Category'),
        array(15,'cpu'),
        array(16,'out of order'),
        array(0,'License'),
        array(18,'MIT'),
        array(0,'Interface'),
        array(20,'spi'),
        array(20,'wishbone'),
        array(0,'Verification'),
        array(23,'formal'),
    );
}
