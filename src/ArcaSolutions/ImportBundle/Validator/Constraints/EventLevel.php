<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @since 11.3.00
 *
 * @Annotation
 */
class EventLevel extends Constraint
{
    public $code = "E-00200";

    /**
     * @var string
     */
    public $message;

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function validatedBy()
    {
        return "validator.event_level";
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

}
