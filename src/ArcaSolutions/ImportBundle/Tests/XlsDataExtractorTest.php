<?php

namespace ArcaSolutions\ImportBundle\Tests;


use ArcaSolutions\CoreBundle\Collections\XlsAnalyzer;
use ArcaSolutions\ImportBundle\Entity\ListingImport;
use ArcaSolutions\ImportBundle\Services\ElasticRepository;
use ArcaSolutions\ImportBundle\Services\Extractor;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class XlsDataExtractorTest
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Tests
 * @since 11.3.00
 */
class XlsDataExtractorTest extends WebTestCase
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
     * @var Extractor
     */
    private $extractor;

    /**
     * @var ContainerInterface
     */
    private static $container;

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     */
    public static function setUpBeforeClass()
    {
        /* Boot Symfony Kernel */
        self::bootKernel(['enviroment' => 'test', 'debug' => false]);
        self::$container = self::$kernel->getContainer();
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->extractor->reset();
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldListingExtractHeadersXls()
    {
        // Given
        $file = new \SplFileObject(__DIR__."/Fixtures/edirectory_sample_medium.xlsx");
        $analyzer = new XlsAnalyzer($file);
        $analyzer->setHeaderRowNumber(0);

        // When
        $columnHeaders = $analyzer->getColumnHeaders();

        // Then
        $this->assertEquals(self::HEADERS, $columnHeaders);
        $this->assertCount(51, $columnHeaders);
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldListingExtractRowsXls()
    {
        // Given
        $file = new \SplFileObject(__DIR__."/Fixtures/edirectory_sample_medium.xlsx");
        $this->extractor->fromXlsFile($file, true)
            ->setClassType(ListingImport::class)
            ->setMapping(self::MAPPING);

        // When
        $rowsItems = $this->extractor->getExtractItems();
        $errors = $this->extractor->getExtractErrors();

        // Then
        $this->assertEquals(10000, $rowsItems->count());
        $this->assertEquals(176, $errors->count());
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldListingExtractorRowsXlsAndPersistInElastic()
    {
        ///////
        /// Extract rows

        // Given
        $file = new \SplFileObject(__DIR__."/Fixtures/edirectory_sample_medium.xlsx");
        $this->extractor->fromXlsFile($file, true)
            ->setClassType(ListingImport::class)
            ->setMapping(self::MAPPING);

        // When
        $rowsItems = $this->extractor->getExtractItems();
        $errors = $this->extractor->getExtractErrors();

        $this->assertNotNull($rowsItems);
        $this->assertEquals(10000, $rowsItems->count());
        $this->assertEquals(176, $errors->count());

        ///////
        /// Persist in elastic

        // Given
        /* @var $elastic ElasticRepository */
        $elastic = self::$container->get('import.elastic_repository')
            ->setIndexName('import-xls-tests')
            ->setClassType($this->extractor->getClassType());

        // When
        $elastic->persistDataDoc($rowsItems, true);
        $elastic->persistErrorDoc($this->extractor->getExtractErrors());

        $insertedDocCount = $elastic->getDocCount();
        $insertedErrorCount = $elastic->getErrorCount();

        // Then
        $this->assertEquals(10000, $insertedDocCount);
        $this->assertEquals(176, $insertedErrorCount);
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     */
    public function testShouldParseCsvAndFindSomeViolations()
    {
        // Given
        $file = new \SplFileObject(__DIR__."/Fixtures/edirectory_sample_error.xlsx");
        $this->extractor->fromXlsFile($file, true)
            ->setClassType(ListingImport::class)
            ->setMapping(self::MAPPING);

        // When
        $this->extractor->getExtractItems();
        $errors = $this->extractor->getExtractErrors();

        $lineErrors = [];
        foreach ($errors as $error) {
            $line = $error['line'];
            $code = $error['code'];

            if (!isset($lineErrors[$line])) {
                $lineErrors[$line] = [];
            }

            array_push($lineErrors[$line], $code);
        }

        // First line
        $this->assertCount(3, $lineErrors[1]);
        // invalid status
        $this->assertContains("C-00500", $lineErrors[1]);
        // invalid level
        $this->assertContains("L-00200", $lineErrors[1]);
        // without title
        $this->assertContains("L-00100", $lineErrors[1]);

        // Second line
        $this->assertCount(2, $lineErrors[2]);
        // invalid latitude
        $this->assertContains("C-00100", $lineErrors[2]);
        // invalid longitude
        $this->assertContains("C-00200", $lineErrors[2]);

        // Third line
        $this->assertCount(3, $lineErrors[3]);
        // exceeded number maximum of words
        $this->assertContains("C-00300", $lineErrors[3]);
        // there is a keyword longer than 50 chars
        $this->assertContains("C-00301", $lineErrors[3]);
        // wrong format of renewal date
        $this->assertContains("C-00400", $lineErrors[3]);

        // Fourth line
        $this->assertCount(1, $lineErrors[4]);
        // renewal date not in future
        $this->assertContains("C-00401", $lineErrors[4]);

        // Fifth line
        $this->assertCount(1, $lineErrors[5]);
        // More than 5 category levels
        $this->assertContains("C-00700", $lineErrors[5]);
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        parent::setUp();
        $this->extractor = self::$container->get('import.extractor');
    }
}
