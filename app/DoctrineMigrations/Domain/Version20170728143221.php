<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170728143221 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE Setting_Google');
        $this->addSql('ALTER TABLE FAQ CHANGE keyword keyword VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX fulltextsearch_title ON Article');
        $this->addSql('DROP INDEX fulltextsearch_title ON Classified');
        $this->addSql('DROP INDEX friendly_url ON Event');
        $this->addSql('DROP INDEX fulltextsearch_title ON Event');
        $this->addSql('ALTER TABLE Listing DROP INDEX friendly_url_2, ADD INDEX friendly_url (friendly_url)');
        $this->addSql('CREATE INDEX image_id ON Listing (image_id)');
        $this->addSql('DROP INDEX fulltextsearch_keyword ON Listing');
        $this->addSql('CREATE FULLTEXT INDEX idx_fulltextsearch_keyword ON Listing (fulltextsearch_keyword)');
        $this->addSql('DROP INDEX fulltextsearch_title ON Promotion');
        $this->addSql('ALTER TABLE Promotion CHANGE listing_latitude listing_latitude VARCHAR(50) DEFAULT NULL, CHANGE listing_longitude listing_longitude VARCHAR(50) DEFAULT NULL');
        $this->addSql('DROP INDEX fulltextsearch_title ON Post');
        $this->addSql('ALTER TABLE Page_Widget DROP FOREIGN KEY FK_6B1F622FC4663E4');
        $this->addSql('ALTER TABLE Page_Widget DROP FOREIGN KEY FK_6B1F622FFBE885E2');
        $this->addSql('DROP INDEX idx_6b1f622fc4663e4 ON Page_Widget');
        $this->addSql('CREATE INDEX page_id ON Page_Widget (page_id)');
        $this->addSql('DROP INDEX idx_6b1f622ffbe885e2 ON Page_Widget');
        $this->addSql('CREATE INDEX widget_id ON Page_Widget (widget_id)');
        $this->addSql('ALTER TABLE Page_Widget ADD CONSTRAINT FK_6B1F622FC4663E4 FOREIGN KEY (page_id) REFERENCES Page (id)');
        $this->addSql('ALTER TABLE Page_Widget ADD CONSTRAINT FK_6B1F622FFBE885E2 FOREIGN KEY (widget_id) REFERENCES Widget (id)');
        $this->addSql('ALTER TABLE Widget_Theme DROP FOREIGN KEY FK_15C34AE259027487');
        $this->addSql('ALTER TABLE Widget_Theme DROP FOREIGN KEY FK_15C34AE2FBE885E2');
        $this->addSql('DROP INDEX idx_15c34ae2fbe885e2 ON Widget_Theme');
        $this->addSql('CREATE INDEX widget_id ON Widget_Theme (widget_id)');
        $this->addSql('DROP INDEX idx_15c34ae259027487 ON Widget_Theme');
        $this->addSql('CREATE INDEX theme_id ON Widget_Theme (theme_id)');
        $this->addSql('ALTER TABLE Widget_Theme ADD CONSTRAINT FK_15C34AE259027487 FOREIGN KEY (theme_id) REFERENCES Theme (id)');
        $this->addSql('ALTER TABLE Widget_Theme ADD CONSTRAINT FK_15C34AE2FBE885E2 FOREIGN KEY (widget_id) REFERENCES Widget (id)');
        $this->addSql('ALTER TABLE Widget_PageType DROP FOREIGN KEY FK_30E6689553A99D0E');
        $this->addSql('ALTER TABLE Widget_PageType DROP FOREIGN KEY FK_30E66895FBE885E2');
        $this->addSql('DROP INDEX idx_30e66895fbe885e2 ON Widget_PageType');
        $this->addSql('CREATE INDEX widget_id ON Widget_PageType (widget_id)');
        $this->addSql('DROP INDEX idx_30e6689553a99d0e ON Widget_PageType');
        $this->addSql('CREATE INDEX pagetype_id ON Widget_PageType (pagetype_id)');
        $this->addSql('ALTER TABLE Widget_PageType ADD CONSTRAINT FK_30E6689553A99D0E FOREIGN KEY (pagetype_id) REFERENCES PageType (id)');
        $this->addSql('ALTER TABLE Widget_PageType ADD CONSTRAINT FK_30E66895FBE885E2 FOREIGN KEY (widget_id) REFERENCES Widget (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Setting_Google (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, value VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE FULLTEXT INDEX fulltextsearch_title ON Article (title)');
        $this->addSql('CREATE FULLTEXT INDEX fulltextsearch_title ON Classified (title)');
        $this->addSql('CREATE INDEX friendly_url ON Event (friendly_url)');
        $this->addSql('CREATE FULLTEXT INDEX fulltextsearch_title ON Event (title)');
        $this->addSql('ALTER TABLE FAQ CHANGE keyword keyword TEXT NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE Listing DROP INDEX friendly_url, ADD UNIQUE INDEX friendly_url_2 (friendly_url)');
        $this->addSql('DROP INDEX image_id ON Listing');
        $this->addSql('DROP INDEX idx_fulltextsearch_keyword ON Listing');
        $this->addSql('CREATE FULLTEXT INDEX fulltextsearch_keyword ON Listing (fulltextsearch_keyword)');
        $this->addSql('ALTER TABLE Page_Widget DROP FOREIGN KEY FK_6B1F622FC4663E4');
        $this->addSql('ALTER TABLE Page_Widget DROP FOREIGN KEY FK_6B1F622FFBE885E2');
        $this->addSql('DROP INDEX page_id ON Page_Widget');
        $this->addSql('CREATE INDEX IDX_6B1F622FC4663E4 ON Page_Widget (page_id)');
        $this->addSql('DROP INDEX widget_id ON Page_Widget');
        $this->addSql('CREATE INDEX IDX_6B1F622FFBE885E2 ON Page_Widget (widget_id)');
        $this->addSql('ALTER TABLE Page_Widget ADD CONSTRAINT FK_6B1F622FC4663E4 FOREIGN KEY (page_id) REFERENCES Page (id)');
        $this->addSql('ALTER TABLE Page_Widget ADD CONSTRAINT FK_6B1F622FFBE885E2 FOREIGN KEY (widget_id) REFERENCES Widget (id)');
        $this->addSql('CREATE FULLTEXT INDEX fulltextsearch_title ON Post (title)');
        $this->addSql('ALTER TABLE Promotion CHANGE listing_latitude listing_latitude VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, CHANGE listing_longitude listing_longitude VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE FULLTEXT INDEX fulltextsearch_title ON Promotion (name)');
        $this->addSql('ALTER TABLE Widget_PageType DROP FOREIGN KEY FK_30E66895FBE885E2');
        $this->addSql('ALTER TABLE Widget_PageType DROP FOREIGN KEY FK_30E6689553A99D0E');
        $this->addSql('DROP INDEX widget_id ON Widget_PageType');
        $this->addSql('CREATE INDEX IDX_30E66895FBE885E2 ON Widget_PageType (widget_id)');
        $this->addSql('DROP INDEX pagetype_id ON Widget_PageType');
        $this->addSql('CREATE INDEX IDX_30E6689553A99D0E ON Widget_PageType (pagetype_id)');
        $this->addSql('ALTER TABLE Widget_PageType ADD CONSTRAINT FK_30E66895FBE885E2 FOREIGN KEY (widget_id) REFERENCES Widget (id)');
        $this->addSql('ALTER TABLE Widget_PageType ADD CONSTRAINT FK_30E6689553A99D0E FOREIGN KEY (pagetype_id) REFERENCES PageType (id)');
        $this->addSql('ALTER TABLE Widget_Theme DROP FOREIGN KEY FK_15C34AE2FBE885E2');
        $this->addSql('ALTER TABLE Widget_Theme DROP FOREIGN KEY FK_15C34AE259027487');
        $this->addSql('DROP INDEX widget_id ON Widget_Theme');
        $this->addSql('CREATE INDEX IDX_15C34AE2FBE885E2 ON Widget_Theme (widget_id)');
        $this->addSql('DROP INDEX theme_id ON Widget_Theme');
        $this->addSql('CREATE INDEX IDX_15C34AE259027487 ON Widget_Theme (theme_id)');
        $this->addSql('ALTER TABLE Widget_Theme ADD CONSTRAINT FK_15C34AE2FBE885E2 FOREIGN KEY (widget_id) REFERENCES Widget (id)');
        $this->addSql('ALTER TABLE Widget_Theme ADD CONSTRAINT FK_15C34AE259027487 FOREIGN KEY (theme_id) REFERENCES Theme (id)');
    }
}
