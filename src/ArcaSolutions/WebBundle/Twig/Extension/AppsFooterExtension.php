<?php

namespace ArcaSolutions\WebBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AppsFooterExtension extends \Twig_Extension
{
    /**
     * ContainerInterface
     *
     * @var object
     */
    protected $container;

    /**
     * @param $container
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
            new \Twig_SimpleFunction(
                'apps',
                [$this, 'apps'],
                ['needs_environment' => true, 'is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param \Twig_Environment $twig_Environment
     * @param array|null $content
     */
    public function apps(\Twig_Environment $twig_Environment, $content = null)
    {
        return $twig_Environment->render('::blocks/apps.html.twig', ['content'    => $content]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'apps';
    }
}
