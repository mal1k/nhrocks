<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use ArcaSolutions\ListingBundle\Repository\ListingLevelRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Class ListingLevelValidator
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 * @since 11.3.00
 */
class ListingLevelValidator extends ConstraintValidator
{

    /**
     * @var array
     */
    private $levels = [];

    /**
     * ListingLevelValidator constructor.
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param DoctrineRegistry $doctrine
     */
    function __construct(RegistryInterface $doctrine)
    {

        /* @var $listingLevelRepository ListingLevelRepository */
        $listingLevelRepository = $doctrine->getRepository("ListingBundle:ListingLevel");

        $listingLevels = $listingLevelRepository->findAll();

        $returnName = function (\ArcaSolutions\ListingBundle\Entity\ListingLevel $level) {
            return mb_strtolower($level->getName());
        };

        $this->levels = array_map($returnName, $listingLevels);
    }

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
