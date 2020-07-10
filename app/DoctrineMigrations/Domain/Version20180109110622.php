<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180109110622 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX expiration_setting ON Banner');
        $this->addSql('DROP INDEX impressions ON Banner');
        $this->addSql('ALTER TABLE Banner DROP unpaid_impressions, DROP impressions, DROP unlimited_impressions, DROP expiration_setting');
        $this->addSql('ALTER TABLE BannerLevel DROP impression_block, DROP impression_price');
        $this->addSql('ALTER TABLE Invoice_Banner DROP impressions');
        $this->addSql('ALTER TABLE Payment_Banner_Log DROP impressions');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Banner ADD unpaid_impressions INT NOT NULL, ADD impressions INT NOT NULL, ADD unlimited_impressions VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci, ADD expiration_setting VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE INDEX expiration_setting ON Banner (expiration_setting)');
        $this->addSql('CREATE INDEX impressions ON Banner (impressions)');
        $this->addSql('ALTER TABLE BannerLevel ADD impression_block INT NOT NULL, ADD impression_price NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE Invoice_Banner ADD impressions INT NOT NULL');
        $this->addSql('ALTER TABLE Payment_Banner_Log ADD impressions INT NOT NULL');
    }
}
