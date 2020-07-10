<?php

namespace ArcaSolutions\ModStoresBundle\Twig\Extension;

use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class HooksExtension
 *
 * @package ArcaSolutions\ModStoresBundle\Twig\Extension\HooksExtension
 * @author Leandro Sanches <leandro.sanches@arcasolutions.com>
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 * @author José Lourenção <jose.lourencao@arcasolutions.com>
 */
class HooksExtension extends Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * HooksExtension constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'HookFire',
                [$this, 'HookFire'],
                ['is_safe' => ['all'], 'needs_environment' => true]
            ),
            new Twig_SimpleFunction(
                'HookExist',
                [$this, 'HookExist'],
                ['is_safe' => ['all'], 'needs_environment' => true]
            ),
        ];
    }

    /**
     * Fire hook on a twig environment
     *
     * @param Twig_Environment $twig_Environment
     * @param string $hookName
     * @param array $params
     * @return array|boolean
     */
    public function HookFire(Twig_Environment $twig_Environment, $hookName, $params = null, $returnResult = false)
    {
        return Hooks::Fire($hookName, $params, $returnResult);
    }

    /**
     * Check if hook exist on a twig environment
     *
     * @param Twig_Environment $twig_Environment
     * @param string $hookName
     * @return boolean
     */
    public function HookExist(Twig_Environment $twig_Environment, $hookName)
    {
        return Hooks::Exist($hookName);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hooks.extension';
    }
}
