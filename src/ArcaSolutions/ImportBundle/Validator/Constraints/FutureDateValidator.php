<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use ArcaSolutions\CoreBundle\Services\Settings;
use Stripe\Util\Set;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class FutureDateValidator
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @since 11.3.00
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 */
class FutureDateValidator extends ConstraintValidator
{

    /**
     * @var string
     */
    private $dateFormat;

    /**
     * FutureDateValidator constructor.
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->dateFormat = $settings->getDomainSetting('date_format');
    }

    /**
     * Checks if the passed value is valid.
     *
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof FutureDate) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\FutureDate');
        }

        if (null === $value || '' === $value) {
            return;
        }

        /* @var $context ExecutionContext */
        $context = $this->context;
        $context->setConstraint($constraint);

        $dt = \DateTime::createFromFormat($this->dateFormat, $value);
        if ($dt === false or $dt < new \DateTime("tomorrow")) {
            $context->buildViolation($constraint->message)
                ->setCode($constraint->code)
                ->addViolation();
        }
    }
}
