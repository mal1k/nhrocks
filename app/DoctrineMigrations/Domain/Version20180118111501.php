<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180118111501 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX `right` ON ListingCategory');
        $this->addSql('DROP INDEX root_id ON ListingCategory');
        $this->addSql('DROP INDEX `left` ON ListingCategory');
        $this->addSql('DROP INDEX cat_tree ON ListingCategory');
        $this->addSql('ALTER TABLE ListingCategory DROP root_id, DROP `left`, DROP `right`');
        $this->addSql('ALTER TABLE BlogCategory DROP root_id, DROP `left`, DROP `right`');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE BlogCategory ADD root_id INT DEFAULT NULL, ADD `left` INT DEFAULT NULL, ADD `right` INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ListingCategory ADD root_id INT DEFAULT NULL, ADD `left` INT DEFAULT NULL, ADD `right` INT DEFAULT NULL');
        $this->addSql('CREATE INDEX `right` ON ListingCategory (`right`)');
        $this->addSql('CREATE INDEX root_id ON ListingCategory (root_id)');
        $this->addSql('CREATE INDEX `left` ON ListingCategory (`left`)');
        $this->addSql('CREATE INDEX cat_tree ON ListingCategory (root_id, `left`, `right`)');
    }
}
