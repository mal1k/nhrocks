<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180626192431 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Slider ADD page_id INT DEFAULT NULL, CHANGE link link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE Slider ADD CONSTRAINT FK_C86B1531C4663E4 FOREIGN KEY (page_id) REFERENCES Page (id)');
        $this->addSql('CREATE INDEX IDX_C86B1531C4663E4 ON Slider (page_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Slider DROP FOREIGN KEY FK_C86B1531C4663E4');
        $this->addSql('DROP INDEX IDX_C86B1531C4663E4 ON Slider');
        $this->addSql('ALTER TABLE Slider DROP page_id, CHANGE link link VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
