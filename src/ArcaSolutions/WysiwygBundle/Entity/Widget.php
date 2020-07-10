<?php

namespace ArcaSolutions\WysiwygBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Widget
 *
 * @ORM\Table(name="Widget")
 * @ORM\Entity(repositoryClass="ArcaSolutions\WysiwygBundle\Repository\WidgetRepository")
 */
class Widget
{
    /**
     * Possibles values for the `type` column
     */
    const HEADER_TYPE     = 'header';
    const COMMON_TYPE     = 'common';
    const SEARCH_TYPE     = 'search';
    const BANNER_TYPE     = 'banners';
    const LISTING_TYPE    = 'listings';
    const CARDS_TYPE      = 'cards';
    const EVENT_TYPE      = 'events';
    const ARTICLE_TYPE    = 'articles';
    const DEAL_TYPE       = 'deals';
    const BLOG_TYPE       = 'blog';
    const CLASSIFIED_TYPE = 'classifieds';
    const FOOTER_TYPE     = 'footer';
    const PRICING_TYPE    = 'pricing';
    const NEWSLETTER_TYPE = 'newsletter';
    const LEAD_TYPE       = 'lead';

    /**
     * Card Types
     */
    const VERTICAL_CARDS_TYPE                 = 'vertical-cards';
    const HORIZONTAL_CARDS_TYPE               = 'horizontal-cards';
    const VERTICAL_CARD_HORIZONTAL_CARDS_TYPE = 'vertical-card-plus-horizontal-cards';
    const TWO_COLUMNS_HORIZONTAL_CARDS_TYPE   = '2-columns-horizontal-cards';
    const CENTRALIZED_HIGHLIGHTED_CARD_TYPE   = 'centralized-highlighted-card';
    const ONE_HORIZONTAL_CARD_TYPE            = 'one-horizontal-card';
    const THREE_VERTICAL_CARDS_TYPE           = 'three-vertical-cards';
    const LIST_OF_HORIZONTAL_CARDS_TYPE       = 'list-of-horizontal-cards';

