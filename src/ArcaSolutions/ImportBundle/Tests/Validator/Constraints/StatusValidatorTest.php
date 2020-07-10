<?php

namespace ArcaSolutions\ImportBundle\Tests\Validator\Constraints;


use ArcaSolutions\ImportBundle\Validator\Constraints as ArcaAssert;
use ArcaSolutions\ImportBundle\Validator\Constraints\StatusValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * Class StatusValidatorTest
 * @package ArcaSolutions\ImportBundle\Tests\Validator\Constraints
 * @since 11.3.00
 */
class StatusValidatorTest extends AbstractConstraintValidatorTest
{

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testIsValid()
    {
        // Given
        $value = "active";

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
        $value = "ativo";

        // When
        $this->validator->validate($value, $this->constraint);

        // Then
        $this->buildViolation("No value was identified for the field status.")
            ->setCode("C-00300")
            ->assertRaised();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        parent::setUp();
        $this->constraint = new ArcaAssert\Status(["message" => "No value was identified for the field status."]);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return StatusValidator
     */
    protected function createValidator()
    {
        return new StatusValidator();
    }
}
