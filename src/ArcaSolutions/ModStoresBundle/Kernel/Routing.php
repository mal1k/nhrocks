<?php

namespace ArcaSolutions\ModStoresBundle\Kernel;

use ArcaSolutions\ModStoresBundle\ModStoresBundle;
use Exception;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class Routing
 *
 * @package ArcaSolutions\ModStoresBundle\Kernel
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 */
final class Routing extends Loader
{
    /**
     * @var ModStoresBundle
     */
    private $modstoreKernel;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Routing constructor
     *
     * @param ModStoresBundle $modstoreKernel
     * @param Logger $logger
     */
    public function __construct(ModStoresBundle $modstoreKernel, Logger $logger)
    {
        $this->modstoreKernel = $modstoreKernel;
        $this->logger = $logger;
    }

    /**
     * Load ModStores route files
     *
     * @param $resource
     * @param $type
     * @return RouteCollection|boolean
     */
    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();

        try {

            // goes trough all installed plugins
            foreach ($this->modstoreKernel->getLoaded() as $plugin) {

                // locate file and import it
                $routes = (new FileLocator($plugin->getConfigPath()))->locate('routing.yml');
                $collection->addCollection($this->import($routes, 'yaml'));

            }

        } catch (Exception $e) {
            $this->logger->addError($e->getMessage());
        }

        return $collection;
    }

    /**
     * Set custom route name
     *
     * @param $resource
     * @param $type
     * @return boolean
     */
    public function supports($resource, $type = null)
    {
        return 'plugins' === $type;
    }
}
