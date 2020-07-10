<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @since 11.3.00
 *
 * @Annotation
 */
class ListingTitle extends Constraint
{

    /**
     * @var string
     */
    public $code = "L-00100";

    /**
     * @var string
     */
    public $message;

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function validatedBy()
    {
        return NotNullOrEmptyValidator::class;
    }

}
