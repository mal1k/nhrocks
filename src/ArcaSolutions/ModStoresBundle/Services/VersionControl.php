<?php

namespace ArcaSolutions\ModStoresBundle\Services;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ModStoresBundle\ModStoresBundle;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle;
use ReflectionException;

/**
 * Class VersionControl
 *
 * @package ArcaSolutions\ModStoresBundle\Helpers
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 */
final class VersionControl
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var ModStoresBundle
     */
    private $modstoreKernel;

    /**
     * VersionControl constructor
     *
     * @param Kernel $kernel
     * @param ModStoresBundle $modstoreKernel
     */
    public function __construct(Kernel $kernel, ModStoresBundle $modstoreKernel)
    {
        $this->kernel = $kernel;
        $this->modstoreKernel = $modstoreKernel;
    }

    /**
     * Returns if eDirectory version matches needs
     *
     * @return bool
     * @throws ReflectionException
     */
    public function isValidModStoreVersion()
    {
        $edirectoryVersion = $this->kernel->getMetadata('version');

        $modstoreRequirement = $this->modstoreKernel->getComposerMetadata(['require', 'arcasolutions/edirectory']);

        return $this->isValidVersion($edirectoryVersion, $modstoreRequirement);
    }

    /**
     * Compare the current version ($fact) against the required version ($require)
     *
     * @param $fact string
     * @param $require string
     * @return boolean
     */
    public function isValidVersion($fact, $require)
    {
        switch ($this->getMatchMethod($require)) {

            case 'great_equal':
                return $this->compareAGreaterEqual($fact, $require);

            case 'equal':
                return $this->compareAEqual($fact, $require);

        }

        return false;
    }

    /**
     * Select match method
     *
     * @param $require string
     * @return string
     */
    private function getMatchMethod($require)
    {
        if (preg_match("/^\^(\w)+/", $require)) {
            return 'great_equal';
        }

        return 'equal';
    }

    /**
     * Interface to compare $a >= $b versions
     *
     * @param $a
     * @param $b
     * @return mixed
     */
    public function compareAGreaterEqual($a, $b)
    {
        return version_compare($this->cleanVersion($a), $this->cleanVersion($b), '>=');
    }

    /**
     * Sanitize version string
     *
     * @param $version
     * @return null|string|string[]
     */
    public function cleanVersion($version)
    {
        return preg_replace("/\^/", '', $version);
    }

    /**
     * Interface to compare $a == $b versions
     *
     * @param $a
     * @param $b
     * @return mixed
     */
    public function compareAEqual($a, $b)
    {
        return version_compare($this->cleanVersion($a), $this->cleanVersion($b), '==');
    }

    /**
     * Returns if Modstore Kernel version matches needs
     *
     * @param $plugin
     * @return bool
     * @throws ReflectionException
     */
    public function isValidPluginVersion($plugin)
    {
        $modstoreVersion = $this->modstoreKernel->getComposerMetadata('version');

        $pluginRequirement = $plugin;
        if ($plugin instanceof AbstractPluginBundle) {
            $pluginRequirement = $plugin->getComposerMetadata(['require', 'arcasolutions/modstore']);
        }

        return $this->isValidVersion($modstoreVersion, $pluginRequirement);
    }

    /**
     * Convert composer style version to array
     *
     * @param $version
     * @return array
     */
    public function extractVersion($version)
    {
        return explode('.', $this->cleanVersion($version));
    }

    /**
     * Interface to compare $a > $b versions
     *
     * @param $a
     * @param $b
     * @return mixed
     */
    public function compareAGreater($a, $b)
    {
        return version_compare($this->cleanVersion($a), $this->cleanVersion($b), '>');
    }
}