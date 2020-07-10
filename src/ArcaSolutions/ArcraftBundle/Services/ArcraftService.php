<?php

namespace ArcaSolutions\ArcraftBundle\Services;

use ArcaSolutions\ArticleBundle\ArticleItemDetail;
use ArcaSolutions\ArticleBundle\Sample\ArticleSample;
use ArcaSolutions\BlogBundle\Entity\Post;
use ArcaSolutions\ClassifiedBundle\ClassifiedItemDetail;
use ArcaSolutions\ClassifiedBundle\Sample\ClassifiedSample;
use ArcaSolutions\EventBundle\EventItemDetail;
use ArcaSolutions\EventBundle\Sample\EventSample;
use ArcaSolutions\ImageBundle\Sample\GalleryImageSample;
use ArcaSolutions\ListingBundle\ListingItemDetail;
use ArcaSolutions\ListingBundle\Sample\ListingSample;
use ArcaSolutions\WebBundle\Repository\ReviewRepository;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use Doctrine\ORM\EntityManagerInterface;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;
use Ivory\GoogleMap\Helper\Builder\MapHelperBuilder;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Overlay\Icon;
use Ivory\GoogleMap\Overlay\Marker;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ArcaSolutions\WebBundle\Form\Type\EnquireType;
use ArcaSolutions\SearchBundle\Entity\Elasticsearch\Category;
use ArcaSolutions\SearchBundle\Services\ParameterHandler;

class ArcraftService
{
    /** @var EntityManagerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getTemplateByName($widgetName, $type)
    {
        switch ($widgetName) {
            case 'latest-members':
                $template = $this->getLatestMembersTemplate()[$type];
                break;
            case 'categories':
                $template = $this->getCategoriesTemplate()[$type];
                break;
            case 'recent-reviews':
                $template = $this->getRecentReviewsTemplate()[$type];
                break;
            case 'video-gallery':
                $template = $this->getVideoGalleryTemplate()[$type];
                break;
            case 'details':
                $template = $this->getDetailsTemplate($type);
                break;
            case 'header':
                $template = $this->getHeaderTemplate()[$type];
                break;
            case 'footer':
                $template = $this->getFooterTemplate()[$type];
                break;
            case 'search-boxes':
                $template = $this->getSearchBoxesTemplate()[$type];
                break;
            case 'search-bars':
                $template = $this->getSearchBarsTemplate()[$type];
                break;
            case 'faq':
                $template = $this->getFaqTemplate()[$type];
                break;
            case 'pricing-plans':
                $template = $this->getPricingPlansTemplate()[$type];
                break;
            case 'events':
                $template = $this->getEventTemplate()[$type];
                break;
            case 'contact-form':
                $template = $this->getContactFormTemplate()[$type];
                break;
            case 'newsletter':
                $template = $this->getSignupForOurNewsletterTemplate()[$type];
                break;
            case 'ads':
                $template = $this->getAdsFormTemplate()[$type];
                break;
            case 'download-our-apps':
                $template = $this->getDownloadOurAppsBarTemplate()[$type];
                break;
            case 'lead-form':
                $template = $this->getLeadFormTemplate()[$type];
                break;
            case 'social-network-bar':
                $template = $this->getSocialNetworkBarTemplate()[$type];
                break;
            case 'contact-information-bar':
                $template = $this->getContactInformationBarTemplate()[$type];
                break;
            case 'call-to-action':
                $template = $this->getCallToActionTemplate()[$type];
                break;
            case 'slider':
                $template = $this->getSliderTemplate()[$type];
                break;
            case 'locations':
                $template = $this->getLocationsTemplate()[$type];
                break;
            case 'cards':
                $template = $this->getCardsTemplate()[$type];
                break;
            case 'article':
                $template = $this->getArticleTemplate()[$type];
                break;
            default :
                $template = null;
                break;
        }

        return $template;
    }

    public function getLatestMembersTemplate()
    {
        $template = [
            1 => [
                '::widgets/common/recent-members.html.twig' => [
                    'content' => [
                        'labelRecentMembers' => 'Recent Members',
                        'backgroundColor' => 'base',
                    ],
                ]
            ]
        ];

        return $template;
    }

    public function getCategoriesTemplate()
    {
        $template = [
            1 => [
                '::widgets/category/browse-by-category.html.twig' => [
                    'content' => [
                        'labelBrowseByCat' => 'Browse by category ',
                        'labelMoreCat' => 'more categories',
                        'limit' => null,
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
            2 => [
                '::widgets/category/browse-by-category-featured.html.twig' => [
                    'content' => [
                        'labelFeaturedCategories' => 'Featured categories with image (Type 2)',
                        'labelAllCategories' => 'All categories',
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
            3 => [
                '::widgets/category/list-of-categories.html.twig' => [
                    'content' => [
                        'labelAllCategories' => 'All Categories',
                        'labelCategories' => 'Categories',
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
            4 => $this->getAllCategoriesTemplate(),
            5 => [
                '::widgets/category/browse-by-category-with-icons.html.twig' => [
                    'content' => [
                        'labelBrowseByCat' => 'Browse by category ',
                        'labelMoreCat' => 'more categories',
                        'limit' => null,
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
            6 => [
                '::widgets/category/browse-by-category-list.html.twig' => [
                    'content' => [
                        'placeholderTitle' => [
                            'value' => 'Trending Topics',
                            'label' => 'Title'
                        ],
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
        ];

        return $template;
    }

    public function getAllCategoriesTemplate()
    {
        $this->container->get('widget.service')->setModule('listing');

        $result = $this->container->get('search.repository.category')->findCategoriesWithItens('listing');

        $template = [
            '::widgets/category/all-categories.html.twig' => [
                'categories' => $result,
                'content' => [
                    'labelExploreAllCategories' => 'Explore All Categories',
                    'backgroundColor' => 'base',
                ],
            ]
        ];

        return $template;
    }

    public function getRecentReviewsTemplate()
    {
        $template = [
            1 => [
                '::widgets/review/recent-reviews.html.twig' => [
                    'content' => [
                        'labelRecentReviews' => 'Recent Reviews',
                        'labelCreateYourProfile' => 'Create your profile today and write a review. It’s free!',
                        'backgroundColor' => 'base',
                        'customBanners' => 'square',
                    ],
                ]
            ]
        ];

        return $template;
    }

    public function getVideoGalleryTemplate()
    {
        $template = [
            1 => [
                '::widgets/common/video-gallery.html.twig' => [
                    'content' => [
                        'labelVideoGallery' => 'Video Gallery',
                        'videos' => [
                            [
                                'url' => 'http://www.youtube.com/v/EGShYZRevqc',
                                'description' => 'Test',
                                'imageUrl' => 'https://img.youtube.com/vi/EGShYZRevqc/0.jpg',
                            ],
                            [
                                'url' => 'http://www.youtube.com/v/yL_MiAJsluc',
                                'description' => '',
                                'imageUrl' => 'https://img.youtube.com/vi/yL_MiAJsluc/0.jpg'
                            ]
                        ],
                        'backgroundColor' => 'base',
                    ],
                ]
            ]
        ];

        return $template;
    }

    public function getDetailsTemplate($type)
    {
        switch ($type) {
            case 1 :
                return $this->getListingDetailTemplate();
            case 2 :
                return $this->getEventDetailTemplate();
            case 3 :
                return $this->getClassifiedDetailTemplate();
            case 4 :
                return $this->getArticleDetailTemplate();
            case 5 :
                return $this->getDealDetailTemplate();
            case 6 :
                return $this->getBlogDetailTemplate();
            default :
                return null;
        }
    }

    public function getListingDetailTemplate()
    {
        $item = new ListingSample('10', $this->container->get('translator'), $this->container->get('doctrine'));
        $listingItemDetail = new ListingItemDetail($this->container, $item);
        /* gets listing's deal */
        $deals = [];
        for ($i = 0; $i < $listingItemDetail->getLevel()->dealCount; $i++) {
            $deals[] = $item->getDeals();
        }

