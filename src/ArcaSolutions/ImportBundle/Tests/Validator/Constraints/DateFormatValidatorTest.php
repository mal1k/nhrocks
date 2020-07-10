<?php

namespace ArcaSolutions\ImportBundle\Tests\Validator\Constraints;


use ArcaSolutions\CoreBundle\Services\Settings;
use ArcaSolutions\ImportBundle\Validator\Constraints\DateFormat;
use ArcaSolutions\ImportBundle\Validator\Constraints\DateFormatValidator;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * Class DateFormatValidatorTest
 * @package ArcaSolutions\ImportBundle\Tests\Validator\Constraints
 * @since 11.3.00
 */
class DateFormatValidatorTest extends AbstractConstraintValidatorTest
{

    /**
     * @var Settings
     */
    private $mockSettings;
    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    public function testIsValid()
    {
        // Given
        $value = "08/12/2030";

        // When
        $this->validator->validate($value, $this->constraint);

        // Then
        $this->assertNoViolation();
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    public function testNoValidDateFormat()
    {
        // Given
        $value = "19-01-2030";

        // When
        $this->validator->validate($value, $this->constraint);

        // Then
        $this->buildViolation("Date format must be the same as your domain configuration.")
            ->setCode("C-00500")
            ->assertRaised();
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        $this->mockSettings = $this->mockSettings();
        parent::setUp();
        $this->constraint = new DateFormat(["message" => "Date format must be the same as your domain configuration."]);
    }

    /**
     * Mock @Settings
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockSettings()
    {
        $mockSettings = $this->getMockBuilder("ArcaSolutions\CoreBundle\Services\Settings")
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->expects($this->any())
            ->method("getDomainSetting")
            ->willReturn('m/d/Y');

        return $mockSettings;
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return DateFormatValidator
     */
    protected function createValidator()
    {
        return new DateFormatValidator($this->mockSettings);
    }

}
