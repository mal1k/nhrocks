<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181024211642 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Claim ADD old_label_additional_phone VARCHAR(255) NOT NULL, ADD new_label_additional_phone VARCHAR(255) NOT NULL, ADD old_additional_phone VARCHAR(255) NOT NULL, ADD new_additional_phone VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE Listing ADD label_additional_phone VARCHAR(255) DEFAULT NULL, ADD additional_phone VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE Report_Listing_Daily ADD additional_phone_view INT NOT NULL');
        $this->addSql('ALTER TABLE Report_Listing_Monthly ADD additional_phone_view INT NOT NULL');

        $this->addSql("UPDATE Claim set old_label_additional_phone = 'fax', new_label_additional_phone = 'fax', old_additional_phone = old_fax, new_additional_phone = new_fax");
        $this->addSql("UPDATE Listing set label_additional_phone = 'fax', additional_phone = fax");
        $this->addSql('UPDATE Report_Listing_Daily set additional_phone_view = fax_view');
        $this->addSql('UPDATE Report_Listing_Monthly set additional_phone_view = fax_view');

        $this->addSql('ALTER TABLE Claim DROP old_fax, DROP new_fax');
        $this->addSql('ALTER TABLE Classified DROP fax');
        $this->addSql('ALTER TABLE Listing DROP fax');
        $this->addSql('ALTER TABLE Report_Listing_Daily DROP fax_view');
        $this->addSql('ALTER TABLE Report_Listing_Monthly DROP fax_view');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Claim ADD old_fax VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD new_fax VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE Classified ADD fax VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE Listing ADD fax VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE Report_Listing_Daily ADD fax_view INT NOT NULL');
        $this->addSql('ALTER TABLE Report_Listing_Monthly ADD fax_view INT NOT NULL');

        $this->addSql('UPDATE Claim set old_fax = old_additional_phone, new_fax = new_additional_phone');
        //Classified missing data
        $this->addSql('UPDATE Listing set fax = additional_phone');
        $this->addSql('UPDATE Report_Listing_Daily set fax_view = additional_phone_view');
        $this->addSql('UPDATE Report_Listing_Monthly set fax_view = additional_phone_view');

        $this->addSql('ALTER TABLE Claim DROP old_label_additional_phone, DROP new_label_additional_phone, DROP old_additional_phone, DROP new_additional_phone');
        $this->addSql('ALTER TABLE Listing DROP label_additional_phone, DROP additional_phone');
        $this->addSql('ALTER TABLE Report_Listing_Daily DROP additional_phone_view');
        $this->addSql('ALTER TABLE Report_Listing_Monthly DROP additional_phone_view');
    }
}
