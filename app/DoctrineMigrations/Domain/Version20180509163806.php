<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180509163806 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ClassifiedLevel CHANGE has_cover_image has_cover_image VARCHAR(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE EventLevel CHANGE has_cover_image has_cover_image VARCHAR(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE ListingLevel CHANGE has_cover_image has_cover_image VARCHAR(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE Promotion DROP FOREIGN KEY FK_43ECFF72D4619D1A');
        $this->addSql('CREATE INDEX listing_start_end ON Promotion (listing_id, start_date, end_date)');
        $this->addSql('DROP INDEX listing_id ON Promotion');
        $this->addSql('CREATE INDEX listing_status ON Promotion (listing_id, listing_status)');
        $this->addSql('ALTER TABLE Promotion ADD CONSTRAINT FK_43ECFF72D4619D1A FOREIGN KEY (listing_id) REFERENCES Listing (id)');
        $this->addSql('ALTER TABLE Page_Widget DROP FOREIGN KEY FK_6B1F622F59027487');
        $this->addSql('ALTER TABLE Page_Widget DROP FOREIGN KEY FK_6B1F622FC4663E4');
        $this->addSql('ALTER TABLE Page_Widget DROP FOREIGN KEY FK_6B1F622FFBE885E2');
        $this->addSql('CREATE INDEX IDX_page_theme ON Page_Widget (page_id, theme_id)');
        $this->addSql('DROP INDEX page_id ON Page_Widget');
        $this->addSql('CREATE INDEX IDX_page_id ON Page_Widget (page_id)');
        $this->addSql('DROP INDEX widget_id ON Page_Widget');
        $this->addSql('CREATE INDEX IDX_widget_id ON Page_Widget (widget_id)');
        $this->addSql('DROP INDEX idx_6b1f622f59027487 ON Page_Widget');
        $this->addSql('CREATE INDEX IDX_theme_id ON Page_Widget (theme_id)');
        $this->addSql('ALTER TABLE Page_Widget ADD CONSTRAINT FK_6B1F622F59027487 FOREIGN KEY (theme_id) REFERENCES Theme (id)');
        $this->addSql('ALTER TABLE Page_Widget ADD CONSTRAINT FK_6B1F622FC4663E4 FOREIGN KEY (page_id) REFERENCES Page (id)');
        $this->addSql('ALTER TABLE Page_Widget ADD CONSTRAINT FK_6B1F622FFBE885E2 FOREIGN KEY (widget_id) REFERENCES Widget (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ClassifiedLevel CHANGE has_cover_image has_cover_image VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE EventLevel CHANGE has_cover_image has_cover_image VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE ListingLevel CHANGE has_cover_image has_cover_image VARCHAR(1) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('DROP INDEX IDX_page_theme ON Page_Widget');
        $this->addSql('ALTER TABLE Page_Widget DROP FOREIGN KEY FK_6B1F622FC4663E4');
        $this->addSql('ALTER TABLE Page_Widget DROP FOREIGN KEY FK_6B1F622FFBE885E2');
        $this->addSql('ALTER TABLE Page_Widget DROP FOREIGN KEY FK_6B1F622F59027487');
        $this->addSql('DROP INDEX idx_theme_id ON Page_Widget');
        $this->addSql('CREATE INDEX IDX_6B1F622F59027487 ON Page_Widget (theme_id)');
        $this->addSql('DROP INDEX idx_page_id ON Page_Widget');
        $this->addSql('CREATE INDEX page_id ON Page_Widget (page_id)');
        $this->addSql('DROP INDEX idx_widget_id ON Page_Widget');
        $this->addSql('CREATE INDEX widget_id ON Page_Widget (widget_id)');
        $this->addSql('ALTER TABLE Page_Widget ADD CONSTRAINT FK_6B1F622FC4663E4 FOREIGN KEY (page_id) REFERENCES Page (id)');
        $this->addSql('ALTER TABLE Page_Widget ADD CONSTRAINT FK_6B1F622FFBE885E2 FOREIGN KEY (widget_id) REFERENCES Widget (id)');
        $this->addSql('ALTER TABLE Page_Widget ADD CONSTRAINT FK_6B1F622F59027487 FOREIGN KEY (theme_id) REFERENCES Theme (id)');
        $this->addSql('DROP INDEX listing_start_end ON Promotion');
        $this->addSql('ALTER TABLE Promotion DROP FOREIGN KEY FK_43ECFF72D4619D1A');
        $this->addSql('DROP INDEX listing_status ON Promotion');
        $this->addSql('CREATE INDEX listing_id ON Promotion (listing_id, listing_status)');
        $this->addSql('ALTER TABLE Promotion ADD CONSTRAINT FK_43ECFF72D4619D1A FOREIGN KEY (listing_id) REFERENCES Listing (id)');
    }
}