        /* Validates if listing has the review active */
        $reviews_active = $this->container->get('doctrine')->getRepository('WebBundle:Setting')
            ->getSetting('review_listing_enabled');

        $editorChoice = $this->container->get('doctrine')->getRepository('ListingBundle:EditorChoice')->findby([
            'available' => 1,
        ]);

        $map = null;
        /* checks if item has latitude and longitude to show the map */
        /* checks if item has latitude and longitude to show the map */
        if ($item->getLatitude() && $item->getLongitude() && $this->container->get('settings')->getDomainSetting('google_map_status') == 'on'
            and $googleMapsKey = $this->container->get('settings')->getDomainSetting('google_api_key')) {
            /* sets map */
            $map = new Map();
            $map->setMapOption('scrollwheel', false);
            $map->setStylesheetOptions([
                'width' => '100%',
                'height' => '255px',
            ]);
            $domain = $this->container->get('multi_domain.information')->getId();
            $theme = lcfirst($this->container->get('theme.service')->getSelectedTheme()->getTitle());
            $defaultIconPath = 'assets/' . $theme . '/icons/';
            $customIconPath = 'custom/domain_' . $domain . '/theme/' . $theme . '/icons/';

            $mapZoom = ($item->getMapZoom() ? $item->getMapZoom() : 15);
            $map->setMapOption('zoom', $mapZoom);

            /* sets the item's location the center of the map */
            $map->setCenter(new Coordinate((float)$item->getLatitude(), (float)$item->getLongitude()));

            $marker = new Marker(new Coordinate((float)$item->getLatitude(), (float)$item->getLongitude(), true));

            /* mark item in map */
            $marker->setOptions([
                'clickable' => false,
                'flat' => true,
            ]);

            if (file_exists($customIconPath . 'listing.svg')) {
                $iconPath = $customIconPath . 'listing.svg';
            } else {
                $iconPath = $defaultIconPath . 'listing.svg';
            }

            $marker->setIcon(new Icon($this->container->get('request')->getSchemeAndHttpHost() . '/' . $iconPath));

            $map->getOverlayManager()->addMarker($marker);

            $mapJSHelper = MapHelperBuilder::create()->build()->renderJavascript($map);
            $apiHelper = ApiHelperBuilder::create()->setKey($googleMapsKey)->build()->render([$map]);

            $jsHandler = $this->container->get('javascripthandler');
            $jsHandler->addJSBlock('::js/summary/map.html.twig');
            $jsHandler->addTwigParameter('mapJSHelper', $mapJSHelper);
            $jsHandler->addTwigParameter('apiHelper', $apiHelper);
        }

        $template = [
            '::widgets/listing/detail-content.html.twig' => [
                'item' => $item,
                'classifieds' => $item->getClassifieds(),
                'level' => $listingItemDetail->getLevel(),
                'gallery' => $item->getGallery(--$listingItemDetail->getLevel()->imageCount),
                'reviews_active' => $reviews_active,
                'reviewsPaginated' =>
                    [
                        'reviews' => $item->getReviews(),
                        'total' => $item->getReviewCount()[1],
                        'pageCount' => $item->getReviewCount()[1] / ReviewRepository::REVIEWS_PER_PAGE
                    ],
                'deals' => $deals,
                'badges' => $editorChoice,
                'isSample' => true
            ]
        ];

