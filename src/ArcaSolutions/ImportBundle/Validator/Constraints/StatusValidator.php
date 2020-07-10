<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Class StatusValidator
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 * @since 11.3.00
 */
class StatusValidator extends ConstraintValidator
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

        if (!in_array(strtolower($value), Status::VALID_STATUSES) and !is_null($value)) {
            /* @var $context ExecutionContext */
            $context = $this->context;
            $context->setConstraint($constraint);
            $context->buildViolation($constraint->message)
                ->setCode($constraint->code)
                ->addViolation();
        }
    }
}
