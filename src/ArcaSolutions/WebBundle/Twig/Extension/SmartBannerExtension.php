<?php

namespace ArcaSolutions\WebBundle\Twig\Extension;

use ArcaSolutions\CoreBundle\Services\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SmartBannerExtension extends \Twig_Extension
{
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * SmartBannerExtension constructor.
     *
     * @param Settings $settings
     * @param ContainerInterface $container
     */
    public function __construct(Settings $settings, ContainerInterface $container)
    {
        $this->settings = $settings;
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'smartBannerAndroid',
                [$this, 'smartBannerAndroid'],
                ['needs_environment' => true, 'is_safe' => ['all']]
            ),
            new \Twig_SimpleFunction(
                'metaTagsSmartBanner',
                [$this, 'metaTagsSmartBanner'],
                ['needs_environment' => true, 'is_safe' => ['all']]
            ),
        ];
    }

    /**
     * Includes js script for smart banner
     *
     * @param \Twig_Environment $twig_Environment
     *
     * @return string
     */
    public function smartBannerAndroid(\Twig_Environment $twig_Environment)
    {
        $return = '';

        if ($this->settings->getDomainSetting('app_status_android') == 'A') {
            $return = $twig_Environment->render(
                '@Web/smartbanner/smartbanner-android.html.twig',
                [
                    'android_screen_image_path' => $this->container->getParameter('android.screen.image.path'),
                    'android_popup_title'       => $this->settings->getDomainSetting('app_popuptitle_android'),
                    'android_tagline'           => $this->settings->getDomainSetting('app_tagline_android'),
                    'android_price'             => $this->settings->getDomainSetting('app_price_android')
                ]
            );
        }

        return $return;
    }

    /**
     * Return apple store meta tag
     *
     * @param \Twig_Environment $twig_Environment
     *
     * @return string
     */
    public function metaTagsSmartBanner(\Twig_Environment $twig_Environment)
    {
        $return = '';

        if ($this->settings->getDomainSetting('app_status_ios') === 'A' || $this->settings->getDomainSetting('app_status_android') === 'A') {
            return $twig_Environment->render('@Web/smartbanner/metatags.html.twig',
                array(
                    'linkiOS' => $this->settings->getDomainSetting('app_storelink_ios'),
                    'linkAndroid' => $this->settings->getDomainSetting('app_storelink_android')
                ));
        }

        return $return;
    }

    /**
     * Return Google Play meta tag
     *
     * @param \Twig_Environment $twig_Environment
     *
     * @return string
     */
    public function metaTagAndroid(\Twig_Environment $twig_Environment)
    {
        $return = '';

        if ($this->settings->getDomainSetting('app_status_android') == 'A') {
            $return = $twig_Environment->render(
                '::blocks/metatag-android.html.twig',
                [
                    'link' => $this->settings->getDomainSetting('app_storelink_android')
                ]
            );
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'smartbanner';
    }
}
