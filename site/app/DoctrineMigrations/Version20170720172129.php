<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170720172129 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SourceRepo DROP FOREIGN KEY FK_AFAC0BAA70AA3482');
        $this->addSql('ALTER TABLE SourceStatsAuthor DROP FOREIGN KEY FK_F16620EBB44B0FC1');
        $this->addSql('ALTER TABLE SourceStatsCommitHistogram DROP FOREIGN KEY FK_81F3A718B44B0FC1');
        $this->addSql('CREATE TABLE Commit (id INT AUTO_INCREMENT NOT NULL, contributor_id INT NOT NULL, commitId VARCHAR(255) NOT NULL, dateCommitted DATETIME NOT NULL, filesModified INT DEFAULT 0 NOT NULL, linesAdded INT DEFAULT 0 NOT NULL, linesRemoved INT DEFAULT 0 NOT NULL, sourceRepo_id INT NOT NULL, UNIQUE INDEX UNIQ_49782B9BEDC2C24D (commitId), INDEX IDX_49782B9B3D4460D4 (sourceRepo_id), INDEX IDX_49782B9B7A19A357 (contributor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Contributor (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, sourceRepo_id INT NOT NULL, INDEX IDX_5CF318443D4460D4 (sourceRepo_id), UNIQUE INDEX UNIQ_5CF31844E7927C743D4460D4 (email, sourceRepo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Commit ADD CONSTRAINT FK_49782B9B3D4460D4 FOREIGN KEY (sourceRepo_id) REFERENCES SourceRepo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Commit ADD CONSTRAINT FK_49782B9B7A19A357 FOREIGN KEY (contributor_id) REFERENCES Contributor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Contributor ADD CONSTRAINT FK_5CF318443D4460D4 FOREIGN KEY (sourceRepo_id) REFERENCES SourceRepo (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE SourceStats');
        $this->addSql('DROP TABLE SourceStatsAuthor');
        $this->addSql('DROP TABLE SourceStatsCommitHistogram');
        $this->addSql('DROP INDEX IDX_AFAC0BAA70AA3482 ON SourceRepo');
        $this->addSql('ALTER TABLE SourceRepo ADD source_stats_available TINYINT(1) NOT NULL, ADD source_stats_totalFiles INT NOT NULL, ADD source_stats_totalLinesOfCode INT NOT NULL, ADD source_stats_totalLinesOfComments INT NOT NULL, ADD source_stats_totalBlankLines INT NOT NULL, ADD source_stats_languageStats LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', DROP stats_id');
        $this->addSql('ALTER TABLE Project DROP FOREIGN KEY FK_E00EE9723D4460D4');
        $this->addSql('ALTER TABLE Project DROP FOREIGN KEY FK_E00EE972DEF3CE4C');
        $this->addSql('ALTER TABLE Project CHANGE status status VARCHAR(255) DEFAULT \'ASSIGNED\' NOT NULL, CHANGE licenseTextAutoUpdate licenseTextAutoUpdate TINYINT(1) DEFAULT \'1\' NOT NULL, CHANGE descriptionTextAutoUpdate descriptionTextAutoUpdate TINYINT(1) DEFAULT \'1\' NOT NULL, CHANGE inProcessing inProcessing TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE Project ADD CONSTRAINT FK_E00EE9723D4460D4 FOREIGN KEY (sourceRepo_id) REFERENCES SourceRepo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Project ADD CONSTRAINT FK_E00EE972DEF3CE4C FOREIGN KEY (parentUser_id) REFERENCES User (id) ON DELETE SET NULL');

        // need to use this UPDATE statement as TEXT columns can not have DEFAULT values
        $this->addSql('UPDATE SourceRepo SET source_stats_languageStats = \'a:0:{}\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Commit DROP FOREIGN KEY FK_49782B9B7A19A357');
        $this->addSql('CREATE TABLE SourceStats (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SourceStatsAuthor (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_520_ci, name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_520_ci, linesInserted INT NOT NULL, linesDeleted INT NOT NULL, commits INT NOT NULL, sourceStats_id INT DEFAULT NULL, INDEX IDX_F16620EBB44B0FC1 (sourceStats_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SourceStatsCommitHistogram (id INT AUTO_INCREMENT NOT NULL, yearMonth INT NOT NULL, commitCount INT NOT NULL, sourceStats_id INT DEFAULT NULL, INDEX IDX_81F3A718B44B0FC1 (sourceStats_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE SourceStatsAuthor ADD CONSTRAINT FK_F16620EBB44B0FC1 FOREIGN KEY (sourceStats_id) REFERENCES SourceStats (id)');
        $this->addSql('ALTER TABLE SourceStatsCommitHistogram ADD CONSTRAINT FK_81F3A718B44B0FC1 FOREIGN KEY (sourceStats_id) REFERENCES SourceStats (id)');
        $this->addSql('DROP TABLE Commit');
        $this->addSql('DROP TABLE Contributor');
        $this->addSql('ALTER TABLE Project DROP FOREIGN KEY FK_E00EE972DEF3CE4C');
        $this->addSql('ALTER TABLE Project DROP FOREIGN KEY FK_E00EE9723D4460D4');
        $this->addSql('ALTER TABLE Project CHANGE status status VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_520_ci, CHANGE licenseTextAutoUpdate licenseTextAutoUpdate TINYINT(1) NOT NULL, CHANGE descriptionTextAutoUpdate descriptionTextAutoUpdate TINYINT(1) NOT NULL, CHANGE inProcessing inProcessing TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE Project ADD CONSTRAINT FK_E00EE972DEF3CE4C FOREIGN KEY (parentUser_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Project ADD CONSTRAINT FK_E00EE9723D4460D4 FOREIGN KEY (sourceRepo_id) REFERENCES SourceRepo (id)');
        $this->addSql('ALTER TABLE SourceRepo ADD stats_id INT DEFAULT NULL, DROP source_stats_available, DROP source_stats_totalFiles, DROP source_stats_totalLinesOfCode, DROP source_stats_totalLinesOfComments, DROP source_stats_totalBlankLines, DROP source_stats_languageStats');
        $this->addSql('ALTER TABLE SourceRepo ADD CONSTRAINT FK_AFAC0BAA70AA3482 FOREIGN KEY (stats_id) REFERENCES SourceStats (id)');
        $this->addSql('CREATE INDEX IDX_AFAC0BAA70AA3482 ON SourceRepo (stats_id)');
    }
}
