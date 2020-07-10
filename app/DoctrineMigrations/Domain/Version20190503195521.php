<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190503195521 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX fulltextsearch_where ON Article');
        $this->addSql('ALTER TABLE Article DROP fulltextsearch_where');
        $this->addSql('DROP INDEX fulltextsearch_where ON Post');
        $this->addSql('ALTER TABLE Post DROP fulltextsearch_where');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Article ADD fulltextsearch_where TEXT NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE FULLTEXT INDEX fulltextsearch_where ON Article (fulltextsearch_where)');
        $this->addSql('ALTER TABLE Post ADD fulltextsearch_where TEXT NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE FULLTEXT INDEX fulltextsearch_where ON Post (fulltextsearch_where)');
    }
}
