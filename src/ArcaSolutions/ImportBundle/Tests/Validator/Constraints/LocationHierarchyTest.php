<?php

namespace ArcaSolutions\ImportBundle\Tests\Validator\Constraints;


use ArcaSolutions\ImportBundle\Entity\ListingImport;
use ArcaSolutions\ImportBundle\Validator\Constraints\LocationHierarchy;
use ArcaSolutions\ImportBundle\Validator\Constraints\LocationHierarchyValidator;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\WebBundle\Entity\SettingLocation;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * Class LocationHierarchyTest
 * @package ArcaSolutions\ImportBundle\Tests\Validator\Constraints
 * @since 11.4.00
 */
class LocationHierarchyTest extends AbstractConstraintValidatorTest
{

    const MOCK_SETTING_LOCATION = [
        [
            "id"      => 1,
            'name'    => 'COUNTRY',
            'enabled' => 'y',
        ],
        [
            "id"      => 2,
            'name'    => 'REGION',
            'enabled' => 'n',
        ],
        [
            "id"      => 3,
            'name'    => 'STATE',
            'enabled' => 'y',
        ],
        [
            "id"      => 4,
            'name'    => 'CITY',
            'enabled' => 'y',
        ],
        [
            "id"      => 5,
            'name'    => 'NEIGHBORHOOD',
            'enabled' => 'n',
        ],
    ];

    /**
     * @var DoctrineRegistry
     */
    private $mockDoctrineRegistry;

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.4.00
     */
    public function testIsValid()
    {
        // Given
        $listingImport = new ListingImport();
        $listingImport->setListingCountry("Brasil");
        $listingImport->setListingState("Acre");
        $listingImport->setListingCity("Rio Branco");

        // When
        $this->validator->validate($listingImport, $this->constraint);

        // Then
        $this->assertNoViolation();
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.4.00
     */
    public function testNotValid()
    {
        // Given
        $listingImport = new ListingImport();
        $listingImport->setListingCountry("Brasil");
        $listingImport->setListingCity("Rio Branco");

        // When
        $this->validator->validate($listingImport, $this->constraint);

        // Then
        $this->buildViolation("Location hierarchy doesn't match the system settings. One or more locations are missing (for instance, city without state).")
            ->setCode("C-00800")
            ->assertRaised();
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.4.00
     */
    protected function setUp()
    {
        $mockSettingLocations = $this->mockSettingLocations();
        $mockSettingLocationRepository = $this->mockSettingLocationRepository($mockSettingLocations);
        $this->mockDoctrineRegistry = $this->mockDoctrineRegistry($mockSettingLocationRepository);

        parent::setUp();
        $this->constraint = new LocationHierarchy([
            "message" => "Location hierarchy doesn't match the system settings. One or more locations are missing (for instance, city without state).",
            "module"  => "listing",
        ]);
    }

    /**
     * Mock list of @SettingLocation
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.4.00
     *
     * @return SettingLocation[]
     */
    private function mockSettingLocations()
    {
        $mockSettingLocations = [];
        foreach (self::MOCK_SETTING_LOCATION as $settingLocation) {
            if ($settingLocation['enabled'] == 'y') {
                $SettingLocation = $this->getMockBuilder(SettingLocation::class)->getMock();
                $SettingLocation->method("getName")
                    ->will($this->returnValue($settingLocation['name']));

                $SettingLocation->method("getId")
                    ->will($this->returnValue($settingLocation['id']));
                $mockSettingLocations[] = $SettingLocation;
            }
        }

        return $mockSettingLocations;
    }

    /**
     * Mock @SettingLocationRepository
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param SettingLocation[] $mockSettingLocation
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockSettingLocationRepository($mockSettingLocation)
    {
        /* @var $mockSettingLocationRepository @SettingLocationRepository */
        $mockSettingLocationRepository = $this->getMockBuilder("ArcaSolutions\WebBundle\Repository\SettingLocationRepository")
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettingLocationRepository->expects($this->any())
            ->method("findBy")
            ->will($this->returnValue($mockSettingLocation));

        return $mockSettingLocationRepository;
    }

    /**
     * Mock @DoctrineRegistry
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $mockSettingLocationRepository
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockDoctrineRegistry($mockSettingLocationRepository)
    {
        $mockDoctrineRegistry = $this->getMockBuilder("ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry")
            ->disableOriginalConstructor()
            ->getMock();

        $mockDoctrineRegistry->expects($this->any())
            ->method("getRepository")
            ->with(SettingLocation::class)
            ->will($this->returnValue($mockSettingLocationRepository));

        return $mockDoctrineRegistry;
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.4.00
     *
     * @return LocationHierarchyValidator
     */
    protected function createValidator()
    {
        return new LocationHierarchyValidator($this->mockDoctrineRegistry);
    }
}
