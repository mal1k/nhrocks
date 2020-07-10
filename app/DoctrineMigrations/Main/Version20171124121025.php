<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171124121025 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE Control_Import_Event');
        $this->addSql('DROP TABLE Control_Import_Listing');
        $this->addSql('ALTER TABLE Contact DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE Contact ADD id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (`id`), CHANGE account_id account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Contact ADD CONSTRAINT FK_83DFDFA49B6B5FBA FOREIGN KEY (account_id) REFERENCES Account (id)');
        $this->addSql('ALTER TABLE Location_1 ADD import_id INT DEFAULT NULL, CHANGE abbreviation abbreviation VARCHAR(100) DEFAULT NULL, CHANGE seo_description seo_description VARCHAR(255) DEFAULT NULL, CHANGE seo_keywords seo_keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE Location_2 ADD import_id INT DEFAULT NULL, CHANGE abbreviation abbreviation VARCHAR(100) DEFAULT NULL, CHANGE seo_description seo_description VARCHAR(255) DEFAULT NULL, CHANGE seo_keywords seo_keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE Location_3 ADD import_id INT DEFAULT NULL, CHANGE abbreviation abbreviation VARCHAR(100) DEFAULT NULL, CHANGE seo_description seo_description VARCHAR(255) DEFAULT NULL, CHANGE seo_keywords seo_keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE Location_4 ADD import_id INT DEFAULT NULL, CHANGE abbreviation abbreviation VARCHAR(100) DEFAULT NULL, CHANGE seo_description seo_description VARCHAR(255) DEFAULT NULL, CHANGE seo_keywords seo_keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE Location_5 ADD import_id INT DEFAULT NULL, CHANGE abbreviation abbreviation VARCHAR(100) DEFAULT NULL, CHANGE seo_description seo_description VARCHAR(255) DEFAULT NULL, CHANGE seo_keywords seo_keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE Profile DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE Profile ADD id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id), CHANGE account_id account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Profile ADD CONSTRAINT FK_4EEA93939B6B5FBA FOREIGN KEY (account_id) REFERENCES Account (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Control_Import_Event (domain_id INT AUTO_INCREMENT NOT NULL, scheduled VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci, running VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci, status VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci, last_run_date DATETIME NOT NULL, last_importlog INT NOT NULL, PRIMARY KEY(domain_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Control_Import_Listing (domain_id INT AUTO_INCREMENT NOT NULL, scheduled VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci, running VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci, status VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci, last_run_date DATETIME NOT NULL, last_importlog INT NOT NULL, PRIMARY KEY(domain_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Location_1 DROP import_id, CHANGE abbreviation abbreviation VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_description seo_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_keywords seo_keywords VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE Location_2 DROP import_id, CHANGE abbreviation abbreviation VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_description seo_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_keywords seo_keywords VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE Location_3 DROP import_id, CHANGE abbreviation abbreviation VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_description seo_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_keywords seo_keywords VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE Location_4 DROP import_id, CHANGE abbreviation abbreviation VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_description seo_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_keywords seo_keywords VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE Location_5 DROP import_id, CHANGE abbreviation abbreviation VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_description seo_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE seo_keywords seo_keywords VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE Contact MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE Contact DROP FOREIGN KEY FK_83DFDFA49B6B5FBA');
        $this->addSql('ALTER TABLE Contact DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE Contact DROP id, CHANGE account_id account_id INT NOT NULL');
        $this->addSql('ALTER TABLE Contact ADD PRIMARY KEY (account_id)');
        $this->addSql('ALTER TABLE Profile MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE Profile DROP FOREIGN KEY FK_4EEA93939B6B5FBA');
        $this->addSql('ALTER TABLE Profile DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE Profile DROP id, CHANGE account_id account_id INT NOT NULL');
        $this->addSql('ALTER TABLE Profile ADD PRIMARY KEY (account_id)');
    }
}
