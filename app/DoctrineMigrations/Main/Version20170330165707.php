<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170330165707 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Account DROP agree_tou, DROP foreignaccount_done, DROP foreignaccount_redirect, DROP foreignaccount_auth, DROP facebook_firstname, DROP facebook_lastname');
        $this->addSql('ALTER TABLE Profile DROP facebook_image_height, DROP facebook_image_width, DROP twitter_account, DROP fb_post, DROP tw_post, DROP usefacebooklocation, DROP tw_oauth_token, DROP location, DROP tw_oauth_token_secret, DROP tw_screen_name, DROP profile_complete');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Account ADD agree_tou VARCHAR(1) DEFAULT NULL, ADD foreignaccount_done VARCHAR(1) DEFAULT NULL, ADD foreignaccount_redirect VARCHAR(255) DEFAULT NULL, ADD foreignaccount_auth LONGTEXT DEFAULT NULL, ADD facebook_firstname VARCHAR(100) DEFAULT NULL, ADD facebook_lastname VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE Profile ADD facebook_image_height INT DEFAULT NULL, ADD facebook_image_width INT DEFAULT NULL, ADD twitter_account VARCHAR(100) DEFAULT NULL, ADD fb_post SMALLINT DEFAULT NULL, ADD tw_post SMALLINT DEFAULT NULL, ADD usefacebooklocation SMALLINT DEFAULT NULL, ADD tw_oauth_token VARCHAR(250) DEFAULT NULL, ADD location VARCHAR(250) DEFAULT NULL, ADD tw_oauth_token_secret VARCHAR(250) DEFAULT NULL, ADD tw_screen_name VARCHAR(250) DEFAULT NULL, ADD profile_complete VARCHAR(1) DEFAULT \'n\' NOT NULL');
    }
}
