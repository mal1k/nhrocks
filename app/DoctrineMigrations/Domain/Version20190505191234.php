<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190505191234 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ArticleCategory ADD icon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ArticleCategory ADD CONSTRAINT FK_EE65E0C354B9D732 FOREIGN KEY (icon_id) REFERENCES Image (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EE65E0C354B9D732 ON ArticleCategory (icon_id)');
        $this->addSql('ALTER TABLE ClassifiedCategory ADD icon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ClassifiedCategory ADD CONSTRAINT FK_E226DCC54B9D732 FOREIGN KEY (icon_id) REFERENCES Image (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E226DCC54B9D732 ON ClassifiedCategory (icon_id)');
        $this->addSql('ALTER TABLE EventCategory ADD icon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE EventCategory ADD CONSTRAINT FK_BD5B78B054B9D732 FOREIGN KEY (icon_id) REFERENCES Image (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD5B78B054B9D732 ON EventCategory (icon_id)');
        $this->addSql('ALTER TABLE ListingCategory ADD icon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ListingCategory ADD CONSTRAINT FK_1C89DF3B54B9D732 FOREIGN KEY (icon_id) REFERENCES Image (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1C89DF3B54B9D732 ON ListingCategory (icon_id)');
        $this->addSql('ALTER TABLE BlogCategory ADD icon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE BlogCategory ADD CONSTRAINT FK_7FB5FC9154B9D732 FOREIGN KEY (icon_id) REFERENCES Image (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7FB5FC9154B9D732 ON BlogCategory (icon_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ArticleCategory DROP FOREIGN KEY FK_EE65E0C354B9D732');
        $this->addSql('DROP INDEX UNIQ_EE65E0C354B9D732 ON ArticleCategory');
        $this->addSql('ALTER TABLE ArticleCategory DROP icon_id');
        $this->addSql('ALTER TABLE BlogCategory DROP FOREIGN KEY FK_7FB5FC9154B9D732');
        $this->addSql('DROP INDEX UNIQ_7FB5FC9154B9D732 ON BlogCategory');
        $this->addSql('ALTER TABLE BlogCategory DROP icon_id');
        $this->addSql('ALTER TABLE ClassifiedCategory DROP FOREIGN KEY FK_E226DCC54B9D732');
        $this->addSql('DROP INDEX UNIQ_E226DCC54B9D732 ON ClassifiedCategory');
        $this->addSql('ALTER TABLE ClassifiedCategory DROP icon_id');
        $this->addSql('ALTER TABLE EventCategory DROP FOREIGN KEY FK_BD5B78B054B9D732');
        $this->addSql('DROP INDEX UNIQ_BD5B78B054B9D732 ON EventCategory');
        $this->addSql('ALTER TABLE EventCategory DROP icon_id');
        $this->addSql('ALTER TABLE ListingCategory DROP FOREIGN KEY FK_1C89DF3B54B9D732');
        $this->addSql('DROP INDEX UNIQ_1C89DF3B54B9D732 ON ListingCategory');
        $this->addSql('ALTER TABLE ListingCategory DROP icon_id');
    }
}
