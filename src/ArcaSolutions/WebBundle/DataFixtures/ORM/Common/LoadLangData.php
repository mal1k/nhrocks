<?php

namespace ArcaSolutions\WebBundle\DataFixtures\ORM\Common;

use ArcaSolutions\WebBundle\Entity\Lang;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadLangData
 * @package ArcaSolutions\WebBundle\DataFixtures\ORM\Common
 */
class LoadLangData extends AbstractFixture implements OrderedFixtureInterface
{
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
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /* These are the standard data of the system */
        $standardInserts = [
            [
                'id'          => 'en_us',
                'name'        => 'English',
                'langEnabled' => 'y',
                'langDefault' => 'y',
                'langOrder'   => 0,
            ],
            [
                'id'          => 'pt_br',
                'name'        => 'Português',
                'langEnabled' => 'n',
                'langDefault' => 'n',
                'langOrder'   => 1,
            ],
            [
                'id'          => 'es_es',
                'name'        => 'Español',
                'langEnabled' => 'n',
                'langDefault' => 'n',
                'langOrder'   => 3,
            ],
            [
                'id'          => 'fr_fr',
                'name'        => 'Français',
                'langEnabled' => 'n',
                'langDefault' => 'n',
                'langOrder'   => 4,
            ],
            [
                'id'          => 'it_it',
                'name'        => 'Italiano',
                'langEnabled' => 'n',
                'langDefault' => 'n',
                'langOrder'   => 2,
            ],
            [
                'id'          => 'ge_ge',
                'name'        => 'Deutsch',
                'langEnabled' => 'n',
                'langDefault' => 'n',
                'langOrder'   => 5,
            ],
            [
                'id'          => 'tr_tr',
                'name'        => 'Türkçe',
                'langEnabled' => 'n',
                'langDefault' => 'n',
                'langOrder'   => 6,
            ],
        ];

        $repository = $manager->getRepository('WebBundle:Lang');

        foreach ($standardInserts as $langInsert) {
            $query = $repository->findOneBy([
                'id' => $langInsert['id'],
            ]);

            $lang = new Lang();

            /* checks if the Lang already exist so they can be updated or added */
            if ($query) {
                $lang = $query;
            }

            $lang->setId($langInsert['id']);
            $lang->setName($langInsert['name']);
            $lang->setLangEnabled($langInsert['langEnabled']);
            $lang->setLangDefault($langInsert['langDefault']);
            $lang->setLangOrder($langInsert['langOrder']);

            $manager->persist($lang);
        }

        $manager->flush();
    }

}
