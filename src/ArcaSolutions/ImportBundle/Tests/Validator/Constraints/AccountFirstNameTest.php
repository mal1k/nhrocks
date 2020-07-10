<?php

namespace ArcaSolutions\ImportBundle\Tests\Validator\Constraints;

use ArcaSolutions\ImportBundle\Entity\ListingImport;
use ArcaSolutions\ImportBundle\Validator\Constraints\AccountFirstName;
use ArcaSolutions\ImportBundle\Validator\Constraints\AccountUsernameValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * Class AccountFirstNameTest
 * @package ArcaSolutions\ImportBundle\Tests\Validator\Constraints
 *
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @since 11.3.00
 */
class AccountFirstNameTest extends AbstractConstraintValidatorTest
{

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    public function testIsValid()
    {
        // Given
        $listingImport = new ListingImport();
        $listingImport->setAccountUsername("username@email.com");
        $listingImport->setAccountFirstName("FirstName");

        // When
        $this->validator->validate($listingImport, $this->constraint);

        // Then
        $this->assertNoViolation();
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    public function testNoValid()
    {
        // Given
        $listingImport = new ListingImport();
        $listingImport->setAccountUsername("username@email.com");
        $listingImport->setAccountFirstName("");

        // When
        $this->validator->validate($listingImport, $this->constraint);

        // Then
        $this->buildViolation("Account First Name cannot be empty if an Account Username is provided.")
            ->setCode("C-00700")
            ->assertRaised();
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        parent::setUp();
        $this->constraint = new AccountFirstName(["message" => "Account First Name cannot be empty if an Account Username is provided."]);
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return AccountUsernameValidator
     */
    protected function createValidator()
    {
        return new AccountUsernameValidator();
    }
}
