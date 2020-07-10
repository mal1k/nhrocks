<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;

/**
 * Class FutureDate
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @since 11.3.00
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
class FutureDate extends Constraint
{
    /**
     * @var string
     */
    public $code = 'C-00501';

    /**
     * @var string
     */
    public $message;
}
