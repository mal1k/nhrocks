<?php

namespace ArcaSolutions\WebBundle\Twig\Extension;

use ArcaSolutions\CoreBundle\Services\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;
use Ivory\GoogleMap\Helper\Builder\MapHelperBuilder;

class GoogleTagsExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * GoogleTagsExtension constructor.
     *
     * @param ContainerInterface $container
     * @param Settings $settings
     */
    public function __construct(ContainerInterface $container, Settings $settings)
    {
        $this->container = $container;
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('googleTagAnalytics', [$this, 'googleTagAnalytics'], [
                'needs_environment' => true,
                'is_safe' => ['html', 'meta']
            ]),
            new \Twig_SimpleFunction('googleTagManager', [$this, 'googleTagManager'], [
                'needs_environment' => true,
                'is_safe' => ['html', 'meta']
            ]),
            new \Twig_SimpleFunction('googleMaps', [$this, 'googleMaps'], [
                'needs_environment' => true,
                'is_safe' => ['html', 'meta']
            ])
        ];
    }

    /**
     * Return google analytic code
     *
     * @param \Twig_Environment $twig
     *
     * @return string
     */
    public function googleTagAnalytics(\Twig_Environment $twig)
    {
        if ($this->settings->getDomainSetting('google_analytics_front') === 'on') {
            return $twig->render('@Web/google/analytics.html.twig', [
                'code' => $this->settings->getDomainSetting('google_analytics_status')
            ]);
        }

        return '';
    }

    /**
     * Returns google tag manager
     *
     * @param \Twig_Environment $twig_Environment
     *
     * @return string
     */
    public function googleTagManager(\Twig_Environment $twig_Environment, $section = 'head')
    {
        if ($this->settings->getDomainSetting('google_tagmanager_status') === 'on') {
            return $twig_Environment->render('@Web/google/tag-manager-'. $section . '.html.twig', [
                    'code' => $this->settings->getDomainSetting('google_tagmanager_clientID')
                ]);
        }

        return '';
    }

    /**
     * Returns google tag manager
     *
     * @param \Twig_Environment $twig_Environment
     *
     * @return string
     */
    public function googleMaps(\Twig_Environment $twig_Environment, $map)
    {
        $mapHTMLHelper = MapHelperBuilder::create()->build()->renderHtml($map);
        $mapCSSHelper = MapHelperBuilder::create()->build()->renderStylesheet($map);


        return $twig_Environment->render('@Web/google/maps.html.twig', [
            'mapHTMLHelper' => $mapHTMLHelper,
            'mapCSSHelper' => $mapCSSHelper,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'google_extension';
    }
}
