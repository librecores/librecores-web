<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190611123733 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE AppNotification (id INT AUTO_INCREMENT NOT NULL, notification_type VARCHAR(255) NOT NULL, date DATETIME NOT NULL, subject VARCHAR(4000) NOT NULL, message VARCHAR(4000) DEFAULT NULL, link VARCHAR(4000) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE NotifiableEntity (id INT AUTO_INCREMENT NOT NULL, identifier VARCHAR(255) NOT NULL, class VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE NotifiableNotification (id INT AUTO_INCREMENT NOT NULL, notification_id INT DEFAULT NULL, seen TINYINT(1) NOT NULL, notifiableEntity_id INT DEFAULT NULL, INDEX IDX_5D38ED561A5573B7 (notifiableEntity_id), INDEX IDX_5D38ED56EF1A9D84 (notification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE NotifiableNotification ADD CONSTRAINT FK_5D38ED561A5573B7 FOREIGN KEY (notifiableEntity_id) REFERENCES NotifiableEntity (id)');
        $this->addSql('ALTER TABLE NotifiableNotification ADD CONSTRAINT FK_5D38ED56EF1A9D84 FOREIGN KEY (notification_id) REFERENCES AppNotification (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE NotifiableNotification DROP FOREIGN KEY FK_5D38ED56EF1A9D84');
        $this->addSql('ALTER TABLE NotifiableNotification DROP FOREIGN KEY FK_5D38ED561A5573B7');
        $this->addSql('DROP TABLE AppNotification');
        $this->addSql('DROP TABLE NotifiableEntity');
        $this->addSql('DROP TABLE NotifiableNotification');
    }
}
