<?php
namespace Librecores\ProjectRepoBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Yaml\Yaml;
use Librecores\ProjectRepoBundle\Entity\ClassificationHierarchy;

/**
 * Insert the classification hierarchy into the database
 *
 * The classification hierarchy is read from a YAML file and inserted into the
 * database table ClassificationHierarchy. All existing content in this table
 * is removed.
 */
class InsertClassificationsCommand extends ContainerAwareCommand
{
    /**
     * Default path to the classification hierarchy in YAML
     *
     * The path is resolved using the Symfony FileLocator and can contain bundle
     * names through the '@BundleName' syntax.
     *
     * @var string
     */
    const YAML_PATH_DEFAULT = '@LibrecoresProjectRepoBundle/DataFixtures/classifications.yml';

    /**
     * ORM Object Manager
     *
     * @var ObjectManager
     */
    protected $orm;

    /**
     * @var FileLocator
     */
    protected $fileLocator;

    /**
     * Path to the YAML file containing the classification hierarchy
     *
     * @var string
     */
    protected $classificationsYamlPath;

    public function __construct(ObjectManager $orm, FileLocator $fileLocator)
    {
        $this->orm = $orm;
        $this->fileLocator = $fileLocator;

        $this->classificationsYamlPath =
            $this->fileLocator->locate(self::YAML_PATH_DEFAULT);

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('librecores:insert-classifications')
            ->setDescription('Insert project classifications into the DB')
            ->setHelp(
                'Load classifications from '.$this->classificationsYamlPath
                .' and insert them into the database table '
                .'ClassificationHierarchy.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $classifications = Yaml::parseFile($this->classificationsYamlPath);

        $this->removeExistingClassificationsFromDb();

        $this->createClsHierEntry(null, null, $classifications);

        $this->orm->flush();

        $output->write(
            "Inserted classifications from ".$this->classificationsYamlPath
            ." into database.",
            true
        );

        return 0;
    }

    /**
     * Remove all existing ClassificationHierarchy entries
     *
     * Additionally reset the auto increment value of `id`.
     */
    private function removeExistingClassificationsFromDb()
    {
        $cmd = $this->orm->getClassMetadata(ClassificationHierarchy::class);
        $connection = $this->orm->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeUpdate($q);
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Recursively called method to create a classification hierarchy entry
     *
     * @param ClassificationHierarchy $parent   parent of the entry
     * @param string                  $name     name of the hierarchy entry
     * @param array                   $children children of the hierarchy entry
     */
    private function createClsHierEntry($parent, $name, $children)
    {
        if ($name === null) {
            // insert a root element
            $che = $parent;
        } else {
            $che = new ClassificationHierarchy();
            $che->setName($name);
            $che->setParent($parent);
            $this->orm->persist($che);
        }

        if (!$children) {
            return;
        }

        // leaf nodes can be specified as array in YAML (as opposed to a dict)
        $isArrayLeafNode = is_array($children) && isset($children[0]);

        if (!$isArrayLeafNode) {
            foreach ($children as $name => $grandChildren) {
                $this->createClsHierEntry($che, $name, $grandChildren);
            }
        } elseif (is_array($children)) {
            foreach ($children as $name) {
                $this->createClsHierEntry($che, $name, []);
            }
        }
    }
}
