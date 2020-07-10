<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171009183541 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Listing CHANGE renewal_date renewal_date DATE DEFAULT NULL, CHANGE seo_title seo_title VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(50) DEFAULT NULL, CHANGE url url VARCHAR(255) DEFAULT NULL, CHANGE display_url display_url VARCHAR(255) DEFAULT NULL, CHANGE address address VARCHAR(50) DEFAULT NULL, CHANGE address2 address2 VARCHAR(50) DEFAULT NULL, CHANGE zip_code zip_code VARCHAR(10) DEFAULT NULL, CHANGE phone phone VARCHAR(255) DEFAULT NULL, CHANGE fax fax VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE seo_description seo_description VARCHAR(255) DEFAULT NULL, CHANGE long_description long_description LONGTEXT DEFAULT NULL, CHANGE keywords keywords LONGTEXT DEFAULT NULL, CHANGE seo_keywords seo_keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE Listing_Category MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX category_listing_id ON Listing_Category');
        $this->addSql('ALTER TABLE Listing_Category DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE Listing_Category DROP FOREIGN KEY FK_E79629012469DE2');
        $this->addSql('ALTER TABLE Listing_Category DROP FOREIGN KEY FK_E796290D4619D1A');
        $this->addSql('ALTER TABLE Listing_Category DROP id');
        $this->addSql('ALTER TABLE Listing_Category ADD PRIMARY KEY (listing_id, category_id)');
        $this->addSql('DROP INDEX listing_id ON Listing_Category');
        $this->addSql('CREATE INDEX IDX_E796290D4619D1A ON Listing_Category (listing_id)');
        $this->addSql('DROP INDEX category_id ON Listing_Category');
        $this->addSql('CREATE INDEX IDX_E79629012469DE2 ON Listing_Category (category_id)');
        $this->addSql('ALTER TABLE Listing_Category ADD CONSTRAINT FK_E79629012469DE2 FOREIGN KEY (category_id) REFERENCES ListingCategory (id)');
        $this->addSql('ALTER TABLE Listing_Category ADD CONSTRAINT FK_E796290D4619D1A FOREIGN KEY (listing_id) REFERENCES Listing (id)');
        $this->addSql('ALTER TABLE ListingCategory CHANGE summary_description summary_description VARCHAR(255) DEFAULT NULL, CHANGE seo_description seo_description VARCHAR(255) DEFAULT NULL, CHANGE page_title page_title VARCHAR(255) DEFAULT NULL, CHANGE keywords keywords VARCHAR(255) DEFAULT NULL, CHANGE seo_keywords seo_keywords VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Listing CHANGE renewal_date renewal_date DATE NOT NULL, CHANGE seo_title seo_title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE email email VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, CHANGE url url VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE display_url display_url VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE address address VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, CHANGE address2 address2 VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, CHANGE zip_code zip_code VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, CHANGE phone phone VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE fax fax VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE description description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_description seo_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE long_description long_description TEXT NOT NULL COLLATE utf8_unicode_ci, CHANGE keywords keywords TEXT NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_keywords seo_keywords VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE ListingCategory CHANGE summary_description summary_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_description seo_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE page_title page_title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE keywords keywords VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_keywords seo_keywords VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE Listing_Category DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE Listing_Category DROP FOREIGN KEY FK_E796290D4619D1A');
        $this->addSql('ALTER TABLE Listing_Category DROP FOREIGN KEY FK_E79629012469DE2');
        $this->addSql('ALTER TABLE Listing_Category ADD id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE INDEX category_listing_id ON Listing_Category (category_id, listing_id)');
        $this->addSql('ALTER TABLE Listing_Category ADD PRIMARY KEY (id)');
        $this->addSql('DROP INDEX idx_e796290d4619d1a ON Listing_Category');
        $this->addSql('CREATE INDEX listing_id ON Listing_Category (listing_id)');
        $this->addSql('DROP INDEX idx_e79629012469de2 ON Listing_Category');
        $this->addSql('CREATE INDEX category_id ON Listing_Category (category_id)');
        $this->addSql('ALTER TABLE Listing_Category ADD CONSTRAINT FK_E796290D4619D1A FOREIGN KEY (listing_id) REFERENCES Listing (id)');
        $this->addSql('ALTER TABLE Listing_Category ADD CONSTRAINT FK_E79629012469DE2 FOREIGN KEY (category_id) REFERENCES ListingCategory (id)');
    }
}
