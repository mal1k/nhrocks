<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @since 11.3.00
 *
 * @Annotation
 */
class TimeFormat extends Constraint
{
    public $message;

    public $code = "C-00600";

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function validatedBy()
    {
        return TimeFormatValidator::class;
    }

}