        return $template;
    }

    public function getEventDetailTemplate()
    {
        $item = new EventSample('10', $this->container->get('translator'), $this->container->get('doctrine'));

        /* normalizes item to validate detail */
        $eventItemDetail = new EventItemDetail($this->container, $item);

        $map = null;
        /* checks if item has latitude and longitude to show the map */
        /* checks if item has latitude and longitude to show the map */
        if ($item->getLatitude() && $item->getLongitude() && $this->container->get('settings')->getDomainSetting('google_map_status') == 'on'
            and $googleMapsKey = $this->container->get('settings')->getDomainSetting('google_api_key')) {
            /* sets map */
            $map = new Map();
            $map->setMapOption('scrollwheel', false);
            $map->setStylesheetOptions([
                'width' => '100%',
                'height' => '255px',
            ]);

            $mapZoom = ($item->getMapZoom() ? $item->getMapZoom() : 15);
            $map->setMapOption('zoom', $mapZoom);

            /* sets the item's location the center of the map */
            $map->setCenter(new Coordinate((float)$item->getLatitude(), (float)$item->getLongitude()));

            $marker = new Marker(new Coordinate((float)$item->getLatitude(), (float)$item->getLongitude(), true));

            /* mark item in map */
            $marker->setOptions([
                'clickable' => false,
                'flat' => true,
            ]);

            $map->getOverlayManager()->addMarker($marker);

            $mapJSHelper = MapHelperBuilder::create()->build()->renderJavascript($map);
            $apiHelper = ApiHelperBuilder::create()->setKey($googleMapsKey)->build()->render([$map]);

            $jsHandler = $this->container->get('javascripthandler');
            $jsHandler->addJSBlock('::js/summary/map.html.twig');
            $jsHandler->addTwigParameter('mapJSHelper', $mapJSHelper);
            $jsHandler->addTwigParameter('apiHelper', $apiHelper);
        }

        $template = [
            '::widgets/event/detail-content.html.twig' => [
                'item' => $item,
                'level' => $eventItemDetail->getLevel(),
                'categories' => $item->getCategories(),
                'gallery' => $item->getGallery(--$eventItemDetail->getLevel()->imageCount),
                'map' => $map,
                'locationsIDs' => $item->getFakeLocationsIds(),
                'locationsObjs' => $item->getLocationObjects(),
                'dateFilter' => $this->container->get('filter.date'),
                'isSample' => true
            ]
        ];

        return $template;
    }

    public function getClassifiedDetailTemplate()
    {
        $item = new ClassifiedSample('10', $this->container->get('translator'), $this->container->get('doctrine'));

        /* normalizes item to validate detail */
        $classifiedItemDetail = new ClassifiedItemDetail($this->container, $item);

        $map = null;
        /* checks if item has latitude and longitude to show the map */
        if ($item->getLatitude() && $item->getLongitude() && $this->container->get('settings')->getDomainSetting('google_map_status') == 'on'
            and $googleMapsKey = $this->container->get('settings')->getDomainSetting('google_api_key')) {
            /* sets map */
            $map = new Map();
            $map->setMapOption('scrollwheel', false);
            $map->setStylesheetOptions([
                'width' => '100%',
                'height' => '255px',
            ]);

            $mapZoom = ($item->getMapZoom() ? $item->getMapZoom() : 15);
            $map->setMapOption('zoom', $mapZoom);

            /* sets the item's location the center of the map */
            $map->setCenter(new Coordinate((float)$item->getLatitude(), (float)$item->getLongitude()));


            $marker = new Marker(new Coordinate((float)$item->getLatitude(), (float)$item->getLongitude(), true));

            /* mark item in map */
            $marker->setOptions([
                'clickable' => false,
                'flat' => true,
            ]);

            $map->getOverlayManager()->addMarker($marker);

            $mapJSHelper = MapHelperBuilder::create()->build()->renderJavascript($map);
            $apiHelper = ApiHelperBuilder::create()->setKey($googleMapsKey)->build()->render([$map]);

            $jsHandler = $this->container->get('javascripthandler');
            $jsHandler->addJSBlock('::js/summary/map.html.twig');
            $jsHandler->addTwigParameter('mapJSHelper', $mapJSHelper);
            $jsHandler->addTwigParameter('apiHelper', $apiHelper);
        }

        $template = [
            '::widgets/classified/detail-content.html.twig' => [
                'item' => $item,
                'level' => $classifiedItemDetail->getLevel(),
                'map' => $map,
                'gallery' => $item->getGallery(--$classifiedItemDetail->getLevel()->imageCount),
                'categories' => $item->getCategories(),
                'locationsIDs' => $item->getFakeLocationsIds(),
                'locationsObjs' => $item->getLocationObjects(),
                'isSample' => true
            ]
        ];

        return $template;
    }

    public function getArticleDetailTemplate()
    {
        $item = new ArticleSample('50', $this->container->get('translator'), $this->container->get('doctrine'));
        $articleItemDetail = new ArticleItemDetail($this->container, $item);

        $template = [
            '::widgets/article/detail-content.html.twig' => [
                'item' => $item,
                'level' => $articleItemDetail->getLevel(),
                'gallery' => $item->getGallery($articleItemDetail->getLevel()->imageCount),
                'categories' => $item->getCategories(),
                'isSample' => true
            ]
        ];

        return $template;
    }

    public function getBlogDetailTemplate()
    {
        $categoryIds = $categories = $categoriesFeatured = [];

        $content = new \stdClass();
        $content->custom = new \stdClass();
        $content->custom->order1 = 'popular';
        $content->custom->order2 = 'random';
        $popularPosts = $this->container->get('search.block')->getCards(ParameterHandler::MODULE_BLOG, 5, $content);
        $this->container->get('widget.service')->setModule(ParameterHandler::MODULE_BLOG);

        $item = new Post();
        $item->setEntered(new \DateTime('2019-03-18 11:04:18'));
        $item->setUpdated(new \DateTime('2010-01-14 08:37:01'));
        $item->setTitle('Beauty Tips');
        $item->setSeoTitle('Beauty Tips');
        $item->setFriendlyUrl('beauty-tips');
        $item->setContent('<p>This is example text for the description text. Hey there where ya goin&#39;, not exactly knowin&#39;, who says you have to call just one place home. He&#39;s goin&#39; everywhere, B.J. McKay and his best friend Bear. He just keeps on movin&#39;, ladies keep improvin&#39;, every day is better than the last. New dreams and better scenes, and best of all I don&#39;t pay property tax. Rollin&#39; down to Dallas, who&#39;s providin&#39; my palace, off to New Orleans or who knows where. Places new and ladies, too, I&#39;m B.J. McKay and this is my best friend Bear.</p><p>Children of the sun, see your time has just begun, searching for your ways, through adventures every day. Every day and night, with the condor in flight, with all your friends in tow, you search for the Cities of Gold. Ah-ah-ah-ah-ah... wishing for The Cities of Gold. Ah-ah-ah-ah-ah... some day we will find The Cities of Gold. Do-do-do-do ah-ah-ah, do-do-do-do, Cities of Gold. Do-do-do-do, Cities of Gold. Ah-ah-ah-ah-ah... some day we will find The Cities of Gold.</p>');
        $item->setStatus('A');
        $item->setNumberViews(61);
        $item->setCategories([4, 3, 1]);

        foreach ($item->getCategories() as $blogCategoryId) {
            $categoryIds[] = Category::create()->setId($blogCategoryId)->setModule(ParameterHandler::MODULE_BLOG);
        }

        $categoriesFeatured = $this->container->get('search.repository.category')->findCategoriesWithItens(ParameterHandler::MODULE_BLOG, true);

        $template = [
            '::widgets/blog/detail-content.html.twig' => [
                'item' => $item,
                'isSample' => true,
                'categories' => $categories,
                'categoryIds' => $categoryIds,
                'categoriesFeatured' => $categoriesFeatured,
                'popularPosts' => $popularPosts,
            ]
        ];

        return $template;
    }

    public function getDealDetailTemplate()
    {
        $listing = new ListingSample('10', $this->container->get('translator'), $this->container->get('doctrine'));
        $listingItemDetail = new ListingItemDetail($this->container, $listing);

        /* Validates if listing has the review active */
        $reviews_active = $this->container->get('doctrine')->getRepository('WebBundle:Setting')
            ->getSetting('review_listing_enabled');

        $reviews_total = $this->container->get('doctrine')->getRepository('WebBundle:Review')
            ->getTotalByItemId($listing->getId(), 'listing');

        $map = null;
        /* checks if item has latitude and longitude to show the map */
        /* checks if item has latitude and longitude to show the map */
        if ($listing->getLatitude() && $listing->getLongitude() && $this->container->get('settings')->getDomainSetting('google_map_status') == 'on'
            and $googleMapsKey = $this->container->get('settings')->getDomainSetting('google_api_key')) {
            /* sets map */
            $map = new Map();
            $map->setMapOption('scrollwheel', false);
            $map->setStylesheetOptions([
                'width' => '100%',
                'height' => '255px',
            ]);

            $mapZoom = ($listing->getMapZoom() ? $listing->getMapZoom() : 15);
            $map->setMapOption('zoom', $mapZoom);

            /* sets the item's location the center of the map */
            $map->setCenter(new Coordinate((float)$listing->getLatitude(), (float)$listing->getLongitude()));

            $marker = new Marker(new Coordinate((float)$listing->getLatitude(), (float)$listing->getLongitude(), true));

            /* mark item in map */
            $marker->setOptions([
                'clickable' => false,
                'flat' => true,
            ]);

            $map->getOverlayManager()->addMarker($marker);

            $mapJSHelper = MapHelperBuilder::create()->build()->renderJavascript($map);
            $apiHelper = ApiHelperBuilder::create()->setKey($googleMapsKey)->build()->render([$map]);

            $jsHandler = $this->container->get('javascripthandler');
            $jsHandler->addJSBlock('::js/summary/map.html.twig');
            $jsHandler->addTwigParameter('mapJSHelper', $mapJSHelper);
            $jsHandler->addTwigParameter('apiHelper', $apiHelper);
        }

        $item = $listing->getDeals();

        $item->setListing($listing);

        $template = [
            '::widgets/deal/detail-content.html.twig' => [
                'item' => $item,
                'listing_level' => $listingItemDetail->getLevel(),
                'gallery' => new GalleryImageSample(640, 480, $this->container->get('translator')->trans('Placeholder')),
                'categories' => $listing->getCategories(),
                'reviews_active' => $reviews_active,
                'reviews_total' => $reviews_total[1],
                'map' => $map,
                'isSample' => true
            ]
        ];

        return $template;
    }

    public function getHeaderTemplate()
    {
        $template = [
            1 => [
                '::widgets/navigation/header-type1.html.twig' => [
                    'content' => [
                        'labelDashboard' => 'Dashboard',
                        'labelProfile' => 'Profile',
                        'labelFaq' => 'Faq',
                        'labelAccountPref' => 'Settings',
                        'labelLogOff' => 'Log Off',
                        'labelListWithUs' => 'List with Us',
                        'labelSignIn' => 'Sign In',
                        'labelMore' => 'More',
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
            2 => [
                '::widgets/navigation/header-type2.html.twig' => [
                    'content' => [
                        'labelDashboard' => 'Dashboard',
                        'labelProfile' => 'Profile',
                        'labelFaq' => 'Faq',
                        'labelAccountPref' => 'Settings',
                        'labelLogOff' => 'Log Off',
                        'labelListWithUs' => 'List with Us',
                        'labelSignIn' => 'Sign In',
                        'labelMore' => 'More',
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
            3 => [
                '::widgets/navigation/header-type3.html.twig' => [
                    'content' => [
                        'labelDashboard' => 'Dashboard',
                        'labelProfile' => 'Profile',
                        'labelFaq' => 'Faq',
                        'labelAccountPref' => 'Settings',
                        'labelLogOff' => 'Log Off',
                        'labelListWithUs' => 'List with Us',
                        'labelSignIn' => 'Sign In',
                        'labelMore' => 'More',
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
            4 => [
                '::widgets/navigation/header-type4.html.twig' => [
                    'content' => [
                        'labelDashboard' => 'Dashboard',
                        'labelProfile' => 'Profile',
                        'labelFaq' => 'Faq',
                        'labelAccountPref' => 'Settings',
                        'labelLogOff' => 'Log Off',
                        'labelListWithUs' => 'List with Us',
                        'labelSignIn' => 'Sign In',
                        'labelMore' => 'More',
                        'backgroundColor' => 'base',
                    ],
                ]
            ]
        ];

        return $template;
    }

    public function getFooterTemplate()
    {
        $template = [
            1 => [
                '::widgets/navigation/footer-type1.html.twig' => [
                    'content' => [
                        'labelSiteContent' => 'Site Content',
                        'labelContactUs' => 'Contact Us',
                        'labelFollowUs' => 'Follow Us',
                        'labelCopyrightText' => 'Copyright 2019 - All rights reserved.',
                        'playStoreLabel' => 'Get it on the Google Play',
                        'AppStoreLabel' => 'Download on the App Store',
                        'linkPlayStore' => 'https://play.google.com/store/apps/details?id=com.arcasolutions',
                        'linkAppleStore' => 'https://itunes.apple.com/br/app/edirectory/id337135168?mt=8',
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
            2 => [
                '::widgets/navigation/footer-type2.html.twig' => [
                    'content' => [
                        'labelSiteContent' => 'Site Content',
                        'labelContactUs' => 'Contact Us',
                        'labelCopyrightText' => 'Copyright 2019 - All rights reserved.',
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
            3 => [
                '::widgets/navigation/footer-type3.html.twig' => [
                    'content' => [
                        'labelCopyrightText' => 'Copyright 2019 - All rights reserved.',
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
            4 => [
                '::widgets/navigation/footer-type4.html.twig' => [
                    'content' => [
                        'labelContactUs' => 'Contact Us',
                        'labelCopyrightText' => 'Copyright 2019 - All rights reserved.',
                        'datainfoSignupFor' => 'Sign up for our newsletter',
                        'datainfoNewsletterDesc' => 'Sign up for our monthly newsletter. No spams, just product updates.',
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
        ];

        return $template;
    }


    public function getSearchBoxesTemplate()
    {
        $template = [
            1 => [
                '::widgets/slider/slider-searchbox.html.twig' => [
                    'content' => [
                        'labelStartYourSearch' => 'Start your search here',
                        'labelWhatLookingFor' => 'What are you looking for?',
                        'placeholderSearchKeyword' => [
                            'value' => 'Food, service, hotel...',
                            'label' => 'Placeholder for search by keyword field'
                        ],
                        'placeholderSearchLocation' => [
                            'value' => 'Enter location...',
                            'label' => 'Placeholder for search by location field'
                        ],
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
        ];

        return $template;
    }

    public function getSearchBarsTemplate()
    {
        $template = [
            1 => [
                '::widgets/searchbox/searchbox.html.twig' => [
                    'content' => [
                        'placeholderSearchKeyword' => [
                            'value' => 'Food, service, hotel...',
                            'label' => 'Placeholder for search by keyword field'
                        ],
                        'placeholderSearchLocation' => [
                            'value' => 'Enter location...',
                            'label' => 'Placeholder for search by location field'
                        ],
                        'backgroundColor' => 'base',
                    ],
                ],
            ],
        ];

        return $template;
    }

    public function getFaqTemplate()
    {
        $template = [
            1 => [
                '::widgets/faq/faq.html.twig' => [
                    'content' => [
                        'labelHowCanIHelp' => 'How can we help you?',
                        'labelParagraph' => 'Here you will type a awesome text!',
                        'labelDidYouNotFind' => 'Did you not find your answer? Contact us.',
                        'backgroundColor' => 'base',
                    ],
                ]
            ]
        ];

        return $template;
    }

    public function getPricingPlansTemplate()
    {
        /* Sets the modules and active tab */
        $modulesObj = $this->container->get('modules');
        $modules = ['listing'];

        foreach ($modulesObj->getAvailableModulesLevel() as $module => $available) {
            if ($available && !in_array($module, ['listing', 'promotion', 'blog'])) {
                $modules[] = $module;
            }
        }

        $template = [
            1 => [
                /* Listing, Event and Classified */
                '::widgets/advertise/main-modules-prices.html.twig' => [
                    'content' => [
                        'labelModuleOptions' => $this->container->get('widget.service')->getModule() ? ucfirst($this->container->get('widget.service')->getModule()) . ' Options' : 'Listing Options',
                        'labelDescription' => '',
                        'module' => $this->container->get('widget.service')->getModule() ?: 'listing',
                        'backgroundColor' => 'base',
                    ]
                ]
            ],
            2 => [
                '::widgets/banners/banner-prices.html.twig' => [
                    'content' => [
                        'labelModuleOptions' => 'Banner Options',
                        'labelDescription' => '',
                        'module' => 'banner',
                        'backgroundColor' => 'base',
                    ]
                ]
            ],
            3 => [
                '::widgets/article/article-prices.html.twig' => [
                    'content' => [
                        'labelModuleOptions' => 'Article Options',
                        'labelDescription' => '',
                        'module' => 'article',
                        'backgroundColor' => 'base',
                    ]
                ]
            ],
            4 => [
                '::widgets/advertise/pricing-plans.html.twig' => [
                    'content' => [
                        'labelPromote' => 'Promote your business today!',
                        'labelPremiumFeatures' => 'Get yourself out there with premium features!',
                        'backgroundColor' => 'base',
                    ],
                    'modules' => $modules
                ]
            ]
        ];

        return $template;
    }

    public function getEventTemplate()
    {
        $template = [
            1 => [
                '::widgets/event/upcoming-events-bar.html.twig' => [
                    'content' => [
                        'labelUpcomingEvents' => 'Upcoming Events',
                        'limit' => 8,
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
            2 => [
                '::widgets/event/upcoming-events-carousel.html.twig' => [
                    'content' => [
                        'labelUpcomingEvents' => 'Upcoming Events',
                        'labelMoreEvents' => 'more events',
                        'backgroundColor' => 'base',
                    ],
                ]
            ],
            3 => [
                '::widgets/event/events-calendar.html.twig' => [
                    'content' => [
                        'labelCalendar' => 'Events Calendar',
                        'backgroundColor' => 'base',
                    ],
                ]
            ]
        ];

        return $template;
    }

    public function getContactFormTemplate()
    {
        /* Creates a new form */
        $form = $this->container->get('form.factory')->create(EnquireType::class);

        $customForm = $this->container->get('web.json_form_builder');
        $customForm->generate($form, 'save.json', 'contact');

        /* Get twig */
        $twig = $this->container->get('twig');

        /* Settings Map */
        if ($this->container->get('settings')->getDomainSetting('google_map_status') == 'on'
            and $contact_latitude = $this->container->get('settings')->getDomainSetting('contact_latitude')
            and $contact_longitude = $this->container->get('settings')->getDomainSetting('contact_longitude')
            and $googleMapsKey = $this->container->get('settings')->getDomainSetting('google_api_key')
        ) {
            /* New map defined */
            $map = new Map();
            $map->setStylesheetOptions([
                'width' => '98%',
                'height' => '240px',
            ]);

            $mapZoom = ($this->container->get('settings')->getDomainSetting('contact_mapzoom') ? $this->container->get('settings')->getDomainSetting('contact_mapzoom') : 15);
            $map->setMapOption('zoom', (int)$mapZoom);

            /* sets the item's location the center of the map */
            $map->setCenter(new Coordinate((float)$contact_latitude, (float)$contact_longitude));

            $marker = new Marker(new Coordinate((float)$contact_latitude, (float)$contact_longitude, true));

            /* mark item in map */
            $marker->setOptions([
                'clickable' => false,
                'flat' => true,
            ]);

            $map->getOverlayManager()->addMarker($marker);


            $mapJSHelper = MapHelperBuilder::create()->build()->renderJavascript($map);
            $apiHelper = ApiHelperBuilder::create()->setKey($googleMapsKey)->build()->render([$map]);

            $jsHandler = $this->container->get('javascripthandler');
            $jsHandler->addJSBlock('::js/summary/map.html.twig');
            $jsHandler->addTwigParameter('mapJSHelper', $mapJSHelper);
            $jsHandler->addTwigParameter('apiHelper', $apiHelper);
            $twig->addGlobal('map', $map);
        }

        $this->container->get('widget.service')->setModule('');

        $contact = [
            'company' => $this->container->get('settings')->getDomainSetting('contact_company'),
            'address' => $this->container->get('settings')->getDomainSetting('contact_address'),
            'zipcode' => $this->container->get('settings')->getDomainSetting('contact_zipcode'),
            'country' => $this->container->get('settings')->getDomainSetting('contact_country'),
            'state' => $this->container->get('settings')->getDomainSetting('contact_state'),
            'city' => $this->container->get('settings')->getDomainSetting('contact_city'),
            'phone' => $this->container->get('settings')->getDomainSetting('contact_phone'),
            'email' => $this->container->get('settings')->getDomainSetting('contact_email'),
            'mapzoom' => $this->container->get('settings')->getDomainSetting('contact_mapzoom'),
        ];

        $template = [
            '1' => [
                '::widgets/contactus/contact-form.html.twig' => [
                    'content' => [
                        'labelContactUs' => 'Contact Us',
                        'labelNeedHelp' => 'Need help with something? Get in touch with us and we\'ll do our best to answer your question as soon as possible.',
                    ],
                    'form' => $form->createView(),
                    'contact' => $contact,
                ]
            ]
        ];

        return $template;
    }


    public function getSignupForOurNewsletterTemplate()
    {
        $template = [
            1 => [
                '::widgets/newsletter/signup-for-our-newsletter.html.twig' => [
                    'content' => [
                        'labelSignupFor' => 'Sign up for our newsletter',
                        'labelNewsletterDesc' => 'Sign up for our monthly newsletter. No spams, just product updates.',
                        'backgroundColor' => 'base',
                    ],
                ],
            ],
        ];

        return $template;
    }

    public function getAdsFormTemplate()
    {
        $template = [
            1 => [
                '::widgets/banners/banner.html.twig' => [
                    'content' => [
                        'bannerType' => 'leaderboard',
                        'isWide' => 'false',
                        'banners' => [
                            1 => 'leaderboard'
                        ],
                        'backgroundColor' => 'base',
                    ]
                ]
            ],
            2 => [
                '::widgets/banners/banner.html.twig' => [
                    'content' => [
                        'bannerType' => 'large-mobile',
                        'isWide' => 'false',
                        'banners' => [
                            1 => 'largebanner',
                            2 => 'largebanner',
                            3 => 'largebanner',
                        ],
                        'backgroundColor' => 'base',
                    ]
                ]
            ],
            3 => [
                '::widgets/banners/banner.html.twig' => [
                    'content' => [
                        'bannerType' => 'large-mobile',
                        'isWide' => 'false',
                        'banners' => [
                            1 => 'largebanner',
                            2 => 'google',
                            3 => 'sponsor-links',
                        ],
                        'backgroundColor' => 'base',
                    ]
                ]
            ],
            4 => [
                '::widgets/banners/banner.html.twig' => [
                    'content' => [
                        'bannerType' => 'square',
                        'isWide' => 'false',
                        'banners' => [
                            1 => 'square'
                        ],
                        'backgroundColor' => 'base',
                    ]
                ]
            ],
            5 => [
                '::widgets/banners/banner.html.twig' => [
                    'content' => [
                        'bannerType' => 'skyscraper',
                        'isWide' => 'true',
                        'banners' => [
                            1 => 'skyscraper'
                        ],
                        'backgroundColor' => 'base',
                    ]
                ]
            ]
        ];

        return $template;
    }

    public function getDownloadOurAppsBarTemplate()
    {
        $template = [
            1 => [
                '::widgets/common/download-our-apps.html.twig' => [
                    'content' => [
                        'labelAvailablePlayStore' => 'Available on the Play Store',
                        'labelDownloadOurApp' => 'Download our App',
                        'labelAvailablePlataforms' => 'Available for iPhone and Android.',
                        'labelAvailableAppleStore' => 'Available on the Apple Store',
                        'linkPlayStore' => 'https://play.google.com/store/apps/details?id=com.arcasolutions',
                        'linkAppleStore' => 'https://itunes.apple.com/br/app/edirectory/id337135168?mt=8',
                        'checkboxOpenWindow' => '',
                        'backgroundColor' => 'base',
                    ],
                ],
            ],
        ];

        return $template;
    }

    public function getLeadFormTemplate()
    {
        $template = [
            1 => [
                '::widgets/common/lead-gen-form.html.twig' => [
                    'content' => [
                        'labelContactUs' => 'Do you want to talk?',
                        'labelNeedHelp' => 'Drop us a line and we’ll get back as soon as we can.',
                        'labelSubmitButton' => 'Submit',
                        'contentSlider' => [
                            0 => [
                                'slideId' => '9148',
                                'imageId' => '8349',
                                'title' => 'Space',
                                'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas eget libero sagittis, dignissim felis ac, pretium velit. Donec congue dolor eget nunc fringilla semper.',
                                'navLink' => '',
                                'sliderCustomLink' => '',
                                'sliderCustomLinkType' => 'internal'
                            ],
                            1 => [
                                'slideId' => '9160',
                                'imageId' => '8350',
                                'title' => 'Space 2',
                                'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas eget libero sagittis, dignissim felis ac, pretium velit. Donec congue dolor eget nunc fringilla semper.',
                                'navLink' => '',
                                'sliderCustomLink' => '',
                                'sliderCustomLinkType' => 'internal'
                            ]
                        ],
                        'backgroundColor' => 'base',
                    ],
                ],
            ],
        ];

        return $template;
    }

    public function getSocialNetworkBarTemplate()
    {
        $template = [
            1 => [
                '::widgets/common/social-network-bar.html.twig' => [
                    'content' => [
                        'backgroundColor' => 'base',
                    ],
                ],
            ],
        ];

        return $template;
    }

    public function getContactInformationBarTemplate()
    {
        $template = [
            1 => [
                '::widgets/common/contact-information-bar.html.twig' => [
                    'content' => [
                        'backgroundColor' => 'base',
                    ],
                ],
            ],
        ];

        return $template;
    }

    public function getCallToActionTemplate()
    {
        $template = [
            1 => [
                '::widgets/common/call-to-action.html.twig' => [
                    'content' => [
                        'unsplash' => 'https://images.unsplash.com/photo-1550096197-dc8ead8af35b?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=1080&fit=max&ixid=eyJhcHBfaWQiOjUxMjM1fQ',
                        'placeholderTitle' => [
                            'value' => 'Sign up today - It\'s quick and simple!',
                        ],
                        'placeholderDescription' => [
                            'value' => 'Demo Directory is proud to announce its new directory service which is now available online to visitors and new suppliers. It boasts endless amounts of new features for customers and suppliers. Your directory items are also controlled entirely by you. We have a members interface where you can log in and change any details, add special promotions for Demo Directory customers and much more!',
                        ],
                        'placeholderCallToAction' => [
                            'value' => 'Call to Action',
                        ],
                        'placeholderLink' => [
                            'value' => 'http://google.com',
                        ],
                        'backgroundColor' => 'base',
                    ],
                ],
            ],
        ];

        return $template;
    }

    public function getSliderTemplate()
    {
        $template = [
            1 => [
                '::widgets/slider/slider.html.twig' => [
                    'content' => [],
                ],
            ],
        ];

        return $template;
    }

    public function getLocationsTemplate()
    {
        $template = [
            1 => $this->getAllLocationsTemplate(),
            2 => [
                '::widgets/location/browse-by-location.html.twig' => [
                    'content' => [
                        'labelBrowseByLocation' => 'Browse by location',
                        'labelExploreAllLocations' => 'Explore all locations',
                        'limit' => 40,
                        'backgroundColor' => 'brand',
                        'enableCounter' => 'true',
                        'customBanners' => 'square',
                    ],
                ],
            ],
        ];

        return $template;
    }

    public function getAllLocationsTemplate()
    {
        $locations_enable = $this->container->get('doctrine')->getRepository('WebBundle:SettingLocation')->getLocationsEnabledID();
        $locations = $this->container->get('helper.location')->getAllLocations($locations_enable, 'listing');

        $template = [
            '::widgets/location/all-locations.html.twig' => [
                'locations' => $locations,
                'content' => [
                    'labelExploreAllLocations' => 'Explore All Locations',
                    'backgroundColor' => 'base',
                ],
            ]
        ];

        return $template;
    }

    /**
     * @param $cardName
     * @param $columnQuantity
     * @return string
     */
    public function getCardByName($cardName, $columnQuantity)
    {
        $modules = [
            'listing',
            'event',
            'classified',
            'article',
            'deal',
            'blog'
        ];

        $template = '';

        switch ($cardName) {
            case 'horizontal-cards':
                foreach ($modules as $module) {
                    $template .= $this->container->get('templating')->render('::widgets/cards/cards.html.twig', [
                        'content' => [
                            'cardType'        => Widget::HORIZONTAL_CARDS_TYPE,
                            'widgetTitle'     => 'Horizontal - '.$module,
                            'widgetLink'      => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => '',
                            ],
                            'module'          => $module,
                            'banner'          => '',
                            'columns'         => $columnQuantity ?: 3,
                            'items'           => [],
                            'custom'          => [
                                'level'      => [],
                                'order1'     => 'random',
                                'order2'     => 'random',
                                'quantity'   => $columnQuantity ?: 3,
                                'categories' => [],
                                'locations'  => [
                                    'location_1' => '',
                                    'location_2' => '',
                                    'location_3' => '',
                                    'location_4' => '',
                                    'location_5' => '',
                                ],
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]);
                }
                break;
            case 'vertical-cards':
                foreach ($modules as $module) {
                    $template .= $this->container->get('templating')->render('::widgets/cards/cards.html.twig', [
                        'content' => [
                            'cardType'        => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle'     => 'Vertical - '.$module,
                            'widgetLink'      => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => '',
                            ],
                            'module'          => $module,
                            'banner'          => '',
                            'columns'         => $columnQuantity ?: 4,
                            'items'           => [],
                            'custom'          => [
                                'level'      => [],
                                'order1'     => 'random',
                                'order2'     => 'random',
                                'quantity'   => $columnQuantity ?: 4,
                                'categories' => [],
                                'locations'  => [
                                    'location_1' => '',
                                    'location_2' => '',
                                    'location_3' => '',
                                    'location_4' => '',
                                    'location_5' => '',
                                ],
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]);
                }
                break;
            case 'vertical-card-plus-horizontal-cards':
                foreach ($modules as $module) {
                    $template .= $this->container->get('templating')->render('::widgets/cards/cards.html.twig', [
                        'content' => [
                            'cardType'        => Widget::VERTICAL_CARD_HORIZONTAL_CARDS_TYPE,
                            'widgetTitle'     => 'Vertical plus Horizontal - '.$module,
                            'widgetLink'      => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => '',
                            ],
                            'module'          => $module,
                            'banner'          => '',
                            'columns'         => 2,
                            'items'           => [],
                            'custom'          => [
                                'level'      => [],
                                'order1'     => 'random',
                                'order2'     => 'random',
                                'quantity'   => 4,
                                'categories' => [],
                                'locations'  => [
                                    'location_1' => '',
                                    'location_2' => '',
                                    'location_3' => '',
                                    'location_4' => '',
                                    'location_5' => '',
                                ],
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]);
                }
                break;
            case 'centralized-highlighted-card':
                foreach ($modules as $module) {
                    $template .= $this->container->get('templating')->render('::widgets/cards/cards.html.twig', [
                        'content' => [
                            'cardType'        => Widget::CENTRALIZED_HIGHLIGHTED_CARD_TYPE,
                            'widgetTitle'     => 'Centralized Highlighted - '.$module,
                            'widgetLink'      => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => '',
                            ],
                            'module'          => $module,
                            'banner'          => '',
                            'columns'         => 3,
                            'items'           => [],
                            'custom'          => [
                                'level'      => [],
                                'order1'     => 'random',
                                'order2'     => 'random',
                                'quantity'   => 5,
                                'categories' => [],
                                'locations'  => [
                                    'location_1' => '',
                                    'location_2' => '',
                                    'location_3' => '',
                                    'location_4' => '',
                                    'location_5' => '',
                                ],
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]);
                }
                break;
            case 'one-horizontal-card':
                foreach ($modules as $module) {
                    $template .= $this->container->get('templating')->render('::widgets/cards/cards.html.twig', [
                        'content' => [
                            'cardType'        => Widget::ONE_HORIZONTAL_CARD_TYPE,
                            'widgetTitle'     => 'One Horizontal - '.$module,
                            'widgetLink'      => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => '',
                            ],
                            'module'          => $module,
                            'banner'          => '',
                            'columns'         => 1,
                            'items'           => [],
                            'custom'          => [
                                'level'      => [],
                                'order1'     => 'random',
                                'order2'     => 'random',
                                'quantity'   => 1,
                                'categories' => [],
                                'locations'  => [
                                    'location_1' => '',
                                    'location_2' => '',
                                    'location_3' => '',
                                    'location_4' => '',
                                    'location_5' => '',
                                ],
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]);
                }
                break;
            case 'three-vertical-cards':
                foreach ($modules as $module) {
                    $template .= $this->container->get('templating')->render('::widgets/cards/cards.html.twig', [
                        'content' => [
                            'cardType'        => Widget::THREE_VERTICAL_CARDS,
                            'widgetTitle'     => 'List of Horizontal - '.$module,
                            'widgetLink'      => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => '',
                            ],
                            'module'          => $module,
                            'banner'          => '',
                            'columns'         => 3,
                            'items'           => [],
                            'custom'          => [
                                'level'      => [],
                                'order1'     => 'random',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => [
                                    'location_1' => '',
                                    'location_2' => '',
                                    'location_3' => '',
                                    'location_4' => '',
                                    'location_5' => '',
                                ],
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]);
                }
                break;
            case 'list-of-horizontal-cards':
                foreach ($modules as $module) {
                    $template .= $this->container->get('templating')->render('::widgets/cards/cards.html.twig', [
                        'content' => [
                            'cardType'        => Widget::LIST_OF_HORIZONTAL_CARDS_TYPE,
                            'widgetTitle'     => 'List of Horizontal - '.$module,
                            'widgetLink'      => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => '',
                            ],
                            'module'          => $module,
                            'banner'          => '',
                            'columns'         => 1,
                            'items'           => [],
                            'custom'          => [
                                'level'      => [],
                                'order1'     => 'random',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => [
                                    'location_1' => '',
                                    'location_2' => '',
                                    'location_3' => '',
                                    'location_4' => '',
                                    'location_5' => '',
                                ],
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]);
                }
                break;
            default:
                $template .= '';
                break;
        }

        return $template;
    }

    public function getArticleTemplate()
    {
        $template = [
            1 => [
                '::widgets/article/recent-articles-plus-popular-articles.html.twig' => [
                    'content' => [
                        'backgroundColor' => 'base',
                        'labelPopularPosts' => 'Popular Posts',
                    ],
                ],
            ],
        ];

        return $template;
    }
}
