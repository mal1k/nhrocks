<?php

namespace ArcaSolutions\ImportBundle\Tests\Validator\Constraints;


use ArcaSolutions\ImportBundle\Validator\Constraints as ArcaAssert;
use ArcaSolutions\ImportBundle\Validator\Constraints\EventLevelValidator;
use ArcaSolutions\EventBundle\Entity\EventLevel;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * Class EventLevelValidatorTest
 * @package ArcaSolutions\ImportBundle\Tests\Validator\Constraints
 * @since 11.3.00
 */
class EventLevelValidatorTest extends AbstractConstraintValidatorTest
{

    const MOCK_LEVEL_NAMES = ["diamond", "gold", "silver"];

    /**
     * @var DoctrineRegistry
     */
    private $mockDoctrineRegistry;

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
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
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
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
            ->setCode("E-00200")
            ->assertRaised();
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        $mockEventLevels = $this->mockEventLevels();
        $mockEventLevelRepository = $this->mockEventLevelRepository($mockEventLevels);
        $this->mockDoctrineRegistry = $this->mockDoctrineRegistry($mockEventLevelRepository);

        parent::setUp();
        $this->constraint = new ArcaAssert\EventLevel(["message" => "Invalid level name. Levels should match the names defined on the Manage Levels section."]);
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return EventLevelValidator
     */
    protected function createValidator()
    {
        return new EventLevelValidator($this->mockDoctrineRegistry);
    }

    /**
     * Mock list of @EventLevel
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return EventLevel[]
     */
    private function mockEventLevels()
    {
        $mockEventLevels = [];
        foreach (self::MOCK_LEVEL_NAMES as $levelName) {
            $eventLevel = $this->getMockBuilder("ArcaSolutions\EventBundle\Entity\EventLevel")->getMock();
            $eventLevel->method("getName")
                ->will($this->returnValue($levelName));
            $mockEventLevels[] = $eventLevel;
        }

        return $mockEventLevels;
    }

    /**
     * Mock @EventLevelRepository
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param EventLevel[] $mockEventLevels
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockEventLevelRepository($mockEventLevels)
    {
        /* @var $mockEventLevelRepository @EventLevelRepository */
        $mockEventLevelRepository = $this->getMockBuilder("ArcaSolutions\EventBundle\Repository\EventLevelRepository")
            ->disableOriginalConstructor()
            ->getMock();

        $mockEventLevelRepository->expects($this->any())
            ->method("findAll")
            ->will($this->returnValue($mockEventLevels));

        return $mockEventLevelRepository;
    }

    /**
     * Mock @DoctrineRegistry
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $mockEventLevelRepository
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockDoctrineRegistry($mockEventLevelRepository)
    {
        $mockDoctrineRegistry = $this->getMockBuilder("ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry")
            ->disableOriginalConstructor()
            ->getMock();

        $mockDoctrineRegistry->expects($this->any())
            ->method("getRepository")
            ->with("EventBundle:EventLevel")
            ->will($this->returnValue($mockEventLevelRepository));

        return $mockDoctrineRegistry;
    }
}
