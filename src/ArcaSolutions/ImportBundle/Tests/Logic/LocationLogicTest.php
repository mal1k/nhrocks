<?php

namespace ArcaSolutions\ImportBundle\Tests\Logic;


use ArcaSolutions\CoreBundle\Entity\Location1;
use ArcaSolutions\CoreBundle\Entity\Location2;
use ArcaSolutions\CoreBundle\Entity\Location3;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\ImportBundle\Exception\InvalidLocationNameException;
use ArcaSolutions\ImportBundle\Logic\LocationLogic;
use ArcaSolutions\ImportBundle\Services\ImportService;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class LocationLogicTest
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Tests\Logic
 * @since 11.3.00
 */
class LocationLogicTest extends WebTestCase
{

    /**
     * @var DoctrineRegistry
     */
    private static $doctrine;

    /**
     * @var LocationLogic
     */
    private $locationLogic;

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::bootKernel();
        $container = self::$kernel->getContainer();
        self::$doctrine = $container->get("doctrine");
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldReturnExitingCountry_whenPassingExistingCountryNameOrAbbreviation()
    {
        // Given
        $country = "Brasil";

        // When
        $location = $this->locationLogic->findOrCreateLocation($country, null, Location1::class);

        // Then
        $this->assertInstanceOf(Location1::class, $location);
        $this->assertEquals("Brasil", $location->getName());
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldThrowInvalidLocationName_whenPassingExistingCountryNameOrAbbreviationButDefault()
    {
        // Expected
        $this->expectException(InvalidLocationNameException::class);

        // Given
        $country = "united states";

        // When
        $this->locationLogic->findOrCreateLocation($country, null, Location1::class);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldReturnNull_whenPassingLocationToNotEnabledLocation()
    {
        // Given
        $region = "Pacific";

        // When
        $location = $this->locationLogic->findOrCreateLocation($region, null, Location2::class);

        // Then
        $this->assertNull($location);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldCreateLocation_whenPassingNotExistingLocation()
    {
        // Given
        $state = "Saint Paul";
        $abbr = "SA";

        // When
        $location = $this->locationLogic->findOrCreateLocation($state, $abbr, Location3::class);

        // Then
        $this->assertInstanceOf(Location3::class, $location);
        $this->assertEquals($state, $location->getName());
        $this->assertEquals($abbr, $location->getAbbreviation());
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldThrowInvalidLocationName_whenPassingNotExistingLocationAndEmptyName()
    {
        // Expected
        $this->expectException(InvalidLocationNameException::class);

        // Given
        $state = "";
        $abbr = "SB";

        // When
        $this->locationLogic->findOrCreateLocation($state, $abbr, Location3::class);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        parent::setUp();

        /* Create a new importLog */
        $importLog = new ImportLog();
        $importLog
            ->setFilename('locationsTest.csv')
            ->setStatus(ImportLog::STATUS_PENDING)
            ->setHasHeader(true)
            ->setContentType(ImportService::CONTENT_TYPE_CSV)
            ->setErrorLines(0)
            ->setTotalLines(1)
            ->setModule('listing');

        /* Persist in database */
        $em = self::$doctrine->getManager();
        $em->persist($importLog);
        $em->flush();

        /* Create new instance of LocationLogic */
        $this->locationLogic = new LocationLogic(self::$kernel->getContainer(), $importLog);
    }

    protected function tearDown()
    {
        parent::tearDown();

        /* @var ClassMetadata $cmd */
        $em = self::$doctrine->getManager();
        $cmd = $em->getClassMetadata(ImportLog::class);

        /* @var Connection $conn */
        $conn = self::$doctrine->getConnection();

        $dbPlatform = $conn->getDatabasePlatform();
        $query = $dbPlatform->getTruncateTableSql($cmd->getTableName());

        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        $conn->executeUpdate($query);
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
    }

}
