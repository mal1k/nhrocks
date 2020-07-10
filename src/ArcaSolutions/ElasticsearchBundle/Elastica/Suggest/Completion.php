<?php

namespace ArcaSolutions\ElasticsearchBundle\Elastica\Suggest;


use \Elastica\Suggest\Completion as ElasticaCompletion;

/**
 * Class Completion
 * @package ArcaSolutions\ElasticsearchBundle\Elastica\Suggest
 */
class Completion extends ElasticaCompletion
{
    const PARAM_NAME = 'contexts';

    /**
     * Add a context to this suggestion clause.
     *
     * @param Context $context
     */
    public function addContext(Context $context)
    {
        $this->addParam(self::PARAM_NAME, $context);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        $baseName = $this->_getBaseName();

        if (isset($array[$baseName][self::PARAM_NAME])) {
            $context = $array[$baseName][self::PARAM_NAME];
            unset($array[$baseName][self::PARAM_NAME]);

            foreach ($context as $key => $value) {
                $array[$baseName][$key] = $value;
            }
        }

        return $array;
    }
}
