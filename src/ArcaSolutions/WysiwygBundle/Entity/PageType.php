<?php

namespace ArcaSolutions\WysiwygBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PageType
 *
 * @ORM\Table(name="PageType")
 * @ORM\Entity(repositoryClass="ArcaSolutions\WysiwygBundle\Repository\PageTypeRepository")
 */
class PageType
{
    /**
     * These constants are used in PageType Entity
     * And are the possible values for the $pageType
     * ALERT: DO NOT CHANGE THE CONSTANT VALUES
     * They are used for the reset feature at sitemgr
     * If you need to change you will have to change the function that have 'get'.constant.'DefaultWidgets'
     * EX:  const HOME_PAGE
     *      function getHomePageDefaultWidgets()
     */
    const HOME_PAGE = 'Home Page';
    const RESULTS_PAGE = 'Directory Results';
    const CONTACT_US_PAGE = 'Contact Us';
    const FAQ_PAGE = 'FAQ';
    const TERMS_OF_SERVICE_PAGE = 'Terms of Use';
    const PRIVACY_POLICY_PAGE = 'Privacy Policy';
    const MAINTENANCE_PAGE = 'Maintenance Page';
    const ERROR404_PAGE = 'Error Page';
    const ITEM_UNAVAILABLE_PAGE = 'Item Unavailable Page';
    const ADVERTISE_PAGE = 'Advertise with Us';
    const CUSTOM_PAGE = 'Custom Page';

    const LISTING_HOME_PAGE = 'Listing Home';
    const LISTING_DETAIL_PAGE = 'Listing Detail';
    const LISTING_CATEGORIES_PAGE = 'Listing View All Categories';
    const LISTING_ALL_LOCATIONS = 'Listing View All Locations';
    const LISTING_REVIEWS = 'Listing Reviews';

    const EVENT_HOME_PAGE = 'Event Home';
    const EVENT_DETAIL_PAGE = 'Event Detail';
    const EVENT_CATEGORIES_PAGE = 'Event View All Categories';
    const EVENT_ALL_LOCATIONS = 'Event View All Locations';

    const CLASSIFIED_HOME_PAGE = 'Classified Home';
    const CLASSIFIED_DETAIL_PAGE = 'Classified Detail';
    const CLASSIFIED_CATEGORIES_PAGE = 'Classified View All Categories';
    const CLASSIFIED_ALL_LOCATIONS = 'Classified View All Locations';

    const DEAL_HOME_PAGE = 'Deal Home';
    const DEAL_DETAIL_PAGE = 'Deal Detail';
    const DEAL_CATEGORIES_PAGE = 'Deal View All Categories';
    const DEAL_ALL_LOCATIONS = 'Deal View All Locations';

    const ARTICLE_HOME_PAGE = 'Article Home';
    const ARTICLE_DETAIL_PAGE = 'Article Detail';
    const ARTICLE_CATEGORIES_PAGE = 'Article View All Categories';

    const BLOG_HOME_PAGE = 'Blog Home';
    const BLOG_DETAIL_PAGE = 'Blog Detail';
    const BLOG_CATEGORIES_PAGE = 'Blog View All Categories';

    /**
     * CUSTOM ADDPAGETYPE
     * here are an example of how you add a PageType constant to be used in the load data
     */
    /*const TEST_PAGE = "Test Page";*/

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
     * @ORM\Column(name="title", type="string", nullable=false)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\WysiwygBundle\Entity\WidgetPageType", mappedBy="pageType")
     */
    private $widgets;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\WysiwygBundle\Entity\Page", mappedBy="pageType")
     * @ORM\JoinColumn(name="id", referencedColumnName="pagetype_id")
     */
    private $pages;

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
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @param mixed $widgets
     */
    public function setWidgets($widgets)
    {
        $this->widgets = $widgets;
    }

    /**
     * @return mixed
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param mixed $pages
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
    }

}
