<?php

namespace ArcaSolutions\ImportBundle\Tests\Validator\Constraints;


use ArcaSolutions\ImportBundle\Validator\Constraints as ArcaAssert;
use ArcaSolutions\ImportBundle\Validator\Constraints\ListingLevelValidator;
use ArcaSolutions\ListingBundle\Entity\ListingLevel;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * Class ListingLevelValidatorTest
 * @package ArcaSolutions\ImportBundle\Tests\Validator\Constraints
 * @since 11.3.00
 */
class ListingLevelValidatorTest extends AbstractConstraintValidatorTest
{

    const MOCK_LEVEL_NAMES = ["diamond", "gold", "silver", "bronze"];

    /**
     * @var DoctrineRegistry
     */
    private $mockDoctrineRegistry;

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testIsValid()
    {
        // Given
        $value = "diamond";

        // When
        $this->validator->validate($value, $this->constraint);

        // Then
        $this->assertNoViolation();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testNotValid()
    {
        // Given
        $value = "ivory";

        // When
        $this->validator->validate($value, $this->constraint);

        // Then
        $this->buildViolation("Invalid level name. Levels should match the names defined on the Manage Levels section.")
            ->setCode("L-00200")
            ->assertRaised();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        $mockListingLevels = $this->mockListingLevels();
        $mockListingLevelRepository = $this->mockListingLevelRepository($mockListingLevels);
        $this->mockDoctrineRegistry = $this->mockDoctrineRegistry($mockListingLevelRepository);

        parent::setUp();
        $this->constraint = new ArcaAssert\ListingLevel(["message" => "Invalid level name. Levels should match the names defined on the Manage Levels section."]);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return ListingLevelValidator
     */
    protected function createValidator()
    {
        return new ListingLevelValidator($this->mockDoctrineRegistry);
    }

    /**
     * Mock list of @ListingLevel
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return ListingLevel[]
     */
    private function mockListingLevels()
    {
        $mockListingLevels = [];
        foreach (self::MOCK_LEVEL_NAMES as $levelName) {
            $listingLevel = $this->getMockBuilder("ArcaSolutions\ListingBundle\Entity\ListingLevel")->getMock();
            $listingLevel->method("getName")
                ->will($this->returnValue($levelName));
            $mockListingLevels[] = $listingLevel;
        }

        return $mockListingLevels;
    }

    /**
     * Mock @ListingLevelRepository
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ListingLevel[] $mockListingLevels
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockListingLevelRepository($mockListingLevels)
    {
        /* @var $mockListingLevelRepository @ListingLevelRepository */
        $mockListingLevelRepository = $this->getMockBuilder("ArcaSolutions\ListingBundle\Repository\ListingLevelRepository")
            ->disableOriginalConstructor()
            ->getMock();

        $mockListingLevelRepository->expects($this->any())
            ->method("findAll")
            ->will($this->returnValue($mockListingLevels));

        return $mockListingLevelRepository;
    }

    /**
     * Mock @DoctrineRegistry
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $mockListingLevelRepository
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockDoctrineRegistry($mockListingLevelRepository)
    {
        $mockDoctrineRegistry = $this->getMockBuilder("ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry")
            ->disableOriginalConstructor()
            ->getMock();

        $mockDoctrineRegistry->expects($this->any())
            ->method("getRepository")
            ->with("ListingBundle:ListingLevel")
            ->will($this->returnValue($mockListingLevelRepository));

        return $mockDoctrineRegistry;
    }
}
