<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Class NotNullOrEmptyValidator
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 * @since 11.3.00
 */
class NotNullOrEmptyValidator extends ConstraintValidator
{

    /**
     * Checks if the passed value is valid.
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value == null || strlen(trim($value)) == 0) {
            /* @var $context ExecutionContext */
            $context = $this->context;

            $context->setConstraint($constraint);
            $context->buildViolation($constraint->message)
                ->setCode($constraint->code)
                ->addViolation();
        }
    }
}
