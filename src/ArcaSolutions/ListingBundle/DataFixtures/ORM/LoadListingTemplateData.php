<?php

namespace ArcaSolutions\ListingBundle\DataFixtures\ORM;

use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadListingTemplateData
 * @package ArcaSolutions\ListingBundle\DataFixtures\ORM
 */
class LoadListingTemplateData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
                'layoutId' => 0,
                'title'    => $translator->trans('Listing'),
                'updated'  => new \DateTime(),
                'entered'  => new \DateTime(),
                'status'   => 'enabled',
                'price'    => 0.00,
                'catId'    => '',
                'editable' => 'n',
            ],
        ];

        $repository = $manager->getRepository('ListingBundle:ListingTemplate');

        foreach ($standardInserts as $listingTemplateInsert) {
            $query = $repository->findOneBy([
                'title' => $listingTemplateInsert['title'],
            ]);

            $listingTemplate = new ListingTemplate();

            /* checks if the ListingTemplate already exist so they can be updated or added */
            if ($query) {
                $listingTemplate = $query;
            }

            $listingTemplate->setLayoutId($listingTemplateInsert['layoutId']);
            $listingTemplate->setTitle($listingTemplateInsert['title']);
            $listingTemplate->setUpdated($listingTemplateInsert['updated']);
            $listingTemplate->setEntered($listingTemplateInsert['entered']);
            $listingTemplate->setStatus($listingTemplateInsert['status']);
            $listingTemplate->setPrice($listingTemplateInsert['price']);
            $listingTemplate->setCatId($listingTemplateInsert['catId']);
            $listingTemplate->setEditable($listingTemplateInsert['editable']);

            $manager->persist($listingTemplate);
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
