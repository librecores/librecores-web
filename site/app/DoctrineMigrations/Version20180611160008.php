<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180611160008 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ProjectClassification (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, classification LONGTEXT NOT NULL, createdAt DATETIME NOT NULL, createdBy_id INT NOT NULL, INDEX IDX_64065819166D1F9C (project_id), INDEX IDX_640658193174800F (createdBy_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ClassificationHierarchy (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_D13880A5727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ProjectClassification ADD CONSTRAINT FK_64065819166D1F9C FOREIGN KEY (project_id) REFERENCES Project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ProjectClassification ADD CONSTRAINT FK_640658193174800F FOREIGN KEY (createdBy_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE ClassificationHierarchy ADD CONSTRAINT FK_D13880A5727ACA70 FOREIGN KEY (parent_id) REFERENCES ClassificationHierarchy (id) ON DELETE CASCADE');

        /*
         * Index the first 200 chars of ProjectClassification.classification
         *
         * This index cannot be added through Doctrine as it provides no way
         * to specify the length of the index.
         */
        $this->addSql('ALTER TABLE ProjectClassification ADD INDEX classification_idx(classification(200))');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ClassificationHierarchy DROP FOREIGN KEY FK_D13880A5727ACA70');
        $this->addSql('DROP TABLE ProjectClassification');
        $this->addSql('DROP TABLE ClassificationHierarchy');
    }
}
