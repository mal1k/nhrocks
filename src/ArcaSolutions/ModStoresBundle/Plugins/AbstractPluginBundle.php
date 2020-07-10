<?php

namespace ArcaSolutions\ModStoresBundle\Plugins;

use ArcaSolutions\ModStoresBundle\Traits\ComposerMetadataTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BasePluginBundle
 *
 * @package ArcaSolutions\ModStoresBundle\Kernel
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 */
abstract class AbstractPluginBundle extends Bundle
{
    use ComposerMetadataTrait;

    /**
     * Returns install command
     *
     * @param $input
     * @param $output
     * @return EdirectoryModstoresInstallCommand
     */
    public function getInstallCommand($input, $output)
    {
        $commandClass = $this->getNamespace().'\\Command\\EdirectoryModstoresInstallCommand';

        return new $commandClass($input, $output, $this);
    }

    /**
     * Returns bundle config folder path
     *
     * @return string
     */
    public function getConfigPath()
    {
        return $this->getResourcePath().'/config';
    }

    /**
     * Returns bundle Resource folder path
     *
     * @return string
     */
    public function getResourcePath()
    {
        return $this->getPath().'/Resources';
    }

    /**
     * Returns full qualified namespace
     *
     * @return string
     */
    public function getQualifiedNamespace()
    {
        return $this->getNamespace().'\\'.$this->getName();
    }

    /**
     * Returns full qualified namespace
     *
     * @return string
     */
    public function getFullyQualifiedNamespace()
    {
        return '\\'.$this->getNamespace().'\\'.$this->getName();
    }

    /**
     * Checks if current loaded page is from sitemgr area
     *
     * @return boolean
     */
    protected function isSitemgr()
    {
        $request = Request::createFromGlobals();

        $alias = $this->container->getParameter('alias_sitemgr_module');

        // verify if sitemgr alias from real URL as well as http referer in case of ajax request
        return (
            (strpos($request->getUri(), $alias) !== false) ||
            ($request->isXmlHttpRequest() === true && strpos($request->server->get('HTTP_REFERER'), $alias) !== false)
        );
    }
}