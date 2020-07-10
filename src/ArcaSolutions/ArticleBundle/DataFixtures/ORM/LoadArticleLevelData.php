<?php

namespace ArcaSolutions\ArticleBundle\DataFixtures\ORM;

use ArcaSolutions\ArticleBundle\Entity\Articlelevel;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadArticleLevelData
 * @package ArcaSolutions\ListingBundle\DataFixtures\ORM
 */
class LoadArticleLevelData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
                'value' => 50,
                'name' => $translator->trans('Article'),
                'defaultLevel' => 'y',
                'detail' => 'y',
                'images' => 5,
                'price' => 30.00,
                'active' => 'y',
                'featured' => 'y',
            ],
        ];

        $repository = $manager->getRepository('ArticleBundle:Articlelevel');

        foreach ($standardInserts as $articleLevelInsert) {
            $query = $repository->findOneBy([
                'value' => $articleLevelInsert['value'],
            ]);

            $articleLevel = new Articlelevel();

            /* checks if the ArticleLevel already exist so they can be updated or added */
            if ($query) {
                $articleLevel = $query;
            }

            $articleLevel->setValue($articleLevelInsert['value']);
            $articleLevel->setName($articleLevelInsert['name']);
            $articleLevel->setDefaultlevel($articleLevelInsert['defaultLevel']);
            $articleLevel->setDetail($articleLevelInsert['detail']);
            $articleLevel->setImages($articleLevelInsert['images']);
            $articleLevel->setPrice($articleLevelInsert['price']);
            $articleLevel->setActive($articleLevelInsert['active']);
            $articleLevel->setFeatured($articleLevelInsert['featured']);

            $manager->persist($articleLevel);
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