    /**
     * Widget Titles
     */
    const HEADER                                             = 'Header';
    const SEARCH_BOX                                         = 'Search box';
    const LEADER_BOARD_AD_BAR                                = 'Leaderboard ad bar (728x90)';
    const THREE_RECTANGLE_AD_BAR                             = '3 rectangle ad bar';
    const UPCOMING_EVENTS                                    = 'Upcoming Events';
    const BROWSE_BY_LOCATION                                 = 'Browse by Location';
    const BANNER_LARGE_MOBILE                                = 'Banner Large Mobile, one banner Sponsored Links and one Google Ads';
    const DOWNLOAD_OUR_APPS_BAR                              = 'Download our apps bar';
    const FOOTER                                             = 'Footer';
    const SEARCH_BAR                                         = 'Search Bar';
    const HORIZONTAL_CARDS                                   = 'Horizontal Cards';
    const VERTICAL_CARDS                                     = 'Vertical Cards';
    const VERTICAL_CARD_PLUS_HORIZONTAL_CARDS                = 'Vertical Card Plus Horizontal Cards';
    const TWO_COLUMNS_HORIZONTAL_CARDS                       = '2 Columns Horizontal Cards';
    const CENTRALIZED_HIGHLIGHTED_CARD                       = 'Centralized Highlighted Card';
    const UPCOMING_EVENTS_CAROUSEL                           = 'Upcoming Events Carousel';
    const RESULTS_INFO                                       = 'Results Info';
    const RESULTS                                            = 'Results';
    const LISTING_DETAIL                                     = 'Listing Detail';
    const EVENT_DETAIL                                       = 'Event Detail';
    const CLASSIFIED_DETAIL                                  = 'Classified Detail';
    const ARTICLE_DETAIL                                     = 'Article Detail';
    const DEAL_DETAIL                                        = 'Deal Detail';
    const BLOG_DETAIL                                        = 'Blog Detail';
    const CONTACT_FORM                                       = 'Contact form';
    const FAQ_BOX                                            = 'Faq box';
    const SECTION_HEADER                                     = 'Section header';
    const CUSTOM_CONTENT                                     = 'Custom Content';
    const PRICING_AND_PLANS                                  = 'Pricing & Plans';
    const ALL_LOCATIONS                                      = 'All Locations';
    const HEADER_WITH_CONTACT_PHONE                          = 'Header with Contact Phone';
    const FOOTER_WITH_NEWSLETTER                             = 'Footer with Newsletter';
    const RECENT_REVIEWS                                     = 'Recent Reviews';
    const NAVIGATION_WITH_CENTERED_LOGO                      = 'Navigation with Centered Logo';
    const FOOTER_WITH_SOCIAL_MEDIA                           = 'Footer with Social Media';
    const NAVIGATION_WITH_LEFT_LOGO_PLUS_SOCIAL_MEDIA        = 'Navigation with left Logo plus Social Media';
    const FOOTER_WITH_LOGO                                   = 'Footer with Logo';
    const REVIEWS_BLOCK                                      = 'Reviews block';
    const EVENTS_CALENDAR                                    = 'Events Calendar';
    const RECENT_MEMBERS                                     = 'Recent Members';
    const LISTING_PRICES                                     = 'Listing Prices';
    const EVENT_PRICES                                       = 'Event Prices';
    const CLASSIFIED_PRICES                                  = 'Classified Prices';
    const BANNER_PRICES                                      = 'Banner Prices';
    const ARTICLE_PRICES                                     = 'Article Prices';
    const NEWSLETTER                                         = 'Newsletter';
    const VIDEO_GALLERY                                      = 'Video Gallery';
    const LEAD_FORM                                          = 'Lead Form';
    const SOCIAL_NETWORK_BAR                                 = 'Social Network Bar';
    const CONTACT_INFORMATION_BAR                            = 'Contact Information Bar';
    const CALL_TO_ACTION                                     = 'Call to Action';
    const SLIDER                                             = 'Slider';
    const TWO_COLUMNS_RECENT_POSTS                           = '2 Columns Recent Posts';
    const FEATURED_CATEGORIES_WITH_IMAGES                    = 'Featured categories with images';
    const ALL_CATEGORIES                                     = 'All Categories';
    const FEATURED_CATEGORIES_WITH_IMAGES_TYPE_2             = 'Featured categories with images (Type 2)';
    const FEATURED_CATEGORIES                                = 'Featured categories';
    const FEATURED_CATEGORIES_WITH_ICONS                     = 'Featured categories with icons';
    const FEATURED_CATEGORIES_TYPE_2                         = 'Featured categories (Type 2)';
    const RECENT_ARTICLES_PLUS_POPULAR_ARTICLES              = 'Recent articles plus popular articles';
    const ONE_HORIZONTAL_CARD                                = 'One horizontal card';
    const THREE_VERTICAL_CARDS                               = 'Three vertical cards';
    const LIST_OF_HORIZONTAL_CARDS                           = 'List of horizontal cards';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", nullable=true)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\WysiwygBundle\Entity\WidgetTheme", mappedBy="widget")
     */
    private $themes;

    /**
     * @var string
     *
     * @ORM\Column(name="twig_file", type="string", nullable=false)
     */
    private $twigFile;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\WysiwygBundle\Entity\WidgetPageType", mappedBy="widget")
     */
    private $pageTypes;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\WysiwygBundle\Entity\PageWidget", mappedBy="widget")
     */
    private $pageWidgets;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="`type`", type="string", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="`modal`", type="string", nullable=true)
     */
    private $modal;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getThemes()
    {
        return $this->themes;
    }

    /**
     * @param mixed $themes
     */
    public function setThemes($themes)
    {
        $this->themes = $themes;
    }

    /**
     * @return string
     */
    public function getTwigFile()
    {
        return $this->twigFile;
    }

    /**
     * @param string $twigFile
     */
    public function setTwigFile($twigFile)
    {
        $this->twigFile = $twigFile;
    }

    /**
     * @return mixed
     */
    public function getPageTypes()
    {
        return $this->pageTypes;
    }

    /**
     * @param mixed $pageTypes
     */
    public function setPageTypes($pageTypes)
    {
        $this->pageTypes = $pageTypes;
    }

    /**
     * @return mixed
     */
    public function getPageWidgets()
    {
        return $this->pageWidgets;
    }

    /**
     * @param mixed $pageWidgets
     */
    public function setPageWidgets($pageWidgets)
    {
        $this->pageWidgets = $pageWidgets;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getModal()
    {
        return $this->modal;
    }

    /**
     * @param string $modal
     */
    public function setModal($modal)
    {
        $this->modal = $modal;
    }


}
