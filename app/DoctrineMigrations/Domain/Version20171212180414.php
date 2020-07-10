<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171212180414 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Event CHANGE level level INT DEFAULT NULL, CHANGE discount_id discount_id VARCHAR(10) DEFAULT NULL, CHANGE seo_title seo_title VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE seo_description seo_description VARCHAR(255) DEFAULT NULL, CHANGE long_description long_description LONGTEXT DEFAULT NULL, CHANGE keywords keywords LONGTEXT DEFAULT NULL, CHANGE seo_keywords seo_keywords VARCHAR(255) DEFAULT NULL, CHANGE location location VARCHAR(255) DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE zip_code zip_code VARCHAR(10) DEFAULT NULL, CHANGE url url VARCHAR(255) DEFAULT NULL, CHANGE contact_name contact_name VARCHAR(255) DEFAULT NULL, CHANGE phone phone VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(100) DEFAULT NULL, CHANGE renewal_date renewal_date DATE DEFAULT NULL, CHANGE fulltextsearch_keyword fulltextsearch_keyword LONGTEXT DEFAULT NULL, CHANGE fulltextsearch_where fulltextsearch_where LONGTEXT DEFAULT NULL, CHANGE video_snippet video_snippet LONGTEXT DEFAULT NULL, CHANGE video_url video_url VARCHAR(255) DEFAULT NULL, CHANGE day day INT DEFAULT NULL, CHANGE dayofweek dayofweek VARCHAR(15) DEFAULT NULL, CHANGE week week VARCHAR(255) DEFAULT NULL, CHANGE month month INT DEFAULT NULL, CHANGE repeat_event repeat_event VARCHAR(1) DEFAULT NULL, CHANGE map_zoom map_zoom INT DEFAULT NULL, CHANGE package_id package_id INT DEFAULT NULL, CHANGE package_price package_price NUMERIC(10, 2) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Event CHANGE level level INT NOT NULL, CHANGE discount_id discount_id VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_title seo_title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE description description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_description seo_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE long_description long_description TEXT NOT NULL COLLATE utf8_unicode_ci, CHANGE keywords keywords TEXT NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_keywords seo_keywords VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE location location VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE address address VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE zip_code zip_code VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, CHANGE url url VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE contact_name contact_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE phone phone VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE email email VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, CHANGE renewal_date renewal_date DATE NOT NULL, CHANGE fulltextsearch_keyword fulltextsearch_keyword TEXT NOT NULL COLLATE utf8_unicode_ci, CHANGE fulltextsearch_where fulltextsearch_where TEXT NOT NULL COLLATE utf8_unicode_ci, CHANGE video_snippet video_snippet TEXT NOT NULL COLLATE utf8_unicode_ci, CHANGE video_url video_url VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE day day INT NOT NULL, CHANGE dayofweek dayofweek VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, CHANGE week week VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE month month INT NOT NULL, CHANGE repeat_event repeat_event VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci, CHANGE map_zoom map_zoom INT NOT NULL, CHANGE package_id package_id INT NOT NULL, CHANGE package_price package_price NUMERIC(10, 2) NOT NULL');
    }
}
