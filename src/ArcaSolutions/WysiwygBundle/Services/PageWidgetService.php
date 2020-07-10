<?php

namespace ArcaSolutions\WysiwygBundle\Services;

use ArcaSolutions\ArticleBundle\Entity\Internal\ArticleLevelFeatures;
use ArcaSolutions\BannersBundle\Entity\Internal\BannerLevelFeatures;
use ArcaSolutions\ClassifiedBundle\Entity\Internal\ClassifiedLevelFeatures;
use ArcaSolutions\CoreBundle\Logic\FriendlyUrlLogic;
use ArcaSolutions\EventBundle\Entity\Internal\EventLevelFeatures;
use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use ArcaSolutions\WysiwygBundle\Entity\PageWidget;
use ArcaSolutions\WysiwygBundle\Entity\Theme;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use ArcaSolutions\WysiwygBundle\Entity\WidgetTheme;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\DataCollectorTranslator;

/**
 * Class PageWidgetService
 *
 * This service handles everything but RENDERING that has something to do with Wysiwyg
 * Create, Edit, Delete pages and their widgets
 * Retrieving the data from DB, saving data in DB.
 *
 */
class PageWidgetService
{
    /**
     * ContainerInterface
     *
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        
        HookFire('pagewidget_construct', [
            'that' => &$this,
        ]);
    }
    
    /**
     * @param Widget $widget
     * @param EntityManager $em
     * @param $content
     * @throws \Doctrine\ORM\ORMException
     */
    public function replicateHeaderOrFooterForAllPages(Widget $widget, $em, $content = null)
    {
        if ($widget && in_array($widget->getType(), [Widget::HEADER_TYPE, Widget::FOOTER_TYPE], true)) {
            
            $pageWidgetsToUpdate = $this->container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')
                ->getPageWidgetByTypeOfAllPages(
                    $widget->getType(),
                    $widget->getId(),
                    $this->container->get('theme.service')->getSelectedTheme()->getId()
                );
            
            foreach ($pageWidgetsToUpdate as $pageWidget) {
                $pageWidget->setWidget($widget);
                $pageWidget->setContent($content ? $content : $widget->getContent());
                
                $em->persist($pageWidget);
            }
        }
    }
    
    /**
     * @param integer $id
     *
     * @return array
     */
    public function getWidgetFromPage($id)
    {
        // Get All widget by ID (Page_Widget Table)
        return $this->container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->findOneBy([
            'id'      => $id,
            'themeId' => $this->container->get('theme.service')->getSelectedTheme()->getId(),
        ]);
    }
    
    /**
     * @param integer $id
     *
     * @return array
     */
    public function getWidgetsPerPage($id)
    {
        return $this->container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')
            ->findBy(['pageId' => $id, 'themeId' => $this->container->get('theme.service')->getSelectedTheme()->getId()], ['order' => 'ASC']);
    }
    
    /**
     * @param integer $pageId
     * @param array $postArray
     *
     * @return array
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @internal param array $pageWidgets
     */
    public function savePageWidgets($pageId, array $postArray)
    {
        $translator = $this->container->get('translator');
        $doctrine = $this->container->get('doctrine');
        /** @var EntityManager $em */
        $em = $doctrine->getManager();
        
        $sitemgrLanguage = substr($this->container->get('settings')->getSetting('sitemgr_language'), 0, 2);
        
        if($pageId) {
            
            /** @var Page $page */
            $page = $doctrine->getRepository('WysiwygBundle:Page')->find($pageId);
            
            /* Success message */
            $return = [
                'success' => true,
                'message' => $translator->trans('Changes successfully saved.', [], 'messages', $sitemgrLanguage),
            ];
            
            // Decode array containing each widget information
            $pageWidgets = json_decode($postArray['serializedPost'], true);
            
            //Set Page Information
            if (!empty($postArray['title'])) {
                $page->setTitle($postArray['title']);
            }
            
            $page->setMetaKey($postArray['keywords']);
            $page->setMetaDescription($postArray['description']);
            $page->setCustomTag($postArray['customTag']);
            $page->setSitemap($postArray['sitemap'] ?: false);
            
            if (!empty($postArray['url'])) {
                /* Checks if a url is unique */
                $pageTypes = [$page->getPageType()->getTitle()];
                
                foreach ($this->container->get('pagetype.service')->urlConfirmation as $key => $types) {
                    if (in_array($page->getPageType()->getTitle(), $types, true)) {
                        $pageTypes = $types;
                    }
                }
                
                if ($page->getPageType()->getTitle() !== PageType::CUSTOM_PAGE) {
                    $pageTypes = $doctrine->getRepository('WysiwygBundle:PageType')->getTypesPageIdLessCustomPage();
                }
                
                $friendlyUrlLogic = new FriendlyUrlLogic($this->container);
                
                $moduleUri = $this->container->get('pagetype.service')->getModuleUri($page->getPageType()->getTitle());
                
                if ($friendlyUrlLogic->checkUniqueFriendlyUrl($postArray['url'], $pageTypes, $pageId, $moduleUri) > 0) {
                    $return['success'] = false;
                    $return['message'] = $translator->trans('The page URL entered is already being used by another page, please choose another URL. The remaining changes were successfully saved.',
                        [], 'messages', $sitemgrLanguage);
                } else {
                    if ($page->getPageType()->getTitle() === PageType::CUSTOM_PAGE) {
                        $postArray['url'] = str_replace('.html', '', $postArray['url']);
                    } else if (!empty($postArray['replica'])) {
                        foreach ($this->container->get('pagetype.service')->urlConfirmation as $type => $types) {
                            if ($type != $postArray['replica']) {
                                continue;
                            }
                            
                            foreach ($types as $pageType) {
                                $this->saveUrl($postArray['url'], $pageType);
                                $pSave = $doctrine->getRepository('WysiwygBundle:Page')->getPageByType($pageType);
                                $pSave->setUrl($postArray['url']);
                            }
                        }
                    } else {
                        $this->saveUrl($postArray['url'], $page->getPageType()->getTitle());
                    }
                    
                    $page->setUrl($postArray['url']);
                }
            }
            
            $em->flush();
            
            $counter = 1;
            $mixpanelProps = [];
            if ($pageWidgets && $postArray['changed']) {
                foreach ($pageWidgets as $order => $item) {
                    if ($item['pageWidgetId']) {
                        /** @var PageWidget $pageWidget */
                        $pageWidget = $doctrine->getRepository('WysiwygBundle:PageWidget')->find($item['pageWidgetId']);
                        $pageWidget->setOrder($order);
                        $mixpanelProps['Widget '.$counter++] = $pageWidget->getWidget()->getTitle();
                    } else {
                        $pageWidget = $this->saveWidget(null, $page->getId(), $item['widgetId']);
                        if ($pageWidget) {
                            $pageWidget->setOrder($order);
                            $em->persist($pageWidget);
                            $mixpanelProps['Widget '.$counter++] = $pageWidget->getWidget()->getTitle();
                        } else {
                            $return['success'] = false;
                            $return['message'] = $translator->trans('Something went wrong!', [], 'widgets',
                                $sitemgrLanguage);
                        }
                    }
                }
                $em->flush();
                $em->clear();
            }
            
            $mixpanelProps['Page'] = $page->getPageType()->getTitle();
            $mixpanelProps['Custom header tag'] = !empty($page->getCustomTag());
            
            $this->container->get('mixpanel.helper')
                ->trackEvent('Page updated', $mixpanelProps);
        } else {
            $return = [
                'success' => false,
                'message' => $translator->trans('The server encountered an internal error or misconfiguration and was unable to complete your request.', [], 'messages', $sitemgrLanguage),
            ];
        }
        
        return $return;
    }
    
    /**
     * Save a changed content of a widget
     *
     * @param integer $id
     * @param string $content
     *
     * @return PageWidget|null|object
     */
    public function saveWidgetContent($id, $content)
    {
        // Save Widget customized content (Page_Widget Table)
        try {
            $em = $this->container->get('doctrine')->getManager();
            if ($id) {
                /** @var PageWidget $pageWidget */
                $pageWidget = $this->container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->find($id);
                $pageWidget->setContent($content);
                
                $em->persist($pageWidget);
                
                $this->replicateHeaderOrFooterForAllPages($pageWidget->getWidget(), $em, $pageWidget->getContent());
                
                $em->flush();
                $em->clear();
                
                if ($pageWidget->getWidget()->getType() === Widget::SEARCH_TYPE && $pageWidget->getWidget()->getTitle() === Widget::RESULTS) {
                    $resultContent = json_decode($pageWidget->getContent(), true);
                    
                    $settingsResultSize = $resultContent['resultView'] === 'list' ? 'defaultSearchResultSize' : 'defaultSearchResultGridSize';
                    
                    $this->container->get('settings')->setSetting('result_size', $settingsResultSize);
                }
                
                return $pageWidget;
            }
        } catch (Exception $e) {
            $this->container->get('logger')->error($e->getMessage());
        }
    }
    
    /**
     * @param $pageWidgetId
     * @return bool
     */
    public function deleteWidgetFromPage($pageWidgetId)
    {
        $container = $this->container;
        $em = $container->get('doctrine')->getManager();
        $return = false;
        
        $pageWidget = $container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->find($pageWidgetId);
        if ($pageWidget) {
            $em->remove($pageWidget);
            $em->flush($pageWidget);
            $return = true;
        }
        
        return $return;
    }
    
