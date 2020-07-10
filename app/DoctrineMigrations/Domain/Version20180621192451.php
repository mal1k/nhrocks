<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180621192451 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE Setting_Navigation SET `custom` = \'1\' WHERE `custom` = \'y\'');
        $this->addSql('UPDATE Setting_Navigation SET `custom` = \'0\' WHERE `custom` = \'n\'');
        $this->addSql('ALTER TABLE Setting_Navigation ADD page_id INT DEFAULT NULL, CHANGE link link VARCHAR(255) DEFAULT NULL, CHANGE custom custom TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE Setting_Navigation ADD CONSTRAINT FK_9D7061FFC4663E4 FOREIGN KEY (page_id) REFERENCES Page (id)');
        $this->addSql('CREATE INDEX IDX_9D7061FFC4663E4 ON Setting_Navigation (page_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Setting_Navigation DROP FOREIGN KEY FK_9D7061FFC4663E4');
        $this->addSql('DROP INDEX IDX_9D7061FFC4663E4 ON Setting_Navigation');
        $this->addSql('ALTER TABLE Setting_Navigation DROP page_id, CHANGE link link VARCHAR(255) NOT NULL COLLATE utf8_general_ci, CHANGE custom custom VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci');
    }
}
