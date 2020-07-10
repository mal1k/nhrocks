<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @since 11.4.00
 *
 * @Annotation
 */
class LocationHierarchy extends Constraint
{
    public $message;

    public $module;

    public $code = "C-00800";

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.4.00
     *
     * @return string
     */
    public function validatedBy()
    {
        return LocationHierarchyValidator::class;
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.4.00
     *
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }


}
