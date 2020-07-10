<?php

namespace ArcaSolutions\WysiwygBundle\DataFixtures\ORM\Common;

use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadPageData
 *
 * This class is responsible for inserting at the Database the standard Pages of the system
 * For example: Home Page, Listing Home, Results.
 *
 */
class LoadPageData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $trans = $this->container->get('translator');

        /* Page title is used as reference in LoadPageWidgetData,
         *  so if you change here don't forget to change there
         **/
        $standardPages = [
            [
                'title'     => $trans->trans('Home Page', [], 'widgets'),
                'url'       => null,
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::HOME_PAGE),
            ],
            [
                'title'     => $trans->trans('Results', [], 'widgets'),
                'url'       => null,
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::RESULTS_PAGE),
            ],
            [
                'title'     => $trans->trans('Listing Home', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_listing_module'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::LISTING_HOME_PAGE),
            ],
            [
                'title'     => $trans->trans('Listing Detail', [], 'widgets'),
                'url'       => null,
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::LISTING_DETAIL_PAGE),
            ],
            [
                'title'     => $trans->trans('Listing Reviews', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_review_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::LISTING_REVIEWS),
            ],
            [
                'title'     => $trans->trans('Listing View All Categories', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_listing_allcategories_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::LISTING_CATEGORIES_PAGE),
            ],
            [
                'title'     => $trans->trans('Listing All Locations', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_alllocations_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::LISTING_ALL_LOCATIONS),
            ],
            [
                'title'     => $trans->trans('Event Home', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_event_module'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::EVENT_HOME_PAGE),
            ],
            [
                'title'     => $trans->trans('Event Detail', [], 'widgets'),
                'url'       => null,
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::EVENT_DETAIL_PAGE),
            ],
            [
                'title'     => $trans->trans('Event View All Categories', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_event_allcategories_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::EVENT_CATEGORIES_PAGE),
            ],
            [
                'title'     => $trans->trans('Event All Locations', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_alllocations_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::EVENT_ALL_LOCATIONS),
            ],
            [
                'title'     => $trans->trans('Classified Home', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_classified_module'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::CLASSIFIED_HOME_PAGE),
            ],
            [
                'title'     => $trans->trans('Classified Detail', [], 'widgets'),
                'url'       => null,
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::CLASSIFIED_DETAIL_PAGE),
            ],
            [
                'title'     => $trans->trans('Classified View All Categories', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_classified_allcategories_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::CLASSIFIED_CATEGORIES_PAGE),
            ],
            [
                'title'     => $trans->trans('Classified All Locations', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_alllocations_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::CLASSIFIED_ALL_LOCATIONS),
            ],
            [
                'title'     => $trans->trans('Article Home', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_article_module'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::ARTICLE_HOME_PAGE),
            ],
            [
                'title'     => $trans->trans('Article Detail', [], 'widgets'),
                'url'       => null,
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::ARTICLE_DETAIL_PAGE),
            ],
            [
                'title'     => $trans->trans('Article View All Categories', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_article_allcategories_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::ARTICLE_CATEGORIES_PAGE),
            ],
            [
                'title'     => $trans->trans('Deal Home', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_promotion_module'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::DEAL_HOME_PAGE),
            ],
            [
                'title'     => $trans->trans('Deal Detail', [], 'widgets'),
                'url'       => null,
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::DEAL_DETAIL_PAGE),
            ],
            [
                'title'     => $trans->trans('Deal View All Categories', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_promotion_allcategories_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::DEAL_CATEGORIES_PAGE),
            ],
            [
                'title'     => $trans->trans('Deal All Locations', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_alllocations_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::DEAL_ALL_LOCATIONS),
            ],
            [
                'title'     => $trans->trans('Blog Home', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_blog_module'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::BLOG_HOME_PAGE),
            ],
            [
                'title'     => $trans->trans('Blog Detail', [], 'widgets'),
                'url'       => null,
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::BLOG_DETAIL_PAGE),
            ],
            [
                'title'     => $trans->trans('Blog View All Categories', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_blog_allcategories_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::BLOG_CATEGORIES_PAGE),
            ],
            [
                'title'     => $trans->trans('Contact Us', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_contactus_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::CONTACT_US_PAGE),
            ],
            [
                'title'     => $trans->trans('Advertise With Us', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_advertise_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::ADVERTISE_PAGE),
            ],
            [
                'title'     => $trans->trans('Faq', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_faq_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::FAQ_PAGE),
            ],
            [
                'title'     => $trans->trans('Terms of Service', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_terms_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::TERMS_OF_SERVICE_PAGE),
            ],
            [
                'title'     => $trans->trans('Privacy Policy', [], 'widgets'),
                'url'       => $this->container->getParameter('alias_privacy_url_divisor'),
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => true,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::PRIVACY_POLICY_PAGE),
            ],
            [
                'title'     => $trans->trans('Maintenance', [], 'widgets'),
                'url'       => null,
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => false,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::MAINTENANCE_PAGE),
            ],
            [
                'title'     => $trans->trans('404 Error', [], 'widgets'),
                'url'       => null,
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => false,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::ERROR404_PAGE),
            ],
            [
                'title'     => $trans->trans('Item Unavailable Page', [], 'widgets'),
                'url'       => null,
                'metaDesc'  => '',
                'metaKey'   => '',
                'sitemap'   => false,
                'customTag' => '',
                'pageType'  => $this->getReference("TYPE_".PageType::ITEM_UNAVAILABLE_PAGE),
            ],
            /**
             * CUSTOM ADDPAGE
             * Here are an example of how you add a page,
             * and you will need to create a PageType for this page
             *
             * Don't forget to create the alias for the url, if needed
             */
            /* [
                 'title'     => $trans->trans('Test Page', [], 'widgets'),
                 'url'       => null,
                 'metaDesc'  => '',
                 'metaKey'   => '',
                 'sitemap'   => true,
                 'customTag' => '',
                 'pageType'  => $this->getReference("TYPE_".PageType::TEST_PAGE),
             ],*/
        ];

        /* ModStores Hooks */
        HookFire("loadpage_after_add_pages", [
            "that"          => &$this,
            "standardPages" => &$standardPages,
        ]);

        $repository = $manager->getRepository('WysiwygBundle:Page');

        foreach ($standardPages as $standardPage) {
            $query = $repository->getPageByType($standardPage['pageType']->getTitle());

            $page = new Page();

            $page->setTitle($standardPage['title']);
            $page->setUrl($standardPage['url']);
            $page->setMetaDescription($standardPage['metaDesc']);
            $page->setMetaKey($standardPage['metaKey']);
            $page->setSitemap($standardPage['sitemap']);
            $page->setCustomTag($standardPage['customTag']);
            $page->setPageType($standardPage['pageType']);

            /* checks if the page already exist so it can be added as reference for LoadPageWidgetData */
            if (count($query) == 1) {
                $page = $query;
            }

            $manager->persist($page);
            $manager->flush();

            /* The page type title is already used as reference, that's why i added the string '_REFERENCE' */
            /* I couldn't think in anything better */
            $this->addReference($page->getPageType()->getTitle().'_REFERENCE', $page);
        }


    }

    /**
     * the order in which fixtures will be loaded
     * the lower the number, the sooner that this fixture is loaded
     *
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
