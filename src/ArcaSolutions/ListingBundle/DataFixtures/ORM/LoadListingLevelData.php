<?php

namespace ArcaSolutions\ListingBundle\DataFixtures\ORM;

use ArcaSolutions\ListingBundle\Entity\ListingLevel;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadListingLevelData
 * @package ArcaSolutions\ListingBundle\DataFixtures\ORM
 */
class LoadListingLevelData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
                /* ID */
                'value'                         => 10,
                'name'                          => $translator->trans('diamond'),
                'defaultLevel'                  => 'y',
                'detail'                        => 'y',
                'images'                        => 9,
                'hasReview'                     => 'y',
                'price'                         => 299.00,
                'freeCategory'                  => 3,
                'categoryPrice'                 => 15.00,
                'active'                        => 'y',
                'popular'                       => 'y',
                'featured'                      => 'y',
                'classifiedQuantityAssociation' => 10,
                'deals'                         => 10,
                'priceYearly'                   => null,
                'trial'                         => null,
            ],
            [
                /* ID */
                'value'                         => 30,
                'name'                          => $translator->trans('gold'),
                'defaultLevel'                  => 'n',
                'detail'                        => 'n',
                'images'                        => 0,
                'hasReview'                     => 'y',
                'price'                         => 199.00,
                'freeCategory'                  => 2,
                'categoryPrice'                 => 18.00,
                'active'                        => 'y',
                'popular'                       => '',
                'featured'                      => '',
                'classifiedQuantityAssociation' => 0,
                'deals'                         => 0,
                'priceYearly'                   => null,
                'trial'                         => null,
            ],
            [
                /* ID */
                'value'                         => 50,
                'name'                          => $translator->trans('silver'),
                'defaultLevel'                  => 'n',
                'detail'                        => 'n',
                'images'                        => 0,
                'hasReview'                     => 'y',
                'price'                         => 0.00,
                'freeCategory'                  => 1,
                'categoryPrice'                 => 20.00,
                'active'                        => 'y',
                'popular'                       => '',
                'featured'                      => '',
                'classifiedQuantityAssociation' => 0,
                'deals'                         => 0,
                'priceYearly'                   => null,
                'trial'                         => null,
            ],
            [
                /* ID */
                'value'                         => 70,
                'name'                          => $translator->trans('bronze'),
                'defaultLevel'                  => 'n',
                'detail'                        => 'n',
                'images'                        => 0,
                'hasReview'                     => 'n',
                'price'                         => 0.00,
                'freeCategory'                  => 1,
                'categoryPrice'                 => 20.00,
                'active'                        => 'y',
                'popular'                       => '',
                'featured'                      => '',
                'classifiedQuantityAssociation' => 0,
                'deals'                         => 0,
                'priceYearly'                   => null,
                'trial'                         => null,
            ],
        ];

        $repository = $manager->getRepository('ListingBundle:ListingLevel');

        foreach ($standardInserts as $listingLevelInsert) {
            $query = $repository->findOneBy([
                'value' => $listingLevelInsert['value'],
            ]);

            $listingLevel = new ListingLevel();

            /* checks if the ListingLevel already exist so they can be updated or added */
            if ($query) {
                $listingLevel = $query;
            }

            $listingLevel->setValue($listingLevelInsert['value']);
            $listingLevel->setName($listingLevelInsert['name']);
            $listingLevel->setDefaultlevel($listingLevelInsert['defaultLevel']);
            $listingLevel->setDetail($listingLevelInsert['detail']);
            $listingLevel->setImages($listingLevelInsert['images']);
            $listingLevel->setHasReview($listingLevelInsert['hasReview']);
            $listingLevel->setPrice($listingLevelInsert['price']);
            $listingLevel->setFreeCategory($listingLevelInsert['freeCategory']);
            $listingLevel->setCategoryPrice($listingLevelInsert['categoryPrice']);
            $listingLevel->setActive($listingLevelInsert['active']);
            $listingLevel->setPopular($listingLevelInsert['popular']);
            $listingLevel->setFeatured($listingLevelInsert['featured']);
            $listingLevel->setClassifiedQuantityAssociation($listingLevelInsert['classifiedQuantityAssociation']);
            $listingLevel->setDeals($listingLevelInsert['deals']);
            $listingLevel->setPriceYearly($listingLevelInsert['priceYearly']);
            $listingLevel->setTrial($listingLevelInsert['trial']);

            $manager->persist($listingLevel);
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
