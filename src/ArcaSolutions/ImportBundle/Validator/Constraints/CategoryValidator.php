<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Class CategoryValidator
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 * @since 11.3.00
 */
class CategoryValidator extends ConstraintValidator
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

        if (null != $value and $this->isValidNumberOfLevels($value, $constraint->maxNumberOfLevels,
                $constraint->separator)) {
            /* @var $context ExecutionContext */
            $context = $this->context;
            $context->setConstraint($constraint);
            $context->buildViolation($constraint->message)
                ->setParameter("{{ maxNumberOfLevels }}", $constraint->maxNumberOfLevels)
                ->setParameter("{{ separator }}", $constraint->separator)
                ->setCode($constraint->code)
                ->addViolation();
        }

    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $value
     * @param $maxNumberOfLevels
     * @param $separator
     * @return bool
     */
    private function isValidNumberOfLevels($value, $maxNumberOfLevels, $separator)
    {
        $categories = explode($separator, $value);

        return count($categories) > $maxNumberOfLevels;
    }
}
