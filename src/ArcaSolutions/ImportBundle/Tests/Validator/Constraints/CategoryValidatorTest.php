<?php

namespace ArcaSolutions\ImportBundle\Tests\Validator\Constraints;

use ArcaSolutions\ImportBundle\Validator\Constraints as ArcaAssert;
use ArcaSolutions\ImportBundle\Validator\Constraints\CategoryValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * Class CategoryValidatorTest
 * @package ArcaSolutions\ImportBundle\Tests\Validator\Constraints
 * @since 11.3.00
 */
class CategoryValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testIsValid()
    {
        // Given
        $value = "Food > Bares and Restaurants > Pizza";

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
        $value = "Food -> Bares and Restaurants -> Vegetarian -> Vegan -> Pizza -> Sweet";

        // When
        $this->validator->validate($value, $this->constraint);

        // Then
        $this->buildViolation("Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
            ->setParameter("{{ maxNumberOfLevels }}", 5)
            ->setParameter("{{ separator }}", "->")
            ->setCode("C-00400")
            ->assertRaised();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        parent::setUp();
        $this->constraint = new ArcaAssert\Category(["message" => "Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded."]);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return CategoryValidator
     */
    protected function createValidator()
    {
        return new CategoryValidator();
    }
}
