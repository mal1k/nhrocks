<?php

namespace ArcaSolutions\ImportBundle\Annotation;


/**
 * @Annotation
 * @Target({"PROPERTY"})
 *
 * @package ArcaSolutions\ImportBundle\Annotation
 */
final class Import
{
    /**
     * @var string
     *
     * @Required
     */
    public $name;

    /**
     * @var bool
     */
    public $mappingRequired = false;

    /**
     * @var bool
     */
    public $isDate = false;
}
