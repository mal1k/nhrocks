<?php

namespace ArcaSolutions\ImportBundle\Tests\Services;


use ArcaSolutions\ImportBundle\Constants;
use ArcaSolutions\ImportBundle\Entity\ListingImport;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\ImportBundle\Exception\ImportNotFoundException;
use ArcaSolutions\ImportBundle\Services\ImportService;
use ArcaSolutions\ImportBundle\Services\ListingImportService;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ListingImportServiceTest
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Tests\Services
 * @since 11.3.00
 */
class ListingImportServiceTest extends WebTestCase
{

    /**
     * @var ContainerInterface
     */
    private static $container;

    /**
     * @var ListingImportService
     */
    private $service;

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testWhenFindImport_shouldReturnTrue()
    {
        // Given
        $importId = $this->insertImportAndReturnId();

        // When
        $this->service->setImportId($importId);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testWhenFindImport_shouldThrowImportNotFoundException()
    {
        // Expected
        $this->expectException(ImportNotFoundException::class);

        // Given
        $importId = -1;

        // When
        $this->service->setImportId($importId);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testWhenImportListing_shouldReturnTrue()
    {
        // Defines the work domain
        self::$container->get('multi_domain.information')->setActiveHost('edirectory.arcasolutions.com');

        // Given
        $importId = $this->insertImportAndReturnId();
        $iListing = new ListingImport();
        $iListing->setListingTitle("Lorem ipsum 2");
        $iListing->setAccountUsername("tony@sample.com");
        $iListing->setAccountFirstName("Tony");
        $iListing->setAccountLastName("Carter");
        $iListing->setAccountPassword("t4o3n2y1");
        $iListing->setListingCategory1(implode(" ".Constants::CATEGORY_SEPARATOR." ", ["Father", "Son", "Brasil"]));
        $iListing->setListingCategory2(implode(" ".Constants::CATEGORY_SEPARATOR." ", ["Entertainment", "Concerts"]));
        $iListing->setListingCountry("Brazil");
        $iListing->setListingCountryAbbreviation("br");
        $iListing->setListingState("Sao Paulo");
        $iListing->setListingStateAbbreviation("SP");
        $iListing->setListingCity("Bauru");
        $iListing->setListingCityAbbreviation("BRU");
        $iListing->setListingNeighborhood("Vila Santa Tereza");
        $iListing->setListingLevel("gold");

        // When
        $retVal = $this->service->setImportId($importId)
            ->persistModuleInDatabase($iListing);

        // Then
        $this->assertTrue($retVal);
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     */
    public function testWhenImportListingWithNewListingType_shouldReturnTrue()
    {
        // Defines the work domain
        self::$container->get('multi_domain.information')->setActiveHost('edirectory.arcasolutions.com');

        // Given
        $importId = $this->insertImportAndReturnId();
        $iListing = new ListingImport();
        $iListing->setListingTitle("Lorem ipsum 2");
        $iListing->setAccountUsername("tony@sample.com");
        $iListing->setAccountFirstName("Tony");
        $iListing->setAccountLastName("Carter");
        $iListing->setAccountPassword("t4o3n2y1");
        $iListing->setListingCategory1(implode(" ".Constants::CATEGORY_SEPARATOR." ", ["Father", "Son", "Brasil"]));
        $iListing->setListingCategory2(implode(" ".Constants::CATEGORY_SEPARATOR." ", ["Entertainment", "Concerts"]));
        $iListing->setListingCountry("Brazil");
        $iListing->setListingCountryAbbreviation("br");
        $iListing->setListingState("Sao Paulo");
        $iListing->setListingStateAbbreviation("SP");
        $iListing->setListingCity("Bauru");
        $iListing->setListingCityAbbreviation("BRU");
        $iListing->setListingNeighborhood("Vila Santa Tereza");
        $iListing->setListingLevel("gold");
        $iListing->setListingListingTypeName('ArcaSolutions');

        // When
        $retVal = $this->service->setImportId($importId)
            ->persistModuleInDatabase($iListing);

        // Then
        $this->assertTrue($retVal);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        parent::setUp();

        self::bootKernel(['environment' => 'test', 'debug' => false]);
        self::$container = self::$kernel->getContainer();

        $this->service = self::$container->get("import.listing_import");
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    protected function tearDown()
    {
        parent::tearDown();

        $em = self::$container->get('doctrine')->getManager();

        /* @var ClassMetadata $cmd */
        $cmd = $em->getClassMetadata(ImportLog::class);

        /* @var Connection $conn */
        $conn = $em->getConnection();

        $dbPlatform = $conn->getDatabasePlatform();
        $query = $dbPlatform->getTruncateTableSql($cmd->getTableName());

        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        $conn->executeUpdate($query);
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return int
     */
    private function insertImportAndReturnId()
    {
        /* @var $doctrine DoctrineRegistry */
        $doctrine = self::$container->get("doctrine");
        $em = $doctrine->getManager();

        $file = new \SplFileObject(__DIR__."/../Fixtures/edirectory_sample_medium.csv");

        $p = new ImportLog();
        $p->setFilename($file->getFilename());
        $p->setHasHeader(true);
        $p->setModule(ImportService::MODULE_LISTING);
        $p->setContentType(ImportService::CONTENT_TYPE_CSV);
        $p->setDelimiter(";");
        $p->setLevelForItemsNotSpecified(null);
        $p->setImportedItemsActive(true);
        $p->setNewCategoriesFeatured(false);
        $p->setUpdateExistingData(true);
        $p->setUpdateFriendlyUrl(true);
        $p->setAccountIdForAllItems(0);
        $p->setStatus(ImportLog::STATUS_PENDING);
        $p->setErrorLines(0);
        $p->setTotalLines(10);

        $em->persist($p);
        $em->flush();

        return $p->getId();
    }

}
