<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @since 11.3.00
 *
 * @Annotation
 */
class AccountFirstName extends Constraint
{
    public $code = "C-00700";

    public $message;

    public $property = "AccountFirstName";

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function validatedBy()
    {
        return AccountUsernameValidator::class;
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}
