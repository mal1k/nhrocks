<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171016090000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Location_1 ENGINE=InnoDB');
        $this->addSql('ALTER TABLE Location_2 ENGINE=InnoDB');
        $this->addSql('ALTER TABLE Location_3 ENGINE=InnoDB');
        $this->addSql('ALTER TABLE Location_4 ENGINE=InnoDB');
        $this->addSql('ALTER TABLE Location_5 ENGINE=InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Location_1 ENGINE=MYISAM');
        $this->addSql('ALTER TABLE Location_2 ENGINE=MYISAM');
        $this->addSql('ALTER TABLE Location_3 ENGINE=MYISAM');
        $this->addSql('ALTER TABLE Location_4 ENGINE=MYISAM');
        $this->addSql('ALTER TABLE Location_5 ENGINE=MYISAM');
    }
}