    /**
     * Reset all widgets of the Page to the default configuration
     *
     * @param $pageId
     *
     * @return array
     */
    public function resetPage($pageId)
    {
        $em = $this->container->get('doctrine')->getManager();
        $translator = $this->container->get('translator');
        $themeService = $this->container->get('theme.service');
        
        $sitemgrLanguage = substr($this->container->get('settings')->getSetting('sitemgr_language'), 0, 2);
        
        /* Success message */
        $return = [
            'success' => true,
            'message' => $translator->trans('Page successfully reset.', [], 'messages', $sitemgrLanguage),
        ];
        
        try {
            $pageWidgets = $this->container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->findBy([
                'pageId'  => $pageId,
                'themeId' => $themeService->getSelectedTheme()->getId(),
            ]);
            
            if ($pageWidgets) {
                foreach ($pageWidgets as $pageWidget) {
                    $em->remove($pageWidget);
                }
                
                $em->flush();
            }
            
            /** @var Page $page */
            $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->find($pageId);
            
            $pageType = $page->getPageType()->getTitle();
            $this->container->get('theme.service')->setTheme($themeService->getSelectedTheme()->getTitle());
            
            /* Get Default Widgets Method */
            $method = 'get'.str_replace(' ', '', $pageType).'DefaultWidgets';
            
            if (method_exists($this, $method)) {
                $featuredLevels['listing'] = ListingLevelFeatures::getAllLevelsAndNormalize($this->container->get('doctrine'));
                $featuredLevels['event'] = EventLevelFeatures::getAllLevelsAndNormalize($this->container->get('doctrine'));
                $featuredLevels['article'] = ArticleLevelFeatures::getAllLevelsAndNormalize($this->container->get('doctrine'));
                $featuredLevels['classified'] = ClassifiedLevelFeatures::getAllLevelsAndNormalize($this->container->get('doctrine'));
                
                /* getting just featured levels */
                foreach($featuredLevels as $key => $featuredLevel) {
                    $featuredLevels[$key] = array_filter(array_map(function ($array) {
                        if ('y' == $array->isFeatured) {
                            return $array->level;
                        }
                    }, $featuredLevel));
                }
                
                $pages = $this->container->get('doctrine')->getRepository('WysiwygBundle:PageType')->getAllPageByType();
                
                $pageWidgetsArr = $this->$method($featuredLevels, $pages, $translator, $sitemgrLanguage);
            } else {
                if (isset($this->$method)) {
                    $pageWidgetsArr = call_user_func($this->$method);
                }
            }
            
            if ($pageWidgetsArr) {
                foreach ($pageWidgetsArr as $pageWidgetTitle) {
                    $content = null;
                    if(is_array($pageWidgetTitle)) {
                        $content = json_encode(current($pageWidgetTitle)['content']);
                        $pageWidgetTitle = key($pageWidgetTitle);
                    }
                    
                    /* @var $widget Widget */
                    $widget = $this->container->get('doctrine')->getRepository('WysiwygBundle:Widget')->findOneBy(['title' => $pageWidgetTitle]);
                    
                    $this->saveWidget(
                        $content,
                        $pageId,
                        $widget->getId()
                    );
                }
            }
        } catch (Exception $e) {
            $return = ['success' => false, 'message' => $e->getMessage()];
        }
        
        return $return;
    }
    
    /**
     * Create a New widget for a page at the bottom
     *
     * @param $content
     * @param null $pageId
     * @param null $widgetId
     * @param false $newPage
     *
     * @return PageWidget|bool
     */
    public function saveWidget($content, $pageId = null, $widgetId = null, $newPage = false)
    {
        try {
            $em = $this->container->get('doctrine')->getManager();
            /** @var Page $page */
            $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->find($pageId);
            $theme = $this->container->get('theme.service')->getSelectedTheme();
            /* @var Widget $widget */
            $widget = $this->container->get('doctrine')->getRepository('WysiwygBundle:Widget')->find($widgetId);
            
            /* checks if the header or footer of other pages are different and get the most used to the new page */
            if (!$content && $widget && in_array($widget->getType(), [Widget::HEADER_TYPE, Widget::FOOTER_TYPE], true)) {
                $widgetInfo = $this->container->get('widget.service')->getMostUsedWidgetInfo($widget->getType());
                if ($newPage) {
                    $widget = $widgetInfo[0][0];
                }
                $widgetContent = json_decode($widgetInfo[0]['content'], true);
                $content = json_decode($widget->getContent(), true);
                
                foreach ($widgetContent as $key => $value) {
                    if (isset($content[$key])) {
                        $content[$key] = $value;
                    }
                }
                $content = json_encode($content);
            }
            
            // Get new widget Order
            $order = $this->container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')
                ->findLastOrder($pageId, $this->container->get('theme.service')->getSelectedTheme());
            
            /* themes that has this $widget */
            $widgetThemes = array_map(function ($widgetTheme) {
                /* @var $widgetTheme WidgetTheme */
                return $widgetTheme->getTheme();
            }, $widget->getThemes()->toArray());
            
            /* validates if the actual $theme has the $widget */
            if (!in_array($theme, $widgetThemes)) {
                return false;
            }
            
            $pageWidget = new PageWidget();
            $pageWidget->setContent($content ?: $widget->getContent());
            $pageWidget->setPage($page);
            $pageWidget->setWidget($widget);
            $pageWidget->setTheme($theme);
            $pageWidget->setOrder($order);
            
            $em->persist($pageWidget);
            
            if (!$newPage) {
                $this->replicateHeaderOrFooterForAllPages($pageWidget->getWidget(), $em, $pageWidget->getContent());
            }
            
            $em->flush();
        } catch (Exception $e) {
            $this->container->get('logger')->error($e->getMessage());
        }
        
        return $pageWidget;
    }
    
    /**
     * @param int $pageId The Page Id
     *
     * @return array
     */
    public function getPageWidget($pageId)
    {
        $return = [];
        
        /** @var PageWidget[] $pageWidgets */
        $pageWidgets = $this->container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->findBy([
            'pageId'  => $pageId,
            'themeId' => $this->container->get('theme.service')->getSelectedTheme()->getId(),
        ]);
        
        foreach ($pageWidgets as $pageWidget) {
            $widget = $pageWidget->getWidget();
            $return[$widget->getTitle()] = $widget;
        }
        
        return $return;
    }
    
    /**
     * @param string $pageUrl The Page Url
     * @param string $pageType The Title of the PageType
     *
     * @return bool|void
     */
    public function saveUrl($pageUrl, $pageType)
    {
        if ($pageType === PageType::CUSTOM_PAGE) {
            return;
        }
        
        try {
            $url = [$this->container->get('pagetype.service')->urlToRoute[$pageType] => $pageUrl];
            
            // Saves configuration in yaml file
            $domain = new \Domain(SELECTED_DOMAIN_ID);
            $classSymfonyYml = new \Symfony('domains/'.$domain->getString('url').'.route.yml');
            $classSymfonyYml->save('Configs', ['parameters' => $url]);
            
            $fileConstPath = EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/conf/constants.inc.php';
            system_writeConstantsFile($fileConstPath, SELECTED_DOMAIN_ID, $url);
        } catch (Exception $exception) {
            $this->container->get('logger')->error('Page Editor: Error on save page url in yaml and constants files, ['. $exception->getMessage() .']');
        }
    }
    
