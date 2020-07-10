<?php

namespace ArcaSolutions\ModStoresBundle;

use ArcaSolutions\ModStoresBundle\Traits\ComposerMetadataTrait;
use ReflectionException;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ModStoreBundle
 *
 * @package ArcaSolutions\ModStoresBundle
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 */
class ModStoresBundle extends Bundle
{
    use ComposerMetadataTrait;

    /**
     * @var array
     */
    private $loaded;

    /**
     * Get all bundles to be loaded
     *
     * @return array
     * @throws ReflectionException
     */
    public function getBundles()
    {
        $bundles = [$this];

        foreach ($this->getLoaded() as $plugin) {
            $bundles[] = $plugin;
        }

        return $bundles;
    }

    /**
     * Get installed plugins
     *
     * @return array
     * @throws ReflectionException
     */
    public function getLoaded()
    {
        if (empty($this->loaded)) {
            $this->loaded = $this->getKernel()->getInstalled();
        }

        return $this->loaded;
    }

    /**
     * Get ModStore Kernel
     *
     * @return Kernel\Kernel
     */
    public function getKernel()
    {
        return new Kernel\Kernel();
    }
}
