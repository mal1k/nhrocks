<?php

namespace ArcaSolutions\WebBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class FacebookExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
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
            new \Twig_SimpleFunction(
                'facebookComments',
                [$this, 'facebookComments'],
                ['needs_environment' => true, 'is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'facebookFanPage',
                [$this, 'facebookFanPage'],
                ['needs_environment' => true, 'is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Twig extension that renders Facebook comments plugin
     * @param \Twig_Environment $twig
     * @param null $width
     * @return string
     */
    public function facebookComments(\Twig_Environment $twig, $width = null)
    {
        $settingsModel = $this->container->get('doctrine')->getRepository('WebBundle:Setting');
        if ('on' != $settingsModel->getSetting('commenting_fb')) {
            return '';
        }

        return $twig->render('@Web/facebook/comments.html.twig', [
            'width'          => $width ?: $this->container->getParameter('facebook.comments.width'),
            'quantity_posts' => 5
        ]);
    }

    /**
     * Twig extension that renders Facebook page plugin
     *
     * @param \Twig_Environment $twig
     * @param string            $fanpage
     * @return string
     */
    public function facebookFanPage(\Twig_Environment $twig, $fanpage = '')
    {
        if (empty($fanpage)) {
            return '';
        }

        return $twig->render('@Web/facebook/page.html.twig', [
            'width'    => $this->container->getParameter('facebook.comments.width'),
            'fan_page' => $fanpage
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'facebook_comments';
    }
}
