<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Allow for different OAuth providers to be connected at the same time
 */
class Version20170212193826 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // add new columns
        $this->addSql('ALTER TABLE User ADD githubOAuthUserId VARCHAR(255) DEFAULT NULL, ADD githubOAuthAccessToken VARCHAR(255) DEFAULT NULL, ADD googleOAuthUserId VARCHAR(255) DEFAULT NULL, ADD googleOAuthAccessToken VARCHAR(255) DEFAULT NULL');

        // copy github entries to new columns
        $this->addSql('UPDATE `User` SET githubOAuthUserId = oauth_user_id, githubOAuthAccessToken = oauth_access_token WHERE oauth_service = "github"');
        $this->addSql('UPDATE `User` SET googleOAuthUserId = oauth_user_id, googleOAuthAccessToken = oauth_access_token WHERE oauth_service = "google"');

        // delete old columns
        $this->addSql('ALTER TABLE User DROP oauth_service, DROP oauth_user_id, DROP oauth_access_token');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Add old columns
        $this->addSql('ALTER TABLE User ADD oauth_service VARCHAR(255) DEFAULT NULL, ADD oauth_user_id VARCHAR(255) DEFAULT NULL, ADD oauth_access_token VARCHAR(255) DEFAULT NULL');

        // copy over data (since only one service is supported, prefer github)
        $this->addSql('UPDATE `User` SET oauth_user_id = googleOAuthUserId, oauth_access_token = googleOAuthAccessToken,  oauth_service = "google" WHERE googleOAuthUserId IS NOT NULL');
        $this->addSql('UPDATE `User` SET oauth_user_id = githubOAuthUserId, oauth_access_token = githubOAuthAccessToken,  oauth_service = "github" WHERE githubOAuthUserId IS NOT NULL');

        // remove new columns
        $this->addSql('ALTER TABLE User DROP githubOAuthUserId, DROP githubOAuthAccessToken, DROP googleOAuthUserId, DROP googleOAuthAccessToken');
    }
}
