<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190111120541 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ListingLevel ADD has_logo_image VARCHAR(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE Listing ADD logo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Listing ADD CONSTRAINT FK_4BD7148F98F144A FOREIGN KEY (logo_id) REFERENCES Image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4BD7148F98F144A ON Listing (logo_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Listing DROP FOREIGN KEY FK_4BD7148F98F144A');
        $this->addSql('DROP INDEX UNIQ_4BD7148F98F144A ON Listing');
        $this->addSql('ALTER TABLE Listing DROP logo_id');
        $this->addSql('ALTER TABLE ListingLevel DROP has_logo_image');
    }
}
