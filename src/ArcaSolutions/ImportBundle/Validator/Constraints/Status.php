<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @since 11.3.00
 *
 * @Annotation
 */
class Status extends Constraint
{

    const VALID_STATUSES = ["active", "pending", "expired", "suspended"];

    public $code = "C-00300";
    public $message;


    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function validatedBy()
    {
        return StatusValidator::class;
    }

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

}
