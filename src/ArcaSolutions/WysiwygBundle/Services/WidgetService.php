<?php

namespace ArcaSolutions\WysiwygBundle\Services;

use ArcaSolutions\SearchBundle\Services\ParameterHandler;
use ArcaSolutions\WysiwygBundle\Entity\Theme;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Wysiwyg
 *
 * This service handles everything but RENDERING that has something to do with Wysiwyg
 * Create, Edit, Delete pages and their widgets
 * Retrieving the data from DB, saving data in DB.
 *
 */
class WidgetService
{
    /**
     * ContainerInterface
     *
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var string
     */
    private $module = ParameterHandler::MODULE_LISTING;
    /**
     * @var string
     */
    private $moduleBanner;
    /**
     * @var string
     */
    private $moduleSearch;
    
    /**
     * @var array
     */
    public $thinStrip     = [
        Widget::CONTACT_INFORMATION_BAR,
        Widget::SOCIAL_NETWORK_BAR,
        Widget::DOWNLOAD_OUR_APPS_BAR,
        Widget::SEARCH_BAR
    ];
    
    /**
     * This array contains the groups of the widgets that can only be one of them per page,
     * if widget's name changing at the load data is necessary change here as well.
     * You can add the title of the widget to block the whole group, or create another group.
     *
     * @var array
     */
    private $widgetNonDuplicate = [
        'header'            => [
            Widget::HEADER,
            Widget::HEADER_WITH_CONTACT_PHONE,
            Widget::NAVIGATION_WITH_CENTERED_LOGO,
            Widget::NAVIGATION_WITH_LEFT_LOGO_PLUS_SOCIAL_MEDIA,
        ],
        'footer'            => [
            Widget::FOOTER,
            Widget::FOOTER_WITH_NEWSLETTER,
            Widget::FOOTER_WITH_SOCIAL_MEDIA,
            Widget::FOOTER_WITH_LOGO,
        ],
        'search'            => [
            Widget::SEARCH_BOX,
            Widget::SEARCH_BAR,
        ],
        'result'            => [
            Widget::RESULTS,
        ],
        'upcoming_event'    => [
            Widget::UPCOMING_EVENTS,
        ],
        'upcoming_corousel' => [
            Widget::UPCOMING_EVENTS_CAROUSEL,
        ],
        'detail'            => [
            Widget::LISTING_DETAIL,
            Widget::EVENT_DETAIL,
            Widget::CLASSIFIED_DETAIL,
            Widget::ARTICLE_DETAIL,
            Widget::DEAL_DETAIL,
            Widget::BLOG_DETAIL,
        ],
        'all_locations'     => [
            Widget::ALL_LOCATIONS,
        ],
        'all_categories'    => [
            Widget::ALL_CATEGORIES,
        ],
        'contact'           => [
            Widget::CONTACT_FORM,
        ],
        'faq_box'           => [
            Widget::FAQ_BOX,
        ],
        'pricing_plans'     => [
            Widget::PRICING_AND_PLANS,
        ],
        'events'            => [
            Widget::EVENTS_CALENDAR,
        ],
        'newsletter'        => [
            Widget::NEWSLETTER,
        ],
        'reviews_block'     => [
            Widget::REVIEWS_BLOCK,
        ],
    ];
    
    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        
        HookFire("widget_construct", [
            "that"               => &$this,
            "widgetNonDuplicate" => &$this->widgetNonDuplicate,
        ]);
    }
    
    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }
    
    /**
     * @param string $module
     */
    public function setModule($module)
    {
        $this->module = $module;
        $this->moduleBanner = $module;
        $this->moduleSearch = $module ?: null;
    }
    
    /**
     * @return string
     */
    public function getModuleBanner()
    {
        return $this->moduleBanner;
    }
    
    /**
     * @param string $module
     */
    public function setModuleBanner($module)
    {
        $this->moduleBanner = $module;
    }
    
    /**
     * @return string
     */
    public function getModuleSearch()
    {
        return $this->moduleSearch;
    }
    
    /**
     * @param string $moduleSearch
     */
    public function setModuleSearch($moduleSearch)
    {
        $this->moduleSearch = $moduleSearch;
    }
    
    /**
     * @param $widgetType
     *
     * @return mixed
     */
    public function getMostUsedWidgetInfo($widgetType)
    {
        $theme = $this->container->get('theme.service')->getSelectedTheme();
        
        /* get a list of widgets of a type order by how many pages contain it */
        $typeWidgets = $this->container->get('doctrine')->getRepository('WysiwygBundle:Widget')
            ->getWidgetsMostUsedByType($widgetType, $theme->getId());
        
        /* pick the first widget info in the array which is the most used */
        
        return $typeWidgets;
    }
    
    /**
     * @param integer $id
     *
     * @return array
     */
    public function getOriginalWidget($id)
    {
        // Get Default Widget information (Widget Table)
        return $this->container->get('doctrine')->getRepository('WysiwygBundle:Widget')->find($id);
    }
    
    /**
     * @param $pageTypeId
     * @return \ArcaSolutions\WysiwygBundle\Entity\Widget[]|array
     */
    public function getGroupedWidgets($pageTypeId)
    {
        // Get Default Widgets (Widget Table)
        $themeId = $this->container->get('theme.service')->getSelectedTheme()->getId();
        
        return $this->container->get('doctrine')->getRepository('WysiwygBundle:Widget')->findAllGrouped($pageTypeId,
            $themeId);
    }
    
    /**
     * @return array
     */
    public function getWidgetTypes()
    {
        // Get Default Widgets (Widget Table)
        return $this->container->get('doctrine')->getRepository('WysiwygBundle:Widget')->findTypes();
    }
    
    /**
     * Returns widgets that cannot exist more than once on each page
     *
     * @return array
     */
    public function getWidgetNonDuplicate()
    {
        return $this->widgetNonDuplicate;
    }
    
    /**
     * @param $content
     * @param $trans
     *
     * @return string
     */
    public function getGenericLabelInputs($content, $trans)
    {
        $translator = $this->container->get('translator');
        
        $sitemgrLanguage = substr($this->container->get('settings')->getSetting('sitemgr_language'), 0, 2);
        
        $exceptionsKeys = array('videos','unsplash','backgroundColor','isTransparent','stickyMenu','enableCounter','customBanner');
        
        /* ModStores Hooks */
        HookFire('widgetservice_get_generic_label_inputs_after_set_exceptions_keys', [
            'exceptionsKeys' => &$exceptionsKeys,
            'content' => &$content
        ]);
        
        $inputs = '';
        foreach ($content as $key => $value) {
            if (strpos($key, 'label') !== false) {
                $inputs .= '<div class="form-group">'
                    .'<label for="'.$key.'" class="control-label">'.$trans[$key].'</label>'
                    .'<input type="text" class="form-control" name="'.$key.'" value="'.$value.'" id="'.$key.'">'
                    .'</div>';
                //Temporary code, unify the conditions when all labels have value index and label
            } elseif (strpos($key, 'placeholder') !== false) {
                if(!empty($value['type'])){
                    switch ($value['type']){
                        case 'textarea':
                            $input = '<textarea class="form-control genericInput" rows="5" name="'.$key.'" id="'.$key.'" data-type="'.$value['type'].'" data-label="'.$value['label'].'">'.$value['value'].' </textarea>';
                            break;
                        case 'link':
                            $input = $this->getLinkSection($key, $value, $sitemgrLanguage);
                            break;
                    }
                } else {
                    $input = '<input type="text" class="form-control genericInput" name="'.$key.'" value="'.$value['value'].'" id="'.$key.'" data-label="'.$value['label'].'">';
                }
                $inputs .= '<div class="form-group">'
                    .'<label for="'.$key.'" class="control-label">'
                    .$translator->trans(/** @Ignore */ $value['label'],[], 'widgets', $sitemgrLanguage)
                    . (!empty($value['hint']) ? '<i class="fa fa-info-circle pull-right" data-toggle="tooltip" data-placement="top" title="' .$translator->trans(/** @Ignore */ $value['hint'],[], 'widgets', $sitemgrLanguage)  . '"></i>' : '')
                    .'</label>'
                    . $input .'</div>';
            } elseif (!in_array($key, $exceptionsKeys, true)) {
                
                if(\is_array($value)) {
                    $value = json_encode($value);
                }
                
                $inputs .= '<input type="hidden" id="'.$key.'" name="'.$key.'" value=\''.$value.'\' />';
            }
        }
        
        /* ModStores Hooks */
        HookFire('widgetservice_after_add_standardwidgets', [
            'that'         => &$this,
            'standardWidgets' => &$standardWidgets,
        ]);
        
        return $inputs;
    }
    
    public function getLinkSection($key, $value, $sitemgrLanguage)
    {
        $allPages = $this->container->get('navigation.service')->getNavigationPages('Header');
        $mainPages = $allPages['mainPages'];
        $customPages = $allPages['customPages'];
        $baseURL = $this->container->get('pagetype.service')->getBaseUrl(\ArcaSolutions\WysiwygBundle\Entity\PageType::HOME_PAGE).'/';;
        
        return $this->container->get('twig')->render('@Wysiwyg/widget-field-link.html.twig', [
            'sitemgrLanguage' => $sitemgrLanguage,
            'key' => $key,
            'field' => $value,
            'mainPages' => $mainPages,
            'customPages' => $customPages,
            'baseURL' => $baseURL,
        ]);
    }
    
    /**
     * This function returns the name of the twig file and its content of the most used widget of its type
     * Is used to get the footer and header file name to profile and sponsor area.
     *
     * @param $widgetType
     *
     * @return mixed
     */
    public function getWidgetInfo($widgetType)
    {
        $mostUsedWidget = $this->getMostUsedWidgetByType($widgetType);
        
        $twigFileParts = explode('/', $mostUsedWidget[0][0]->getTwigFile());
        $lastPart = end($twigFileParts);
        
        $widgetInfo['twig'] = str_replace('.html.twig', '', $lastPart);
        $widgetInfo['content'] = json_decode($mostUsedWidget[0]['content'], true);
        
        return $widgetInfo;
    }
    
    /**
     * @param $widgetType
     *
     * @return Widget
     */
    public function getMostUsedWidgetByType($widgetType)
    {
        return $this->getMostUsedWidgetInfo($widgetType);
    }
    
    /**
     * Return standard Widgets
     *
     * @return array
     */
    public function getWidgets()
    {
        $standardWidgets = [
            [
                'title'    => Widget::HEADER,
                'twigFile' => '/navigation/header-type1.html.twig',
                'type'     => Widget::HEADER_TYPE,
                'content'  => [
                    'labelDashboard'   => 'Dashboard',
                    'labelProfile'     => 'Profile',
                    'labelFaq'         => 'Faq',
                    'labelAccountPref' => 'Settings',
                    'labelLogOff'      => 'Log Off',
                    'labelListWithUs'  => 'List with Us',
                    'labelSignIn'      => 'Sign In',
                    'labelMore'        => 'More',
                    'hasDesign'        => 'true',
                    'isTransparent'    => 'false',
                    'stickyMenu'       => 'false',
                    'backgroundColor'  => 'brand',
                ],
                'modal'    => 'edit-header-modal',
            ],
            [
                'title'    => Widget::NAVIGATION_WITH_LEFT_LOGO_PLUS_SOCIAL_MEDIA,
                'twigFile' => '/navigation/header-type2.html.twig',
                'type'     => Widget::HEADER_TYPE,
                'content'  => [
                    'labelDashboard'   => 'Dashboard',
                    'labelProfile'     => 'Profile',
                    'labelFaq'         => 'Faq',
                    'labelAccountPref' => 'Settings',
                    'labelLogOff'      => 'Log Off',
                    'labelListWithUs'  => 'List with Us',
                    'labelSignIn'      => 'Sign In',
                    'labelMore'        => 'More',
                    'hasDesign'        => 'true',
                    'isTransparent'    => 'false',
                    'stickyMenu'       => 'false',
                    'backgroundColor'  => 'brand',
                ],
                'modal'    => 'edit-header-modal',
            ],
            [
                'title'    => Widget::HEADER_WITH_CONTACT_PHONE,
                'twigFile' => '/navigation/header-type3.html.twig',
                'type'     => Widget::HEADER_TYPE,
                'content'  => [
                    'labelDashboard'   => 'Dashboard',
                    'labelProfile'     => 'Profile',
                    'labelFaq'         => 'Faq',
                    'labelAccountPref' => 'Settings',
                    'labelLogOff'      => 'Log Off',
                    'labelListWithUs'  => 'List with Us',
                    'labelSignIn'      => 'Sign In',
                    'labelMore'        => 'More',
                    'hasDesign'        => 'true',
                    'isTransparent'    => 'false',
                    'stickyMenu'       => 'false',
                    'backgroundColor'  => 'brand',
                ],
                'modal'    => 'edit-header-type3-modal',
            ],
            [
                'title'    => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                'twigFile' => '/navigation/header-type4.html.twig',
                'type'     => Widget::HEADER_TYPE,
                'content'  => [
                    'labelDashboard'   => 'Dashboard',
                    'labelProfile'     => 'Profile',
                    'labelFaq'         => 'Faq',
                    'labelAccountPref' => 'Settings',
                    'labelLogOff'      => 'Log Off',
                    'labelListWithUs'  => 'List with Us',
                    'labelSignIn'      => 'Sign In',
                    'labelMore'        => 'More',
                    'hasDesign'        => 'true',
                    'isTransparent'    => 'false',
                    'stickyMenu'       => 'false',
                    'backgroundColor'  => 'brand',
                ],
                'modal'    => 'edit-header-modal',
            ],
            [
                'title'    => Widget::FOOTER,
                'twigFile' => '/navigation/footer-type1.html.twig',
                'type'     => Widget::FOOTER_TYPE,
                'content'  => [
                    'labelSiteContent'   => 'Site Content',
                    'labelContactUs'     => 'Contact Us',
                    'labelFollowUs'      => 'Follow Us',
                    'labelCopyrightText' => '',
                    'playStoreLabel'     => '',
                    'AppStoreLabel'      => '',
                    'linkPlayStore'      => '',
                    'linkAppleStore'     => '',
                    'hasDesign'          => 'true',
                    'backgroundColor'    => 'brand',
                ],
                'modal'    => 'edit-footer-modal',
            ],
            [
                'title'    => Widget::FOOTER_WITH_LOGO,
                'twigFile' => '/navigation/footer-type2.html.twig',
                'type'     => Widget::FOOTER_TYPE,
                'content'  => [
                    'labelSiteContent'   => 'Site Content',
                    'labelContactUs'     => 'Contact Us',
                    'labelCopyrightText' => '',
                    'hasDesign'          => 'true',
                    'backgroundColor'    => 'brand',
                ],
                'modal'    => 'edit-footer-type2-modal',
            ],
            [
                'title'    => Widget::FOOTER_WITH_SOCIAL_MEDIA,
                'twigFile' => '/navigation/footer-type3.html.twig',
                'type'     => Widget::FOOTER_TYPE,
                'content'  => [
                    'labelCopyrightText' => '',
                    'hasDesign'          => 'true',
                    'backgroundColor'    => 'brand',
                ],
                'modal'    => 'edit-footer-type3-modal',
            ],
            [
                'title'    => Widget::FOOTER_WITH_NEWSLETTER,
                'twigFile' => '/navigation/footer-type4.html.twig',
                'type'     => Widget::FOOTER_TYPE,
                'content'  => [
                    'labelContactUs'         => 'Contact Us',
                    'labelCopyrightText'     => '',
                    'datainfoSignupFor'      => 'Sign up for our newsletter',
                    'datainfoNewsletterDesc' => 'Sign up for our monthly newsletter. No spams, just product updates.',
                    'hasDesign'              => 'true',
                    'backgroundColor'        => 'brand',
                ],
                'modal'    => 'edit-footer-type4-modal',
            ],
            [
                'title'    => Widget::DOWNLOAD_OUR_APPS_BAR,
                'twigFile' => '/common/download-our-apps.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelAvailablePlayStore'  => '',
                    'labelDownloadOurApp'      => 'Download our App',
                    'labelAvailablePlataforms' => '',
                    'labelAvailableAppleStore' => '',
                    'linkPlayStore'            => '',
                    'linkAppleStore'           => '',
                    'checkboxOpenWindow'       => '',
                    'hasDesign'                => 'true',
                    'backgroundColor'          => 'brand',
                ],
                'modal'    => 'edit-downloadapp-modal',
            ],
            [
                'title'    => Widget::SEARCH_BOX,
                'twigFile' => '/slider/slider-searchbox.html.twig',
                'type'     => Widget::SEARCH_TYPE,
                'content'  => [
                    'labelStartYourSearch'      => 'Start your search here',
                    'labelWhatLookingFor'       => 'What are you looking for?',
                    'placeholderSearchKeyword'  => [
                        'value' => 'Food, service, hotel...',
                        'label' => 'Placeholder for search by keyword field'
                    ],
                    'placeholderSearchLocation' => [
                        'value' => 'Enter location...',
                        'label' => 'Placeholder for search by location field'
                    ],
                    'hasDesign'           => 'true',
                    'dataAlignment'       => 'center'
                ],
                'modal'    => 'edit-slider-modal',
            ],
            [
                'title'    => Widget::LEADER_BOARD_AD_BAR,
                'twigFile' => '/banners/banner.html.twig',
                'type'     => Widget::BANNER_TYPE,
                'content'  => [
                    'bannerType'      => 'leaderboard',
                    'isWide'          => 'false',
                    'banners'         => [
                        1 => 'leaderboard'
                    ],
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::THREE_RECTANGLE_AD_BAR,
                'twigFile' => '/banners/banner.html.twig',
                'type'     => Widget::BANNER_TYPE,
                'content'  => [
                    'bannerType'      => 'large-mobile',
                    'isWide'          => 'false',
                    'banners'         => [
                        1 => 'largebanner',
                        2 => 'largebanner',
                        3 => 'largebanner',
                    ],
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::UPCOMING_EVENTS,
                'twigFile' => '/event/upcoming-events-bar.html.twig',
                'type'     => Widget::EVENT_TYPE,
                'content'  => [
                    'labelUpcomingEvents' => 'Upcoming Events',
                    'limit'               => 8,
                    'hasDesign'           => 'true',
                    'backgroundColor'     => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::BROWSE_BY_LOCATION,
                'twigFile' => '/location/browse-by-location.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelExploreMorePlaces' => 'Browse by location',
                    'labelMoreLocations'     => 'more locations',
                    'limit'                  => 65,
                    'hasDesign'              => 'true',
                    'backgroundColor'        => 'brand',
                    'hasCounter'             => 'true',
                    'enableCounter'          => 'true',
                    'customBanners'          => 'empty',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::BANNER_LARGE_MOBILE,
                'twigFile' => '/banners/banner.html.twig',
                'type'     => Widget::BANNER_TYPE,
                'content'  => [
                    'bannerType'      => 'large-mobile',
                    'isWide'          => 'false',
                    'banners'         => [
                        1 => 'largebanner',
                        2 => 'google',
                        3 => 'sponsor-links',
                    ],
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::SEARCH_BAR,
                'twigFile' => '/searchbox/searchbox.html.twig',
                'type'     => Widget::SEARCH_TYPE,
                'content'  => [
                    'placeholderSearchKeyword'  => [
                        'value' => 'Food, service, hotel...',
                        'label' => 'Placeholder for search by keyword field',
                    ],
                    'placeholderSearchLocation' => [
                        'value' => 'Enter location...',
                        'label' => 'Placeholder for search by location field',
                        'hint'  => 'This field won\'t be shown when used on Article and Blog pages'
                    ],
                    'placeholderSearchDate'     => [
                        'value' => 'Date',
                        'label' => 'Placeholder for search by date field',
                        'hint'  => 'This field will be shown only when used on Events Pages'
                    ],
                    'hasDesign'                 => 'true',
                    'backgroundColor'           => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::HORIZONTAL_CARDS,
                'twigFile' => '/cards/cards.html.twig',
                'type'     => Widget::CARDS_TYPE,
                'content'  => [
                    'cardType' => Widget::HORIZONTAL_CARDS_TYPE,
                    'widgetTitle' => '', //Ex: "Featured Listing"
                    'widgetLink' => [
                        'label' => '', //Ex: "view more"
                        'page_id' => '',
                        'link' => '', //Ex: "/listing"
                    ],
                    'module' => '', //listing, event, classified, article, deal, blog
                    'banner' => '', //null, square, wide skyscraper
                    'columns' => '', //2, 3, 4
                    'items' => [], //items id
                    'custom'  => [
                        'level' => [], //10, 30, 50, 70
                        'order1' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'order2' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'quantity' => '', //3, 6, 9, 12 or 4, 8, 12, 16 ou 2, 4, 6, 8
                        'categories' => [], //categories IDs
                        'locations' => [ //locations IDs
                            'location_1' => '',
                            'location_2' => '',
                            'location_3' => '',
                            'location_4' => '',
                            'location_5' => '',
                        ],
                    ],
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-cards-modal',
            ],
            [
                'title'    => Widget::VERTICAL_CARDS,
                'twigFile' => '/cards/cards.html.twig',
                'type'     => Widget::CARDS_TYPE,
                'content'  => [
                    'cardType' => Widget::VERTICAL_CARDS_TYPE,
                    'widgetTitle' => '', //Ex: "Featured Listing"
                    'widgetLink' => [
                        'label' => '', //Ex: "view more"
                        'page_id' => '',
                        'link' => '', //Ex: "/listing"
                    ],
                    'module' => '', //listing, event, classified, article, deal, blog
                    'banner' => '', //null, square, wide skyscraper
                    'columns' => '', //2, 3, 4
                    'items' => [], //items id
                    'custom'  => [
                        'level' => [], //10, 30, 50, 70
                        'order1' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'order2' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'quantity' => '', //3, 6, 9, 12 or 4, 8, 12, 16 ou 2, 4, 6, 8
                        'categories' => [], //categories IDs
                        'locations' => [ //locations IDs
                            'location_1' => '',
                            'location_2' => '',
                            'location_3' => '',
                            'location_4' => '',
                            'location_5' => '',
                        ],
                    ],
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-cards-modal',
            ],
            [
                'title'    => Widget::VERTICAL_CARD_PLUS_HORIZONTAL_CARDS,
                'twigFile' => '/cards/cards.html.twig',
                'type'     => Widget::CARDS_TYPE,
                'content'  => [
                    'cardType' => Widget::VERTICAL_CARD_HORIZONTAL_CARDS_TYPE,
                    'widgetTitle' => '', //Ex: "Featured Listing"
                    'widgetLink' => [
                        'label' => '', //Ex: "view more"
                        'page_id' => '',
                        'link' => '', //Ex: "/listing"
                    ],
                    'module' => '', //listing, event, classified, article, deal, blog
                    'banner' => false,
                    'columns' => 2,
                    'items' => [], //items id
                    'custom'  => [
                        'level' => [], //10, 30, 50, 70
                        'order1' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'order2' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'quantity' => 4,
                        'categories' => [], //categories IDs
                        'locations' => [ //locations IDs
                            'location_1' => '',
                            'location_2' => '',
                            'location_3' => '',
                            'location_4' => '',
                            'location_5' => '',
                        ],
                    ],
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-cards-modal',
            ],
            [
                'title'    => Widget::TWO_COLUMNS_HORIZONTAL_CARDS,
                'twigFile' => '/cards/cards.html.twig',
                'type'     => Widget::CARDS_TYPE,
                'content'  => [
                    'cardType' => Widget::TWO_COLUMNS_HORIZONTAL_CARDS_TYPE,
                    'widgetTitle' => '', //Ex: "Featured Listing"
                    'widgetLink' => [
                        'label' => '', //Ex: "view more"
                        'page_id' => '',
                        'link' => '', //Ex: "/listing"
                    ],
                    'module' => '', //listing, event, classified, article, deal, blog
                    'banner' => false,
                    'columns' => 2,
                    'items' => [], //items id
                    'custom'  => [
                        'level' => [], //10, 30, 50, 70
                        'order1' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'order2' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'quantity' => 3, //3, 6, 9, 12 or 4, 8, 12, 16 ou 2, 4, 6, 8
                        'categories' => [], //categories IDs
                        'locations' => [ //locations IDs
                            'location_1' => '',
                            'location_2' => '',
                            'location_3' => '',
                            'location_4' => '',
                            'location_5' => '',
                        ],
                    ],
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-cards-modal',
            ],
            [
                'title'    => Widget::CENTRALIZED_HIGHLIGHTED_CARD,
                'twigFile' => '/cards/cards.html.twig',
                'type'     => Widget::CARDS_TYPE,
                'content'  => [
                    'cardType' => Widget::CENTRALIZED_HIGHLIGHTED_CARD_TYPE,
                    'widgetTitle' => '', //Ex: "Featured Listing"
                    'widgetLink' => [
                        'label' => '', //Ex: "view more"
                        'page_id' => '',
                        'link' => '', //Ex: "/listing"
                    ],
                    'module' => '', //listing, event, classified, article, deal, blog
                    'banner' => false,
                    'columns' => 3,
                    'items' => [], //items id
                    'custom'  => [
                        'level' => [], //10, 30, 50, 70
                        'order1' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'order2' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'quantity' => 5,
                        'categories' => [], //categories IDs
                        'locations' => [ //locations IDs
                            'location_1' => '',
                            'location_2' => '',
                            'location_3' => '',
                            'location_4' => '',
                            'location_5' => '',
                        ],
                    ],
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-cards-modal',
            ],
            [
                'title'    => Widget::UPCOMING_EVENTS_CAROUSEL,
                'twigFile' => '/event/upcoming-events-carousel.html.twig',
                'type'     => Widget::EVENT_TYPE,
                'content'  => [
                    'labelUpcomingEvents' => 'Upcoming Events',
                    'labelMoreEvents'     => 'more events',
                    'hasDesign'           => 'true',
                    'backgroundColor'     => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::RESULTS_INFO,
                'twigFile' => '/results/results-info.html.twig',
                'type'     => Widget::SEARCH_TYPE,
                'content'  => [],
                'modal'    => '',
            ],
            [
                'title'    => Widget::RESULTS,
                'twigFile' => '/results/results.html.twig',
                'type'     => Widget::SEARCH_TYPE,
                'content'  => [
                    'filterSide'   => 'left',
                    'hasDesign'    => 'true',
                    'resultView'   => 'list-grid'
                ],
                'modal'    => 'edit-results-modal',
            ],
            [
                'title'    => Widget::LISTING_DETAIL,
                'twigFile' => '/listing/detail-content.html.twig',
                'type'     => Widget::LISTING_TYPE,
                'content'  => [],
                'modal'    => '',
            ],
            [
                'title'    => Widget::EVENT_DETAIL,
                'twigFile' => '/event/detail-content.html.twig',
                'type'     => Widget::EVENT_TYPE,
                'content'  => [],
                'modal'    => '',
            ],
            [
                'title'    => Widget::CLASSIFIED_DETAIL,
                'twigFile' => '/classified/detail-content.html.twig',
                'type'     => Widget::CLASSIFIED_TYPE,
                'content'  => [],
                'modal'    => '',
            ],
            [
                'title'    => Widget::ARTICLE_DETAIL,
                'twigFile' => '/article/detail-content.html.twig',
                'type'     => Widget::ARTICLE_TYPE,
                'content'  => [],
                'modal'    => '',
            ],
            [
                'title'    => Widget::DEAL_DETAIL,
                'twigFile' => '/deal/detail-content.html.twig',
                'type'     => Widget::DEAL_TYPE,
                'content'  => [],
                'modal'    => '',
            ],
            [
                'title'    => Widget::BLOG_DETAIL,
                'twigFile' => '/blog/detail-content.html.twig',
                'type'     => Widget::BLOG_TYPE,
                'content'  => [
                    'labelCategories'     => 'Categories',
                    'labelPopularPosts'   => 'Popular Posts',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::CONTACT_FORM,
                'twigFile' => '/contactus/contact-form.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelContactUs' => 'Contact Us',
                    'labelNeedHelp'  => 'Need help with something? Get in touch with us and we\'ll do our best to answer your question as soon as possible.',
                ],
                'modal'    => 'edit-contactform-modal',
            ],
            [
                'title'    => Widget::FAQ_BOX,
                'twigFile' => '/faq/faq.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelHowCanIHelp'   => 'How can we help you?',
                    'labelParagraph'     => 'Here you will type a awesome text!',
                    'labelDidYouNotFind' => 'Did you not find your answer? Contact us.',
                    'hasDesign'          => 'false',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::SECTION_HEADER,
                'twigFile' => '/common/section-header.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelHeader'        => 'Section Header',
                    'hasDesign'          => 'true',
                    'backgroundColor'    => 'brand',
                    'dataAlignment'      => 'center'
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::CUSTOM_CONTENT,
                'twigFile' => '/custompage/content.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [],
                'modal'    => 'edit-customcontent-modal',
            ],
            [
                'title'    => Widget::PRICING_AND_PLANS,
                'twigFile' => '/advertise/pricing-plans.html.twig',
                'type'     => Widget::PRICING_TYPE,
                'content'  => [
                    'hasDesign'            => 'true',
                    'backgroundColor'      => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::ALL_LOCATIONS,
                'twigFile' => '/location/all-locations.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelExploreAllLocations' => 'Explore All Locations',
                    'hasDesign'                => 'true',
                    'backgroundColor'          => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::RECENT_REVIEWS,
                'twigFile' => '/review/recent-reviews.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelRecentReviews'     => 'Recent Reviews',
                    'hasDesign'              => 'true',
                    'backgroundColor'        => 'brand',
                    'customBanners'          => 'empty',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::REVIEWS_BLOCK,
                'twigFile' => '/review/module-review.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [],
                'modal'    => '',
            ],
            [
                'title'    => Widget::EVENTS_CALENDAR,
                'twigFile' => '/event/events-calendar.html.twig',
                'type'     => Widget::EVENT_TYPE,
                'content'  => [
                    'labelCalendar'   => 'Events Calendar',
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::RECENT_MEMBERS,
                'twigFile' => '/common/recent-members.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelRecentMembers' => 'Recent Members',
                    'hasDesign'          => 'true',
                    'backgroundColor'    => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::LISTING_PRICES,
                'twigFile' => '/advertise/main-modules-prices.html.twig',
                'type'     => Widget::PRICING_TYPE,
                'content'  => [
                    'labelModuleOptions' => 'Listing Options',
                    'labelDescription'   => '',
                    'module'             => 'listing',
                    'hasDesign'          => 'true',
                    'backgroundColor'    => 'brand',
                ],
                'modal'    => 'edit-module-prices-modal',
            ],
            [
                'title'    => Widget::EVENT_PRICES,
                'twigFile' => '/advertise/main-modules-prices.html.twig',
                'type'     => Widget::PRICING_TYPE,
                'content'  => [
                    'labelModuleOptions' => 'Event Options',
                    'labelDescription'   => '',
                    'module'             => 'event',
                    'hasDesign'          => 'true',
                    'backgroundColor'    => 'brand',
                ],
                'modal'    => 'edit-module-prices-modal',
            ],
            [
                'title'    => Widget::CLASSIFIED_PRICES,
                'twigFile' => '/advertise/main-modules-prices.html.twig',
                'type'     => Widget::PRICING_TYPE,
                'content'  => [
                    'labelModuleOptions' => 'Classified Options',
                    'labelDescription'   => '',
                    'module'             => 'classified',
                    'hasDesign'          => 'true',
                    'backgroundColor'    => 'brand',
                ],
                'modal'    => 'edit-module-prices-modal',
            ],
            [
                'title'    => Widget::BANNER_PRICES,
                'twigFile' => '/banners/banner-prices.html.twig',
                'type'     => Widget::PRICING_TYPE,
                'content'  => [
                    'labelModuleOptions' => 'Banner Options',
                    'labelDescription'   => '',
                    'module'             => 'banner',
                    'hasDesign'          => 'true',
                    'backgroundColor'    => 'brand',
                ],
                'modal'    => 'edit-module-prices-modal',
            ],
            [
                'title'    => Widget::ARTICLE_PRICES,
                'twigFile' => '/article/article-prices.html.twig',
                'type'     => Widget::PRICING_TYPE,
                'content'  => [
                    'labelModuleOptions' => 'Article Options',
                    'labelDescription'   => '',
                    'module'             => 'article',
                    'hasDesign'          => 'true',
                    'backgroundColor'    => 'brand',
                ],
                'modal'    => 'edit-module-prices-modal',
            ],
            [
                'title' => Widget::NEWSLETTER,
                'twigFile' => '/newsletter/signup-for-our-newsletter.html.twig',
                'type' => Widget::NEWSLETTER_TYPE,
                'content' => [
                    'labelSignupFor'      => 'Sign up for our newsletter',
                    'labelNewsletterDesc' => 'Sign up for our monthly newsletter. No spams, just product updates.',
                    'imageId'             => '',
                    'hasDesign'           => 'true',
                    'dataAlignment'       => 'center'
                ],
                'modal' => 'edit-genericimage-modal'
            ],
            [
                'title'    => Widget::TWO_COLUMNS_RECENT_POSTS,
                'twigFile' => '/blog/2-columns-recent-posts.html.twig',
                'type'     => Widget::BLOG_TYPE,
                'content'  => [
                    'labelCategories'     => 'Categories',
                    'labelPopularPosts'   => 'Popular Posts',
                    'hasDesign'           => 'false',
                    'backgroundColor'     => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::VIDEO_GALLERY,
                'twigFile' => '/common/video-gallery.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelVideoGallery' => 'Video Gallery',
                    'videos'            => [],
                    'hasDesign'         => 'true',
                    'backgroundColor'   => 'brand',
                    'dataColumn'        => '4'
                ],
                'modal'    => 'edit-video-modal'
            ],
            [
                'title'    => Widget::LEAD_FORM,
                'twigFile' => '/common/lead-gen-form.html.twig',
                'type'     => Widget::LEAD_TYPE,
                'content'  => [
                    'labelContactUs'     => 'Do you want to talk?',
                    'labelNeedHelp'      => 'Drop us a line and we\'ll get back as soon as we can.',
                    'labelSubmitButton'  => 'Submit',
                    'hasDesign'          => 'true',
                    'dataAlignment'      => 'right'
                ],
                'modal'    => 'edit-leadgenform-modal',
            ],
            [
                'title'    => Widget::SOCIAL_NETWORK_BAR,
                'twigFile' => '/common/social-network-bar.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::CONTACT_INFORMATION_BAR,
                'twigFile' => '/common/contact-information-bar.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::CALL_TO_ACTION,
                'twigFile' => '/common/call-to-action.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'unsplash'                => '',
                    'placeholderTitle'        => [
                        'value' => '',
                        'label' => 'Title'
                    ],
                    'placeholderDescription'  => [
                        'value' => '',
                        'type' => 'textarea',
                        'label' => 'Description'
                    ],
                    'placeholderCallToAction' => [
                        'value' => '',
                        'label' => 'Button Text'
                    ],
                    'placeholderLink'         => [
                        'type' => 'link',
                        'label' => 'Button Link',
                        'value' => 'custom',
                        'target' => 'external',
                        'customLink' => '',
                        'openWindow' => '',
                    ],
                    'imageId'                 => '',
                    'hasDesign'               => 'true',
                    'dataAlignment'           => 'center'
                ],
                'modal'    => 'edit-genericimage-modal',
            ],
            [
                'title'    => Widget::SLIDER,
                'twigFile' => '/slider/slider.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [],
                'modal'    => 'edit-slider-modal',
            ],
            [
                'title'    => Widget::FEATURED_CATEGORIES_WITH_IMAGES,
                'twigFile' => '/category/browse-by-category.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelBrowseByCat' => 'Browse by category',
                    'labelMoreCat'     => 'more categories',
                    'limit'            => null,
                    'hasDesign'        => 'true',
                    'backgroundColor'  => 'brand',
                    'hasCounter'       => 'true',
                    'enableCounter'    => 'true',
                    'customBanners'    => 'empty',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::ALL_CATEGORIES,
                'twigFile' => '/category/all-categories.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelExploreAllCategories' => 'Explore All Categories',
                    'hasDesign'                 => 'true',
                    'backgroundColor'           => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::FEATURED_CATEGORIES_WITH_IMAGES_TYPE_2,
                'twigFile' => '/category/browse-by-category-featured.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelFeaturedCategories' => 'Featured categories',
                    'labelAllCategories'      => 'All categories',
                    'hasDesign'               => 'true',
                    'backgroundColor'         => 'brand',
                    'hasCounter'              => 'true',
                    'enableCounter'           => 'true',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::FEATURED_CATEGORIES,
                'twigFile' => '/category/list-of-categories.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelAllCategories' => 'All Categories',
                    'labelCategories'    => 'Categories',
                    'hasDesign'          => 'true',
                    'backgroundColor'    => 'brand',
                    'customBanners'      => 'empty',
                    'hasCounter'         => 'true',
                    'enableCounter'      => 'true',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::FEATURED_CATEGORIES_WITH_ICONS,
                'twigFile' => '/category/browse-by-category-with-icons.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'labelBrowseByCat' => 'Find by category',
                    'labelMoreCat'     => 'All categories',
                    'limit'            => null,
                    'hasDesign'        => 'true',
                    'backgroundColor'  => 'brand',
                    'hasCounter'       => 'true',
                    'enableCounter'    => 'true',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::FEATURED_CATEGORIES_TYPE_2,
                'twigFile' => '/category/browse-by-category-list.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'placeholderTitle' => [
                        'value' => 'Trending Topics',
                        'label' => 'Title'
                    ],
                    'hasDesign'        => 'true',
                    'hasCounter'       => 'true',
                    'enableCounter'    => 'true',
                    'backgroundColor'  => 'brand',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::RECENT_ARTICLES_PLUS_POPULAR_ARTICLES,
                'twigFile' => '/article/recent-articles-plus-popular-articles.html.twig',
                'type'     => Widget::ARTICLE_TYPE,
                'content'  => [
                    'backgroundColor'   => 'brand',
                    'hasDesign'         => 'true',
                    'labelPopularPosts' => 'Popular Articles',
                ],
                'modal'    => 'edit-generic-modal',
            ],
            [
                'title'    => Widget::ONE_HORIZONTAL_CARD,
                'twigFile' => '/cards/cards.html.twig',
                'type'     => Widget::CARDS_TYPE,
                'content'  => [
                    'cardType' => Widget::ONE_HORIZONTAL_CARD_TYPE,
                    'widgetTitle' => '', //Ex: "Featured Listing"
                    'widgetLink' => [
                        'label' => '', //Ex: "view more"
                        'page_id' => '',
                        'link' => '', //Ex: "/listing"
                    ],
                    'module' => '', //listing, event, classified, article, deal, blog
                    'banner' => false,
                    'columns' => 1,
                    'items' => [], //items id
                    'custom'  => [
                        'level' => [], //10, 30, 50, 70
                        'order1' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'order2' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'quantity' => 1,
                        'categories' => [], //categories IDs
                        'locations' => [ //locations IDs
                            'location_1' => '',
                            'location_2' => '',
                            'location_3' => '',
                            'location_4' => '',
                            'location_5' => '',
                        ],
                    ],
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-cards-modal',
            ],
            [
                'title'    => Widget::THREE_VERTICAL_CARDS,
                'twigFile' => '/cards/cards.html.twig',
                'type'     => Widget::CARDS_TYPE,
                'content'  => [
                    'cardType' => Widget::THREE_VERTICAL_CARDS_TYPE,
                    'widgetTitle' => '', //Ex: "Featured Listing"
                    'widgetLink' => [
                        'label' => '', //Ex: "view more"
                        'page_id' => '',
                        'link' => '', //Ex: "/listing"
                    ],
                    'module' => '', //listing, event, classified, article, deal, blog
                    'banner' => false,
                    'columns' => 3,
                    'items' => [], //items id
                    'custom'  => [
                        'level' => [], //10, 30, 50, 70
                        'order1' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'order2' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'quantity' => 3,
                        'categories' => [], //categories IDs
                        'locations' => [ //locations IDs
                            'location_1' => '',
                            'location_2' => '',
                            'location_3' => '',
                            'location_4' => '',
                            'location_5' => '',
                        ],
                    ],
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-cards-modal',
            ],
            [
                'title'    => Widget::LIST_OF_HORIZONTAL_CARDS,
                'twigFile' => '/cards/cards.html.twig',
                'type'     => Widget::CARDS_TYPE,
                'content'  => [
                    'cardType' => Widget::LIST_OF_HORIZONTAL_CARDS_TYPE,
                    'widgetTitle' => '', //Ex: "Featured Listing"
                    'widgetLink' => [
                        'label' => '', //Ex: "view more"
                        'page_id' => '',
                        'link' => '', //Ex: "/listing"
                    ],
                    'module' => '', //listing, event, classified, article, deal, blog
                    'banner' => false,
                    'columns' => 1,
                    'items' => [], //items id
                    'custom'  => [
                        'level' => [], //10, 30, 50, 70
                        'order1' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'order2' => '', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                        'quantity' => 3,
                        'categories' => [], //categories IDs
                        'locations' => [ //locations IDs
                            'location_1' => '',
                            'location_2' => '',
                            'location_3' => '',
                            'location_4' => '',
                            'location_5' => '',
                        ],
                    ],
                    'hasDesign'       => 'true',
                    'backgroundColor' => 'brand',
                ],
                'modal'    => 'edit-cards-modal',
            ],
            /* CUSTOM ADDWIDGET
            * here are an example of how you add the widget 'Widget test'
            */
            /*  [
                   'title'    => 'Widget test',
                   'twigFile' => '/test/widget-test.html.twig',
                   'type'     => Widget::COMMON_TYPE,
                   'content'  => [
                       'labelTest'        => 'Test',
                   ],
                   'modal'    => 'edit-generic-modal',
                ],
            */
        ];
        
        return $standardWidgets;
    }
}
