<?php

namespace ArcaSolutions\WysiwygBundle\Services;

use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use ArcaSolutions\WysiwygBundle\Entity\PageWidget;
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
class PageTypeService
{

    /**
     * @var array
     */
    public $urlNonEditable     = [
        PageType::HOME_PAGE,
        PageType::RESULTS_PAGE,
        PageType::ERROR404_PAGE,
        PageType::ITEM_UNAVAILABLE_PAGE,
        PageType::MAINTENANCE_PAGE,
        PageType::LISTING_DETAIL_PAGE,
        PageType::ARTICLE_DETAIL_PAGE,
        PageType::BLOG_DETAIL_PAGE,
        PageType::CLASSIFIED_DETAIL_PAGE,
        PageType::DEAL_DETAIL_PAGE,
        PageType::EVENT_DETAIL_PAGE,
    ];
    /**
     * @var array
     */
    public $pageViewNotAllowed = [
        PageType::HOME_PAGE,
        PageType::RESULTS_PAGE,
        PageType::ERROR404_PAGE,
        PageType::ITEM_UNAVAILABLE_PAGE,
        PageType::MAINTENANCE_PAGE,
        PageType::LISTING_DETAIL_PAGE,
        PageType::ARTICLE_DETAIL_PAGE,
        PageType::BLOG_DETAIL_PAGE,
        PageType::CLASSIFIED_DETAIL_PAGE,
        PageType::DEAL_DETAIL_PAGE,
        PageType::EVENT_DETAIL_PAGE,
        PageType::LISTING_REVIEWS,
    ];

    /**
     * @var array
     */
    public $urlToRoute = [
        PageType::ARTICLE_CATEGORIES_PAGE    => 'alias_article_allcategories_url_divisor',
        PageType::ARTICLE_HOME_PAGE          => 'alias_article_module',
        PageType::BLOG_CATEGORIES_PAGE       => 'alias_blog_allcategories_url_divisor',
        PageType::BLOG_HOME_PAGE             => 'alias_blog_module',
        PageType::CLASSIFIED_CATEGORIES_PAGE => 'alias_classified_allcategories_url_divisor',
        PageType::CLASSIFIED_HOME_PAGE       => 'alias_classified_module',
        PageType::EVENT_CATEGORIES_PAGE      => 'alias_event_allcategories_url_divisor',
        PageType::EVENT_HOME_PAGE            => 'alias_event_module',
        PageType::LISTING_CATEGORIES_PAGE    => 'alias_listing_allcategories_url_divisor',
        PageType::LISTING_HOME_PAGE          => 'alias_listing_module',
        PageType::DEAL_CATEGORIES_PAGE       => 'alias_promotion_allcategories_url_divisor',
        PageType::DEAL_HOME_PAGE             => 'alias_promotion_module',
        PageType::ADVERTISE_PAGE             => 'alias_advertise_url_divisor',
        PageType::CONTACT_US_PAGE            => 'alias_contactus_url_divisor',
        PageType::FAQ_PAGE                   => 'alias_faq_url_divisor',
        PageType::TERMS_OF_SERVICE_PAGE      => 'alias_terms_url_divisor',
        PageType::PRIVACY_POLICY_PAGE        => 'alias_privacy_url_divisor',
        PageType::LISTING_ALL_LOCATIONS      => 'alias_alllocations_url_divisor',
        PageType::CLASSIFIED_ALL_LOCATIONS   => 'alias_alllocations_url_divisor',
        PageType::DEAL_ALL_LOCATIONS         => 'alias_alllocations_url_divisor',
        PageType::EVENT_ALL_LOCATIONS        => 'alias_alllocations_url_divisor',
        PageType::LISTING_REVIEWS            => 'alias_review_url_divisor',
    ];

    /**
     * @var array
     */
    public $urlConfirmation    = [
        'location' => [
            PageType::LISTING_ALL_LOCATIONS,
            PageType::CLASSIFIED_ALL_LOCATIONS,
            PageType::DEAL_ALL_LOCATIONS,
            PageType::EVENT_ALL_LOCATIONS,
        ],
        'category' => [
            PageType::LISTING_CATEGORIES_PAGE,
            PageType::ARTICLE_CATEGORIES_PAGE,
            PageType::CLASSIFIED_CATEGORIES_PAGE,
            PageType::BLOG_CATEGORIES_PAGE,
            PageType::DEAL_CATEGORIES_PAGE,
            PageType::EVENT_CATEGORIES_PAGE,
        ],
        'review'   => [
            PageType::LISTING_REVIEWS,
        ],
    ];

    /**
     * @var array
     */
    private $pagesWithoutSEO = [
        PageType::RESULTS_PAGE,
        PageType::LISTING_DETAIL_PAGE,
        PageType::EVENT_DETAIL_PAGE,
        PageType::CLASSIFIED_DETAIL_PAGE,
        PageType::DEAL_DETAIL_PAGE,
        PageType::ARTICLE_DETAIL_PAGE,
        PageType::BLOG_DETAIL_PAGE,
        PageType::ERROR404_PAGE,
        PageType::ITEM_UNAVAILABLE_PAGE,
    ];

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