    /**
     * Returns an array with all the standard pages and its own array of default widgets
     * USED IN LOAD DATA
     *
     * @return array
     * @throws \Twig_Error
     */
    public function getAllPageDefaultWidgets()
    {
        $featuredLevels['listing'] = ListingLevelFeatures::getAllLevelsAndNormalize($this->container->get('doctrine'));
        $featuredLevels['event'] = EventLevelFeatures::getAllLevelsAndNormalize($this->container->get('doctrine'));
        $featuredLevels['article'] = ArticleLevelFeatures::getAllLevelsAndNormalize($this->container->get('doctrine'));
        $featuredLevels['classified'] = ClassifiedLevelFeatures::getAllLevelsAndNormalize($this->container->get('doctrine'));
        
        /* getting just featured levels */
        foreach($featuredLevels as $key => $featuredLevel) {
            $featuredLevels[$key] = array_filter(array_map(function ($array) {
                if ('y' == $array->isFeatured) {
                    return $array->level;
                }
            }, $featuredLevel));
        }
        
        $pages = $this->container->get('doctrine')->getRepository('WysiwygBundle:PageType')->getAllPageByType();
        
        $translator = $this->container->get('translator');
        $sitemgrLanguage = substr($this->container->get('settings')->getSetting('sitemgr_language'), 0, 2);
        
        $pagesDefault = [];
        $pagesDefault[PageType::HOME_PAGE] = $this->getHomePageDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::RESULTS_PAGE] = $this->getDirectoryResultsDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::LISTING_HOME_PAGE] = $this->getListingHomeDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::LISTING_DETAIL_PAGE] = $this->getListingDetailDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::LISTING_REVIEWS] = $this->getListingReviewsDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::LISTING_CATEGORIES_PAGE] = $this->getListingViewAllCategoriesDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::LISTING_ALL_LOCATIONS] = $this->getListingViewAllLocationsDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::EVENT_HOME_PAGE] = $this->getEventHomeDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::EVENT_DETAIL_PAGE] = $this->getEventDetailDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::EVENT_CATEGORIES_PAGE] = $this->getEventViewAllCategoriesDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::EVENT_ALL_LOCATIONS] = $this->getEventViewAllLocationsDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::CLASSIFIED_HOME_PAGE] = $this->getClassifiedHomeDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::CLASSIFIED_DETAIL_PAGE] = $this->getClassifiedDetailDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::CLASSIFIED_CATEGORIES_PAGE] = $this->getClassifiedViewAllCategoriesDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::CLASSIFIED_ALL_LOCATIONS] = $this->getClassifiedViewAllLocationsDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::ARTICLE_HOME_PAGE] = $this->getArticleHomeDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::ARTICLE_DETAIL_PAGE] = $this->getArticleDetailDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::ARTICLE_CATEGORIES_PAGE] = $this->getArticleViewAllCategoriesDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::DEAL_HOME_PAGE] = $this->getDealHomeDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::DEAL_DETAIL_PAGE] = $this->getDealDetailDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::DEAL_CATEGORIES_PAGE] = $this->getDealViewAllCategoriesDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::DEAL_ALL_LOCATIONS] = $this->getDealViewAllLocationsDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::BLOG_HOME_PAGE] = $this->getBlogHomeDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::BLOG_DETAIL_PAGE] = $this->getBlogDetailDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::BLOG_CATEGORIES_PAGE] = $this->getBlogViewAllCategoriesDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::CONTACT_US_PAGE] = $this->getContactUsDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::ADVERTISE_PAGE] = $this->getAdvertisewithUsDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::FAQ_PAGE] = $this->getFaqDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::TERMS_OF_SERVICE_PAGE] = $this->getTermsofUseDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::PRIVACY_POLICY_PAGE] = $this->getPrivacyPolicyDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::MAINTENANCE_PAGE] = $this->getMaintenancePageDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::ERROR404_PAGE] = $this->getErrorPageDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        $pagesDefault[PageType::ITEM_UNAVAILABLE_PAGE] = $this->getItemUnavailablePageDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);
        /**
         * CUSTOM ADDPAGE
         *  you have to add these line to be used in loadPageWidgetData, and when the sitemgr changes the theme.
         */
        /*$pagesDefault[PageType::TEST_PAGE] = $this->getTestPageDefaultWidgets($featuredLevels, $pages, $translator, $sitemgrLanguage);*/
        
        /* ModStores Hooks */
        HookFire('pagewidgetservice_after_add_pagedefaultwidgets', [
            'that'         => &$this,
            'pagesDefault' => &$pagesDefault,
        ]);
        
        return $pagesDefault;
    }
    
    /**
     * Returns the widgets that compose the Home Page
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getHomePageDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SEARCH_BOX => [
                        'content'  => [
                            'labelStartYourSearch'      => $translator->trans('Start your search here', [], 'widgets', $sitemgrLanguage),
                            'labelWhatLookingFor'       => $translator->trans('What are you looking for?', [], 'widgets', $sitemgrLanguage),
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Food, service, hotel...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchLocation' => [
                                'value' => $translator->trans('Enter location...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by location field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'           => 'true',
                            'dataAlignment'       => 'center'
                        ],
                    ]
                ],
                '3' => [
                    Widget::HORIZONTAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Popular Deals', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => $translator->trans('more deals', [], 'widgets', $sitemgrLanguage),
                                'page_id' => $pages[PageType::DEAL_HOME_PAGE]['id'],
                                'link'    => ''
                            ],
                            'module'  => 'promotion',
                            'banner'  => false,
                            'columns' => 2,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'most_viewed',
                                'order2'     => 'random',
                                'quantity'   => 4,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'brand',
                        ]
                    ]
                ],
                '4' => Widget::LEADER_BOARD_AD_BAR,
                '5' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Listings', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => $translator->trans('more listings', [], 'messages', $sitemgrLanguage),
                                'page_id' => $pages[PageType::LISTING_HOME_PAGE]['id'],
                                'link'    => ''
                            ],
                            'module'  => 'listing',
                            'banner'  => false,
                            'columns' => 4,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['listing'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 4,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '6' => [
                    Widget::UPCOMING_EVENTS => [
                        'content'  => [
                            'labelUpcomingEvents' => $translator->trans('Upcoming Events', [], 'widgets', $sitemgrLanguage),
                            'limit'               => 8,
                            'hasDesign'           => 'true',
                            'backgroundColor'     => 'base',
                        ],
                    ]
                ],
                '7' => [
                    Widget::FEATURED_CATEGORIES_WITH_IMAGES => [
                        'content'  => [
                            'labelBrowseByCat' => $translator->trans('Browse by category', [], 'widgets', $sitemgrLanguage),
                            'labelMoreCat'     => $translator->trans('more categories', [], 'widgets', $sitemgrLanguage),
                            'limit'            => null,
                            'hasDesign'        => 'true',
                            'backgroundColor'  => 'neutral',
                            'hasCounter'       => 'true',
                            'enableCounter'    => 'true',
                            'customBanners'    => 'square',
                        ],
                    ]
                ],
                '8' => [
                    Widget::THREE_RECTANGLE_AD_BAR => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'largebanner',
                                3 => 'largebanner',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'neutral',
                        ],
                    ]
                ],
                '9' => Widget::BROWSE_BY_LOCATION,
                '10' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '11' => [
                    Widget::HORIZONTAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Classifieds', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => $translator->trans('more classifieds', [], 'widgets', $sitemgrLanguage),
                                'page_id' => $pages[PageType::CLASSIFIED_HOME_PAGE]['id'],
                                'link'    => ''
                            ],
                            'module'  => 'classified',
                            'banner'  => 'square',
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['classified'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 6,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '12' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Recent Articles', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => $translator->trans('more articles', [], 'messages', $sitemgrLanguage),
                                'page_id' => $pages[PageType::ARTICLE_HOME_PAGE]['id'],
                                'link'    => ''
                            ],
                            'module'  => 'article',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'recently_added',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '13' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '14' => Widget::FOOTER,
                /*
                 * CUSTOM ADDWIDGET
                 * here are an example of how you add the widget 'Widget test'
                 * at the Home Page default widgets for Default theme
                 * if you need that 'Widget test' to be available for all themes you have
                 * add in each array below
                 */
                /* 'Widget test',*/
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SEARCH_BOX => [
                        'content'  => [
                            'labelStartYourSearch'      => $translator->trans('Find What You Need', [], 'widgets', $sitemgrLanguage),
                            'labelWhatLookingFor'       => $translator->trans('We provides always our best services for our clients and  always try to achieve our client\'s trust and satisfaction.', [], 'widgets', $sitemgrLanguage),
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Search anything...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchLocation' => [
                                'value' => $translator->trans('Location', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by location field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'           => 'true',
                            'dataAlignment'       => 'center'
                        ],
                    ]
                ],
                '3' => [
                    Widget::FEATURED_CATEGORIES_WITH_IMAGES_TYPE_2 => [
                        'content'  => [
                            'labelFeaturedCategories' => $translator->trans('Featured categories', [], 'widgets', $sitemgrLanguage),
                            'labelAllCategories'      => $translator->trans('All categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'               => 'true',
                            'backgroundColor'         => 'base',
                            'hasCounter'              => 'false',
                            'enableCounter'           => 'true',
                        ],
                    ]
                ],
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content'  => [
                            'cardType' => Widget::THREE_VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Best doctors for you across 75 specialties', [], 'widgets', $sitemgrLanguage), //Ex: "Featured Listing"
                            'widgetLink' => [
                                'label'   => $translator->trans('more doctors', [], 'messages', $sitemgrLanguage),
                                'page_id' => $pages[PageType::LISTING_HOME_PAGE]['id'],
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => '', //listing, event, classified, article, deal, blog
                            'banner' => false,
                            'columns' => 3,
                            'items' => [], //items id
                            'custom'  => [
                                'level' => [], //10, 30, 50, 70
                                'order1' => 'avg_reviews', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'level', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::LEAD_FORM,
                '6' => [
                    Widget::RECENT_REVIEWS => [
                        'content'  => [
                            'labelRecentReviews'     => $translator->trans('Recent Reviews', [], 'messages', $sitemgrLanguage),
                            'hasDesign'              => 'true',
                            'backgroundColor'        => 'base',
                            'customBanners'          => 'empty',
                        ],
                    ]
                ],
                '7' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SLIDER,
                '3' => Widget::SEARCH_BAR,
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('TRY THESE DELICIOUS NEWS DISHES', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => $translator->trans('more deals', [], 'widgets', $sitemgrLanguage),
                                'page_id' => $pages[PageType::DEAL_HOME_PAGE]['id'],
                                'link'    => ''
                            ],
                            'module'  => 'promotion',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'most_viewed',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::UPCOMING_EVENTS => [
                        'content'  => [
                            'labelUpcomingEvents' => $translator->trans('UPCOMING EVENTS', [], 'widgets', $sitemgrLanguage),
                            'limit'               => 8,
                            'hasDesign'           => 'true',
                            'backgroundColor'     => 'base',
                        ],
                    ]
                ],
                '6' => [
                    Widget::FEATURED_CATEGORIES_WITH_IMAGES => [
                        'content'  => [
                            'labelBrowseByCat' => $translator->trans('DELICIOUS DISHES', [], 'widgets', $sitemgrLanguage),
                            'labelMoreCat'     => $translator->trans('more categories', [], 'widgets', $sitemgrLanguage),
                            'limit'            => null,
                            'hasDesign'        => 'true',
                            'backgroundColor'  => 'brand',
                            'hasCounter'       => 'true',
                            'enableCounter'    => 'true',
                            'customBanners'    => 'empty',
                        ],
                    ]
                ],
                '7' => Widget::THREE_RECTANGLE_AD_BAR,
                '8' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('TOP RESTAURANTS', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'listing',
                            'banner'  => false,
                            'columns' => 4,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'avg_reviews',
                                'order2'     => 'random',
                                'quantity'   => 4,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '9' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('TRY THESE DELICIOUS DESSERTS NEXT TIME YOU EAT OUT ', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => $translator->trans('more classifieds', [], 'widgets', $sitemgrLanguage),
                                'page_id' => $pages[PageType::CLASSIFIED_HOME_PAGE]['id'],
                                'link'    => ''
                            ],
                            'module'  => 'classified',
                            'banner'  => false,
                            'columns' => 2,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'random',
                                'order2'     => 'random',
                                'quantity'   => 2,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '10' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SEARCH_BOX => [
                        'content'  => [
                            'labelStartYourSearch'      => $translator->trans('We Can Help You!', [], 'widgets', $sitemgrLanguage),
                            'labelWhatLookingFor'       => $translator->trans('Find the best people to help you with your wedding, Let\'s explore.', [], 'widgets', $sitemgrLanguage),
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Search anything...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchLocation' => [
                                'value' => $translator->trans('Location', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by location field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'           => 'true',
                            'dataAlignment'       => 'center'
                        ],
                    ]
                ],
                '3' => [
                    Widget::HORIZONTAL_CARDS => [
                        'content'  => [
                            'cardType' => Widget::HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('FIND AND BOOK THE BEST VENDORS', [], 'widgets', $sitemgrLanguage),
                            'widgetLink' => [
                                'label' => $translator->trans('more vendors', [], 'widgets', $sitemgrLanguage),
                                'page_id' => $pages[PageType::CLASSIFIED_HOME_PAGE]['id'],
                                'link' => '',
                            ],
                            'module' => 'classified',
                            'banner' => '',
                            'columns' => 2,
                            'items' => [],
                            'custom'  => [
                                'level' => [],
                                'order1' => 'level',
                                'order2' => 'random',
                                'quantity' => 4,
                                'categories' => [],
                                'locations' => [
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
                    ]
                ],
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content'  => [
                            'cardType' => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('GET TO KNOW YOUR VENDORS', [], 'widgets', $sitemgrLanguage),
                            'widgetLink' => [
                                'label' => $translator->trans('more business', [], 'widgets', $sitemgrLanguage),
                                'page_id' => $pages[PageType::LISTING_HOME_PAGE]['id'],
                                'link' => '',
                            ],
                            'module' => 'listing',
                            'banner' => '',
                            'columns' => 3,
                            'items' => [],
                            'custom'  => [
                                'level' => [],
                                'order1' => 'level',
                                'order2' => 'random',
                                'quantity' => 3,
                                'categories' => [],
                                'locations' => [
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
                    ]
                ],
                '5' => [
                    Widget::FEATURED_CATEGORIES_WITH_ICONS => [
                        'content'  => [
                            'labelBrowseByCat' => $translator->trans('ALL OUR VENDORS', [], 'widgets', $sitemgrLanguage),
                            'labelMoreCat'     => $translator->trans('All categories', [], 'widgets', $sitemgrLanguage),
                            'limit'            => null,
                            'hasDesign'        => 'true',
                            'backgroundColor'  => 'brand',
                            'hasCounter'       => 'true',
                            'enableCounter'    => 'false',
                        ],
                    ]
                ],
                '6' => [
                    Widget::CENTRALIZED_HIGHLIGHTED_CARD => [
                        'content' => [
                            'cardType'    => Widget::CENTRALIZED_HIGHLIGHTED_CARD_TYPE,
                            'widgetTitle' => $translator->trans('HELPING YOUR WEDDING', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => $translator->trans('more posts', [], 'messages', $sitemgrLanguage),
                                'page_id' => $pages[PageType::BLOG_HOME_PAGE]['id'],
                                'link'    => ''
                            ],
                            'module'  => 'blog',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'most_viewed',
                                'order2'     => 'random',
                                'quantity'   => 5,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '7' => [
                    Widget::RECENT_REVIEWS => [
                        'content'  => [
                            'labelRecentReviews'     => $translator->trans('Recent Reviews', [], 'messages', $sitemgrLanguage),
                            'hasDesign'              => 'true',
                            'backgroundColor'        => 'base',
                            'customBanners'          => 'square',
                        ],
                    ]
                ],
                '8' => Widget::NEWSLETTER,
                '9' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Directory Results
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getDirectoryResultsDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::RESULTS_INFO,
                '5' => Widget::RESULTS,
                '6' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '7' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '8' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::RESULTS_INFO,
                '5' => Widget::RESULTS,
                '6' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '7' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::RESULTS_INFO,
                '5' => Widget::RESULTS,
                '6' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '7' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::RESULTS_INFO,
                '5' => Widget::RESULTS,
                '6' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '7' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Listing Home
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getListingHomeDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::FEATURED_CATEGORIES_WITH_IMAGES,
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Listings', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'listing',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['listing'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::THREE_RECTANGLE_AD_BAR => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'largebanner',
                                3 => 'largebanner',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => [
                    Widget::BROWSE_BY_LOCATION => [
                        'content'  => [
                            'labelExploreMorePlaces' => $translator->trans('Browse by location', [], 'widgets', $sitemgrLanguage),
                            'labelMoreLocations'     => $translator->trans('more locations', [], 'widgets', $sitemgrLanguage),
                            'limit'                  => 65,
                            'hasDesign'              => 'true',
                            'backgroundColor'        => 'neutral',
                            'hasCounter'             => 'true',
                            'enableCounter'          => 'true',
                            'customBanners'          => 'square',
                        ],
                    ]
                ],
                '7' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '8' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '9' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::FEATURED_CATEGORIES_WITH_IMAGES,
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Listings', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'listing',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['listing'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::THREE_RECTANGLE_AD_BAR => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'largebanner',
                                3 => 'largebanner',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => [
                    Widget::BROWSE_BY_LOCATION => [
                        'content'  => [
                            'labelExploreMorePlaces' => $translator->trans('Browse by location', [], 'widgets', $sitemgrLanguage),
                            'labelMoreLocations'     => $translator->trans('more locations', [], 'widgets', $sitemgrLanguage),
                            'limit'                  => 65,
                            'hasDesign'              => 'true',
                            'backgroundColor'        => 'neutral',
                            'hasCounter'             => 'true',
                            'enableCounter'          => 'true',
                            'customBanners'          => 'square',
                        ],
                    ]
                ],
                '7' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '8' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '9' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::FEATURED_CATEGORIES_WITH_IMAGES,
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Listings', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'listing',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['listing'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::THREE_RECTANGLE_AD_BAR => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'largebanner',
                                3 => 'largebanner',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => [
                    Widget::BROWSE_BY_LOCATION => [
                        'content'  => [
                            'labelExploreMorePlaces' => $translator->trans('Browse by location', [], 'widgets', $sitemgrLanguage),
                            'labelMoreLocations'     => $translator->trans('more locations', [], 'widgets', $sitemgrLanguage),
                            'limit'                  => 65,
                            'hasDesign'              => 'true',
                            'backgroundColor'        => 'neutral',
                            'hasCounter'             => 'true',
                            'enableCounter'          => 'true',
                            'customBanners'          => 'square',
                        ],
                    ]
                ],
                '7' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '8' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '9' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::FEATURED_CATEGORIES_WITH_IMAGES,
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Listings', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'listing',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['listing'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::THREE_RECTANGLE_AD_BAR => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'largebanner',
                                3 => 'largebanner',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => [
                    Widget::BROWSE_BY_LOCATION => [
                        'content'  => [
                            'labelExploreMorePlaces' => $translator->trans('Browse by location', [], 'widgets', $sitemgrLanguage),
                            'labelMoreLocations'     => $translator->trans('more locations', [], 'widgets', $sitemgrLanguage),
                            'limit'                  => 65,
                            'hasDesign'              => 'true',
                            'backgroundColor'        => 'neutral',
                            'hasCounter'             => 'true',
                            'enableCounter'          => 'true',
                            'customBanners'          => 'square',
                        ],
                    ]
                ],
                '7' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '8' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '9' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Listing Detail
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getListingDetailDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LISTING_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '6' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LISTING_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LISTING_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LISTING_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE  => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Listing Reviews
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getListingReviewsDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::LEADER_BOARD_AD_BAR,
                '3' => Widget::REVIEWS_BLOCK,
                '4' => [
                    Widget::THREE_RECTANGLE_AD_BAR => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'largebanner',
                                3 => 'largebanner',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '6' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::REVIEWS_BLOCK,
                '5' => Widget::THREE_RECTANGLE_AD_BAR,
                '6' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::REVIEWS_BLOCK,
                '5' => Widget::THREE_RECTANGLE_AD_BAR,
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::REVIEWS_BLOCK,
                '5' => Widget::THREE_RECTANGLE_AD_BAR,
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Listing View All Categories
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getListingViewAllCategoriesDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Listings Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '7' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Listings Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Listings Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Listings Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Listing View All Location
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getListingViewAllLocationsDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Listings Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '7' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Listings Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Listings Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Listings Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Event Home
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getEventHomeDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SEARCH_BAR => [
                        'content' => [
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Food, service, hotel...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchLocation' => [
                                'value' => $translator->trans('Enter location...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by location field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field won\'t be shown when used on Article and Blog pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchDate' => [
                                'value' => $translator->trans('Date', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by date field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field will be shown only when used on Events Pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'brand',
                        ]
                    ]
                ],
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::VERTICAL_CARD_PLUS_HORIZONTAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARD_HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Events', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'event',
                            'banner'  => false,
                            'columns' => 2,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['event'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 4,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::THREE_RECTANGLE_AD_BAR => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'largebanner',
                                3 => 'largebanner',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::UPCOMING_EVENTS_CAROUSEL,
                '7' => [
                    Widget::FEATURED_CATEGORIES_WITH_IMAGES => [
                        'content'  => [
                            'labelBrowseByCat' => $translator->trans('Browse by category', [], 'widgets', $sitemgrLanguage),
                            'labelMoreCat'     => $translator->trans('more categories', [], 'widgets', $sitemgrLanguage),
                            'limit'            => null,
                            'hasDesign'        => 'true',
                            'backgroundColor'  => 'neutral',
                            'hasCounter'       => 'true',
                            'enableCounter'    => 'true',
                            'customBanners'    => 'square',
                        ],
                    ]
                ],
                '8' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '9' => Widget::BROWSE_BY_LOCATION,
                '10' => [
                    Widget::EVENTS_CALENDAR => [
                        'content'  => [
                            'labelCalendar'   => $translator->trans('Events Calendar', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '11' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '12' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SEARCH_BAR => [
                        'content' => [
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Food, service, hotel...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchLocation' => [
                                'value' => $translator->trans('Enter location...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by location field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field won\'t be shown when used on Article and Blog pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchDate' => [
                                'value' => $translator->trans('Date', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by date field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field will be shown only when used on Events Pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'brand',
                        ]
                    ]
                ],
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::VERTICAL_CARD_PLUS_HORIZONTAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARD_HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Events', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'event',
                            'banner'  => false,
                            'columns' => 2,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['event'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 4,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::THREE_RECTANGLE_AD_BAR => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'largebanner',
                                3 => 'largebanner',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::UPCOMING_EVENTS_CAROUSEL,
                '7' => [
                    Widget::FEATURED_CATEGORIES_WITH_IMAGES => [
                        'content'  => [
                            'labelBrowseByCat' => $translator->trans('Browse by category', [], 'widgets', $sitemgrLanguage),
                            'labelMoreCat'     => $translator->trans('more categories', [], 'widgets', $sitemgrLanguage),
                            'limit'            => null,
                            'hasDesign'        => 'true',
                            'backgroundColor'  => 'neutral',
                            'hasCounter'       => 'true',
                            'enableCounter'    => 'true',
                            'customBanners'    => 'square',
                        ],
                    ]
                ],
                '8' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '9' => Widget::BROWSE_BY_LOCATION,
                '10' => [
                    Widget::EVENTS_CALENDAR => [
                        'content'  => [
                            'labelCalendar'   => $translator->trans('Events Calendar', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '11' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '12' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => [
                    Widget::SEARCH_BAR => [
                        'content' => [
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Food, service, hotel...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchLocation' => [
                                'value' => $translator->trans('Enter location...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by location field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field won\'t be shown when used on Article and Blog pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchDate' => [
                                'value' => $translator->trans('Date', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by date field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field will be shown only when used on Events Pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'brand',
                        ]
                    ]
                ],
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::VERTICAL_CARD_PLUS_HORIZONTAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARD_HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Events', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'event',
                            'banner'  => false,
                            'columns' => 2,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['event'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 4,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::THREE_RECTANGLE_AD_BAR => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'largebanner',
                                3 => 'largebanner',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::UPCOMING_EVENTS_CAROUSEL,
                '7' => [
                    Widget::FEATURED_CATEGORIES_WITH_IMAGES => [
                        'content'  => [
                            'labelBrowseByCat' => $translator->trans('Browse by category', [], 'widgets', $sitemgrLanguage),
                            'labelMoreCat'     => $translator->trans('more categories', [], 'widgets', $sitemgrLanguage),
                            'limit'            => null,
                            'hasDesign'        => 'true',
                            'backgroundColor'  => 'neutral',
                            'hasCounter'       => 'true',
                            'enableCounter'    => 'true',
                            'customBanners'    => 'square',
                        ],
                    ]
                ],
                '8' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '9' => Widget::BROWSE_BY_LOCATION,
                '10' => [
                    Widget::EVENTS_CALENDAR => [
                        'content'  => [
                            'labelCalendar'   => $translator->trans('Events Calendar', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '11' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '12' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SEARCH_BAR => [
                        'content' => [
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Food, service, hotel...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchLocation' => [
                                'value' => $translator->trans('Enter location...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by location field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field won\'t be shown when used on Article and Blog pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchDate' => [
                                'value' => $translator->trans('Date', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by date field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field will be shown only when used on Events Pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'brand',
                        ]
                    ]
                ],
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::VERTICAL_CARD_PLUS_HORIZONTAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARD_HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Events', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'event',
                            'banner'  => false,
                            'columns' => 2,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['event'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 4,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::THREE_RECTANGLE_AD_BAR => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'largebanner',
                                3 => 'largebanner',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::UPCOMING_EVENTS_CAROUSEL,
                '7' => [
                    Widget::FEATURED_CATEGORIES_WITH_IMAGES => [
                        'content'  => [
                            'labelBrowseByCat' => $translator->trans('Browse by category', [], 'widgets', $sitemgrLanguage),
                            'labelMoreCat'     => $translator->trans('more categories', [], 'widgets', $sitemgrLanguage),
                            'limit'            => null,
                            'hasDesign'        => 'true',
                            'backgroundColor'  => 'neutral',
                            'hasCounter'       => 'true',
                            'enableCounter'    => 'true',
                            'customBanners'    => 'square',
                        ],
                    ]
                ],
                '8' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '9' => Widget::BROWSE_BY_LOCATION,
                '10' => [
                    Widget::EVENTS_CALENDAR => [
                        'content'  => [
                            'labelCalendar'   => $translator->trans('Events Calendar', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '11' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '12' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Event Detail
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getEventDetailDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SEARCH_BAR => [
                        'content' => [
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Food, service, hotel...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchLocation' => [
                                'value' => $translator->trans('Enter location...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by location field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field won\'t be shown when used on Article and Blog pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchDate' => [
                                'value' => $translator->trans('Date', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by date field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field will be shown only when used on Events Pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'        => 'true',
                            'backgroundColor'  => 'brand',
                        ]
                    ]
                ],
                '3' => Widget::EVENT_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '6' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SEARCH_BAR => [
                        'content' => [
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Food, service, hotel...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchLocation' => [
                                'value' => $translator->trans('Enter location...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by location field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field won\'t be shown when used on Article and Blog pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchDate' => [
                                'value' => $translator->trans('Date', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by date field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field will be shown only when used on Events Pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'        => 'true',
                            'backgroundColor'  => 'brand',
                        ]
                    ]
                ],
                '3' => Widget::EVENT_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => [
                    Widget::SEARCH_BAR => [
                        'content' => [
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Food, service, hotel...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchLocation' => [
                                'value' => $translator->trans('Enter location...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by location field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field won\'t be shown when used on Article and Blog pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchDate' => [
                                'value' => $translator->trans('Date', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by date field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field will be shown only when used on Events Pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'brand',
                        ]
                    ]
                ],
                '3' => Widget::EVENT_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SEARCH_BAR => [
                        'content' => [
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Food, service, hotel...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchLocation' => [
                                'value' => $translator->trans('Enter location...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by location field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field won\'t be shown when used on Article and Blog pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderSearchDate' => [
                                'value' => $translator->trans('Date', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by date field', [], 'widgets', $sitemgrLanguage),
                                'hint'  => $translator->trans('This field will be shown only when used on Events Pages', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'brand',
                        ]
                    ]
                ],
                '3' => Widget::EVENT_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Event View All Categories
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getEventViewAllCategoriesDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Events Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '7' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Events Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Events Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Events Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Event View All Location
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getEventViewAllLocationsDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Events Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '7' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Events Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Events Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Events Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Classified Home
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getClassifiedHomeDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Classifieds', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'classified',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['classified'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => Widget::FEATURED_CATEGORIES_WITH_IMAGES,
                '6' => Widget::THREE_RECTANGLE_AD_BAR,
                '7' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Popular Classifieds', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'classified',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'most_viewed',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '8' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '9' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '10' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Classifieds', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'classified',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['classified'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => Widget::FEATURED_CATEGORIES_WITH_IMAGES,
                '6' => Widget::THREE_RECTANGLE_AD_BAR,
                '7' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Popular Classifieds', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'classified',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'most_viewed',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '8' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '9' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '10' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Classifieds', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'classified',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['classified'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => Widget::FEATURED_CATEGORIES_WITH_IMAGES,
                '6' => Widget::THREE_RECTANGLE_AD_BAR,
                '7' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Popular Classifieds', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'classified',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'most_viewed',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '8' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '9' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '10' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Featured Classifieds', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'classified',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => $featuredLevels['classified'],
                                'order1'     => 'level',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => Widget::FEATURED_CATEGORIES_WITH_IMAGES,
                '6' => Widget::THREE_RECTANGLE_AD_BAR,
                '7' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Popular Classifieds', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'classified',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'most_viewed',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '8' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '9' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '10' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Classified Detail
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getClassifiedDetailDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::CLASSIFIED_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '6' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::CLASSIFIED_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::CLASSIFIED_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::CLASSIFIED_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Classified View All Categories
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getClassifiedViewAllCategoriesDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Classifieds Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '7' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Classifieds Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Classifieds Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Classifieds Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Classified View All Location
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getClassifiedViewAllLocationsDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Classifieds Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '7' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Classifieds Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Classifieds Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Classifieds Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Article Home
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getArticleHomeDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SECTION_HEADER => [
                        'content' => [
                            'labelHeader'        => $translator->trans('Articles', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'          => 'true',
                            'backgroundColor'    => 'brand',
                            'dataAlignment'      => 'center'
                        ]
                    ],
                ],
                '3' => [
                    Widget::FEATURED_CATEGORIES_TYPE_2 => [
                        'content'  => [
                            'placeholderTitle' => [
                                'value' => $translator->trans('Trending Topics', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Title', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'        => 'true',
                            'hasCounter'       => 'true',
                            'enableCounter'    => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '4' => [
                    Widget::ONE_HORIZONTAL_CARD => [
                        'content'  => [
                            'cardType' => Widget::ONE_HORIZONTAL_CARD_TYPE,
                            'widgetTitle' => '',
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'article', //listing, event, classified, article, deal, blog
                            'banner' => false,
                            'columns' => 1,
                            'items' => [], //items id
                            'custom'  => [
                                'level' => [], //10, 30, 50, 70
                                'order1' => 'recently_added', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => [
                    Widget::RECENT_ARTICLES_PLUS_POPULAR_ARTICLES => [
                        'content'  => [
                            'backgroundColor'   => 'base',
                            'hasDesign'         => 'true',
                            'labelPopularPosts' => $translator->trans('Popular Articles', [], 'widgets', $sitemgrLanguage),
                        ]
                    ]
                ],
                '6' => [
                    Widget::THREE_VERTICAL_CARDS => [
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
                                'order1' => 'recently_added', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '7' => [
                    Widget::LIST_OF_HORIZONTAL_CARDS => [
                        'content'  => [
                            'cardType' => Widget::LIST_OF_HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => '', //Ex: "Featured Listing"
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'article', //listing, event, classified, article, deal, blog
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '8' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '9' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SECTION_HEADER => [
                        'content' => [
                            'labelHeader'        => $translator->trans('Articles', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'          => 'true',
                            'backgroundColor'    => 'brand',
                            'dataAlignment'      => 'center'
                        ]
                    ],
                ],
                '3' => [
                    Widget::FEATURED_CATEGORIES_TYPE_2 => [
                        'content'  => [
                            'placeholderTitle' => [
                                'value' => $translator->trans('Trending Topics', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Title', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'        => 'true',
                            'hasCounter'       => 'true',
                            'enableCounter'    => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '4' => [
                    Widget::ONE_HORIZONTAL_CARD => [
                        'content'  => [
                            'cardType' => Widget::ONE_HORIZONTAL_CARD_TYPE,
                            'widgetTitle' => '',
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'article', //listing, event, classified, article, deal, blog
                            'banner' => false,
                            'columns' => 1,
                            'items' => [], //items id
                            'custom'  => [
                                'level' => [], //10, 30, 50, 70
                                'order1' => 'recently_added', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => [
                    Widget::RECENT_ARTICLES_PLUS_POPULAR_ARTICLES => [
                        'content'  => [
                            'backgroundColor'   => 'base',
                            'hasDesign'         => 'true',
                            'labelPopularPosts' => $translator->trans('Popular Articles', [], 'widgets', $sitemgrLanguage),
                        ]
                    ]
                ],
                '6' => [
                    Widget::THREE_VERTICAL_CARDS => [
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
                                'order1' => 'recently_added', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '7' => [
                    Widget::LIST_OF_HORIZONTAL_CARDS => [
                        'content'  => [
                            'cardType' => Widget::LIST_OF_HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => '', //Ex: "Featured Listing"
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'article', //listing, event, classified, article, deal, blog
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '8' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '9' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => [
                    Widget::SECTION_HEADER => [
                        'content' => [
                            'labelHeader'        => $translator->trans('Articles', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'          => 'true',
                            'backgroundColor'    => 'brand',
                            'dataAlignment'      => 'center'
                        ]
                    ],
                ],
                '3' => [
                    Widget::FEATURED_CATEGORIES_TYPE_2 => [
                        'content'  => [
                            'placeholderTitle' => [
                                'value' => $translator->trans('Trending Topics', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Title', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'        => 'true',
                            'hasCounter'       => 'true',
                            'enableCounter'    => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '4' => [
                    Widget::ONE_HORIZONTAL_CARD => [
                        'content'  => [
                            'cardType' => Widget::ONE_HORIZONTAL_CARD_TYPE,
                            'widgetTitle' => '',
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'article', //listing, event, classified, article, deal, blog
                            'banner' => false,
                            'columns' => 1,
                            'items' => [], //items id
                            'custom'  => [
                                'level' => [], //10, 30, 50, 70
                                'order1' => 'recently_added', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => [
                    Widget::RECENT_ARTICLES_PLUS_POPULAR_ARTICLES => [
                        'content'  => [
                            'backgroundColor'   => 'base',
                            'hasDesign'         => 'true',
                            'labelPopularPosts' => $translator->trans('Popular Articles', [], 'widgets', $sitemgrLanguage),
                        ]
                    ]
                ],
                '6' => [
                    Widget::THREE_VERTICAL_CARDS => [
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
                                'order1' => 'recently_added', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '7' => [
                    Widget::LIST_OF_HORIZONTAL_CARDS => [
                        'content'  => [
                            'cardType' => Widget::LIST_OF_HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => '', //Ex: "Featured Listing"
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'article', //listing, event, classified, article, deal, blog
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '8' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '9' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SECTION_HEADER => [
                        'content' => [
                            'labelHeader'        => $translator->trans('Articles', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'          => 'true',
                            'backgroundColor'    => 'brand',
                            'dataAlignment'      => 'center'
                        ]
                    ],
                ],
                '3' => [
                    Widget::FEATURED_CATEGORIES_TYPE_2 => [
                        'content'  => [
                            'placeholderTitle' => [
                                'value' => $translator->trans('Trending Topics', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Title', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'        => 'true',
                            'hasCounter'       => 'true',
                            'enableCounter'    => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '4' => [
                    Widget::ONE_HORIZONTAL_CARD => [
                        'content'  => [
                            'cardType' => Widget::ONE_HORIZONTAL_CARD_TYPE,
                            'widgetTitle' => '',
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'article', //listing, event, classified, article, deal, blog
                            'banner' => false,
                            'columns' => 1,
                            'items' => [], //items id
                            'custom'  => [
                                'level' => [], //10, 30, 50, 70
                                'order1' => 'recently_added', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => [
                    Widget::RECENT_ARTICLES_PLUS_POPULAR_ARTICLES => [
                        'content'  => [
                            'backgroundColor'   => 'base',
                            'hasDesign'         => 'true',
                            'labelPopularPosts' => $translator->trans('Popular Articles', [], 'widgets', $sitemgrLanguage),
                        ]
                    ]
                ],
                '6' => [
                    Widget::THREE_VERTICAL_CARDS => [
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
                                'order1' => 'recently_added', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '7' => [
                    Widget::LIST_OF_HORIZONTAL_CARDS => [
                        'content'  => [
                            'cardType' => Widget::LIST_OF_HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => '', //Ex: "Featured Listing"
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'article', //listing, event, classified, article, deal, blog
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '8' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '9' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Article Detail
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getArticleDetailDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SEARCH_BAR => [
                        'content' => [
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Food, service, hotel...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'brand',
                        ]
                    ]
                ],
                '3' => Widget::ARTICLE_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '6' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SEARCH_BAR => [
                        'content' => [
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Food, service, hotel...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'brand',
                        ]
                    ]
                ],
                '3' => Widget::ARTICLE_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => [
                    Widget::SEARCH_BAR => [
                        'content' => [
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Food, service, hotel...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'brand',
                        ]
                    ]
                ],
                '3' => Widget::ARTICLE_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SEARCH_BAR => [
                        'content' => [
                            'placeholderSearchKeyword'  => [
                                'value' => $translator->trans('Food, service, hotel...', [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Placeholder for search by keyword field', [], 'widgets', $sitemgrLanguage)
                            ],
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'brand',
                        ]
                    ]
                ],
                '3' => Widget::ARTICLE_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Article View All Categories
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getArticleViewAllCategoriesDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Articles Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '7' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Articles Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Articles Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Articles Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Deal Home
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getDealHomeDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Special Deals', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'promotion',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'most_viewed',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => Widget::FEATURED_CATEGORIES_WITH_IMAGES,
                '6' => Widget::THREE_RECTANGLE_AD_BAR,
                '7' => [
                    Widget::HORIZONTAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('New Deals', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'promotion',
                            'banner'  => false,
                            'columns' => 2,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'recently_added',
                                'order2'     => 'random',
                                'quantity'   => 4,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'brand',
                        ]
                    ]
                ],
                '8' => Widget::BROWSE_BY_LOCATION,
                '9' => [
                    Widget::ONE_HORIZONTAL_CARD => [
                        'content'  => [
                            'cardType'        => Widget::ONE_HORIZONTAL_CARD_TYPE,
                            'widgetTitle'     => $translator->trans('Best Deal', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'      => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => '',
                            ],
                            'module'          => 'promotion',
                            'banner'          => false,
                            'columns'         => 1,
                            'items'           => [],
                            'custom'          => [
                                'level'      => [],
                                'order1'     => 'most_viewed',
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
                    ]
                ],
                '10' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '11' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '12' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Special Deals', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'promotion',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'most_viewed',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => Widget::FEATURED_CATEGORIES_WITH_IMAGES,
                '6' => Widget::THREE_RECTANGLE_AD_BAR,
                '7' => [
                    Widget::HORIZONTAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('New Deals', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'promotion',
                            'banner'  => false,
                            'columns' => 2,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'recently_added',
                                'order2'     => 'random',
                                'quantity'   => 4,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'brand',
                        ]
                    ]
                ],
                '8' => Widget::BROWSE_BY_LOCATION,
                '9' => [
                    Widget::ONE_HORIZONTAL_CARD => [
                        'content'  => [
                            'cardType' => Widget::ONE_HORIZONTAL_CARD_TYPE,
                            'widgetTitle' => $translator->trans('Best Deal', [], 'widgets', $sitemgrLanguage), //Ex: "Featured Listing"
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'promotion', //listing, event, classified, article, deal, blog
                            'banner' => false,
                            'columns' => 1,
                            'items' => [], //items id
                            'custom'  => [
                                'level' => [], //10, 30, 50, 70
                                'order1' => 'most_viewed', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '10' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '11' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '12' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Special Deals', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'promotion',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'most_viewed',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => Widget::FEATURED_CATEGORIES_WITH_IMAGES,
                '6' => Widget::THREE_RECTANGLE_AD_BAR,
                '7' => [
                    Widget::HORIZONTAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('New Deals', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'promotion',
                            'banner'  => false,
                            'columns' => 2,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'recently_added',
                                'order2'     => 'random',
                                'quantity'   => 4,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'brand',
                        ]
                    ]
                ],
                '8' => Widget::BROWSE_BY_LOCATION,
                '9' => [
                    Widget::ONE_HORIZONTAL_CARD => [
                        'content'  => [
                            'cardType' => Widget::ONE_HORIZONTAL_CARD_TYPE,
                            'widgetTitle' => $translator->trans('Best Deal', [], 'widgets', $sitemgrLanguage), //Ex: "Featured Listing"
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'promotion', //listing, event, classified, article, deal, blog
                            'banner' => false,
                            'columns' => 1,
                            'items' => [], //items id
                            'custom'  => [
                                'level' => [], //10, 30, 50, 70
                                'order1' => 'most_viewed', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '10' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '11' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '12' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('Special Deals', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'promotion',
                            'banner'  => false,
                            'columns' => 3,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'most_viewed',
                                'order2'     => 'random',
                                'quantity'   => 3,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ]
                    ]
                ],
                '5' => Widget::FEATURED_CATEGORIES_WITH_IMAGES,
                '6' => Widget::THREE_RECTANGLE_AD_BAR,
                '7' => [
                    Widget::HORIZONTAL_CARDS => [
                        'content' => [
                            'cardType'    => Widget::HORIZONTAL_CARDS_TYPE,
                            'widgetTitle' => $translator->trans('New Deals', [], 'widgets', $sitemgrLanguage),
                            'widgetLink'  => [
                                'label'   => '',
                                'page_id' => '',
                                'link'    => ''
                            ],
                            'module'  => 'promotion',
                            'banner'  => false,
                            'columns' => 2,
                            'items'   => [],
                            'custom'  => [
                                'level'      => [],
                                'order1'     => 'recently_added',
                                'order2'     => 'random',
                                'quantity'   => 4,
                                'categories' => [],
                                'locations'  => []
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'brand',
                        ]
                    ]
                ],
                '8' => Widget::BROWSE_BY_LOCATION,
                '9' => [
                    Widget::ONE_HORIZONTAL_CARD => [
                        'content'  => [
                            'cardType' => Widget::ONE_HORIZONTAL_CARD_TYPE,
                            'widgetTitle' => $translator->trans('Best Deal', [], 'widgets', $sitemgrLanguage), //Ex: "Featured Listing"
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'promotion', //listing, event, classified, article, deal, blog
                            'banner' => false,
                            'columns' => 1,
                            'items' => [], //items id
                            'custom'  => [
                                'level' => [], //10, 30, 50, 70
                                'order1' => 'most_viewed', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '10' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '11' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '12' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Deal Detail
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getDealDetailDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::DEAL_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '6' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::DEAL_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::DEAL_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::DEAL_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Deal View All Categories
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getDealViewAllCategoriesDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Deals Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '7' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Deals Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Deals Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Deals Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Deal View All Location
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getDealViewAllLocationsDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Deal Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '7' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Deal Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Deal Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_LOCATIONS => [
                        'content' => [
                            'labelExploreAllLocations' => $translator->trans('Explore All Deal Locations', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                => 'true',
                            'backgroundColor'          => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Blog Home
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getBlogHomeDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SECTION_HEADER => [
                        'content' => [
                            'labelHeader'        => $translator->trans('Blog', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'          => 'true',
                            'backgroundColor'    => 'brand',
                            'dataAlignment'      => 'center'
                        ]
                    ],
                ],
                '3' => [
                    Widget::TWO_COLUMNS_RECENT_POSTS => [
                        'content'  => [
                            'labelCategories'     => $translator->trans('Categories', [], 'widgets', $sitemgrLanguage),
                            'labelPopularPosts'   => $translator->trans('Popular Posts', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'           => 'false',
                            'backgroundColor'     => 'base',
                        ],
                    ]
                ],
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content'  => [
                            'cardType' => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => '', //Ex: "Featured Listing"
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'blog', //listing, event, classified, article, deal, blog
                            'banner' => '', //null, square, wide skyscraper
                            'columns' => 3, //2, 3, 4
                            'items' => [], //items id
                            'custom'  => [
                                'level' => [], //10, 30, 50, 70
                                'order1' => 'most_viewed', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::NEWSLETTER,
                '7' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '8' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SECTION_HEADER => [
                        'content' => [
                            'labelHeader'        => $translator->trans('Blog', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'          => 'true',
                            'backgroundColor'    => 'brand',
                            'dataAlignment'      => 'center'
                        ]
                    ],
                ],
                '3' => [
                    Widget::TWO_COLUMNS_RECENT_POSTS => [
                        'content'  => [
                            'labelCategories'     => $translator->trans('Categories', [], 'widgets', $sitemgrLanguage),
                            'labelPopularPosts'   => $translator->trans('Popular Posts', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'           => 'false',
                            'backgroundColor'     => 'base',
                        ],
                    ]
                ],
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content'  => [
                            'cardType' => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => '', //Ex: "Featured Listing"
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'blog', //listing, event, classified, article, deal, blog
                            'banner' => '', //null, square, wide skyscraper
                            'columns' => 3, //2, 3, 4
                            'items' => [], //items id
                            'custom'  => [
                                'level' => [], //10, 30, 50, 70
                                'order1' => 'most_viewed', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::NEWSLETTER,
                '7' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '8' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => [
                    Widget::SECTION_HEADER => [
                        'content' => [
                            'labelHeader'        => $translator->trans('Blog', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'          => 'true',
                            'backgroundColor'    => 'brand',
                            'dataAlignment'      => 'center'
                        ]
                    ],
                ],
                '3' => [
                    Widget::TWO_COLUMNS_RECENT_POSTS => [
                        'content'  => [
                            'labelCategories'     => $translator->trans('Categories', [], 'widgets', $sitemgrLanguage),
                            'labelPopularPosts'   => $translator->trans('Popular Posts', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'           => 'false',
                            'backgroundColor'     => 'base',
                        ],
                    ]
                ],
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content'  => [
                            'cardType' => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => '', //Ex: "Featured Listing"
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'blog', //listing, event, classified, article, deal, blog
                            'banner' => '', //null, square, wide skyscraper
                            'columns' => 3, //2, 3, 4
                            'items' => [], //items id
                            'custom'  => [
                                'level' => [], //10, 30, 50, 70
                                'order1' => 'most_viewed', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::NEWSLETTER,
                '7' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '8' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SECTION_HEADER => [
                        'content' => [
                            'labelHeader'        => $translator->trans('Blog', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'          => 'true',
                            'backgroundColor'    => 'brand',
                            'dataAlignment'      => 'center'
                        ]
                    ],
                ],
                '3' => [
                    Widget::TWO_COLUMNS_RECENT_POSTS => [
                        'content'  => [
                            'labelCategories'     => $translator->trans('Categories', [], 'widgets', $sitemgrLanguage),
                            'labelPopularPosts'   => $translator->trans('Popular Posts', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'           => 'false',
                            'backgroundColor'     => 'base',
                        ],
                    ]
                ],
                '4' => [
                    Widget::VERTICAL_CARDS => [
                        'content'  => [
                            'cardType' => Widget::VERTICAL_CARDS_TYPE,
                            'widgetTitle' => '', //Ex: "Featured Listing"
                            'widgetLink' => [
                                'label' => '', //Ex: "view more"
                                'page_id' => '',
                                'link' => '', //Ex: "/listing"
                            ],
                            'module' => 'blog', //listing, event, classified, article, deal, blog
                            'banner' => '', //null, square, wide skyscraper
                            'columns' => 3, //2, 3, 4
                            'items' => [], //items id
                            'custom'  => [
                                'level' => [], //10, 30, 50, 70
                                'order1' => 'most_viewed', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
                                'order2' => 'random', //level, alphabetical, average reviews (for listings and articles only), recently added, recently updated, most viewed, upcoming (for events only), random
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
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::NEWSLETTER,
                '7' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '8' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Blog Detail
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getBlogDetailDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::BLOG_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '6' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::BLOG_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::BLOG_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::BLOG_DETAIL,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Blog View All Categories
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getBlogViewAllCategoriesDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Blog Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '7' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Blog Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Blog Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => [
                    Widget::ALL_CATEGORIES => [
                        'content' => [
                            'labelExploreAllCategories' => $translator->trans('Explore All Blog Categories', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'                 => 'true',
                            'backgroundColor'           => 'base',
                        ]
                    ]
                ],
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Contact Us
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getContactUsDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::CONTACT_FORM,
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '7' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::CONTACT_FORM,
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::CONTACT_FORM,
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::CONTACT_FORM,
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Advertise with Us
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getAdvertisewithUsDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::CALL_TO_ACTION => [
                        'content'  => [
                            'unsplash' => '',
                            'placeholderTitle' => [
                                'value' => $translator->trans("Sign up today - It's quick and simple!", [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Title', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderDescription' => [
                                'value' => $translator->trans("Demo Directory is proud to announce its new directory service which is now available online to visitors and new suppliers. It boasts endless amounts of new features for customers and suppliers. \nYour directory items are also controlled entirely by you. We have a members interface where you can log in and change any details, add special promotions for Demo Directory customers and much more!", [], 'widgets', $sitemgrLanguage),
                                'type'  => 'textarea',
                                'label' => $translator->trans('Description', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderCallToAction' => [
                                'value' => '',
                                'label' => $translator->trans('Button Text', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderLink' => [
                                'value' => 'custom',
                                'label' => $translator->trans('Button Link', [], 'widgets', $sitemgrLanguage),
                                'type'  => 'link',
                                'target' => 'external',
                                'customLink' => '',
                                'openWindow' => '',
                            ],
                            'imageId'             => '',
                            'hasDesign'      => 'true',
                            'dataAlignment'  => 'center'
                        ],
                    ],
                ],
                '3' => Widget::PRICING_AND_PLANS,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '6' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::CALL_TO_ACTION => [
                        'content'  => [
                            'unsplash' => '',
                            'placeholderTitle' => [
                                'value' => $translator->trans("Sign up today - It's quick and simple!", [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Title', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderDescription' => [
                                'value' => $translator->trans("Demo Directory is proud to announce its new directory service which is now available online to visitors and new suppliers. It boasts endless amounts of new features for customers and suppliers. \nYour directory items are also controlled entirely by you. We have a members interface where you can log in and change any details, add special promotions for Demo Directory customers and much more!", [], 'widgets', $sitemgrLanguage),
                                'type'  => 'textarea',
                                'label' => $translator->trans('Description', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderCallToAction' => [
                                'value' => '',
                                'label' => $translator->trans('Button Text', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderLink' => [
                                'value' => 'custom',
                                'label' => $translator->trans('Button Link', [], 'widgets', $sitemgrLanguage),
                                'type'  => 'link',
                                'target' => 'external',
                                'customLink' => '',
                                'openWindow' => '',
                            ],
                            'imageId'             => '',
                            'hasDesign'      => 'true',
                            'dataAlignment'  => 'center'
                        ],
                    ],
                ],
                '3' => Widget::PRICING_AND_PLANS,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => [
                    Widget::CALL_TO_ACTION => [
                        'content'  => [
                            'unsplash' => '',
                            'placeholderTitle' => [
                                'value' => $translator->trans("Sign up today - It's quick and simple!", [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Title', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderDescription' => [
                                'value' => $translator->trans("Demo Directory is proud to announce its new directory service which is now available online to visitors and new suppliers. It boasts endless amounts of new features for customers and suppliers. \nYour directory items are also controlled entirely by you. We have a members interface where you can log in and change any details, add special promotions for Demo Directory customers and much more!", [], 'widgets', $sitemgrLanguage),
                                'type'  => 'textarea',
                                'label' => $translator->trans('Description', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderCallToAction' => [
                                'value' => '',
                                'label' => $translator->trans('Button Text', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderLink' => [
                                'value' => 'custom',
                                'label' => $translator->trans('Button Link', [], 'widgets', $sitemgrLanguage),
                                'type'  => 'link',
                                'target' => 'external',
                                'customLink' => '',
                                'openWindow' => '',
                            ],
                            'imageId'             => '',
                            'hasDesign'      => 'true',
                            'dataAlignment'  => 'center'
                        ],
                    ],
                ],
                '3' => Widget::PRICING_AND_PLANS,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::CALL_TO_ACTION => [
                        'content'  => [
                            'unsplash' => '',
                            'placeholderTitle' => [
                                'value' => $translator->trans("Sign up today - It's quick and simple!", [], 'widgets', $sitemgrLanguage),
                                'label' => $translator->trans('Title', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderDescription' => [
                                'value' => $translator->trans("Demo Directory is proud to announce its new directory service which is now available online to visitors and new suppliers. It boasts endless amounts of new features for customers and suppliers. \nYour directory items are also controlled entirely by you. We have a members interface where you can log in and change any details, add special promotions for Demo Directory customers and much more!", [], 'widgets', $sitemgrLanguage),
                                'type'  => 'textarea',
                                'label' => $translator->trans('Description', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderCallToAction' => [
                                'value' => '',
                                'label' => $translator->trans('Button Text', [], 'widgets', $sitemgrLanguage)
                            ],
                            'placeholderLink' => [
                                'value' => 'custom',
                                'label' => $translator->trans('Button Link', [], 'widgets', $sitemgrLanguage),
                                'type'  => 'link',
                                'target' => 'external',
                                'customLink' => '',
                                'openWindow' => '',
                            ],
                            'imageId'             => '',
                            'hasDesign'      => 'true',
                            'dataAlignment'  => 'center'
                        ],
                    ],
                ],
                '3' => Widget::PRICING_AND_PLANS,
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the FAQ
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getFAQDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SECTION_HEADER => [
                        'content' => [
                            'labelHeader'        => $translator->trans('Frequently Asked Questions', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'          => 'true',
                            'backgroundColor'    => 'brand',
                            'dataAlignment'      => 'center'
                        ]
                    ],
                ],
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::FAQ_BOX,
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '7' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SECTION_HEADER => [
                        'content' => [
                            'labelHeader'        => $translator->trans('Frequently Asked Questions', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'          => 'true',
                            'backgroundColor'    => 'brand',
                            'dataAlignment'      => 'center'
                        ]
                    ],
                ],
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::FAQ_BOX,
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => [
                    Widget::SECTION_HEADER => [
                        'content' => [
                            'labelHeader'        => $translator->trans('Frequently Asked Questions', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'          => 'true',
                            'backgroundColor'    => 'brand',
                            'dataAlignment'      => 'center'
                        ]
                    ],
                ],
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::FAQ_BOX,
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => [
                    Widget::SECTION_HEADER => [
                        'content' => [
                            'labelHeader'        => $translator->trans('Frequently Asked Questions', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'          => 'true',
                            'backgroundColor'    => 'brand',
                            'dataAlignment'      => 'center'
                        ]
                    ],
                ],
                '3' => Widget::LEADER_BOARD_AD_BAR,
                '4' => Widget::FAQ_BOX,
                '5' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '6' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Terms of Service
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     * @throws \Twig_Error
     */
    public function getTermsofUseDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::LEADER_BOARD_AD_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-terms-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '6' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::LEADER_BOARD_AD_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-terms-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::LEADER_BOARD_AD_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-terms-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::LEADER_BOARD_AD_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-terms-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Privacy Policy
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     * @throws \Twig_Error
     */
    public function getPrivacyPolicyDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::LEADER_BOARD_AD_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-privacy-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '6' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::LEADER_BOARD_AD_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-privacy-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::LEADER_BOARD_AD_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-privacy-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::LEADER_BOARD_AD_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-privacy-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * CUSTOM ADDPAGE
     * here are an example of how you can create the default widgets for a Page
     * these list will be used to reset the Page
     */
    /*public function getTestPageDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                'Widget test',
            ],
            Theme::DOCTOR_THEME     => [
                'Widget test',
            ],
            Theme::RESTAURANT_THEME => [
                'Widget test',
            ],
            Theme::WEDDING_THEME    => [
                'Widget test',
            ],
        ];

        return $pageWidgetsTheme[$this->container->get('theme.service')->getSelectedTheme()->getTitle()];
    }*/
    
    //endregion
    
    /**
     * Returns the widgets that compose the Maintenance Page
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     * @throws \Twig_Error
     */
    public function getMaintenancePageDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        return [
            '1' => [
                Widget::CUSTOM_CONTENT => [
                    'content' => [
                        'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-maintenance-content.html.twig')
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Returns the widgets that compose the Error Page
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     * @throws \Twig_Error
     */
    public function getErrorPageDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-error404-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '6' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-error404-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-error404-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-error404-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    /**
     * Returns the widgets that compose the Item Unavailable Page
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     * @throws \Twig_Error
     */
    public function getItemUnavailablePageDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-itemunavailable-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::DOWNLOAD_OUR_APPS_BAR,
                '6' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => [
                    Widget::HEADER_WITH_CONTACT_PHONE => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-itemunavailable-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::SEARCH_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-itemunavailable-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => [
                    Widget::HEADER => [
                        'content'  => [
                            'labelDashboard'   => $translator->trans('Dashboard', [], 'widgets', $sitemgrLanguage),
                            'labelProfile'     => $translator->trans('Profile', [], 'widgets', $sitemgrLanguage),
                            'labelFaq'         => $translator->trans('Faq', [], 'widgets', $sitemgrLanguage),
                            'labelAccountPref' => $translator->trans('Settings', [], 'widgets', $sitemgrLanguage),
                            'labelLogOff'      => $translator->trans('Log Off', [], 'widgets', $sitemgrLanguage),
                            'labelListWithUs'  => $translator->trans('List with Us', [], 'widgets', $sitemgrLanguage),
                            'labelSignIn'      => $translator->trans('Sign In', [], 'widgets', $sitemgrLanguage),
                            'labelMore'        => $translator->trans('More', [], 'widgets', $sitemgrLanguage),
                            'hasDesign'        => 'true',
                            'isTransparent'    => 'false',
                            'stickyMenu'       => 'false',
                            'backgroundColor'  => 'base',
                        ],
                    ]
                ],
                '2' => Widget::SEARCH_BAR,
                '3' => [
                    Widget::CUSTOM_CONTENT => [
                        'content' => [
                            'customHtml' => $this->container->get('templating')->render('WysiwygBundle::default-itemunavailable-content.html.twig')
                        ]
                    ]
                ],
                '4' => [
                    Widget::BANNER_LARGE_MOBILE => [
                        'content'  => [
                            'bannerType'      => 'large-mobile',
                            'isWide'          => 'false',
                            'banners'         => [
                                1 => 'largebanner',
                                2 => 'google',
                                3 => 'sponsor-links',
                            ],
                            'hasDesign'       => 'true',
                            'backgroundColor' => 'base',
                        ],
                    ]
                ],
                '5' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getTheme()];
    }
    
    
    /**
     * Returns the widgets that compose the Custom Page
     * Used for load data and reset feature at sitemgr
     *
     * @param $featuredLevels
     * @param $pages
     * @param DataCollectorTranslator $translator
     * @param null $sitemgrLanguage
     * @return mixed
     */
    public function getCustomPageDefaultWidgets($featuredLevels = null, $pages = null, $translator = null, $sitemgrLanguage = null)
    {
        $pageWidgetsTheme = [
            Theme::DEFAULT_THEME    => [
                '1' => Widget::HEADER,
                '2' => Widget::CUSTOM_CONTENT,
                '3' => Widget::FOOTER,
            ],
            Theme::DOCTOR_THEME     => [
                '1' => Widget::HEADER_WITH_CONTACT_PHONE,
                '2' => Widget::CUSTOM_CONTENT,
                '3' => Widget::FOOTER_WITH_NEWSLETTER,
            ],
            Theme::RESTAURANT_THEME => [
                '1' => Widget::NAVIGATION_WITH_CENTERED_LOGO,
                '2' => Widget::CUSTOM_CONTENT,
                '3' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
            Theme::WEDDING_THEME    => [
                '1' => Widget::HEADER,
                '2' => Widget::CUSTOM_CONTENT,
                '3' => Widget::FOOTER_WITH_SOCIAL_MEDIA,
            ],
        ];
        
        return $pageWidgetsTheme[$this->container->get('theme.service')->getSelectedTheme()->getTitle()];
    }
}
