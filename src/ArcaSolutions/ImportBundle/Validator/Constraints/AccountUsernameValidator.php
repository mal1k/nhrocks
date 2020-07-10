<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;

use ArcaSolutions\CoreBundle\Services\AccountHandler;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Class AccountUsernameValidator
 *
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 * @since 11.3.00
 */
class AccountUsernameValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param mixed $module
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($module, Constraint $constraint)
    {
        if ($module->getAccountUsername() != null && !empty(trim($module->getAccountUsername()))) {

            $propertyToValidate = "get".$constraint->property;
            $value = $module->$propertyToValidate();

            /* validates if the value is empty */
            if ($value == null || empty(trim($value)) ||
                /* when the property to validate is AccountPassword it need to be in a length range */
                (strpos($propertyToValidate, 'Password') &&
                    (AccountHandler::PASSWORD_MIN_LEN > strlen($value) || AccountHandler::PASSWORD_MAX_LEN < strlen($value)))
            ) {
                /* @var $context ExecutionContext */
                $context = $this->context;
                $context->setConstraint($constraint);

                $context->buildViolation($constraint->message)
                    ->setCode($constraint->code)
                    ->addViolation();
            }
        }
    }

}
