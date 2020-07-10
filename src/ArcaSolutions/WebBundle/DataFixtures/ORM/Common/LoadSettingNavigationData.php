<?php

namespace ArcaSolutions\WebBundle\DataFixtures\ORM\Common;

use ArcaSolutions\WebBundle\Entity\SettingNavigation;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadSettingNavigationData
 * @package ArcaSolutions\WebBundle\DataFixtures\ORM\Common
 */
class LoadSettingNavigationData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $translator = $this->container->get('translator');

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page');

        /* These are the standard data of the system */
        $standardInserts = [
            [
                'order'  => 0,
                'label'  => $translator->trans('Home'),
                'link'   => 'NULL',
                'area'   => 'footer',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::HOME_PAGE)
            ],
            [
                'order'  => 0,
                'label'  => $translator->trans('Home'),
                'link'   => 'NULL',
                'area'   => 'header',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::HOME_PAGE)
            ],
            [
                'order'  => 0,
                'label'  => $translator->trans('Home'),
                'link'   => 'home',
                'area'   => 'tabbar',
                'custom' => 0
            ],
            [
                'order'  => 1,
                'label'  => $translator->trans('Listings'),
                'link'   => 'NULL',
                'area'   => 'footer',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::LISTING_HOME_PAGE)
            ],
            [
                'order'  => 1,
                'label'  => $translator->trans('Listings'),
                'link'   => 'NULL',
                'area'   => 'header',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::LISTING_HOME_PAGE)
            ],
            [
                'order'  => 1,
                'label'  => $translator->trans('Listings'),
                'link'   => 'listings',
                'area'   => 'tabbar',
                'custom' => 0
            ],
            [
                'order'  => 2,
                'label'  => $translator->trans('Events'),
                'link'   => 'NULL',
                'area'   => 'footer',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::EVENT_HOME_PAGE)
            ],
            [
                'order'  => 2,
                'label'  => $translator->trans('Events'),
                'link'   => 'NULL',
                'area'   => 'header',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::EVENT_HOME_PAGE)
            ],
            [
                'order'  => 2,
                'label'  => $translator->trans('Events'),
                'link'   => 'events',
                'area'   => 'tabbar',
                'custom' => 0
            ],
            [
                'order'  => 3,
                'label'  => $translator->trans('Classifieds'),
                'link'   => 'NULL',
                'area'   => 'footer',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::CLASSIFIED_HOME_PAGE)
            ],
            [
                'order'  => 3,
                'label'  => $translator->trans('Classifieds'),
                'link'   => 'NULL',
                'area'   => 'header',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::CLASSIFIED_HOME_PAGE)
            ],
            [
                'order'  => 3,
                'label'  => $translator->trans('Classifieds'),
                'link'   => 'classifieds',
                'area'   => 'tabbar',
                'custom' => 0
            ],
            [
                'order'  => 4,
                'label'  => $translator->trans('Articles'),
                'link'   => 'NULL',
                'area'   => 'footer',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::ARTICLE_HOME_PAGE)
            ],
            [
                'order'  => 4,
                'label'  => $translator->trans('Articles'),
                'link'   => 'NULL',
                'area'   => 'header',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::ARTICLE_HOME_PAGE)
            ],
            [
                'order'  => 4,
                'label'  => $translator->trans('Articles'),
                'link'   => 'articles',
                'area'   => 'tabbar',
                'custom' => 0
            ],
            [
                'order'  => 5,
                'label'  => $translator->trans('Deals'),
                'link'   => 'NULL',
                'area'   => 'footer',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::DEAL_HOME_PAGE)
            ],
            [
                'order'  => 5,
                'label'  => $translator->trans('Deals'),
                'link'   => 'NULL',
                'area'   => 'header',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::DEAL_HOME_PAGE)
            ],
            [
                'order'  => 5,
                'label'  => $translator->trans('Deals'),
                'link'   => 'deals',
                'area'   => 'tabbar',
                'custom' => 0
            ],
            [
                'order'  => 6,
                'label'  => $translator->trans('Blog'),
                'link'   => 'NULL',
                'area'   => 'footer',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::BLOG_HOME_PAGE)
            ],
            [
                'order'  => 6,
                'label'  => $translator->trans('Blog'),
                'link'   => 'NULL',
                'area'   => 'header',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::BLOG_HOME_PAGE)
            ],
            [
                'order'  => 6,
                'label'  => $translator->trans('Blog'),
                'link'   => 'blog',
                'area'   => 'tabbar',
                'custom' => 0
            ],
            [
                'order'  => 7,
                'label'  => $translator->trans('Advertise'),
                'link'   => 'NULL',
                'area'   => 'footer',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::ADVERTISE_PAGE)
            ],
            [
                'order'  => 7,
                'label'  => $translator->trans('Advertise'),
                'link'   => 'NULL',
                'area'   => 'header',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::ADVERTISE_PAGE)
            ],
            [
                'order'  => 7,
                'label'  => $translator->trans('My Favorites'),
                'link'   => 'favorites',
                'area'   => 'tabbar',
                'custom' => 0
            ],
            [
                'order'  => 8,
                'label'  => $translator->trans('Contact us'),
                'link'   => 'NULL',
                'area'   => 'footer',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::CONTACT_US_PAGE)
            ],
            [
                'order'  => 8,
                'label'  => $translator->trans('Contact us'),
                'link'   => 'NULL',
                'area'   => 'header',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::CONTACT_US_PAGE)
            ],
            [
                'order'  => 8,
                'label'  => $translator->trans('My Deals'),
                'link'   => 'mydeals',
                'area'   => 'tabbar',
                'custom' => 0
            ],
            [
                'order'  => 9,
                'label'  => $translator->trans('FAQ'),
                'link'   => 'NULL',
                'area'   => 'footer',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::FAQ_PAGE)
            ],
            [
                'order'  => 9,
                'label'  => $translator->trans('My Reviews'),
                'link'   => 'myreviews',
                'area'   => 'tabbar',
                'custom' => 0
            ],
            [
                'order'  => 10,
                'label'  => $translator->trans('About Us'),
                'link'   => 'about',
                'area'   => 'tabbar',
                'custom' => 0
            ],
            [
                'order'  => 11,
                'label'  => $translator->trans('Terms of Use'),
                'link'   => 'NULL',
                'area'   => 'footer',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::TERMS_OF_SERVICE_PAGE)
            ],
            [
                'order'  => 12,
                'label'  => $translator->trans('Privacy Policy'),
                'link'   => 'NULL',
                'area'   => 'footer',
                'custom' => 0,
                'page'   => $page->getPageByType(PageType::PRIVACY_POLICY_PAGE)
            ],
        ];

        $repository = $manager->getRepository('WebBundle:SettingNavigation');

        foreach ($standardInserts as $settingNavInsert) {
            $query = $repository->findOneBy([
                'link' => $settingNavInsert['link'],
                'area' => $settingNavInsert['area']
            ]);

            $settingNavigation = new SettingNavigation();

            /* checks if the setting already exist so they can be updated or added */
            if ($query) {
                $settingNavigation = $query;
            }

            $settingNavigation->setOrder($settingNavInsert['order']);
            $settingNavigation->setLabel($settingNavInsert['label']);
            $settingNavigation->setLink($settingNavInsert['link']);
            $settingNavigation->setArea($settingNavInsert['area']);
            $settingNavigation->setCustom($settingNavInsert['custom']);
            !empty($settingNavInsert['page']) and $settingNavigation->setPage($settingNavInsert['page']);

            $manager->persist($settingNavigation);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 5;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
