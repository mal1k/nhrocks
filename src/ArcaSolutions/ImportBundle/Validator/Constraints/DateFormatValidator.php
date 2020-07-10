<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use ArcaSolutions\CoreBundle\Services\Settings;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class DateFormatValidator
 *
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 * @since 11.3.00
 */
class DateFormatValidator extends ConstraintValidator
{

    /**
     * @var string
     */
    public $dateFormat;

    /**
     * DateFormatValidator constructor.
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param Settings $settings
     */
    function __construct(Settings $settings)
    {
        $this->dateFormat = $settings->getDomainSetting('date_format');
    }

    /**
     * Checks if the passed value is valid.
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DateFormat) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\DateFormat');
        }

        if ($constraint->empty && ($value == null || strlen(trim($value)) == 0)) {
            return;
        }

        /* @var $context ExecutionContext */
        $context = $this->context;
        $context->setConstraint($constraint);

        $dt = \DateTime::createFromFormat($this->dateFormat, $value);
        if ($dt === false or $dt->format($this->dateFormat) !== $value) {
            $context->buildViolation($constraint->message)
                ->setCode($constraint->code)
                ->addViolation();
        }
    }
}
