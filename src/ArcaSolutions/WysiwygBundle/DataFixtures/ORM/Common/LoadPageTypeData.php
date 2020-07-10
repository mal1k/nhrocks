<?php

namespace ArcaSolutions\WysiwygBundle\DataFixtures\ORM\Common;

use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadPageTypeData
 * @package ArcaSolutions\WysiwygBundle\DataFixtures\ORM\Common
 */
class LoadPageTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $standardPageTypes = [
            [
                'title' => PageType::HOME_PAGE,
            ],
            [
                'title' => PageType::LISTING_HOME_PAGE,
            ],
            [
                'title' => PageType::EVENT_HOME_PAGE,
            ],
            [
                'title' => PageType::CLASSIFIED_HOME_PAGE,
            ],
            [
                'title' => PageType::ARTICLE_HOME_PAGE,
            ],
            [
                'title' => PageType::DEAL_HOME_PAGE,
            ],
            [
                'title' => PageType::BLOG_HOME_PAGE,
            ],
            [
                'title' => PageType::RESULTS_PAGE,
            ],
            [
                'title' => PageType::LISTING_DETAIL_PAGE,
            ],
            [
                'title' => PageType::EVENT_DETAIL_PAGE,
            ],
            [
                'title' => PageType::CLASSIFIED_DETAIL_PAGE,
            ],
            [
                'title' => PageType::ARTICLE_DETAIL_PAGE,
            ],
            [
                'title' => PageType::DEAL_DETAIL_PAGE,
            ],
            [
                'title' => PageType::BLOG_DETAIL_PAGE,
            ],
            [
                'title' => PageType::CONTACT_US_PAGE,
            ],
            [
                'title' => PageType::FAQ_PAGE,
            ],
            [
                'title' => PageType::TERMS_OF_SERVICE_PAGE,
            ],
            [
                'title' => PageType::PRIVACY_POLICY_PAGE,
            ],
            [
                'title' => PageType::MAINTENANCE_PAGE,
            ],
            [
                'title' => PageType::ERROR404_PAGE,
            ],
            [
                'title' => PageType::ADVERTISE_PAGE,
            ],
            [
                'title' => PageType::CUSTOM_PAGE,
            ],
            [
                'title' => PageType::LISTING_CATEGORIES_PAGE,
            ],
            [
                'title' => PageType::EVENT_CATEGORIES_PAGE,
            ],
            [
                'title' => PageType::CLASSIFIED_CATEGORIES_PAGE,
            ],
            [
                'title' => PageType::ARTICLE_CATEGORIES_PAGE,
            ],
            [
                'title' => PageType::DEAL_CATEGORIES_PAGE,
            ],
            [
                'title' => PageType::BLOG_CATEGORIES_PAGE,
            ],
            [
                'title' => PageType::LISTING_ALL_LOCATIONS,
            ],
            [
                'title' => PageType::EVENT_ALL_LOCATIONS,
            ],
            [
                'title' => PageType::CLASSIFIED_ALL_LOCATIONS,
            ],
            [
                'title' => PageType::DEAL_ALL_LOCATIONS,
            ],
            [
                'title' => PageType::LISTING_REVIEWS,
            ],
            [
                'title' => PageType::ITEM_UNAVAILABLE_PAGE,
            ]
            /*
             * CUSTOM ADDPAGETYPE
             * here are an example of how you add a PageType to be used in LoadPageData
             */
            /* [
               'title' => PageType::TEST_PAGE,
           ],*/
        ];

        /* ModStores Hooks */
        HookFire("loadpagetype_after_add_pagetypes", [
            "standardPageTypes" => &$standardPageTypes,
        ]);

        $repository = $manager->getRepository('WysiwygBundle:PageType');
        foreach ($standardPageTypes as $standardPageType) {
            $pageType = new PageType();

            $query = $repository->findOneBy(['title' => $standardPageType['title']]);
            if (count($query) != 0) {
                $pageType = $query;
            }

            $pageType->setTitle($standardPageType['title']);

            $manager->persist($pageType);
            $manager->flush();

            $this->addReference("TYPE_".$pageType->getTitle(), $pageType);
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
        return 1;
    }
}
