<?php

namespace ArcaSolutions\ImportBundle\Tests\Validator\Constraints;

use ArcaSolutions\ImportBundle\Validator\Constraints as ArcaAssert;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * Class LongitudeValidatorTest
 * @package ArcaSolutions\ImportBundle\Tests\Validator\Constraints
 * @since 11.3.00
 */
class LongitudeValidatorTest extends AbstractConstraintValidatorTest
{

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testIsValid()
    {
        // Given
        $longitude = 72.9000;

        // When
        $this->validator->validate($longitude, $this->constraint);

        // Then
        $this->expectNoValidate();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testNotValid()
    {
        // Given
        $longitude = (double)192.022;

        // When
        $this->validator->validate($longitude, $this->constraint);

        // Then
        $this->buildViolation("Longitude should be a number between -180 and 180.")
            ->setCode("C-00101")
            ->assertRaised();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        parent::setUp();
        $this->constraint = new ArcaAssert\Longitude(["message" => "Longitude should be a number between -180 and 180."]);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return ArcaAssert\NumberRangeValidator
     */
    protected function createValidator()
    {
        return new ArcaAssert\NumberRangeValidator();
    }

}
