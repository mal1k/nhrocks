<?php

namespace ArcaSolutions\ImportBundle\Validator\Constraints;

/**
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @since 11.3.00
 *
 * @Annotation
 */
class Longitude extends NumberRange
{


    public $code = "C-00101";

    /**
     * Longitude constructor.
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->min = -180;
        $this->max = 180;
    }

}
