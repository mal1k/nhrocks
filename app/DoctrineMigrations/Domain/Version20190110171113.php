<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190110171113 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ListingLevel DROP has_sms, DROP has_call');
        $this->addSql('DROP INDEX clicktocall_number ON Listing');
        $this->addSql('ALTER TABLE Listing DROP clicktocall_number, DROP clicktocall_extension, DROP clicktocall_date');
        $this->addSql('ALTER TABLE Report_Listing_Monthly DROP click_call');
        $this->addSql('ALTER TABLE Report_Listing_Daily DROP click_call');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Listing ADD clicktocall_number VARCHAR(15) DEFAULT NULL COLLATE utf8_unicode_ci, ADD clicktocall_extension INT DEFAULT NULL, ADD clicktocall_date DATE DEFAULT NULL');
        $this->addSql('CREATE INDEX clicktocall_number ON Listing (clicktocall_number)');
        $this->addSql('ALTER TABLE ListingLevel ADD has_sms VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci, ADD has_call VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE Report_Listing_Daily ADD click_call INT NOT NULL');
        $this->addSql('ALTER TABLE Report_Listing_Monthly ADD click_call INT NOT NULL');
    }
}
