<?php

namespace ArcaSolutions\ListingBundle\Search;

use ArcaSolutions\CoreBundle\Search\BaseConfiguration;
use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\SearchBlock;
use ArcaSolutions\SearchBundle\Services\SearchEngine;

/**
 * Class ListingConfiguration
 *
 * Lucas Trentim <lucas.trentim@arcasolutions.com>
 * @since 11.0.00
 * @package ArcaSolutions\ListingBundle\Search
 */
class ListingConfiguration extends BaseConfiguration
{
    /**
     * @var string|null
     */
    public static $elasticType = 'listing';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events = [
            'search.global'     => 'registerItem',
            'featured.listing'  => 'registerFeatured',
            'listing.card'       => 'registerCard'
        ];

        /* ModStores Hooks */
        HookFire("listingconfiguration_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    public function registerItem(SearchEvent $searchEvent)
    {
        $this->register($searchEvent);
        $qb = SearchEngine::getElasticaQueryBuilder();

        $query = $this->createDefaultSearchQuery();

        $filter = $qb->filter()->bool()
            ->addMust($qb->filter()->type(self::$elasticType))
            ->addMust($qb->filter()->term()->setTerm('status', true));

        /* ModStores Hooks */
        HookFire("listingconfiguration_before_setup_itemquery", [
            "that"        => &$this,
            "filter"      => &$filter,
            "query"       => &$query,
            "searchEvent" => &$searchEvent,
        ]);

        $finalQuery = $qb->query()->filtered($query, $filter);

        /* ModStores Hooks */
        HookFire("listingconfiguration_before_return_itemquery", [
            "that"        => &$this,
            "searchEvent" => &$searchEvent,
            "finalQuery"  => &$finalQuery,
        ]);


        $this->setElasticaQuery($finalQuery);
    }

    /**
     * Gets features listings using elasticSearch
     *
     * @param SearchEvent $searchEvent
     */
    public function registerFeatured(SearchEvent $searchEvent)
    {
        /* registers this event */
        $this->register($searchEvent);

        $qb = SearchEngine::getElasticaQueryBuilder();

        /* all levels with module as a key */
        $this->getLevelFeatures($featuredLevels);

        /* getting just featured levels */
        $featuredLevels = array_filter(array_map(function ($array) {
            if ('y' == $array->isFeatured) {
                return $array->level;
            }
        }, current($featuredLevels)));

        $query = $qb->query()->match_all();

        $filter =  $qb->filter()->bool()
            ->addMust($qb->filter()->terms()->setTerms('level', $featuredLevels))
            ->addMust($qb->filter()->term()->setTerm('status', true))
            ->addMustNot($qb->filter()->terms()->setTerms('_id',
                SearchBlock::$previousItems[self::$elasticType]));

        /* ModStores Hooks */
        HookFire("listingconfiguration_before_setup_featuredquery", [
            "that"           => &$this,
            "filter"         => &$filter,
            "query"          => &$query,
            "searchEvent"    => &$searchEvent,
            "featuredLevels" => &$featuredLevels
        ]);

        $finalQuery = $qb->query()->filtered($query, $filter);

        /* ModStores Hooks */
        HookFire("listingconfiguration_before_return_featuredquery", [
            "that"           => &$this,
            "searchEvent"    => &$searchEvent,
            "finalQuery"     => &$finalQuery,
            "featuredLevels" => &$featuredLevels
        ]);

        $this->setElasticaQuery($finalQuery);
    }

    /**
     * {@inheritdoc}
     */
    public function getLevelFeatures(&$features)
    {
        /* Sets Listing level information to be used while rendering the summary templates */
        $features[self::$elasticType] = ListingLevelFeatures::getAllLevelsAndNormalize($this->container->get('doctrine'));
    }

    /**
     * @param SearchEvent $searchEvent
     *
     * @throws \Exception
     */
    public function registerCard(SearchEvent $searchEvent)
    {
        /* registers this event */
        $this->register($searchEvent);

        $qb = SearchEngine::getElasticaQueryBuilder();

        $options = $searchEvent->getOptions();

        $mainQuery = $qb->query()->match_all();

        $filter =  $qb->filter()->bool();

        $filter->addMust($qb->filter()->term()->setTerm('status', true));

        if(empty($options['items'])) {
            if (!empty($options['custom']->level)) {
                $filter->addMust($qb->filter()->terms()->setTerms('level', (array)$options['custom']->level));
            }

            if (!empty($options['custom']->locations)) {
                $locations = [];
                $options['custom']->locations->location_1 and $locations = 'L1:' . $options['custom']->locations->location_1;
                $options['custom']->locations->location_2 and $locations = 'L2:' . $options['custom']->locations->location_2;
                $options['custom']->locations->location_3 and $locations = 'L3:' . $options['custom']->locations->location_3;
                $options['custom']->locations->location_4 and $locations = 'L4:' . $options['custom']->locations->location_4;
                $options['custom']->locations->location_5 and $locations = 'L5:' . $options['custom']->locations->location_5;

                !empty($locations) and $filter->addMust($qb->filter()->terms()->setTerms('locationId', (array)$locations));
            }

            $filter->addMustNot($qb->filter()->terms()->setTerms('_id',
                    SearchBlock::$previousItems[self::$elasticType]));

            $searchEvent->setCardSorter([$options['custom']->order1, $options['custom']->order2]);

        } else {
            $filter->addMust($qb->filter()->terms()->setTerms('_id',$options['items']));
        }

        /* ModStores Hooks */
        HookFire("listingconfiguration_before_setup_bestofquery", [
            "that"        => &$this,
            "filter"      => &$filter,
            "mainQuery"   => &$mainQuery,
            "searchEvent" => &$searchEvent,
        ]);

        $mainQuery = $qb->query()->filtered($mainQuery, $filter);

        /* ModStores Hooks */
        HookFire("listingconfiguration_before_return_bestofquery", [
            "that"        => &$this,
            "searchEvent" => &$searchEvent,
            "finalQuery"  => &$mainQuery,
        ]);


        $this->setElasticaQuery($mainQuery);
    }

    /**
     * {@inheritdoc}
     */
    public function getResultsJS()
    {
        return ['listing.summary' => '::modules/listing/js/summary.js.twig'];
    }
}
