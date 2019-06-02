<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190601195156 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ProjectPreferences (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, alertForMissingLicenseVisible TINYINT(1) DEFAULT \'1\' NOT NULL, alertForMissingHomePageVisible TINYINT(1) DEFAULT \'1\' NOT NULL, alertForMissingReadmeVisible TINYINT(1) DEFAULT \'1\' NOT NULL, alertForMissingIssueTrackerVisible TINYINT(1) DEFAULT \'1\' NOT NULL, UNIQUE INDEX UNIQ_49061A58166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ProjectPreferences ADD CONSTRAINT FK_49061A58166D1F9C FOREIGN KEY (project_id) REFERENCES Project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Project ADD preferences_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Project ADD CONSTRAINT FK_E00EE9727CCD6FB7 FOREIGN KEY (preferences_id) REFERENCES ProjectPreferences (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E00EE9727CCD6FB7 ON Project (preferences_id)');
        $this->addSql('DROP INDEX classification_idx ON ProjectClassification');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Project DROP FOREIGN KEY FK_E00EE9727CCD6FB7');
        $this->addSql('DROP TABLE ProjectPreferences');
        $this->addSql('DROP INDEX UNIQ_E00EE9727CCD6FB7 ON Project');
        $this->addSql('ALTER TABLE Project DROP preferences_id');
        $this->addSql('CREATE INDEX classification_idx ON ProjectClassification (classification(200))');
    }
}
