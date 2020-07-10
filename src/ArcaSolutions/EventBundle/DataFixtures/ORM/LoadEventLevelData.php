<?php

namespace ArcaSolutions\EventBundle\DataFixtures\ORM;

use ArcaSolutions\EventBundle\Entity\EventLevel;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadEventLevelData
 * @package ArcaSolutions\EventBundle\DataFixtures\ORM
 */
class LoadEventLevelData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
                'value' => 10,
                'name' => $translator->trans('diamond'),
                'defaultLevel' => 'y',
                'detail' => 'y',
                'images' => 3,
                'price' => 50.00,
                'active' => 'y',
                'popular' => 'y',
                'featured' => 'y',
                'priceYearly' => NULL,
                'trial' => NULL
            ],
            [
                'value' => 30,
                'name' => $translator->trans('gold'),
                'defaultLevel' => 'n',
                'detail' => 'n',
                'images' => 0,
                'price' => 25.00,
                'active' => 'y',
                'popular' => '',
                'featured' => '',
                'priceYearly' => NULL,
                'trial' => NULL
            ],
            [
                'value' => 50,
                'name' => $translator->trans('silver'),
                'defaultLevel' => 'n',
                'detail' => 'n',
                'images' => 0,
                'price' => 0.00,
                'active' => 'y',
                'popular' => '',
                'featured' => '',
                'priceYearly' => NULL,
                'trial' => NULL
            ],
        ];

        $repository = $manager->getRepository('EventBundle:EventLevel');

        foreach ($standardInserts as $eventLevelInsert) {
            $query = $repository->findOneBy([
                'value' => $eventLevelInsert['value'],
            ]);

            $eventLevel = new EventLevel();

            /* checks if the EventLevel already exist so they can be updated or added */
            if ($query) {
                $eventLevel = $query;
            }

            $eventLevel->setValue($eventLevelInsert['value']);
            $eventLevel->setName($eventLevelInsert['name']);
            $eventLevel->setDefaultlevel($eventLevelInsert['defaultLevel']);
            $eventLevel->setDetail($eventLevelInsert['detail']);
            $eventLevel->setImages($eventLevelInsert['images']);
            $eventLevel->setPrice($eventLevelInsert['price']);
            $eventLevel->setActive($eventLevelInsert['active']);
            $eventLevel->setPopular($eventLevelInsert['popular']);
            $eventLevel->setFeatured($eventLevelInsert['featured']);
            $eventLevel->setPriceYearly($eventLevelInsert['priceYearly']);
            $eventLevel->setTrial($eventLevelInsert['trial']);

            $manager->persist($eventLevel);
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
