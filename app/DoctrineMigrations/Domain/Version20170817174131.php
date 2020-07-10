<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170817174131 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ListingCategory DROP full_friendly_url');
        $this->addSql('ALTER TABLE Listing_Category DROP FOREIGN KEY FK_E796290BEDE28F8');
        $this->addSql('DROP INDEX status ON Listing_Category');
        $this->addSql('DROP INDEX category_status ON Listing_Category');
        $this->addSql('DROP INDEX IDX_E796290BEDE28F8 ON Listing_Category');
        $this->addSql('DROP INDEX category_listing_id ON Listing_Category');
        $this->addSql('ALTER TABLE Listing_Category DROP category_root_id, DROP category_node_left, DROP category_node_right, DROP status');
        $this->addSql('CREATE INDEX category_listing_id ON Listing_Category (category_id, listing_id)');
        $this->addSql('ALTER TABLE Promotion CHANGE listing_latitude listing_latitude VARCHAR(50) DEFAULT NULL, CHANGE listing_longitude listing_longitude VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE BlogCategory DROP full_friendly_url');
        $this->addSql('DROP INDEX status ON Blog_Category');
        $this->addSql('DROP INDEX category_status ON Blog_Category');
        $this->addSql('ALTER TABLE Blog_Category DROP category_root_id, DROP category_node_left, DROP category_node_right, DROP status');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE BlogCategory ADD full_friendly_url LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE Blog_Category ADD category_root_id INT NOT NULL, ADD category_node_left INT NOT NULL, ADD category_node_right INT NOT NULL, ADD status VARCHAR(1) NOT NULL');
        $this->addSql('CREATE INDEX status ON Blog_Category (status)');
        $this->addSql('CREATE INDEX category_status ON Blog_Category (category_id, status)');
        $this->addSql('ALTER TABLE ListingCategory ADD full_friendly_url LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX category_listing_id ON Listing_Category');
        $this->addSql('ALTER TABLE Listing_Category ADD category_root_id INT DEFAULT NULL, ADD category_node_left INT DEFAULT NULL, ADD category_node_right INT DEFAULT NULL, ADD status VARCHAR(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE Listing_Category ADD CONSTRAINT FK_E796290BEDE28F8 FOREIGN KEY (category_root_id) REFERENCES ListingCategory (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX status ON Listing_Category (status)');
        $this->addSql('CREATE INDEX category_status ON Listing_Category (category_id, status)');
        $this->addSql('CREATE INDEX IDX_E796290BEDE28F8 ON Listing_Category (category_root_id)');
        $this->addSql('CREATE INDEX category_listing_id ON Listing_Category (category_id, category_root_id, listing_id)');
        $this->addSql('ALTER TABLE Promotion CHANGE listing_latitude listing_latitude VARCHAR(50) NOT NULL, CHANGE listing_longitude listing_longitude VARCHAR(50) NOT NULL');
    }
}
