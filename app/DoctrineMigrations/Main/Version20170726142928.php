<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170726142928 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Profile ADD CONSTRAINT FK_4EEA93939B6B5FBA FOREIGN KEY (account_id) REFERENCES Account (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4EEA93939B6B5FBA ON Profile (account_id)');
        $this->addSql('CREATE INDEX username ON Account (username)');
        $this->addSql('ALTER TABLE Contact ADD CONSTRAINT FK_83DFDFA49B6B5FBA FOREIGN KEY (account_id) REFERENCES Account (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_83DFDFA49B6B5FBA ON Contact (account_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX username ON Account');
        $this->addSql('ALTER TABLE Contact DROP FOREIGN KEY FK_83DFDFA49B6B5FBA');
        $this->addSql('DROP INDEX UNIQ_83DFDFA49B6B5FBA ON Contact');
        $this->addSql('ALTER TABLE Profile DROP FOREIGN KEY FK_4EEA93939B6B5FBA');
        $this->addSql('DROP INDEX UNIQ_4EEA93939B6B5FBA ON Profile');
    }
}
