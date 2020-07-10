<?php

use ArcaSolutions\ModStoresBundle\Kernel\Hooks;

/**
 * Fire Hooks::Exist globally through autoload
 *
 * @param string $hookName
 * @return boolean
 */
if (!function_exists('HookExist')) {

    function HookExist($hookName = null)
    {
        return Hooks::Exist($hookName);
    }

}