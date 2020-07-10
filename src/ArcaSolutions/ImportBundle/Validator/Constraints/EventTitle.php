<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @since 11.3.00
 *
 * @Annotation
 */
class EventTitle extends Constraint
{

    /**
     * @var string
     */
    public $code = "E-00100";

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
        return NotNullOrEmptyValidator::class;
    }

}
