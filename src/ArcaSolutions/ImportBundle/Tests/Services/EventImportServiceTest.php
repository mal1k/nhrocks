<?php

namespace ArcaSolutions\ImportBundle\Services;


use ArcaSolutions\ImportBundle\Constants;
use ArcaSolutions\ImportBundle\Entity\EventImport;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\ImportBundle\Exception\ImportNotFoundException;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EventImportServiceTest
 *
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Services
 * @since 11.3.00
 */
class EventImportServiceTest extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    private static $container;

    /**
     * @var EventImportService
     */
    private $service;

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    public static function setUpBeforeClass()
    {
        /* Boot Symfony Kernel */
        self::bootKernel(['environment' => 'test', 'debug' => false]);
        self::$container = self::$kernel->getContainer();
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    public function testWhenFindParameters_shouldReturnTrue()
    {
        // Given
        $parametersId = $this->insertParametersAndReturnId();

        // When
        $this->service->setImportId($parametersId);
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return int
     */
    private function insertParametersAndReturnId()
    {
        /* @var $doctrine DoctrineRegistry */
        $doctrine = self::$container->get("doctrine");
        $em = $doctrine->getManager();

        $file = new \SplFileObject(__DIR__."/../Fixtures/edirectory_sample_medium.csv");

        $p = new ImportLog();
        $p->setFilename($file->getFilename());
        $p->setHasHeader(true);
        $p->setModule(ImportService::MODULE_EVENT);
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

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    public function testWhenFindParameters_shouldThrowImportNotFoundException()
    {
        // Expected
        $this->expectException(ImportNotFoundException::class);

        // Given
        $parametersId = -1;

        // When
        $this->service->setImportId($parametersId);
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    public function testWhenImportEvent_shouldReturnTrue()
    {
        // Defines the work domain
        self::$container->get('multi_domain.information')->setActiveHost('edirectory.arcasolutions.com');

        // Given
        $parametersId = $this->insertParametersAndReturnId();
        $importEvent = new EventImport();
        $importEvent->setEventTitle("Lorem ipsum 2");

        $importEvent->setEventKeywords("keyword");
        $importEvent->setEventSeoTitle("keyword");
        $importEvent->setEventSeoDescription("keyword");
        $importEvent->setEventLongDescription("keyword");
        $importEvent->setEventShortDescription("keyword");

        $importEvent->setEventStartDate("01/21/2018");
        $importEvent->setEventRenewalDate("01/21/1991");

        $importEvent->setEventCategory1(implode(" ".Constants::CATEGORY_SEPARATOR." ", ["Father", "Son", "Brasil"]));
        $importEvent->setEventCategory2(implode(" ".Constants::CATEGORY_SEPARATOR." ", ["Entertainment", "Concerts"]));

        $importEvent->setEventAddress("5th Street");
        $importEvent->setEventCountry("Brazil");
        $importEvent->setEventCountryAbbreviation("br");
        $importEvent->setEventState("Sao Paulo");
        $importEvent->setEventStateAbbreviation("SP");
        $importEvent->setEventCity("Bauru");
        $importEvent->setEventCityAbbreviation("BRU");
        $importEvent->setEventNeighborhood("Vila Santa Tereza");
        $importEvent->setEventLocation('Arca Solutions');
        $importEvent->setEventZipCode("17054110");

        $importEvent->setEventLevel("gold");
        $importEvent->setEventUrl('event.com.br');
        $importEvent->setEventContactName('Arca');
        $importEvent->setEventPhone('5599999999');
        $importEvent->setEventEmail('event@event.com');

        $importEvent->setAccountUsername("tony@sample.com");
        $importEvent->setAccountFirstName("Tony");
        $importEvent->setAccountLastName("Carter");
        $importEvent->setAccountPassword("t4o3n2y1");

        // When
        $retVal = $this->service->setImportId($parametersId)
            ->persistModuleInDatabase($importEvent);

        // Then
        $this->assertTrue($retVal);
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        parent::setUp();
        $this->service = self::$container->get("import.event_import");
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
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

}
