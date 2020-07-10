<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use ArcaSolutions\WebBundle\Entity\SettingLocation;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class LocationHierarchyValidator
 *
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 * @since 11.4.00
 */
class LocationHierarchyValidator extends ConstraintValidator
{

    /**
     * @var SettingLocation[]
     */
    public $activeLocations;

    /**
     * LocationHierarchyValidator constructor.
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.4.00
     *
     * @param RegistryInterface $doctrine
     */
    function __construct(RegistryInterface $doctrine)
    {
        /* reverse the levels to check if there is any location child without a parent */
        $this->enableLocations = array_reverse($doctrine->getRepository(SettingLocation::class)->findBy(['enabled' => 'y']));
    }

    /**
     * Checks if the location hierarchy is valid.
     *
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.4.00
     *
     * @param mixed $module The import entity
     * @param Constraint $constraint The constraint for the validation
     * @return bool
     */
    public function validate($module, Constraint $constraint)
    {
        /* @var SettingLocation $enableLocation */
        for ($i = 0; $i < count($this->enableLocations); $i++) {
            $enableLocation = $this->enableLocations[$i];
            $locationFieldName = 'get'.ucfirst($constraint->module).ucfirst(strtolower($enableLocation->getName()));

            if ($module->$locationFieldName() && isset($this->enableLocations[$i + 1])) {
                $parentLocation = $this->enableLocations[$i + 1];
                $parentFieldName = 'get'.ucfirst($constraint->module).ucfirst(strtolower($parentLocation->getName()));

                if (empty(trim($module->$parentFieldName()))) {
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
}
