<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @since 11.3.00
 *
 * @Annotation
 */
class DateFormat extends Constraint
{
    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $code = "C-00500";

    /**
     * @var bool
     */
    public $empty = true;

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function validatedBy()
    {
        return DateFormatValidator::class;
    }

}
