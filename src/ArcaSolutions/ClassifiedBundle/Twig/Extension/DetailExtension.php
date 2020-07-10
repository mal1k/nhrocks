<?php

namespace ArcaSolutions\ClassifiedBundle\Twig\Extension;

use ArcaSolutions\ClassifiedBundle\Entity\Classified;
use ArcaSolutions\ClassifiedBundle\Entity\Internal\ClassifiedLevelFeatures;
use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BlocksExtension
 *
 * @package ArcaSolutions\ClassifiedBundle\Twig\Extension
 */
class DetailExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $containerInterface
     */
    public function __construct(ContainerInterface $containerInterface)
    {
        $this->container = $containerInterface;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('classifiedContent', [$this, 'classifiedContent']),
        ];
    }

    public function classifiedContent(Classified $classified, ClassifiedLevelFeatures $classifiedLevel, $gallery, $address, $map)
    {
        $contentCount = 0;

        //Overview
        !empty($gallery) and $contentCount++;
        $classifiedLevel->hasSummaryDescription && !empty($classified->getSummarydesc()) and $contentCount++;
        $classifiedLevel->hasLongDescription && !empty($classified->getDetaildesc()) and $contentCount++;
        $classifiedLevel->hasAdditionalFiles && !empty($classified->getAttachmentFile()) and $contentCount++;
        $classifiedLevel->hasVideo && !empty($classified->getVideoSnippet()) and $contentCount++;
        !empty($address) && !empty($map) and $contentCount++;
        if(empty($contentCount) && !empty($classified->getListing())) {
            $itemLevel = ListingLevelFeatures::normalizeLevel($classified->getListing()->getLevelObj(), $this->container->get('doctrine'));

            !empty($itemLevel->classifiedQuantityAssociation) and $contentCount++;
        }

        return [
            'content'   => $contentCount
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'detail_classified';
    }
}
