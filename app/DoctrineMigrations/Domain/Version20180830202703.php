<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180830202703 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Article ADD author_image_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Article ADD CONSTRAINT FK_CD8737FAD2350FD8 FOREIGN KEY (author_image_id) REFERENCES Image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CD8737FAD2350FD8 ON Article (author_image_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Article DROP FOREIGN KEY FK_CD8737FAD2350FD8');
        $this->addSql('DROP INDEX UNIQ_CD8737FAD2350FD8 ON Article');
        $this->addSql('ALTER TABLE Article DROP author_image_id');
    }
}
