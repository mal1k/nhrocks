<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;

use ArcaSolutions\ImportBundle\Constants;
use Symfony\Component\Validator\Constraint;

/**
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @since 11.3.00
 *
 * @Annotation
 */
class Category extends Constraint
{

    /**
     * @var int
     */
    public $maxNumberOfLevels = 5;

    /**
     * @var string
     */
    public $separator = Constants::CATEGORY_SEPARATOR;

    public $message;

    public $code = "C-00400";

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function validatedBy()
    {
        return CategoryValidator::class;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

}
