<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181123154907 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE Cron_Log');
        $this->addSql('DROP TABLE SQL_Log');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Cron_Log (id INT AUTO_INCREMENT NOT NULL, domain_id INT NOT NULL, cron VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, date DATETIME NOT NULL, history TEXT NOT NULL COLLATE utf8_unicode_ci, finished VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci, time VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SQL_Log (id INT AUTO_INCREMENT NOT NULL, `sql` TEXT NOT NULL COLLATE utf8_unicode_ci, type VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, date DATE NOT NULL, time TIME NOT NULL, ip VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, page VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, session VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci, username VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, execution_time VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }
}
