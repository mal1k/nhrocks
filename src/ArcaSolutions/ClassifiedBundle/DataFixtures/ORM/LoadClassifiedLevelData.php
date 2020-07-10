<?php

namespace ArcaSolutions\ClassifiedBundle\DataFixtures\ORM;

use ArcaSolutions\ClassifiedBundle\Entity\ClassifiedLevel;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class LoadClassifiedLevelData
 * @package ArcaSolutions\ClassifiedBundle\DataFixtures\ORM
 */
class LoadClassifiedLevelData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
                'images' => 7,
                'price' => 50.00,
                'active' => 'y',
                'popular' => 'y',
                'featured' => 'y',
                'priceYearly' => NULL,
                'trial' => NULL,
                'video' => 'n',
                'additionalFiles' => 'n'
            ],
            [
                'value' => 30,
                'name' => $translator->trans('gold'),
                'defaultLevel' => 'n',
                'detail' => 'y',
                'images' => 3,
                'price' => 25.00,
                'active' => 'y',
                'popular' => '',
                'featured' => 'y',
                'priceYearly' => NULL,
                'trial' => NULL,
                'video' => 'n',
                'additionalFiles' => 'n'
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
                'trial' => NULL,
                'video' => 'n',
                'additionalFiles' => 'n'
            ],
        ];

        $repository = $manager->getRepository('ClassifiedBundle:ClassifiedLevel');

        foreach ($standardInserts as $classifiedLevelInsert) {
            $query = $repository->findOneBy([
                'value' => $classifiedLevelInsert['value'],
            ]);

            $classifiedLevel = new ClassifiedLevel();

            /* checks if the ClassifiedLevel already exist so they can be updated or added */
            if ($query) {
                $classifiedLevel = $query;
            }

            $classifiedLevel->setValue($classifiedLevelInsert['value']);
            $classifiedLevel->setName($classifiedLevelInsert['name']);
            $classifiedLevel->setDefaultlevel($classifiedLevelInsert['defaultLevel']);
            $classifiedLevel->setDetail($classifiedLevelInsert['detail']);
            $classifiedLevel->setImages($classifiedLevelInsert['images']);
            $classifiedLevel->setPrice($classifiedLevelInsert['price']);
            $classifiedLevel->setPriceYearly($classifiedLevelInsert['priceYearly']);
            $classifiedLevel->setActive($classifiedLevelInsert['active']);
            $classifiedLevel->setFeatured($classifiedLevelInsert['featured']);
            $classifiedLevel->setPopular($classifiedLevelInsert['popular']);
            $classifiedLevel->setTrial($classifiedLevelInsert['trial']);
            $classifiedLevel->setVideo($classifiedLevelInsert['video']);
            $classifiedLevel->setAdditionalFiles($classifiedLevelInsert['additionalFiles']);

            $manager->persist($classifiedLevel);
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
