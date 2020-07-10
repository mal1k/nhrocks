<?php

namespace ArcaSolutions\ImportBundle\Tests\Validator\Constraints;


use ArcaSolutions\CoreBundle\Services\Settings;
use ArcaSolutions\ImportBundle\Validator\Constraints\TimeFormat;
use ArcaSolutions\ImportBundle\Validator\Constraints\TimeFormatValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * Class DateFormatValidatorTest
 * @package ArcaSolutions\ImportBundle\Tests\Validator\Constraints
 * @since 11.3.00
 */
class TimeFormatValidatorTest extends AbstractConstraintValidatorTest
{

    /**
     * @var Settings
     */
    private $mockSettings;

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    public function testIsValidForStupidTimeFormat()
    {
        // Given
        $values = ["12:00 PM", "8:00 AM", "2:00pM", "", null];
        $errors = [];
        $this->validator->timeFormat = 12;

        // When
        foreach ($values as $value){
            if(!$this->validator->validate($value, $this->constraint)) {
                $errors[] = $value;
            }
        }

        $this->assertCount(0, $errors);
    }


    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    public function testIsValidForRightTime()
    {
        // Given
        $values = ["12:30 p m", " 8 : 10 ", " 23:59 ", "12:11:01", "", null, implode(":", [8, 10])];
        $errors = 0;
        $this->validator->timeFormat = 24;

        // When
        foreach ($values as $value){
            if(!$this->validator->validate($value, $this->constraint)) {
                $errors++;
            }
        }

        $this->assertCount($errors, []);
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    public function testTimeNotValidForStupidTimeFormat()
    {
        // Given
        $values = ["19-01-2030", "asdasd", "13:00 AM", "12:60 PM", "10:11:34", "03:30 p m"];
        $errors = 0;
        $this->validator->timeFormat = 12;

        // When
        foreach ($values as $value){
            if(!$this->validator->validate($value, $this->constraint)) {
                $errors++;
            }
        }

        $this->assertCount($errors, $values);
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    public function testTimeNotValidForRightTimeFormat()
    {
        // Given
        $values = ["19-01-2030", "asdasd", "25:00", "24:60 PM", "24:71"];
        $errors = 0;
        $this->validator->timeFormat = 24;

        // When
        foreach ($values as $value){
            if(!$this->validator->validate($value, $this->constraint)) {
                $errors++;
            }
        }

        $this->assertCount($errors, $values);
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        $this->mockSettings = $this->mockSettings();
        parent::setUp();
        $this->constraint = new TimeFormat(["message" => "Time format must be the same as your domain configuration."]);
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
            ->willReturn(24);

        return $mockSettings;
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return TimeFormatValidator
     */
    protected function createValidator()
    {
        return new TimeFormatValidator($this->mockSettings);
    }

}
