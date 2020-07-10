<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190103183419 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Listing CHANGE address address VARCHAR(120) DEFAULT NULL, CHANGE address2 address2 VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE Promotion CHANGE listing_address listing_address VARCHAR(120) DEFAULT NULL, CHANGE listing_address2 listing_address2 VARCHAR(120) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Listing CHANGE address address VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE address2 address2 VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE Promotion CHANGE listing_address listing_address VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE listing_address2 listing_address2 VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
