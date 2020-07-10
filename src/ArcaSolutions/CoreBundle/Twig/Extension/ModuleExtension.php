<?php

namespace ArcaSolutions\CoreBundle\Twig\Extension;

use ArcaSolutions\CoreBundle\Services\Modules;

/**
 * Class ModuleExtension
 *
 * @author Diego de Biagi <diego.biagi@arcasolutions.com>
 * @since VERSION
 */
class ModuleExtension extends \Twig_Extension
{

    const NAME = 'module_extension';

    /** @var Modules */
    private $modules;

    public function __construct(Modules $modules)
    {
        $this->modules = $modules;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_module_active', [$this, 'isModuleActive']),
        ];
    }

    public function getName()
    {
        return self::NAME;
    }

    public function isModuleActive($module)
    {
        return $this->modules->isModuleAvailable($module);
    }
}