<?php

namespace ArcaSolutions\ClassifiedBundle\DataFixtures\ORM;

use ArcaSolutions\ClassifiedBundle\Entity\ClassifiedLevelField;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadClassifiedLevelFieldData
 * @package ArcaSolutions\ClassifiedBundle\DataFixtures\ORM
 */
class LoadClassifiedLevelFieldData extends AbstractFixture implements OrderedFixtureInterface
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
                'field' => 'contact_name'
            ],
            [
                'level' => 30,
                'field' => 'contact_name'
            ],
            [
                'level' => 10,
                'field' => 'additional_phone'
            ],
            [
                'level' => 10,
                'field' => 'url'
            ],
            [
                'level' => 10,
                'field' => 'long_description'
            ],
            [
                'level' => 30,
                'field' => 'long_description'
            ],
            [
                'level' => 10,
                'field' => 'main_image'
            ],
            [
                'level' => 30,
                'field' => 'main_image'
            ],
            [
                'level' => 50,
                'field' => 'main_image'
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
                'level' => 50,
                'field' => 'summary_description'
            ],
            [
                'level' => 10,
                'field' => 'contact_phone'
            ],
            [
                'level' => 30,
                'field' => 'contact_phone'
            ],
            [
                'level' => 50,
                'field' => 'contact_phone'
            ],
            [
                'level' => 10,
                'field' => 'contact_email'
            ],
            [
                'level' => 30,
                'field' => 'contact_email'
            ],
            [
                'level' => 50,
                'field' => 'contact_email'
            ],
            [
                'level' => 10,
                'field' => 'price'
            ],
            [
                'level' => 30,
                'field' => 'price'
            ],
            [
                'level' => 50,
                'field' => 'price'
            ],
        ];

        $repository = $manager->getRepository('ClassifiedBundle:ClassifiedLevelField');

        foreach ($standardInserts as $classifiedLevelFieldInsert) {
            $query = $repository->findOneBy([
                'level' => $classifiedLevelFieldInsert['level'],
                'field' => $classifiedLevelFieldInsert['field'],
            ]);

            $classifiedLevelField = new ClassifiedLevelField();

            /* checks if the ClassifiedLevelField already exist so they can be updated or added */
            if (!$query) {
                $classifiedLevelField->setLevel($classifiedLevelFieldInsert['level']);
                $classifiedLevelField->setField($classifiedLevelFieldInsert['field']);

                $manager->persist($classifiedLevelField);
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
