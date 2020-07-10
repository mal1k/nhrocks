<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * Class EventEndDate
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @since 11.3.00
 * @package ArcaSolutions\ImportBundle\Validator\Constraints
 *
 * @Annotation
 */
class EventEndDate extends Constraint
{

    /**
     * @var string
     */
    public $code = 'E-00300';

    /**
     * @var string
     */
    public $message;

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
