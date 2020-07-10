<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Class KeywordValidator
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 * @since 11.3.00
 */
class KeywordValidator extends ConstraintValidator
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
        if ($value == null || !($constraint instanceof Keyword)) {
            return;
        }

        /* @var $context ExecutionContext */
        $context = $this->context;
        $context->setConstraint($constraint);

        $words = explode($constraint->separator, $value);
        if (count($words) > $constraint->maxNumberOfWords) {
            $context->buildViolation($constraint->message)
                ->setParameter("{{ maxNumberOfWords }}", $constraint->maxNumberOfWords)
                ->setParameter("{{ separator }}", $constraint->separator)
                ->setParameter("{{ maxNumberOfCharsPerWord }}", $constraint->maxNumberOfCharsPerWord)
                ->setCode($constraint->code)
                ->addViolation();
        }

        foreach ($words as $word) {
            if (strlen(trim($word)) > $constraint->maxNumberOfCharsPerWord) {
                $context->buildViolation($constraint->message)
                    ->setParameter("{{ maxNumberOfWords }}", $constraint->maxNumberOfWords)
                    ->setParameter("{{ separator }}", $constraint->separator)
                    ->setParameter("{{ maxNumberOfCharsPerWord }}", $constraint->maxNumberOfCharsPerWord)
                    ->setCode($constraint->code)
                    ->addViolation();
            }
        }
    }
}
