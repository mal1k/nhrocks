<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use ArcaSolutions\CoreBundle\Services\Settings;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Class TimeFormatValidator
 *
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 * @since 11.3.00
 */
class TimeFormatValidator extends ConstraintValidator
{

    /**
     * @var string
     */
    public $timeFormat;

    /**
     * TimeFormatValidator constructor.
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param Settings $settings
     */
    function __construct(Settings $settings)
    {
        $this->timeFormat = (int)$settings->getDomainSetting('clock_type');
    }

    /**
     * Checks if the passed value is valid.
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     * @return bool
     */
    public function validate($value, Constraint $constraint)
    {
        $error = false;
        if ($value == null || strlen(trim($value)) == 0) {
            return !$error;
        }

        /* @var $context ExecutionContext */
        $context = $this->context;
        $context->setConstraint($constraint);

        if ($this->timeFormat == 12) {

            $timeMode = substr(trim($value), -2);
            if (!$timeMode || !in_array(strtolower($timeMode), ["am", "pm"])) {
                $error = true;
            }

            $timeValue = substr(trim($value), 0, -2);
            $timeSplited = explode(":", trim($timeValue));

            if ((count($timeSplited) > 3) ||
                (count($timeSplited) < 2) ||
                ((int)$timeSplited[0] > 12) ||
                ((int)$timeSplited[1] > 59) ||
                (isset($timeSplited[2]) && (int)$timeSplited[2] > 59))
            {
                $error = true;
            }

        } else if ($this->timeFormat == 24) {

            $timeSplited = explode(":", trim($value));
            if (count($timeSplited) > 3 ||
                (count($timeSplited) < 2) ||
                (int)$timeSplited[0] > 24 ||
                (int)$timeSplited[1] > 59 ||
                (isset($timeSplited[2]) && (int)$timeSplited[2] > 59))
            {
                $error = true;
            }
        }

        if ($error) {
            $context->buildViolation($constraint->message)
                ->setCode($constraint->code)
                ->addViolation();
        }

        return !$error;
    }
}
