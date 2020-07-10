<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180410123930 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ClassifiedLevel ADD has_cover_image VARCHAR(1)');
        $this->addSql('ALTER TABLE EventLevel ADD has_cover_image VARCHAR(1)');
        $this->addSql('ALTER TABLE ListingLevel ADD has_cover_image VARCHAR(1)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ClassifiedLevel DROP has_cover_image');
        $this->addSql('ALTER TABLE EventLevel DROP has_cover_image');
        $this->addSql('ALTER TABLE ListingLevel DROP has_cover_image');
    }
}
