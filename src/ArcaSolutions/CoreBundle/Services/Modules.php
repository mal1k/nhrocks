<?php

namespace ArcaSolutions\CoreBundle\Services;

use ArcaSolutions\ArticleBundle\Entity\Articlelevel;
use ArcaSolutions\BannersBundle\Entity\Bannerlevel;
use ArcaSolutions\ClassifiedBundle\Entity\ClassifiedLevel;
use ArcaSolutions\EventBundle\Entity\EventLevel;
use ArcaSolutions\ListingBundle\Entity\ListingLevel;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;

/**
 * Class Modules
 *
 * @package ArcaSolutions\CoreBundle\Services
 */
class Modules
{
    /**
     * @var DoctrineRegistry
     */
    private $doctrine;

    /**
     * Deal has a different name in DB
     * @var array
     */
    private $modules = ['listing', 'event', 'classified', 'article', 'banner', 'promotion', 'blog'];

    /**
     * Modules having a level
     * @var array
     */
    private $modulesLevel = ['listing', 'event', 'classified', 'article', 'banner'];

    /**
     * Modules constructor.
     *
     * @param DoctrineRegistry $doctrine
     */
    public function __construct(DoctrineRegistry $doctrine)
    {
        $this->doctrine = $doctrine;

        /* ModStores Hooks */
        HookFire("modules_construct", [
            "that" => &$this,
        ]);

    }

    public function getAvailableModulesLevel()
    {
        $availableModules = $this->getAvailableModules();
        $modulesLevel = array_flip($this->modulesLevel);
        $modulesLevel = array_intersect_key($availableModules, $modulesLevel);

        return $modulesLevel;
    }

    /**
     * It checks if edirectory's modules are enabled. It uses its variable called modules for it
     * To add new modules, just change the variable
     *
     * @return array
     */
    public function getAvailableModules()
    {
        /* per domain */
        $modules = $this->doctrine->getRepository('WebBundle:Setting')->whichModulesAreAvailable($this->modules);

        /* ModStores Hooks */
        HookFire("modules_before_return_availablemodules", [
            "modules" => &$modules,
        ]);

        return $modules;
    }

    public function getLevelsFromAllModules()
    {
        $levelEntities = [
            'listing'    => ListingLevel::class,
            'event'      => EventLevel::class,
            'classified' => ClassifiedLevel::class,
            'banner'     => Bannerlevel::class,
            'article'    => Articlelevel::class,
        ];

        $levels = [];
        foreach ($levelEntities as $module => $entity) {
            if (!$this->isModuleAvailable($module)) {
                continue;
            }

            $repo = $this->doctrine->getRepository($entity);

            $levels[$module] = $repo->findBy(['active' => 'y'], ['value' => 'DESC']);
        }


        /* ModStores Hooks */
        HookFire("modules_before_return_availablemoduleslevel", [
            "modules" => &$levels,
        ]);

        return $levels;
    }

    /**
     * It checks if module is available
     *
     * @param string $module
     * @return bool
     * @throws \Exception
     */
    public function isModuleAvailable($module = '')
    {
        if (!$this->isModule($module)) {
            throw new \Exception('You must pass a valid module');
        }

        if ($module == 'deal') {
            $module = 'promotion';
        }

        $available = $this->doctrine->getRepository('WebBundle:Setting')->isModuleAvailable($module);

        /* ModStores Hooks */
        HookFire("modules_before_return_ismoduleavailable", [
            "module"    => &$module,
            "available" => &$available,
        ]);

        return $available;
    }

    public function isModule($possibleModule = '')
    {
        if ($possibleModule == 'deal') {
            $possibleModule = 'promotion';
        }

        $isModule = in_array($possibleModule, $this->modules);

        /* ModStores Hooks */
        HookFire("modules_before_return_ismodule", [
            "possibleModule" => &$possibleModule,
            "isModule"       => &$isModule,
        ]);

        return $isModule;
    }
}
