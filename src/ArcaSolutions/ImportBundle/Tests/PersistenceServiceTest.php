<?php

namespace ArcaSolutions\ImportBundle\Tests\Services\Persist;


use ArcaSolutions\ImportBundle\Entity\ListingImport;
use ArcaSolutions\ImportBundle\Services\ElasticRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class PersistenceServiceTest
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Tests\Services\Persist
 * @since 11.3.00
 */
class PersistenceServiceTest extends KernelTestCase
{

    /**
     * @var ElasticRepository
     */
    private static $elastic;

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public static function setUpBeforeClass()
    {
        self::bootKernel(["environment" => 'test', "debug" => false]);

        $container = static::$kernel->getContainer();
        self::$elastic = $container->get("import.elastic_repository");
        self::$elastic->setBulkSize(1000);
        self::$elastic->setIndexName("import-index-tests");
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldPersistListingWithoutErrors()
    {
        // Given
        $listing1 = new ListingImport();
        $listing1->setListingTitle('Diego');

        $listing2 = new ListingImport();
        $listing2->setListingTitle('Diego');

        $listings = new \ArrayIterator([
            "0" => $listing1,
            "2" => $listing2,
        ]);

        // When
        self::$elastic->setClassType(ListingImport::class);
        self::$elastic->persistDataDoc($listings, true);

        // Then
        $this->assertEquals(2, self::$elastic->getDocCount());
    }

}
