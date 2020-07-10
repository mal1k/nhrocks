<?php

namespace ArcaSolutions\ImportBundle\Tests\Validator\Constraints;

use ArcaSolutions\ImportBundle\Validator\Constraints\ListingTitle;
use ArcaSolutions\ImportBundle\Validator\Constraints\NotNullOrEmptyValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * Class TitleValidatorTest
 * @package ArcaSolutions\ImportBundle\Tests\Validator\Constraints
 * @since 11.3.00
 */
class TitleValidatorTest extends AbstractConstraintValidatorTest
{

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testIsValid()
    {
        // Given
        $value = "Some text to represent title";

        // When
        $this->validator->validate($value, $this->constraint);

        // Then
        $this->assertNoViolation();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testNoValid()
    {
        // Given
        $value = "  ";

        // When
        $this->validator->validate($value, $this->constraint);

        // Then
        $this->buildViolation("Title cannot be empty.")
            ->setCode("L-00100")
            ->assertRaised();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        parent::setUp();
        $this->constraint = new ListingTitle(["message" => "Title cannot be empty."]);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return NotNullOrEmptyValidator
     */
    protected function createValidator()
    {
        return new NotNullOrEmptyValidator();
    }
}
