<?php

namespace ArcaSolutions\ModStoresBundle\Kernel;

use Exception;

/**
 * Class Kernel
 *
 * @package ArcaSolutions\ModStoresBundle\Kernel
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 */
final class Kernel
{
    /**
     * @var string
     */
    private $basePluginNamespace = 'ArcaSolutions\\ModStoresBundle\\Plugins\\';

    /**
     * Gets all activated ModStores items from php autoload
     *
     * @param $namespaceOnly boolean
     * @return array
     */
    public function getActivated($namespaceOnly = false)
    {
        // gets activated bundles
        $activated = $this->loadActivated();

        if (!is_array($activated)) {
            return [];
        }

        $activated = array_map([$this, 'buildBundleNamespace'], $activated);

        // returns just namespace
        if ($namespaceOnly) {
            return $activated;
        }

        // returns loaded bundles
        $plugins = [];
        foreach ($activated as $className) {
            $plugins[] = new $className();
        }

        return $plugins;
    }

    /**
     * Loads activated yml file or retrieve loaded array
     *
     * @return array
     */
    private function loadActivated()
    {
        // verify if has installed plugins autoload, if not, returns empty
        if (!file_exists(__DIR__.'/../Resources/config/activated.php')) {
            return [];
        }

        try {

            $activated = require __DIR__.'/../Resources/config/activated.php';

        } catch (Exception $e) { /*...*/
        }

        return is_array($activated) ? $activated : [];
    }

    /**
     * Gets all installed ModStores items from php autoload
     *
     * @param bool $namespaceOnly
     * @return array
     */
    public function getInstalled($namespaceOnly = false)
    {
        // gets installed bundles
        $installed = $this->loadInstalled();

        if (!is_array($installed)) {
            return [];
        }

        $installed = array_map([$this, 'buildBundleNamespace'], array_keys($installed));

        // returns just namespace
        if ($namespaceOnly) {
            return $installed;
        }

        // returns loaded bundles
        $plugins = [];
        foreach ($installed as $className) {
            $plugins[] = new $className();
        }

        return $plugins;
    }

    /**
     * Loads installed yml file or retrieve loaded array
     *
     * @return array
     */
    private function loadInstalled()
    {
        // verify if has installed plugins autoload, if not, returns empty
        if (!file_exists(__DIR__.'/../Resources/config/installed.php')) {
            return [];
        }

        try {

            $installed = require __DIR__.'/../Resources/config/installed.php';

        } catch (Exception $e) { /*...*/
        }

        return is_array($installed) ? $installed : [];
    }

    /**
     * Gets all installed ModStores items from php autoload with locked metadata
     *
     * @return array
     */
    public function getVersionLock()
    {
        // gets bundle name/metadata
        $installed = $this->loadInstalled();

        if (!is_array($installed)) {
            return [];
        }

        return $installed;
    }

    /**
     * Generate qualified bundle namespace with bundle name
     *
     * @param $bundle
     * @return string
     */
    public function buildBundleNamespace($bundle)
    {
        return sprintf($this->basePluginNamespace.'%s\%s', str_replace('Bundle', '', $bundle), $bundle);
    }
}