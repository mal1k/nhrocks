<?php

namespace ArcaSolutions\EventBundle\Twig\Extension;

use ArcaSolutions\EventBundle\Entity\Event;
use ArcaSolutions\EventBundle\Entity\Internal\EventLevelFeatures;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BlocksExtension
 *
 * @package ArcaSolutions\ListingBundle\Twig\Extension
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
            new \Twig_SimpleFunction('eventContent', [$this, 'eventContent']),
        ];
    }

    public function eventContent(Event $event, EventLevelFeatures $eventLevel, $address, $map, $gallery)
    {
        $contentCount = 0;

        //Photos
        !empty($gallery) and $contentCount++ and $activeTab = 2;

        //Overview
        $eventLevel->hasSummaryDescription && !empty($event->getDescription()) and $contentCount++;
        $eventLevel->hasLongDescription && !empty($event->getLongDescription()) and $contentCount++;
        $eventLevel->hasVideo && !empty($event->getVideoSnippet()) and $contentCount++;
        if(!HookExist('eventdetail_overwrite_facebookpage')) {
            $eventLevel->hasFacebookPage && !empty($event->getFacebookPage()) and $contentCount++;
        }
        !empty($address) && !empty($map) and $contentCount++;

        return [
            'content'   => $contentCount
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'detail_event';
    }
}
