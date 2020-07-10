<?php

namespace ArcaSolutions\BannersBundle\Twig\Extension;

use ArcaSolutions\BannersBundle\Entity\Banner;
use ArcaSolutions\BannersBundle\Entity\Helpers\BannerType;
use ArcaSolutions\BannersBundle\Repository\BannerRepository;
use ArcaSolutions\CoreBundle\Services\Settings;
use ArcaSolutions\DealBundle\Search\DealConfiguration;
use ArcaSolutions\ImageBundle\Entity\Image;
use ArcaSolutions\ListingBundle\Search\ListingConfiguration;
use ArcaSolutions\ReportsBundle\Services\ReportHandler;
use ArcaSolutions\SearchBundle\Entity\Elasticsearch\Category;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class BannersExtension
 *
 * Class with the functions used to call banners in twig file
 *
 * @package ArcaSolutions\BannersBundle\Twig\Extension
 */
class BannersExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * BannersExtension constructor.
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
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'bannerAnchor',
                [$this, 'anchor'],
                ['is_safe' => ['all']]
            ),
            new \Twig_SimpleFunction(
                'renderImageBanner',
                [$this, 'renderImageBanner'],
                ['is_safe' => ['all'], 'needs_environment' => true]
            ),
            new \Twig_SimpleFunction('getBanner', [$this, 'getBanner'], [
                'is_safe'           => ['html'],
            ]),
        ];
    }

    /**
     * Renders an image banner, taking into account its type, module and categories, as well as css classes and a custom twig template.
     *
     * @param \Twig_Environment $env
     * @param array $module These are the modules whose banners shall be randomly picked from
     * @param array $banners These are the banners whose will be rendered
     * @param array $category These are the categories whose banners shall be randomly picked from
     * @param string $template This is a path to a twig template which will render this specific banner
     * @return null|string
     * @throws \Exception
     */
    public function renderImageBanner(
        \Twig_Environment $env,
        $module = [],
        $banners = [],
        $category = [],
        $template = '::blocks/banners/imagebanner.html.twig'
    ) {
        if (!$this->container->get('modules')->isModuleAvailable('banner')) {
            return '';
        }

        $return = '';
        $counter = 0;
        $adClient = $this->settings->getDomainSetting('google_ads_client');
        $adStatus = $this->settings->getDomainSetting('google_ads_status');

        $bannerType = new BannerType();

        try {
            foreach ($banners as $banner) {
                if ($banner === 'google' && $adStatus == 'on' && !empty($adClient)) {

                    $adsType = $this->settings->getDomainSetting('google_ads_type');

                    switch ($adsType) {
                        case 1: $adsType = 'text';
                        break;
                        case 2: $adsType = 'image';
                        break;
                        case 3: $adsType = 'text_image';
                        break;
                    }

                    $counter++;
                    $return .= $env->render(
                        '::blocks/banners/adwords.html.twig',
                        [
                            'client'  => $adClient,
                            'type'    => $adsType,
                            'channel' => $this->settings->getDomainSetting('google_ads_channel'),
                            // These constants are in a config.yml file inside the bundle
                            'width'   => $this->container->getParameter('google.ad.width'),
                            'height'  => $this->container->getParameter('google.ad.height')
                        ]
                    );

                    continue;
                }

                if ($banner === 'sponsor-links' && $bannerEntity = $this->fetch($bannerType->banners[$banner], $module, $category)) {
                    $url = $this->anchor($bannerEntity);
                    $displayUrl = trim($bannerEntity->getDisplayUrl()) or $displayUrl = trim($bannerEntity->getDestinationUrl());
                    $counter++;
                    $return .= $env->render(
                        '::blocks/banners/sponsoredlink.html.twig',
                        [
                            'banner'     => $bannerEntity,
                            'url'        => $url,
                            'displayUrl' => $displayUrl,
                        ]
                    );

                    continue;
                }

                if ($bannerEntity = $this->fetch($bannerType->banners[$banner], $module, $category)) {
                    $context = [
                        'banner'     => $bannerEntity,
                        'bannerType' => $banner,
                    ];

                    if ($bannerEntity->getShowType() == BannerType::SHOWTYPE_IMAGE) {
                        $context['url'] = $this->anchor($bannerEntity);

                        if ($bannerEntity->getImageId() and $image = $bannerEntity->getImage()) {
                            /* @var Image $image */
                            $context['image_type'] = $image->getType();
                            $context['image'] = $this->container->get('imagehandler')->getPath($image);
                        } else {
                            throw new \Exception("No image found for IMAGE banner of id {$bannerEntity->getId()}");
                        }

                        $context['target'] = $bannerEntity->getTargetWindow() == BannerType::TARGET_NEW ? 'target="_blank"' : '';
                    }

                    $counter++;
                    $return .= $env->render(
                        $template,
                        $context
                    );
                }
            }
        } catch (\Exception $e) {
            $this->container->get('logger')->critical("Couldn't get banner.", ['Exception' => $e->getMessage()]);
        }

        $bannerReturn['counter'] = $counter;
        $bannerReturn['banners'] = $return;

        return $bannerReturn;
    }

    /**
     * Fetches a banner according to its $type, $modules and $categories.
     *
     * @param string $type A integer defined by constants on the BannerType class
     * @param string[] $modules An array of modules whose banners shall be randomly picked from
     * @param int[] $categories An array of categories whose banners shall be randomly picked from
     * @return Banner|null
     */
    public function fetch($type, $modules = [], $categories = [])
    {
        $return = null;

        is_array($modules) or $modules = (array)$modules;
        is_array($categories) or $categories = (array)$categories;

        if ($type
            and $doctrine = $this->container->get('doctrine')
            and $manager = $doctrine->getManager()
            and $levelRepository = $manager->getRepository('BannersBundle:Bannerlevel')
            and $level = $levelRepository->getLevelActive($type)
        ) {
            $parameterHandler = $this->container->get('search.parameters');

            $categorizedSections = [];

            if ($modules or $modules = $parameterHandler->getModules()) {
                $categorizedSections = array_fill_keys($modules, null);
            }

            if ($categories or $categories = $parameterHandler->getCategories()) {

                /* @var $category Category */
                while ($category = array_pop($categories)) {
                    $module = $category->getModule();
                    $categoryId = preg_replace("/[^\d]/", '', $category->getId());

                    $categorizedSections[$module][] = $categoryId;

                    if ($module == ListingConfiguration::$elasticType) {
                        $categorizedSections[DealConfiguration::$elasticType][] = $categoryId;
                    }
                }
            }

            /* @var $repository BannerRepository */
            $repository = $doctrine->getRepository('BannersBundle:Banner');

            /* ModStores Hooks */
            HookFire("bannerextension_after_setup_bannerrepository", [
                'level'               => &$level,
                'repository'          => &$repository,
                'categorizedSections' => &$categorizedSections,
            ]);

            /* @var $banner Banner */
            if ($banner = $repository->getBanner($level, $categorizedSections)) {
                $this->container->get('reporthandler')->addBannerReport($banner->getId(), ReportHandler::BANNER_VIEW);
                $return = $banner;
            }
        }

        return $return;
    }

    /**
     * Alias for create a banner anchor
     *
     * @param Banner $banner
     * @return string
     */
    public function anchor(Banner $banner)
    {
        if (!$this->container->get('modules')->isModuleAvailable('banner')) {
            return '';
        }

        $anchor = 'javascript:void(0);';

        if ($banner and $banner->getDestinationUrl()) {
            $anchor = $this->container->get('router')
                ->generate(
                    'banners_reports',
                    ['bannerId' => $this->container->get('url_encryption')->encrypt($banner->getId())]
                );
        }

        return $anchor;
    }

    public function getBanner($bannerType)
    {
        if(!empty($bannerType) && $bannerType !== 'empty') {
            $bannerBlock = $this->container->get('twig')->render('::widgets/banners/banner.html.twig', [
                'content' => [
                    'bannerType' => $bannerType,
                    'banners'    => $bannerType === 'skyscraper' ? $bannerType : [
                        $bannerType,
                        $bannerType
                    ],
                    'isWide'     => $bannerType === 'skyscraper' ? 'true' : 'false',
                ]
            ]);
        } else {
            $bannerBlock = '';
        }

        return $bannerBlock;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'banner';
    }
}