        /* ModStorpagees Hooks */
        HookFire("pagetype_construct", [
            "that"               => &$this,
            "urlToRoute"         => &$this->urlToRoute,
            "urlNonEditable"     => &$this->urlNonEditable,
            "pageViewNotAllowed" => &$this->pageViewNotAllowed,
            "pagesWithoutSEO"    => &$this->pagesWithoutSEO,
        ]);
    }

    /**
     * Return the base url to the wysiwyg pages
     *
     * @param string $pageType
     *
     * @return string
     */
    public function getBaseUrl($pageType)
    {
        $uri = $this->getModuleUri($pageType);

        $domainUrl = $this->container->get('multi_domain.information')->getOriginalActiveHost();

        if ($this->container->get('request_stack')->getCurrentRequest()) {
            $scheme = $this->container->get('request_stack')->getCurrentRequest()->getScheme().'://';
        } else {
            $scheme = '';
        }

        return $scheme.$domainUrl.($uri ? '/'.$uri : '');
    }

    /**
     * Return the module uri from pageType
     *
     * @param string $pageType
     *
     * @return string
     */
    public function getModuleUri($pageType)
    {
        $uri = '';

        switch ($pageType) {
            case PageType::LISTING_ALL_LOCATIONS:
            case PageType::LISTING_CATEGORIES_PAGE:
            case PageType::LISTING_REVIEWS:
                $uri = $this->container->getParameter('alias_listing_module');
                break;
            case PageType::ARTICLE_CATEGORIES_PAGE:
                $uri = $this->container->getParameter('alias_article_module');
                break;
            case PageType::BLOG_CATEGORIES_PAGE:
                $uri = $this->container->getParameter('alias_blog_module');
                break;
            case PageType::CLASSIFIED_ALL_LOCATIONS:
            case PageType::CLASSIFIED_CATEGORIES_PAGE:
                $uri = $this->container->getParameter('alias_classified_module');
                break;
            case PageType::DEAL_ALL_LOCATIONS:
            case PageType::DEAL_CATEGORIES_PAGE:
                $uri = $this->container->getParameter('alias_promotion_module');
                break;
            case PageType::EVENT_ALL_LOCATIONS:
            case PageType::EVENT_CATEGORIES_PAGE:
                $uri = $this->container->getParameter('alias_event_module');
                break;
        }

        return $uri;
    }

    /**
     * @param string $pageType The title of the PageType
     *
     * @return array
     */
    public function getMessageUrlConfirmation($pageType)
    {
        $return = [
            'no' => ''
        ];

        $translator = $this->container->get('translator');
        $sitemgrLanguage = substr($this->container->get('settings')->getSetting('sitemgr_language'), 0, 2);

        switch ($pageType) {
            case PageType::LISTING_ALL_LOCATIONS:
            case PageType::EVENT_ALL_LOCATIONS:
            case PageType::CLASSIFIED_ALL_LOCATIONS:
            case PageType::DEAL_ALL_LOCATIONS:
                $return['text'] = $translator->trans('The new page URL will be applied for all locations page.', [],
                    'messages', $sitemgrLanguage);
                $return['yes'] = $translator->trans('Ok, continue', [], 'messages', $sitemgrLanguage);
                $return['replica'] = 'location';
                break;
            case PageType::LISTING_CATEGORIES_PAGE:
            case PageType::DEAL_CATEGORIES_PAGE:
            case PageType::CLASSIFIED_CATEGORIES_PAGE:
            case PageType::ARTICLE_CATEGORIES_PAGE:
            case PageType::EVENT_CATEGORIES_PAGE:
            case PageType::BLOG_CATEGORIES_PAGE:
                $return['text'] = $translator->trans('Would you like to update the page URL for all categories pages with the same value of this page?',
                    [], 'messages', $sitemgrLanguage);
                $return['yes'] = $translator->trans('Ok, continue', [], 'messages', $sitemgrLanguage);
                $return['no'] = $translator->trans('No', [], 'messages', $sitemgrLanguage);
                $return['replica'] = 'category';
                break;
            case PageType::LISTING_REVIEWS:
                $return['text'] = $translator->trans('The new page URL will be applied for all reviews page.', [],
                    'messages', $sitemgrLanguage);
                $return['yes'] = $translator->trans('Ok, continue', [], 'messages', $sitemgrLanguage);
                $return['replica'] = 'review';
                break;
        }

        return $return;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getCustomContentAndTitlePerPageType($type)
    {
        /** @var PageType $pageType */
        $pageType = $this->container->get('doctrine')->getRepository('WysiwygBundle:PageType')->findOneBy(['title' => $type]);

        /** @var Page $page */
        $pageType and $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->findOneBy(['pageTypeId' => $pageType->getId()]);

        /** @var Widget $customContent */
        $page and $customContent = $this->container->get('doctrine')->getRepository('WysiwygBundle:Widget')->findOneBy(['title' => 'Custom Content']);

        /** @var PageWidget[] $customWidgets */
        $customContent and $customWidgets = $this->container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')
            ->findBy([
                'pageId'   => $page->getId(),
                'themeId'  => $this->container->get('theme.service')->getSelectedTheme()->getId(),
                'widgetId' => $customContent->getId(),
            ], ['order' => 'ASC']);

        $html = '';
        if ($customWidgets) {
            $count = 0;
            foreach ($customWidgets as $eachCustomWidget) {
                $content = json_decode($eachCustomWidget->getContent());
                $html .= ($count ? '<br />' : '').$content->{'customHtml'};
                $count++;
            }
        }

        return ['title' => $page ? $page->getTitle() : '', 'body' => $html];
    }

    /**
     * @return array
     */
    public function getPagesWithoutSEO()
    {
        return $this->pagesWithoutSEO;
    }
}
