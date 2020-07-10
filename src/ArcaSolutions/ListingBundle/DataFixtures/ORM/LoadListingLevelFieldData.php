<?php

namespace ArcaSolutions\ListingBundle\DataFixtures\ORM;

use ArcaSolutions\ListingBundle\Entity\ListingLevelField;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadListingLevelFieldData
 * @package ArcaSolutions\ListingBundle\DataFixtures\ORM
 */
class LoadListingLevelFieldData extends AbstractFixture implements OrderedFixtureInterface
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
                'level' => 30,
                'field' => 'email'
            ],
            [
                'level' => 50,
                'field' => 'email'
            ],
            [
                'level' => 10,
                'field' => 'url'
            ],
            [
                'level' => 30,
                'field' => 'url'
            ],
            [
                'level' => 50,
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
                'level' => 70,
                'field' => 'phone'
            ],
            [
                'level' => 10,
                'field' => 'has_logo_image'
            ],
            [
                'level' => 10,
                'field' => 'additional_phone'
            ],
            [
                'level' => 30,
                'field' => 'additional_phone'
            ],
            [
                'level' => 10,
                'field' => 'video'
            ],
            [
                'level' => 10,
                'field' => 'attachment_file'
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
                'field' => 'hours_of_work'
            ],
            [
                'level' => 10,
                'field' => 'badges'
            ],
            [
                'level' => 30,
                'field' => 'badges'
            ],
            [
                'level' => 10,
                'field' => 'locations'
            ],
            [
                'level' => 10,
                'field' => 'main_image'
            ],
            [
                'level' => 10,
                'field' => 'social_network'
            ],
            [
                'level' => 10,
                'field' => 'features'
            ],
        ];

        $repository = $manager->getRepository('ListingBundle:ListingLevelField');

        foreach ($standardInserts as $listingLevelFieldInsert) {
            $query = $repository->findOneBy([
                'level' => $listingLevelFieldInsert['level'],
                'field' => $listingLevelFieldInsert['field'],
            ]);

            $listingLevelField = new ListingLevelField();

            /* checks if the listingLevelField already exist so they can be updated or added */
            if (!$query) {
                $listingLevelField->setLevel($listingLevelFieldInsert['level']);
                $listingLevelField->setField($listingLevelFieldInsert['field']);

                $manager->persist($listingLevelField);
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
