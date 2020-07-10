<?php

namespace ArcaSolutions\ImportBundle\Tests\Services;


use ArcaSolutions\ImportBundle\Entity\ListingImport;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\ImportBundle\Exception\InvalidContentTypeException;
use ArcaSolutions\ImportBundle\Exception\InvalidModuleException;
use ArcaSolutions\ImportBundle\Services\ImportService;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\MultiDomainBundle\Services\Settings;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class ImportServiceTest
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Tests\Services
 * @since 11.3.00
 */
class ImportServiceTest extends WebTestCase
{

    //@formatter:off
    const HEADERS = [
        "Listing Title", "Listing SEO Title", "Listing Email", "Listing URL",
        "Listing Address", "Listing Address2", "Listing Country", "Listing Country Abbreviation", "Listing Region",
        "Listing Region Abbreviation", "Listing State", "Listing State Abbreviation", "Listing City",
        "Listing City Abbreviation", "Listing Neighborhood", "Listing Neighborhood Abbreviation",
        "Listing Postal Code", "Listing Latitude", "Listing Longitude", "Listing Phone",
        "Listing Short Description", "Listing Long Description", "Listing SEO Description", "Listing Keywords",
        "Listing Renewal Date", "Listing Status", "Listing Level", "Listing Category 1", "Listing Category 2",
        "Listing Category 3", "Listing Category 4", "Listing Category 5", "Listing Template", "Listing DB Id",
        "Custom ID", "Account Username", "Account Password", "Account Contact First Name", "Account Contact Last Name",
        "Account Contact Company", "Account Contact Address", "Account Contact Address2", "Account Contact Country",
        "Account Contact State", "Account Contact City", "Account Contact Postal Code", "Account Contact Phone",
        "Account Contact Email", "Account Contact URL",
    ];

    const ALIAS = ["listingTitle" => "Listing Title", "listingSeoTitle" => "Listing SEO Title", "listingEmail" => "Listing Email",
        "listingUrl" => "Listing Url", "listingAddress" => "Listing Address", "listingAddress2" => "Listing Address2",
        "listingCountry" => "Listing Country", "listingCountryAbbreviation" => "Listing Country Abbreviation", "listingRegion" => "Listing Region",
        "listingRegionAbbreviation" => "Listing Region Abbreviation", "listingState" => "Listing State",
        "listingStateAbbreviation" => "Listing State Abbreviation", "listingCity" => "Listing City",
        "listingCityAbbreviation" => "Listing City Abbreviation", "listingNeighborhood" => "Listing Neighborhood",
        "listingNeighborhoodAbbreviation" => "Listing Neighborhood Abbreviation", "listingZipCode" => "Listing Postal Code",
        "listingLatitude" => "Listing Latitude", "listingLongitude" => "Listing Longitude", "listingPhone" => "Listing Phone",
        "listingShortDescription" => "Listing Short Description",
        "listingLongDescription" => "Listing Long Description", "listingSeoDescription" => "Listing Seo Description",
        "listingKeywords" => "Listing Keywords", "listingRenewalDate" => "Listing Renewal Date", "listingStatus" => "Listing Status",
        "listingLevel" => "Listing Level", "listingCategory1" => "Listing Category 1", "listingCategory2" => "Listing Category 2",
        "listingCategory3" => "Listing Category 3", "listingCategory4" => "Listing Category 4", "listingCategory5" => "Listing Category 5",
        "listingListingTypeName" => "Listing Template", "listingId" => "Listing DB Id", "listingThirdPartyId" => "Listing Custom Id",
        "accountUsername" => "Account Username", "accountPassword" => "Account Password",
        "accountFirstName" => "Account Contact First Name", "accountLastName" => "Account Contact Last Name",
        "accountCompany" => "Account Contact Company", "accountAddress" => "Account Contact Address",
        "accountAddress2" => "Account Contact Address2", "accountCountry" => "Account Contact Country",
        "accountState" => "Account Contact State", "accountCity" => "Account Contact City",
        "accountZipCode" => "Account Contact Postal Code", "accountPhone" => "Account Contact Phone",
        "accountEmail" => "Account Contact Email",
        "accountUrl" => "Account Contact URL"];

