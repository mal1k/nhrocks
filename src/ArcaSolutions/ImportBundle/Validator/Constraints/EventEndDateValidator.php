<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use ArcaSolutions\CoreBundle\Services\Settings;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class EventEndDateValidator
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @since 11.3.00
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 */
class EventEndDateValidator extends ConstraintValidator
{

    /**
     * @var string
     */
    private $dateFormat;

    /**
     * EventEndDateValidator constructor.
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
     * @param mixed $module
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($module, Constraint $constraint)
    {
        if (!$constraint instanceof EventEndDate) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\EventEndDate');
        }

        $endDate = \DateTime::createFromFormat($this->dateFormat, $module->getEventEndDate());
        $startDate = \DateTime::createFromFormat($this->dateFormat, $module->getEventStartDate());

        if (($endDate === false or $startDate === false) or $endDate < $startDate) {
            $this->context->buildViolation($constraint->message)
                ->setCode($constraint->code)
                ->addViolation();
        }
    }
}
