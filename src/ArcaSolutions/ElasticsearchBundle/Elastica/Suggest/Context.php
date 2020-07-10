<?php

namespace ArcaSolutions\ElasticsearchBundle\Elastica\Suggest;


use Elastica\Exception\InvalidException;
use Elastica\NameableInterface;
use Elastica\Param;

/**
 * Class Context
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @package ArcaSolutions\ElasticsearchBundle\Elastica\Suggest
 */
class Context extends Param implements NameableInterface
{
    protected $_name = '';

    public function __construct()
    {
        $this->setName($this->_getBaseName());
    }

    /**
     * Retrieve the name of this object.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set the name of this object.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        if (empty($name)) {
            throw new InvalidException('Suggest name has to be set');
        }
        $this->_name = $name;

        return $this;
    }

    public function toArray()
    {
        $array = parent::toArray();

        $baseName = $this->_getBaseName();

        if (isset($array[$baseName])) {
            $contexs = $array[$baseName];
            unset($array[$baseName]);

            foreach ($contexs as $key => $value) {
                $array[$key] = $value;
            }
        }

        return $array;
    }
}
