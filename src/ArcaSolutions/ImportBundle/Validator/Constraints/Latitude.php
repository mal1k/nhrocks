<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;


/**
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @since 11.3.00
 *
 * @Annotation
 */
class Latitude extends NumberRange
{
    public $code = "C-00100";

    /**
     * Latitude constructor.
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->min = -90;
        $this->max = 90;
    }


}
