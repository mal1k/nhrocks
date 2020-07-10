<?php

namespace ArcaSolutions\CoreBundle\Logic;


use ArcaSolutions\ArticleBundle\Entity\Articlecategory;
use ArcaSolutions\BlogBundle\Entity\Blogcategory;
use ArcaSolutions\ClassifiedBundle\Entity\Classifiedcategory;
use ArcaSolutions\CoreBundle\Entity\Location1;
use ArcaSolutions\CoreBundle\Entity\Location2;
use ArcaSolutions\CoreBundle\Entity\Location3;
use ArcaSolutions\CoreBundle\Entity\Location4;
use ArcaSolutions\CoreBundle\Entity\Location5;
use ArcaSolutions\CoreBundle\Inflector;
use ArcaSolutions\EventBundle\Entity\Eventcategory;
use ArcaSolutions\ListingBundle\Entity\ListingCategory;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class FriendlyUrlLogic
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Logic
 * @since 11.3.00
 */
class FriendlyUrlLogic
{
    /**
     * @var ObjectManager
     */
    private $entityManagerDomain;

    /**
     * @var ObjectManager
     */
    private $entityManagerMain;

    /**
     * @var DoctrineRegistry
     */
    private $doctrine;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->doctrine = $container->get('doctrine');
        $this->entityManagerDomain = $this->doctrine->getManager('domain');
        $this->entityManagerMain = $this->doctrine->getManager('main');

    }


    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $title
     * @param bool $withSuffix
     *
     * @return string
     */
    public function buildFriendlyUrl($title, $withSuffix = false)
    {
        $friendly = strtolower(Inflector::friendly_title($title));
        if ($withSuffix) {
            $friendly = uniqid("$friendly-");
        }

        return $friendly;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string $title
     * @param string $class
     *
     * @return string
     */
    public function buildUniqueModuleFriendlyUrl($title, $class)
    {
        $friendly = strtolower(Inflector::friendly_title($title));
        $repository = $this->entityManagerDomain->getRepository($class);
        $entity = null;
        try {
            $entity = $repository->findBy(['friendlyUrl' => $friendly]);
        } catch (\UnexpectedValueException $ignored) {
        }

        if ($entity) {
            $friendly = uniqid("$friendly-");
        }

        return $friendly;
    }


    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $title
     *
     * @return string
     */
    public function buildUniqueFriendlyUrl($title)
    {
        $friendly = strtolower(Inflector::friendly_title($title));

        $count = $this->checkUniqueFriendlyUrl($friendly);

        if ($count > 0) {
            $friendly = uniqid("$friendly-");
        }

        return $friendly;

    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since v11.4.00
     *
     * @param string $friendly
     * @param array $pageTypes
     * @param int $id
     *
     * @param $moduleUri
     * @return int|mixed
     */
    public function checkUniqueFriendlyUrl($friendly, array $pageTypes = [], $id = 0, $moduleUri = null)
    {
        $count = 0;
        $classes = [
            'main'   => [Location1::class, Location2::class, Location3::class, Location4::class, Location5::class],
            'domain' => [
                ListingCategory::class,
                Eventcategory::class,
                Classifiedcategory::class,
                Blogcategory::class,
                Articlecategory::class,
            ],
        ];

        foreach ($classes['main'] as $class) {
            $count += $this->entityManagerMain->getRepository($class)->createQueryBuilder('c')
                ->select('count(c)')
                ->andWhere('c.friendlyUrl = :friendlyUrl')
                ->setParameter('friendlyUrl', $friendly)
                ->getQuery()->getSingleScalarResult();
        }

        foreach ($classes['domain'] as $class) {
            $count += $this->entityManagerDomain->getRepository($class)->createQueryBuilder('c')
                ->select('count(c)')
                ->andWhere('c.friendlyUrl = :friendlyUrl')
                ->setParameter('friendlyUrl', $friendly)
                ->getQuery()->getSingleScalarResult();
        }

        /* @var array $pageTypes */
        $pageTypes = $pageTypes?: $this->doctrine->getRepository('WysiwygBundle:PageType')->getTypesPageIdLessCustomPage();
        try {
            $pages = $this->doctrine->getRepository('WysiwygBundle:Page')->uniqueUrl($friendly, $pageTypes, $id, $moduleUri);

            $friendlyUrl = ($moduleUri ? $moduleUri . '/' : '') . $friendly;

            foreach($pages as $page) {
                $pageType = $this->doctrine->getRepository('WysiwygBundle:PageType')->find($page['pageTypeId']);

                $pageModuleUri = $this->container->get('pagetype.service')->getModuleUri($pageType->getTitle());

                $pageFriendlyUrl = ($pageModuleUri ? $pageModuleUri . '/' : '') . $page['url'];

                if($pageFriendlyUrl === $friendlyUrl) {
                    $count++;
                }
            }
        } catch (NoResultException $e) {
        } catch (NonUniqueResultException $e) {
        }

        return $count;
    }

}
