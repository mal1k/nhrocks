<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181004162236 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Claim ADD old_categories VARCHAR(255) DEFAULT NULL, ADD new_categories VARCHAR(255) DEFAULT NULL, ADD old_description VARCHAR(255) DEFAULT NULL, ADD new_description VARCHAR(255) DEFAULT NULL, ADD old_long_description LONGTEXT DEFAULT NULL, ADD new_long_description LONGTEXT DEFAULT NULL, ADD old_keywords LONGTEXT DEFAULT NULL, ADD new_keywords LONGTEXT DEFAULT NULL, ADD old_locations LONGTEXT DEFAULT NULL, ADD new_locations LONGTEXT DEFAULT NULL, ADD old_features LONGTEXT DEFAULT NULL, ADD new_features LONGTEXT DEFAULT NULL, ADD old_hours_work LONGTEXT DEFAULT NULL, ADD new_hours_work LONGTEXT DEFAULT NULL, ADD old_additional_fields LONGTEXT DEFAULT NULL, ADD new_additional_fields LONGTEXT DEFAULT NULL, ADD old_seo_title VARCHAR(255) DEFAULT NULL, ADD new_seo_title VARCHAR(255) DEFAULT NULL, ADD old_seo_keywords VARCHAR(255) DEFAULT NULL, ADD new_seo_keywords VARCHAR(255) DEFAULT NULL, ADD old_seo_description VARCHAR(255) DEFAULT NULL, ADD new_seo_description VARCHAR(255) DEFAULT NULL, ADD old_social_network LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', ADD new_social_network LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', ADD old_latitude VARCHAR(50) DEFAULT NULL, ADD new_latitude VARCHAR(50) DEFAULT NULL, ADD old_longitude VARCHAR(50) DEFAULT NULL, ADD new_longitude VARCHAR(50) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Claim DROP old_categories, DROP new_categories, DROP old_description, DROP new_description, DROP old_long_description, DROP new_long_description, DROP old_keywords, DROP new_keywords, DROP old_locations, DROP new_locations, DROP old_features, DROP new_features, DROP old_hours_work, DROP new_hours_work, DROP old_additional_fields, DROP new_additional_fields, DROP old_seo_title, DROP new_seo_title, DROP old_seo_keywords, DROP new_seo_keywords, DROP old_seo_description, DROP new_seo_description, DROP old_social_network, DROP new_social_network, DROP old_latitude, DROP new_latitude, DROP old_longitude, DROP new_longitude');
    }
}
