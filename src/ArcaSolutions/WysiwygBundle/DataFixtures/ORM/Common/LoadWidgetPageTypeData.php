<?php


namespace ArcaSolutions\WysiwygBundle\DataFixtures\ORM\Common;


use ArcaSolutions\WysiwygBundle\Entity\PageType;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use ArcaSolutions\WysiwygBundle\Entity\WidgetPageType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadWidgetPageTypeData
 *
 * This class is responsible for inserting at the Database the standard Widget_PageType of the system
 *
 */
class LoadWidgetPageTypeData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Get all loaded widgets
        $widgets = $this->container->get('doctrine')->getRepository('WysiwygBundle:Widget')->findAll();
        $standardWidgetPageTypes = [];

        // Widget that have page type exception
        $exceptionsWidgets = [
            Widget::RESULTS_INFO                                      => $this->getReference('TYPE_'.PageType::RESULTS_PAGE),
            Widget::RESULTS                                           => $this->getReference('TYPE_'.PageType::RESULTS_PAGE),
            Widget::LISTING_DETAIL                                    => $this->getReference('TYPE_'.PageType::LISTING_DETAIL_PAGE),
            Widget::EVENT_DETAIL                                      => $this->getReference('TYPE_'.PageType::EVENT_DETAIL_PAGE),
            Widget::CLASSIFIED_DETAIL                                 => $this->getReference('TYPE_'.PageType::CLASSIFIED_DETAIL_PAGE),
            Widget::ARTICLE_DETAIL                                    => $this->getReference('TYPE_'.PageType::ARTICLE_DETAIL_PAGE),
            Widget::DEAL_DETAIL                                       => $this->getReference('TYPE_'.PageType::DEAL_DETAIL_PAGE),
            Widget::BLOG_DETAIL                                       => $this->getReference('TYPE_'.PageType::BLOG_DETAIL_PAGE),
            Widget::CONTACT_FORM                                      => $this->getReference('TYPE_'.PageType::CONTACT_US_PAGE),
            Widget::FAQ_BOX                                           => $this->getReference('TYPE_'.PageType::FAQ_PAGE),
            Widget::PRICING_AND_PLANS                                 => $this->getReference('TYPE_'.PageType::ADVERTISE_PAGE),
            Widget::REVIEWS_BLOCK                                     => $this->getReference('TYPE_'.PageType::LISTING_REVIEWS),
            Widget::ALL_LOCATIONS                                     => [
                $this->getReference('TYPE_'.PageType::LISTING_ALL_LOCATIONS),
                $this->getReference('TYPE_'.PageType::CLASSIFIED_ALL_LOCATIONS),
                $this->getReference('TYPE_'.PageType::DEAL_ALL_LOCATIONS),
                $this->getReference('TYPE_'.PageType::EVENT_ALL_LOCATIONS),
            ],
            Widget::ALL_CATEGORIES                                    => [
                $this->getReference('TYPE_'.PageType::LISTING_CATEGORIES_PAGE),
                $this->getReference('TYPE_'.PageType::CLASSIFIED_CATEGORIES_PAGE),
                $this->getReference('TYPE_'.PageType::EVENT_CATEGORIES_PAGE),
                $this->getReference('TYPE_'.PageType::DEAL_CATEGORIES_PAGE),
                $this->getReference('TYPE_'.PageType::ARTICLE_CATEGORIES_PAGE),
                $this->getReference('TYPE_'.PageType::BLOG_CATEGORIES_PAGE),
            ],
            /* CUSTOM ADDWIDGET
             * here are an example of how you create an exception for the 'Widget test'
             * this way the widget will be available only for the Home Page
             */
            /*  'Widget test'                        => [
               $this->getReference("TYPE_".PageType::TEST_PAGE),
           ],*/
        ];

        /* ModStores Hooks */
        HookFire("loadwidget_after_add_exceptions", [
            "that"              => $this,
            "exceptionsWidgets" => &$exceptionsWidgets
        ]);

        foreach ($widgets as $widget) {
            // add to array widgets that can be used in more than 1 page but not all
            if (isset($exceptionsWidgets[$widget->getTitle()]) and is_array($exceptionsWidgets[$widget->getTitle()])) {
                foreach ($exceptionsWidgets[$widget->getTitle()] as $exceptionsWidget) {
                    $standardWidgetPageTypes[] = [
                        'widget'   => $widget,
                        'pageType' => $exceptionsWidget,
                    ];
                }
            } else {
                // Null pagetype is for universal widgets
                $pageType = null;
                if (isset($exceptionsWidgets[$widget->getTitle()])) {
                    $pageType = $exceptionsWidgets[$widget->getTitle()];
                }
                $standardWidgetPageTypes[] = [
                    'widget'   => $widget,
                    'pageType' => $pageType,
                ];
            }
        }

        $repository = $manager->getRepository('WysiwygBundle:WidgetPageType');

        foreach ($standardWidgetPageTypes as $standardWidgetPageType) {
            $query = $repository->findOneBy([
                'pageType' => $standardWidgetPageType['pageType'],
                'widget'   => $standardWidgetPageType['widget'],
            ]);

            $widgetPageType = new WidgetPageType();
            /* checks if the widget pagetype already exist so they can be   or added */
            if (!$query) {
                $widgetPageType->setWidget($standardWidgetPageType['widget']);
                $widgetPageType->setPageType($standardWidgetPageType['pageType']);
            } else {
                $widgetPageType = $query;
                $widgetPageType->setPageType($standardWidgetPageType['pageType']);
            }

            $manager->persist($widgetPageType);
            $manager->flush();
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
        return 4;
    }
}
