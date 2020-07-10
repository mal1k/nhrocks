<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @since 11.3.00
 *
 * @Annotation
 */
class Keyword extends Constraint
{

    /**
     * @var int
     */
    public $maxNumberOfWords = 10;

    /**
     * @var int
     */
    public $maxNumberOfCharsPerWord = 50;

    /**
     * @var string
     */
    public $separator = "||";

    public $code = "C-00200";
    public $message;

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function validatedBy()
    {
        return KeywordValidator::class;
    }

}
