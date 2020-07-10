<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171212174627 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE EventCategory CHANGE summary_description summary_description VARCHAR(255) DEFAULT NULL, CHANGE seo_description seo_description VARCHAR(255) DEFAULT NULL, CHANGE page_title page_title VARCHAR(255) DEFAULT NULL, CHANGE keywords keywords VARCHAR(255) DEFAULT NULL, CHANGE seo_keywords seo_keywords VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE EventCategory CHANGE summary_description summary_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_description seo_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE page_title page_title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE keywords keywords VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_keywords seo_keywords VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
