<?php

namespace ArcaSolutions\ImportBundle\Tests\Validator\Constraints;

use ArcaSolutions\ImportBundle\Validator\Constraints as ArcaAssert;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * Class LatitudeValidatorTest
 * @package ArcaSolutions\ImportBundle\Tests\Validator\Constraints
 * @since 11.3.00
 */
class LatitudeValidatorTest extends AbstractConstraintValidatorTest
{

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testIsValid()
    {
        // Given
        $latitude = 72.9000;

        // When
        $this->validator->validate($latitude, $this->constraint);

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
        $latitude = (double)92.022;

        // When
        $this->validator->validate($latitude, $this->constraint);

        // Then
        $this->buildViolation("Latitude should be a number between -90 and 90.")
            ->setCode("C-00100")
            ->assertRaised();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        parent::setUp();
        $this->constraint = new ArcaAssert\Latitude(["message" => "Latitude should be a number between -90 and 90."]);
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
