<?php

namespace ArcaSolutions\ModStoresBundle\Kernel;

/**
 * Class Hooks
 *
 * @package ArcaSolutions\ModStoresBundle\Kernel
 * @author Leandro Sanches <leandro.sanches@arcasolutions.com>
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 * @author José Lourenção <jose.lourencao@arcasolutions.com>
 */
final class Hooks
{
    /**
     * @var array
     */
    private static $callbacks = [];

    /**
     * Register hook on callbacks
     *
     * @param string $hookName
     * @param method $callback
     * @return void
     */
    public static function Register($hookName, $callback = null)
    {
        // adds to list of callbacks
        self::$callbacks[$hookName][] = $callback;
    }

    /**
     * Check if hook exist on registered items
     *
     * @param string $hookName
     * @return boolean
     */
    public static function Exist($hookName)
    {
        return isset(self::$callbacks[$hookName]);
    }

    /**
     * Register hook on callbacks
     *
     * @param string $hookName
     * @param array $params
     * @param boolean $returnResult
     * @return array|boolean
     */
    public static function Fire($hookName, $params = null, $returnResult = false)
    {
        if (isset(self::$callbacks[$hookName]) && is_array(self::$callbacks[$hookName])) {

            // initialize results array
            $result = null;
            $params['_return'] = &$result;

            // run hooks action
            foreach (self::$callbacks[$hookName] as $callback) {
                $callback($params);
            }

            // returns results array or just boolean if no return param was found
            if (!is_null($params['_return']) && $returnResult) {
                return $params['_return'];
            } else {
                return true;
            }

        }

        // returns false if hook wasn't registered
        return false;
    }
}
