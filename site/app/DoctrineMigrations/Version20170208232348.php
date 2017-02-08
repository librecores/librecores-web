<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170208232348 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE SourceStats (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SourceStatsAuthor (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, linesInserted INT NOT NULL, linesDeleted INT NOT NULL, commits INT NOT NULL, sourceStats_id INT DEFAULT NULL, INDEX IDX_F16620EBB44B0FC1 (sourceStats_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SourceStatsCommitHistogram (id INT AUTO_INCREMENT NOT NULL, yearMonth INT NOT NULL, commitCount INT NOT NULL, sourceStats_id INT DEFAULT NULL, INDEX IDX_81F3A718B44B0FC1 (sourceStats_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SourceRepo (id INT AUTO_INCREMENT NOT NULL, stats_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_AFAC0BAA70AA3482 (stats_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Organization (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, displayName VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, creatorId INT DEFAULT NULL, UNIQUE INDEX UNIQ_D9DFB8845E237E06 (name), INDEX IDX_D9DFB88424B2CCF6 (creatorId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE OrganizationMember (id INT AUTO_INCREMENT NOT NULL, permission VARCHAR(255) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, organizationId INT NOT NULL, userId INT NOT NULL, INDEX IDX_2EB14D257D8C8404 (organizationId), INDEX IDX_2EB14D2564B64DCC (userId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Project (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, tagline VARCHAR(140) DEFAULT NULL, projectUrl VARCHAR(255) DEFAULT NULL, issueTracker VARCHAR(255) DEFAULT NULL, licenseName VARCHAR(100) DEFAULT NULL, licenseText TEXT DEFAULT NULL, licenseTextAutoUpdate TINYINT(1) NOT NULL, descriptionText TEXT DEFAULT NULL, descriptionTextAutoUpdate TINYINT(1) NOT NULL, inProcessing TINYINT(1) NOT NULL, dateAdded DATETIME NOT NULL, dateLastModified DATETIME NOT NULL, parentUser_id INT DEFAULT NULL, parentOrganization_id INT DEFAULT NULL, sourceRepo_id INT DEFAULT NULL, INDEX IDX_E00EE972DEF3CE4C (parentUser_id), INDEX IDX_E00EE97278A9B4C4 (parentOrganization_id), UNIQUE INDEX UNIQ_E00EE9723D4460D4 (sourceRepo_id), UNIQUE INDEX projectname_full (name, parentUser_id, parentOrganization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE User (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', oauth_service VARCHAR(255) DEFAULT NULL, oauth_user_id VARCHAR(255) DEFAULT NULL, oauth_access_token VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, UNIQUE INDEX UNIQ_2DA1797792FC23A8 (username_canonical), UNIQUE INDEX UNIQ_2DA17977A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_2DA17977C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE SourceStatsAuthor ADD CONSTRAINT FK_F16620EBB44B0FC1 FOREIGN KEY (sourceStats_id) REFERENCES SourceStats (id)');
        $this->addSql('ALTER TABLE SourceStatsCommitHistogram ADD CONSTRAINT FK_81F3A718B44B0FC1 FOREIGN KEY (sourceStats_id) REFERENCES SourceStats (id)');
        $this->addSql('ALTER TABLE SourceRepo ADD CONSTRAINT FK_AFAC0BAA70AA3482 FOREIGN KEY (stats_id) REFERENCES SourceStats (id)');
        $this->addSql('ALTER TABLE Organization ADD CONSTRAINT FK_D9DFB88424B2CCF6 FOREIGN KEY (creatorId) REFERENCES User (id)');
        $this->addSql('ALTER TABLE OrganizationMember ADD CONSTRAINT FK_2EB14D257D8C8404 FOREIGN KEY (organizationId) REFERENCES Organization (id)');
        $this->addSql('ALTER TABLE OrganizationMember ADD CONSTRAINT FK_2EB14D2564B64DCC FOREIGN KEY (userId) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Project ADD CONSTRAINT FK_E00EE972DEF3CE4C FOREIGN KEY (parentUser_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Project ADD CONSTRAINT FK_E00EE97278A9B4C4 FOREIGN KEY (parentOrganization_id) REFERENCES Organization (id)');
        $this->addSql('ALTER TABLE Project ADD CONSTRAINT FK_E00EE9723D4460D4 FOREIGN KEY (sourceRepo_id) REFERENCES SourceRepo (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SourceStatsAuthor DROP FOREIGN KEY FK_F16620EBB44B0FC1');
        $this->addSql('ALTER TABLE SourceStatsCommitHistogram DROP FOREIGN KEY FK_81F3A718B44B0FC1');
        $this->addSql('ALTER TABLE SourceRepo DROP FOREIGN KEY FK_AFAC0BAA70AA3482');
        $this->addSql('ALTER TABLE Project DROP FOREIGN KEY FK_E00EE9723D4460D4');
        $this->addSql('ALTER TABLE OrganizationMember DROP FOREIGN KEY FK_2EB14D257D8C8404');
        $this->addSql('ALTER TABLE Project DROP FOREIGN KEY FK_E00EE97278A9B4C4');
        $this->addSql('ALTER TABLE Organization DROP FOREIGN KEY FK_D9DFB88424B2CCF6');
        $this->addSql('ALTER TABLE OrganizationMember DROP FOREIGN KEY FK_2EB14D2564B64DCC');
        $this->addSql('ALTER TABLE Project DROP FOREIGN KEY FK_E00EE972DEF3CE4C');
        $this->addSql('DROP TABLE SourceStats');
        $this->addSql('DROP TABLE SourceStatsAuthor');
        $this->addSql('DROP TABLE SourceStatsCommitHistogram');
        $this->addSql('DROP TABLE SourceRepo');
        $this->addSql('DROP TABLE Organization');
        $this->addSql('DROP TABLE OrganizationMember');
        $this->addSql('DROP TABLE Project');
        $this->addSql('DROP TABLE User');
    }
}
