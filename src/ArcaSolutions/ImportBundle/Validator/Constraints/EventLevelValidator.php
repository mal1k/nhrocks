<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use ArcaSolutions\EventBundle\Repository\EventLevelRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Class EventLevelValidator
 *
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 * @since 11.3.00
 */
class EventLevelValidator extends ConstraintValidator
{

    /**
     * @var array
     */
    private $levels = [];

    /**
     * EventLevelValidator constructor.
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param RegistryInterface $doctrine
     */
    function __construct(RegistryInterface $doctrine)
    {

        /* @var $eventLevelRepository EventLevelRepository */
        $eventLevelRepository = $doctrine->getRepository("EventBundle:EventLevel");

        $eventLevels = $eventLevelRepository->findAll();

        $returnName = function (\ArcaSolutions\EventBundle\Entity\EventLevel $level) {
            return mb_strtolower($level->getName());
        };

        $this->levels = array_map($returnName, $eventLevels);
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
        if (!in_array(mb_strtolower($value), $this->levels) and !is_null($value)) {

            /* @var $context ExecutionContext */
            $context = $this->context;
            $context->setConstraint($constraint);

            $context->buildViolation($constraint->message)
                ->setCode($constraint->code)
                ->addViolation();
        }
    }

}
