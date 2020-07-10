<?php

namespace ArcaSolutions\ModStoresBundle\Traits;

use ReflectionClass;
use ReflectionException;

/**
 * Class ComposerMetadataTrait
 *
 * @package ArcaSolutions\ModStoresBundle\Traits
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 */
trait ComposerMetadataTrait
{
    /**
     * Get bundle's composer metadata
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function getComposerMetadata($key = null)
    {
        // select composer json file
        $reflector = new ReflectionClass(get_class($this));
        $file = dirname($reflector->getFileName()).'/composer.json';

        if (!file_exists($file)) {
            return [];
        }

        // proper decode it
        $array = json_decode(file_get_contents($file), true);

        // select a one level deep key if needed
        if (is_string($key)) {
            return $array[$key];
        }

        // select a multi-level deep key if needed
        if (is_array($key)) {
            foreach ($key as $level) {
                if (isset($array[$level])) {
                    $array = $array[$level];
                }
            }
        }

        return $array;
    }
}