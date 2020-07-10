<?php

namespace ArcaSolutions\EventBundle\DataFixtures\ORM;

use ArcaSolutions\EventBundle\Entity\EventLevelField;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadEventLevelFieldData
 * @package ArcaSolutions\EventBundle\DataFixtures\ORM
 */
class LoadEventLevelFieldData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /* These are the standard data of the system */
        $standardInserts = [
            [
                'level' => 10,
                'field' => 'email'
            ],
            [
                'level' => 10,
                'field' => 'url'
            ],
            [
                'level' => 10,
                'field' => 'phone'
            ],
            [
                'level' => 30,
                'field' => 'phone'
            ],
            [
                'level' => 50,
                'field' => 'phone'
            ],
            [
                'level' => 10,
                'field' => 'video'
            ],
            [
                'level' => 10,
                'field' => 'summary_description'
            ],
            [
                'level' => 30,
                'field' => 'summary_description'
            ],
            [
                'level' => 10,
                'field' => 'long_description'
            ],
            [
                'level' => 10,
                'field' => 'contact_name'
            ],
            [
                'level' => 10,
                'field' => 'start_time'
            ],
            [
                'level' => 10,
                'field' => 'end_time'
            ],
            [
                'level' => 30,
                'field' => 'start_time'
            ],
            [
                'level' => 30,
                'field' => 'end_time'
            ],
            [
                'level' => 10,
                'field' => 'main_image'
            ],
            [
                'level' => 10,
                'field' => 'fbpage'
            ],
        ];

        $repository = $manager->getRepository('EventBundle:EventLevelField');

        foreach ($standardInserts as $eventLevelFieldInsert) {
            $query = $repository->findOneBy([
                'level' => $eventLevelFieldInsert['level'],
                'field' => $eventLevelFieldInsert['field'],
            ]);

            $eventLevelField = new EventLevelField();

            /* checks if the ListingLevelField already exist so they can be updated or added */
            if (!$query) {
                $eventLevelField->setLevel($eventLevelFieldInsert['level']);
                $eventLevelField->setField($eventLevelFieldInsert['field']);

                $manager->persist($eventLevelField);
            }
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
}