    const MAPPING = [
        0  => "listingTitle",     1  => "listingSeoTitle",           2  => "listingEmail",           3  => "listingUrl",
        4  => "listingAddress",   5  => "listingAddress2",           6  => "listingCountry",         7  => "listingCountryAbbreviation",
        8  => "listingRegion",    9  => "listingRegionAbbreviation", 10 => "listingState",           11 => "listingStateAbbreviation",
        12 => "listingCity",      13 => "listingCityAbbreviation",   14 => "listingNeighborhood",   15 => "listingNeighborhoodAbbreviation",
        16 => "listingZipCode",   17 => "listingLatitude",           18 => "listingLongitude",       19 => "listingPhone",
        21 => "listingShortDescription",   22 => "listingLongDescription", 23 => "listingSeoDescription",
        24 => "listingKeywords",  25 => "listingRenewalDate",        26 => "listingStatus",          27 => "listingLevel",
        28 => "listingCategory1", 29 => "listingCategory2",          30 => "listingCategory3",       31 => "listingCategory4",
        32 => "listingCategory5", 33 => "listingListingTypeName",    34 => "listingId",              35 => "listingThirdPartyId",
        36 => "accountUsername",  37 => "accountPassword",           38 => "accountFirstName",       39 => "accountLastName",
        40 => "accountCompany",   41 => "accountAddress",            42 => "accountAddress2",        43 => "accountCountry",
        44 => "accountState",     45 => "accountCity",               46 => "accountZipCode",         47 => "accountPhone",
        49 => "accountEmail",     50 => "accountUrl",
    ];
    //@formatter:on

    /**
     * @var ImportService
     */
    private $worker;

    /**
     * @var ContainerInterface
     */
    private static $container;

    /**
     * @var DoctrineRegistry
     */
    private $doctrine;

    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var integer
     */
    private $existingParamterId;

