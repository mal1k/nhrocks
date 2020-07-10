<?php

namespace ArcaSolutions\BannersBundle\DataFixtures\ORM;

use ArcaSolutions\BannersBundle\Entity\Bannerlevel;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadBannerLevelData
 * @package ArcaSolutions\BannersBundle\DataFixtures\ORM
 */
class LoadBannerLevelData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $translator = $this->container->get("translator");

        /* These are the standard data of the system */
        $standardInserts = [
            [
                'value' => 1,
                'name' => 'leaderboard',
                'defaultLevel' => 'y',
                'price' => 50.00,
                'width' => 728,
                'height' => 90,
                'active' => 'y',
                'popular' => 'n',
                'displayName' => 'leaderboard',
            ],
            [
                'value' => 2,
                'name' => 'large mobile ',
                'defaultLevel' => 'n',
                'price' => 20.00,
                'width' => 320,
                'height' => 100,
                'active' => 'y',
                'popular' => 'n',
                'displayName' => 'large mobile banner',
            ],
            [
                'value' => 3,
                'name' => 'square',
                'defaultLevel' => 'n',
                'price' => 40.00,
                'width' => 250,
                'height' => 250,
                'active' => 'y',
                'popular' => 'n',
                'displayName' => 'square',
            ],
            [
                'value' => 4,
                'name' => 'wide skyscraper',
                'defaultLevel' => 'n',
                'price' => 40.00,
                'width' => 160,
                'height' => 600,
                'active' => 'y',
                'popular' => 'n',
                'displayName' => 'wide skyscraper',
            ],
            [
                'value' => 50,
                'name' => 'sponsored links',
                'defaultLevel' => 'n',
                'price' => 10.00,
                'width' => 320,
                'height' => 100,
                'active' => 'y',
                'popular' => 'n',
                'displayName' => 'sponsored links',
            ],
        ];

        $repository = $manager->getRepository('BannersBundle:Bannerlevel');

        foreach ($standardInserts as $bannerLevelInsert) {
            $query = $repository->findOneBy([
                'value' => $bannerLevelInsert['value'],
            ]);

            $bannerLevel = new Bannerlevel();

            /* checks if the BannerLevel already exist so they can be updated or added */
            if ($query) {
                $bannerLevel = $query;
            }

            $bannerLevel->setValue($bannerLevelInsert['value'])
                ->setName($bannerLevelInsert['name'])
                ->setDefaultlevel($bannerLevelInsert['defaultLevel'])
                ->setPrice($bannerLevelInsert['price'])
                ->setWidth($bannerLevelInsert['width'])
                ->setHeight($bannerLevelInsert['height'])
                ->setActive($bannerLevelInsert['active'])
                ->setPopular($bannerLevelInsert['popular'])
                ->setDisplayname($bannerLevelInsert['displayName']);

            $manager->persist($bannerLevel);
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
        return 1;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}

