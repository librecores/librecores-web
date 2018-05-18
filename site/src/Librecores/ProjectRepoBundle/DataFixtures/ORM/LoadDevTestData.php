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

        // classification hierarchy
        foreach (LoadDevTestData::classifiers as $category) {
            $classifier =  $this->createClassificationHierarchy($category[0], $category[1]);
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
     * @param int $parentId
     * @param string $name
     * @return \Librecores\ProjectRepoBundle\Entity\ClassificationHierarchy
     */
    private function createClassificationHierarchy($parentId, $name)
    {
        $classifier = new ClassificationHierarchy();
        $classifier->setParentId($parentId);
        $classifier->setName($name);

        return $classifier;
    }

    /**
     * Data for ClassificationHierarchy object
     *
     * Each row of the array contains two values. First parameter
     * for the parent classifier id. It is 0 if the classifier
     * does not have a parent classifier and the second one is
     * for the classifier name.
     *
     * @var array classifiers
     *
     */
   const classifiers = [
       [0, 'Language'],
       [1, 'Free and Open'],
       [2, 'Permissive'],
       [3, 'BSD'],
       [3, 'MIT'],
       [3, 'Apache License'],
       [3, 'Solderpad License'],
       [3, 'other'],
       [2, 'week copyleft'],
       [9, 'Mozilla Public License (MPL)'],
       [9, 'Solderpad License'],
       [9, 'GNU Lesser General Public License v2 (LGPLv2)'],
       [9, 'GNU Lesser General Public License v2 or later'],
       [9, 'GNU Lesser General Public License v3 (LGPLv3)'],
       [9, 'GNU Lesser General Public License v3 or other'],
       [9, 'other'],
       [2, 'copyleft'],
       [17, 'GNU Public License v2 (GPLv2)'],
       [17, 'GNU Public License v2 or later (GPLv2+)'],
       [17, 'GNU Public License v3 (GPLv3)'],
       [17, 'GNU Public License v3 or later (GPLv3+)'],
       [1, 'Other/Proprietary License'],
       [1, 'Public Domain/CC0'],
       [0, 'Tool'],
       [24, 'Simulation'],
       [25, 'Verilator'],
       [25, 'Icarus Verilog'],
       [25, 'GHDL'],
       [25, 'Synopsys VCS'],
       [25, 'Mentor ModelSim/Questa'],
       [25, 'Cadence Incisive (NCsim)'],
       [25, 'Aldec Riviera'],
       [25, 'other'],
       [24, 'Synthesis/Implementation'],
       [34, 'Synopsys Synplify'],
       [34, 'Cadence Genus'],
       [34, 'Xilinx Vivado'],
       [34, 'Xilinx ISE'],
       [34, 'Altera Quartus'],
       [34, 'Yosys'],
       [0, 'Target'],
       [41, 'Simulation'],
       [41, 'FPGA'],
       [43, 'Xilinx'],
       [44, 'Spartan 3'],
       [44, 'Spartan 6'],
       [44, '7 series'],
       [44, 'UltraScale'],
       [44, 'other'],
       [43, 'Altera/Intel'],
       [43, 'Lattice'],
       [43, 'Microsemi'],
       [43, 'other'],
       [41, 'ASIC'],
       [0, 'Proven on'],
       [55, 'FPGA'],
       [55, 'ASIC'],
       [0, 'Programming Language'],
       [58, 'Verilog'],
       [59, 'Verilog 95'],
       [59, 'Verilog 2001'],
       [59, 'SystemVerilog 2005 (IEEE 1800-2005)'],
       [59, 'SystemVerilog 2009 (IEEE 1800-2009)'],
       [59, 'SystemVerilog 2012 (IEEE 1800-2012)'],
       [59, 'SystemVerilog 2017 (IEEE 1800-2017)'],
       [58, 'VHDL'],
       [66, 'VHDL 1987/1993/2000/2002 (IEEE 1076-1987/1993/2000/2002)'],
       [66, 'VHDL 2008 (IEEE 1076-2008)'],
       [58, 'Chisel'],
       [58, 'MyHDL'],
       [58, 'TL-Verilog'],
       [58, 'SystemC'],
       [58, 'C'],
       [58, 'C++'],
       [58, 'Perl'],
       [58, 'Python'],
       [58, 'Java'],
       [58, 'TCL'],
       [58, 'other'],
       [0, 'Topic'],
       [80, 'Hardware'],
       [81, 'CPU'],
       [82, 'OpenRISC'],
       [82, 'RISC-V'],
       [82, 'other'],
       [81, 'GPU'],
       [81, 'DSP'],
       [81,'I/O'],
       [88, 'UART'],
       [88, 'USB'],
       [88, 'PCI Express (PCIe)'],
       [88, 'GPIO'],
       [88, 'Ethernet'],
       [81, 'Interconnect'],
       [94, 'Wishbone'],
       [94, 'AXI'],
       [81, 'Debug and Monitoing'],
       [81, 'Crypto and Hashing'],
       [81, 'other'],
       [80, 'Software'],
       [100, 'Application'],
       [100, 'Library'],
       [0, 'Support'],
       [103, 'Commercially supported'],
       [103, 'Community supported'],
       [0, 'LibreCores'],
       [106, 'Featured']
   ];
}
