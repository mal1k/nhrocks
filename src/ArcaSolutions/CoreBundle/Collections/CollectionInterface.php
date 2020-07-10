<?php

namespace ArcaSolutions\CoreBundle\Collections;


/**
 * Iterator that reads data to be imported
 *
 * @author Diego Mosela <diego.mosela@arcasolutuions.com>
 * @package ArcaSolutions\CoreBundle\Analyzers
 */
interface CollectionInterface extends \Iterator
{

    /**
     * Get the field (column, property) names
     *
     * @return array
     */
    public function getFields();

    /**
     * Count rows
     *
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since 11.3.00
     * @return int
     */
    public function count();
}
