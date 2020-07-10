<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170724133244 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Classified CHANGE latitude latitude VARCHAR(50) DEFAULT NULL, CHANGE longitude longitude VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE Event CHANGE latitude latitude VARCHAR(50) DEFAULT NULL, CHANGE longitude longitude VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE Listing CHANGE latitude latitude VARCHAR(50) DEFAULT NULL, CHANGE longitude longitude VARCHAR(50) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Classified CHANGE latitude latitude VARCHAR(50) NOT NULL, CHANGE longitude longitude VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE Event CHANGE latitude latitude VARCHAR(50) NOT NULL, CHANGE longitude longitude VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE Listing CHANGE latitude latitude VARCHAR(50) NOT NULL, CHANGE longitude longitude VARCHAR(50) NOT NULL');
    }
}
