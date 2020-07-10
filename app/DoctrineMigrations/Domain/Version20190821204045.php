<?php

namespace Application\Migrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190821204045 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws AbortMigrationException
     * @throws DBALException
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        if ($schema->hasTable('Gallery_Image')) {
            if ($schema->getTable('Gallery_Image')->hasColumn('thumb_id')) {
                $this->addSql(/** @lang MySQL */ 'ALTER TABLE Gallery_Image DROP thumb_id');
            }
            if ($schema->getTable('Gallery_Image')->hasColumn('image_caption')&&
                !$schema->getTable('Gallery_Image')->hasColumn('alt_caption')) {
                $this->addSql(/** @lang MySQL */'ALTER TABLE Gallery_Image CHANGE image_caption alt_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
                if ($schema->getTable('Gallery_Image')->hasColumn('thumb_caption')) {
                    $this->addSql(/** @lang MySQL */'ALTER TABLE Gallery_Image CHANGE thumb_caption image_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
                }
            }
            if ($schema->getTable('Gallery_Image')->hasColumn('thumb_caption')&&
                $schema->getTable('Gallery_Image')->hasColumn('alt_caption') &&
                !$schema->getTable('Gallery_Image')->hasColumn('image_caption')) {
                $this->addSql(/** @lang MySQL */'ALTER TABLE Gallery_Image CHANGE thumb_caption image_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
            }
        }
        if ($schema->hasTable('Gallery_Temp')) {
            if ($schema->getTable('Gallery_Temp')->hasColumn('thumb_id')) {
                $this->addSql(/** @lang MySQL */'ALTER TABLE Gallery_Temp DROP thumb_id');
            }
            if ($schema->getTable('Gallery_Temp')->hasColumn('image_caption') &&
                !$schema->getTable('Gallery_Temp')->hasColumn('alt_caption')) {
                $this->addSql(/** @lang MySQL */ 'ALTER TABLE Gallery_Temp CHANGE image_caption alt_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');

                if ($schema->getTable('Gallery_Temp')->hasColumn('thumb_caption')) {
                    $this->addSql(/** @lang MySQL */ 'ALTER TABLE Gallery_Temp CHANGE thumb_caption image_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
                }
            }
            if ($schema->getTable('Gallery_Temp')->hasColumn('thumb_caption')&&
                $schema->getTable('Gallery_Temp')->hasColumn('alt_caption') &&
                !$schema->getTable('Gallery_Temp')->hasColumn('image_caption')) {
                $this->addSql(/** @lang MySQL */ 'ALTER TABLE Gallery_Temp CHANGE thumb_caption image_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
            }
        }
        if ($schema->hasTable('ArticleCategory') && $schema->getTable('ArticleCategory')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE ArticleCategory DROP thumb_id');
        }
        if ($schema->hasTable('Article') && $schema->getTable('Article')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE Article DROP thumb_id');
        }
        if ($schema->hasTable('ClassifiedCategory') && $schema->getTable('ClassifiedCategory')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE ClassifiedCategory DROP thumb_id');
        }
        if ($schema->hasTable('Classified') && $schema->getTable('Classified')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE Classified DROP thumb_id');
        }
        if ($schema->hasTable('Event') && $schema->getTable('Event')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE Event DROP thumb_id');
        }
        if ($schema->hasTable('EventCategory') && $schema->getTable('EventCategory')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE EventCategory DROP thumb_id');
        }
        if($schema->hasTable('Listing')) {
            if ($schema->getTable('Listing')->hasIndex('thumb_id')) {
                $this->addSql(/** @lang MySQL */'DROP INDEX thumb_id ON Listing');
            }
            if ($schema->getTable('Listing')->hasColumn('thumb_id')) {
                $this->addSql(/** @lang MySQL */'ALTER TABLE Listing DROP thumb_id');
            }
        }
        if($schema->hasTable('ListingCategory') && $schema->getTable('ListingCategory')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE ListingCategory DROP thumb_id');
        }
        if ($schema->hasTable('Promotion') && $schema->getTable('Promotion')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE Promotion DROP thumb_id');
        }
        if($schema->hasTable('Post')) {
            if ($schema->getTable('Post')->hasColumn('thumb_id')) {
                $this->addSql(/** @lang MySQL */'ALTER TABLE Post DROP thumb_id');
            }
            if ($schema->getTable('Post')->hasColumn('image_caption') &&
                !$schema->getTable('Post')->hasColumn('alt_caption')) {
                $this->addSql(/** @lang MySQL */ 'ALTER TABLE Post CHANGE image_caption alt_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
                if ($schema->getTable('Post')->hasColumn('thumb_caption')) {
                    $this->addSql(/** @lang MySQL */ 'ALTER TABLE Post CHANGE thumb_caption image_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
                }
            }
            if ($schema->getTable('Post')->hasColumn('thumb_caption')&&
                $schema->getTable('Post')->hasColumn('alt_caption') &&
                !$schema->getTable('Post')->hasColumn('image_caption')) {
                    $this->addSql(/** @lang MySQL */ 'ALTER TABLE Post CHANGE thumb_caption image_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
            }
        }
        if ($schema->hasTable('BlogCategory') && $schema->getTable('BlogCategory')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE BlogCategory DROP thumb_id');
        }
    }

    /**
     * @param Schema $schema
     * @throws DBALException
     * @throws AbortMigrationException
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        if ($schema->hasTable('Gallery_Image')) {
            if (!$schema->getTable('Gallery_Image')->hasColumn('thumb_id')) {
                $this->addSql(/** @lang MySQL */'ALTER TABLE Gallery_Image ADD thumb_id INT DEFAULT NULL COLLATE utf8_unicode_ci');
            }

            if($schema->getTable('Gallery_Image')->hasColumn('image_caption')) {
                if (!$schema->getTable('Gallery_Image')->hasColumn('thumb_caption')&&
                    $schema->getTable('Gallery_Image')->hasColumn('alt_caption')) {
                    $this->addSql(/** @lang MySQL */'ALTER TABLE Gallery_Image CHANGE image_caption thumb_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
                    $this->addSql(/** @lang MySQL */ 'ALTER TABLE Gallery_Image CHANGE alt_caption image_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
                }
            } else if ($schema->getTable('Gallery_Image')->hasColumn('thumb_caption') &&
                       $schema->getTable('Gallery_Image')->hasColumn('alt_caption')){
                $this->addSql(/** @lang MySQL */ 'ALTER TABLE Gallery_Image CHANGE alt_caption image_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
            }
        }
        if ($schema->hasTable('Gallery_Temp')) {
            if (!$schema->getTable('Gallery_Temp')->hasColumn('thumb_id')) {
                $this->addSql(/** @lang MySQL */'ALTER TABLE Gallery_Temp ADD thumb_id INT NOT NULL COLLATE utf8_unicode_ci');
            }

            if($schema->getTable('Gallery_Temp')->hasColumn('image_caption')) {
                if (!$schema->getTable('Gallery_Temp')->hasColumn('thumb_caption')&&
                    $schema->getTable('Gallery_Temp')->hasColumn('alt_caption')) {
                    $this->addSql(/** @lang MySQL */'ALTER TABLE Gallery_Temp CHANGE image_caption thumb_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
                    $this->addSql(/** @lang MySQL */'ALTER TABLE Gallery_Temp CHANGE alt_caption image_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
                }
            } else if ($schema->getTable('Gallery_Temp')->hasColumn('thumb_caption') &&
                $schema->getTable('Gallery_Temp')->hasColumn('alt_caption')){
                $this->addSql(/** @lang MySQL */'ALTER TABLE Gallery_Temp CHANGE alt_caption image_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
            }
        }
        if ($schema->hasTable('ArticleCategory') && !$schema->getTable('ArticleCategory')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE ArticleCategory ADD thumb_id INT DEFAULT NULL');
        }
        if ($schema->hasTable('Article') && !$schema->getTable('Article')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE Article ADD thumb_id INT DEFAULT NULL');
        }
        if ($schema->hasTable('ClassifiedCategory') && !$schema->getTable('ClassifiedCategory')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE ClassifiedCategory ADD thumb_id INT DEFAULT NULL');
        }
        if ($schema->hasTable('Classified') && !$schema->getTable('Classified')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE Classified ADD thumb_id INT DEFAULT NULL');
        }
        if ($schema->hasTable('Event') && !$schema->getTable('Event')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE Event ADD thumb_id INT DEFAULT NULL');
        }
        if ($schema->hasTable('EventCategory') && !$schema->getTable('EventCategory')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE EventCategory ADD thumb_id INT DEFAULT NULL');
        }
        if ($schema->hasTable('Listing')) {
            if (!$schema->getTable('Listing')->hasColumn('thumb_id')) {
                $this->addSql(/** @lang MySQL */'ALTER TABLE Listing ADD thumb_id INT DEFAULT NULL');
            }
            if (!$schema->getTable('Listing')->hasIndex('thumb_id') && $schema->getTable('Listing')->hasColumn('thumb_id')) {
                $this->addSql(/** @lang MySQL */'CREATE INDEX thumb_id ON Listing (thumb_id)');
            }
        }
        if ($schema->hasTable('ListingCategory') && !$schema->getTable('ListingCategory')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE ListingCategory ADD thumb_id INT DEFAULT NULL');
        }
        if ($schema->hasTable('Promotion') && !$schema->getTable('Promotion')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE Promotion ADD thumb_id INT DEFAULT NULL');
        }
        if ($schema->hasTable('Post')) {
            if (!$schema->getTable('Post')->hasColumn('thumb_id')) {
                $this->addSql(/** @lang MySQL */'ALTER TABLE Post ADD thumb_id INT DEFAULT NULL COLLATE utf8_unicode_ci');
            }

            if($schema->getTable('Post')->hasColumn('image_caption')) {
                if (!$schema->getTable('Post')->hasColumn('thumb_caption')&&
                    $schema->getTable('Post')->hasColumn('alt_caption')) {
                    $this->addSql(/** @lang MySQL */'ALTER TABLE Post CHANGE image_caption thumb_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
                    $this->addSql(/** @lang MySQL */'ALTER TABLE Post CHANGE alt_caption image_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
                }
            } else if ($schema->getTable('Post')->hasColumn('thumb_caption') &&
                $schema->getTable('Post')->hasColumn('alt_caption')){
                $this->addSql(/** @lang MySQL */'ALTER TABLE Post CHANGE alt_caption image_caption VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
            }
        }
        if ($schema->hasTable('BlogCategory') && !$schema->getTable('BlogCategory')->hasColumn('thumb_id')) {
            $this->addSql(/** @lang MySQL */'ALTER TABLE BlogCategory ADD thumb_id INT DEFAULT NULL');
        }
    }
}
