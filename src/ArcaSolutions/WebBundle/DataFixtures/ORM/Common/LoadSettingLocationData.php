<?php

namespace ArcaSolutions\WebBundle\DataFixtures\ORM\Common;

use ArcaSolutions\WebBundle\Entity\SettingLocation;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadSettingLocationData
 * @package ArcaSolutions\WebBundle\DataFixtures\ORM\Common
 */
class LoadSettingLocationData extends AbstractFixture implements OrderedFixtureInterface
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
                'defaultId'  => 0,
                'name'       => 'COUNTRY',
                'namePlural' => 'COUNTRIES',
                'enabled'    => 'y',
                'show'       => 'b',
            ],
            [
                'defaultId'  => 0,
                'name'       => 'REGION',
                'namePlural' => 'REGIONS',
                'enabled'    => 'n',
                'show'       => 'b',
            ],
            [
                'defaultId'  => 0,
                'name'       => 'STATE',
                'namePlural' => 'STATES',
                'enabled'    => 'y',
                'show'       => 'b',
            ],
            [
                'defaultId'  => 0,
                'name'       => 'CITY',
                'namePlural' => 'CITIES',
                'enabled'    => 'y',
                'show'       => 'b',
            ],
            [
                'defaultId'  => 0,
                'name'       => 'NEIGHBORHOOD',
                'namePlural' => 'NEIGHBORHOODS',
                'enabled'    => 'n',
                'show'       => 'b',
            ],
        ];

        $repository = $manager->getRepository('WebBundle:SettingLocation');

        foreach ($standardInserts as $settingLocationInsert) {
            $query = $repository->findOneBy([
                'name' => $settingLocationInsert['name'],
                'namePlural' => $settingLocationInsert['namePlural'],
            ]);

            $settingLocation = new SettingLocation();

            /* checks if the setting already exist so they can be updated or added */
            if ($query) {
                $settingLocation = $query;
            }

            $settingLocation->setDefaultId($settingLocationInsert['defaultId']);
            $settingLocation->setName($settingLocationInsert['name']);
            $settingLocation->setNamePlural($settingLocationInsert['namePlural']);
            $settingLocation->setEnabled($settingLocationInsert['enabled']);
            $settingLocation->setShow($settingLocationInsert['show']);

            $manager->persist($settingLocation);
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
