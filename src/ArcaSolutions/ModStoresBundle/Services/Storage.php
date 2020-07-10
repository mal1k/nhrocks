<?php

namespace ArcaSolutions\ModStoresBundle\Services;

/**
 * Class Storage
 *
 * @package ArcaSolutions\ModStoresBundle\Services
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 */
final class Storage
{
    /**
     * @var array
     */
    private $storage;

    /**
     * Store data to index
     *
     * @param $index
     * @param $value
     * @param string $channel
     */
    public function store($index, $value, $channel = 'default')
    {
        $this->storage[$channel][$index] = $value;
    }

    /**
     * Retrieve data from index
     *
     * @param $index
     * @param string $channel
     * @return mixed|null
     */
    public function retrieve($index, $channel = 'default')
    {
        if (!isset($this->storage[$channel][$index])) {
            return null;
        }

        return $this->storage[$channel][$index];
    }

    /**
     * Retrive all data
     *
     * @param string $channel
     * @return array
     */
    public function retrieveAll($channel = 'default')
    {
        return $this->storage[$channel];
    }

    /**
     * Retrieve data from index and unset it
     *
     * @param $index
     * @param string $channel
     * @return null
     */
    public function retrieveAndDestroy($index, $channel = 'default')
    {
        if (!isset($this->storage[$channel][$index])) {
            return null;
        }

        $result = $this->storage[$channel][$index];

        unset($this->storage[$channel][$index]);

        return $result;
    }
}