    public static function setUpBeforeClass()
    {
        self::bootKernel(['environment' => 'test', 'debug' => false]);
        self::$container = self::$kernel->getContainer();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldSavedImportImport()
    {
        // Given
        $id = $this->existingParamterId;
        $useLevelForItemsNotSpecified = "diamond";
        $setImportedItemsAsActive = true;
        $setNewCategoriesAsFeatured = true;
        $updateExistingData = true;
        $updateFriendlyUrl = true;
        $useAccountIdInAllItems = 0;

        // When
        $response = $this->worker->setImport($id, $useLevelForItemsNotSpecified, $setImportedItemsAsActive,
            $setNewCategoriesAsFeatured, $updateExistingData, $updateFriendlyUrl, $useAccountIdInAllItems);

        // Then
        $this->assertTrue($response);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldParseFile()
    {
        // Given
        $id = $this->existingParamterId;
        $mapping = self::MAPPING;

        // When
        $response = $this->worker->parseFileWithMapping($id, $mapping);

        // Then
        $this->assertNotNull($response);
        $this->assertCount(88, $response["errors"]);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldCreateImport()
    {
        // Given
        $file = new \SplFileObject(__DIR__."/../Fixtures/edirectory_sample_medium.csv");
        $contentType = ImportService::CONTENT_TYPE_CSV;
        $module = ImportService::MODULE_LISTING;
        $status = ImportLog::STATUS_PENDING;
        $errorLines = 0;
        $totalLines = 10090;
        $hasHeader = true;
        $delimiter = ";";

        // When
        $id = $this->worker->create($file, $contentType, $module, $status, $errorLines, $totalLines, $hasHeader,
            $delimiter);

        // Then
        $this->assertNotNull($id);
        $this->assertGreaterThan(0, $id);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldResultColumnHeaders()
    {
        // Given
        $id = $this->existingParamterId;

        // When
        $columnHeaders = $this->worker->getColumnHeaders($id);

        // Then
        $this->assertEquals(self::HEADERS, $columnHeaders);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldFetch10Docs()
    {
        // Given
        $id = 1;
        $elastic = $this->worker->getElastic($id);

        // When
        $docs = $elastic->fetch10Documents();

        // Then
        $this->assertCount(10, $docs);

    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldDeleteDocument()
    {
        // Given
        $elastic = $this->worker->getElastic(1);

        /* @var ListingImport[] $docs */
        $docs = $elastic->fetch10Documents();

        // WHen
        $success = $elastic->deleteDocument($docs[0]->getId());

        // Then
        $this->assertTrue($success);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldReturnPropertiesAlias()
    {
        // Given
        $id = $this->existingParamterId;

        // When
        $propertiesAlias = $this->worker->getPropertiesAlias($id);

        // Then
        $this->assertEquals(self::ALIAS, $propertiesAlias);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldFailWithInvalidModuleException()
    {
        // Expected
        $this->expectException(InvalidModuleException::class);

        // Given
        $file = new \SplFileObject(__DIR__."/../Fixtures/edirectory_sample_error.csv");
        $contentType = ImportService::CONTENT_TYPE_CSV;
        $module = 'Article';
        $status = ImportLog::STATUS_PENDING;

        // When
        $this->worker->create($file, $contentType, $module, $status, 0, 0);

    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldFailWithInvalidContentTypeException()
    {
        // Expected
        $this->expectException(InvalidContentTypeException::class);

        // Given
        $file = new \SplFileObject(__DIR__."/../Fixtures/edirectory_sample_error.csv");
        $contentType = "application/json";
        $module = ImportService::MODULE_LISTING;
        $status = ImportLog::STATUS_PENDING;

        // When
        $this->worker->create($file, $contentType, $module, $status, 0, 0);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        parent::setUp();
        $this->doctrine = self::$container->get("doctrine");
        $this->entityManager = $this->doctrine->getManager();
        $this->worker = self::$container->get("import.worker")
            ->setFilePath(__DIR__."/../Fixtures/");
        $this->worker->setSettings($this->mockSettings());

        $f = new \SplFileObject(__DIR__."/../Fixtures/edirectory_sample_medium.csv");
        $p = new ImportLog();
        $p->setFilename($f->getFilename());
        $p->setHasHeader(true);
        $p->setModule(ImportService::MODULE_LISTING);
        $p->setContentType(ImportService::CONTENT_TYPE_CSV);
        $p->setDelimiter(";");
        $p->setTotalLines(10090);
        $p->setErrorLines(0);
        $p->setStatus(ImportLog::STATUS_PENDING);

        $this->entityManager->persist($p);
        $this->entityManager->flush();
        $this->existingParamterId = $p->getId();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    protected function tearDown()
    {
        parent::tearDown();

        /* @var ClassMetadata $cmd */
        $cmd = $this->entityManager->getClassMetadata(ImportLog::class);

        /* @var Connection $conn */
        $conn = $this->doctrine->getConnection();

        $dbPlatform = $conn->getDatabasePlatform();
        $query = $dbPlatform->getTruncateTableSql($cmd->getTableName());

        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        $conn->executeUpdate($query);
        $conn->query("SET FOREIGN_KEY_CHECKS=1");

    }

    /**
     * Mock Settings Elastic index
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return Settings|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockSettings()
    {
        $settings = $this->getMockBuilder("ArcaSolutions\MultiDomainBundle\Services\Settings")
            ->disableOriginalConstructor()
            ->getMock();
        $settings->expects($this->any())
            ->method("getElastic")
            ->will($this->returnValue("index_name"));

        return $settings;
    }

}
