<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @since 11.3.00
 *
 * @Annotation
 */
class ListingLevel extends Constraint
{

    public $message;
    public $code = "L-00200";

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function validatedBy()
    {
        return "validator.listing_level";
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
