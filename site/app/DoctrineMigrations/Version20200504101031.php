<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200504101031 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Project ADD licenseTextLastUpdate DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\' AFTER licenseText, ADD descriptionTextLastUpdate DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\' AFTER descriptionText, DROP inProcessing');
        $this->addSql('UPDATE Project SET licenseTextLastUpdate = dateLastModified, descriptionTextLastUpdate = dateLastModified');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Project ADD inProcessing TINYINT(1) DEFAULT \'0\' NOT NULL, DROP licenseTextLastUpdate, DROP descriptionTextLastUpdate');
    }
}
