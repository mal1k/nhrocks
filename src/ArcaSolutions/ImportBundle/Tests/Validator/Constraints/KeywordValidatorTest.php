<?php

namespace ArcaSolutions\ImportBundle\Tests\Validator\Constraints;


use ArcaSolutions\ImportBundle\Validator\Constraints\Keyword;
use ArcaSolutions\ImportBundle\Validator\Constraints\KeywordValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * Class KeywordValidatorTest
 * @package ArcaSolutions\ImportBundle\Tests\Validator\Constraints
 * @since 11.3.00
 */
class KeywordValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testIsValid()
    {
        // Given
        $keywords = "Word1 || Nor Words || Three of words || the pretty large word with 49 characters complete ";

        // When
        $this->validator->validate($keywords, $this->constraint);

        // Then
        $this->assertNoViolation();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testNoValidWordsCount()
    {
        // Given
        $keywords = "word1 | word2 | word3 | word4 | word5 | word6 | word7 | word8 | word9 | word10 | word11";

        // When
        $this->validator->validate($keywords, $this->constraint);

        // Then
        $this->buildViolation("Maximum of {{ maxNumberOfWords }} keywords allowed. Keywords should be separated by {{ separator }} and can have up to {{ maxNumberOfCharsPerWord }} characters.")
            ->setParameter("{{ maxNumberOfWords }}", $this->constraint->maxNumberOfWords)
            ->setParameter("{{ separator }}", $this->constraint->separator)
            ->setParameter("{{ maxNumberOfCharsPerWord }}", $this->constraint->maxNumberOfCharsPerWord)
            ->setCode("C-00200")
            ->assertRaised();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    public function testNoValidWordLength()
    {
        // Given
        $keywords = " word1 | to large word length, no expected more then 50 characters ";

        // When
        $this->validator->validate($keywords, $this->constraint);

        // Then
        $this->buildViolation("Maximum of {{ maxNumberOfWords }} keywords allowed. Keywords should be separated by {{ separator }} and can have up to {{ maxNumberOfCharsPerWord }} characters.")
            ->setParameter("{{ maxNumberOfWords }}", $this->constraint->maxNumberOfWords)
            ->setParameter("{{ separator }}", $this->constraint->separator)
            ->setParameter("{{ maxNumberOfCharsPerWord }}", $this->constraint->maxNumberOfCharsPerWord)
            ->setCode("C-00200")
            ->assertRaised();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    protected function setUp()
    {
        parent::setUp();
        $this->constraint = new Keyword(["message" => "Maximum of {{ maxNumberOfWords }} keywords allowed. Keywords should be separated by {{ separator }} and can have up to {{ maxNumberOfCharsPerWord }} characters."]);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return KeywordValidator
     */
    protected function createValidator()
    {
        return new KeywordValidator();
    }
}
