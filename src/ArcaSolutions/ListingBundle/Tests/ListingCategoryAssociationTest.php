<?php
/**
 * Created by PhpStorm.
 * User: betorcs
 * Date: 9/18/17
 * Time: 9:53 AM
 */

namespace ArcaSolutions\ListingBundle\Tests;


use ArcaSolutions\CoreBundle\Inflector;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingCategory;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ListingCategoryAssociationTest extends WebTestCase
{
    /* @var ContainerInterface */
    private static $container;
    /* @var DoctrineRegistry */
    private $doctrine;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::bootKernel();
        self::$container = self::$kernel->getContainer();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->doctrine = self::$container->get("doctrine");
    }

    public function testWhenAddedCategories_shouldReturnThem() {
        // Given
        $c1 = $this->buildCategoryWithTitle("Category 1");
        $c2 = $this->buildCategoryWithTitle("Category 2");
        $l = new Listing();
        $l->setTitle("Listing title");
        $l->setFriendlyUrl(strtolower(Inflector::friendly_title($l->getTitle())));
        $l->setStatus("A");
        $l->setCategories(new ArrayCollection([$c1, $c2]));
        $em = $this->doctrine->getManager();

        // When
        $em->persist($l);
        $em->flush();

        // Then
        $this->assertNotNull($c1->getId());
        $this->assertNotNull($c2->getId());
    }

    private function buildCategoryWithTitle($title) {
        $c = new ListingCategory();
        $c->setTitle($title);
        $c->setFeatured("n");
        $c->setSummaryDescription("What is it?");
        $c->setSeoDescription("Why do you need this here?");
        $c->setPageTitle("Page title, are you sure?");
        $c->setKeywords($c->getTitle());
        $c->setSeoKeywords($c->getTitle());
        $c->setEnabled(true);
        $c->setFriendlyUrl(strtolower(Inflector::friendly_title($title)));
        return $c;
    }
}