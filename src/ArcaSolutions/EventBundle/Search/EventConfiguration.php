<?php

namespace ArcaSolutions\EventBundle\Search;

use ArcaSolutions\CoreBundle\Search\BaseConfiguration;
use ArcaSolutions\EventBundle\Entity\Internal\EventLevelFeatures;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\SearchBlock;
use ArcaSolutions\SearchBundle\Services\SearchEngine;
use Elastica\QueryBuilder;
use Elastica\Script\Script;

/**
 * Class EventConfiguration
 *
 * Lucas Trentim <lucas.trentim@arcasolutions.com>
 * @since 11.0.00
 * @package ArcaSolutions\EventBundle\Search
 */
class EventConfiguration extends BaseConfiguration
{
    /**
     * @var string|null
     */
    public static $elasticType = 'event';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events = [
            'search.global'     => 'registerItem',
            'upcoming.event'    => 'registerUpcoming',
            'featured.event'    => 'registerFeatured',
            'event.card'        => 'registerCard'
        ];

        /* ModStores Hooks */
        HookFire("eventconfiguration_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    public function registerItem(SearchEvent $searchEvent)
    {
        if (array_key_exists(self::$elasticType, $this->searchEngine->getActiveModules())) {
            $this->register($searchEvent);
            $qB = SearchEngine::getElasticaQueryBuilder();

            $query = $this->createDefaultSearchQuery();

            $filter = $this->createFilter();

            /* ModStores Hooks */
            HookFire("eventconfiguration_before_setup_itemquery", [
                "that"        => &$this,
                "filter"      => &$filter,
                "query"       => &$query,
                "searchEvent" => &$searchEvent,
            ]);

            $finalQuery = $qB->query()->filtered($query, $filter);

            /* ModStores Hooks */
            HookFire("eventconfiguration_before_return_itemquery", [
                "that"        => &$this,
                "searchEvent" => &$searchEvent,
                "finalQuery"  => &$finalQuery,
            ]);

            $this->setElasticaQuery($finalQuery);
        }
    }

    /**
     * @return mixed
     */
    protected function createFilter()
    {
        $qB = SearchEngine::getElasticaQueryBuilder();

        $filter = $qB->filter()->bool()
            ->addMust($qB->filter()->type(self::$elasticType))
            ->addMust($qB->filter()->term()->setTerm('status', true));

        /* ModStores Hooks */
        HookFire("eventconfiguration_before_return_filterquery", [
            "that"   => &$this,
            "filter" => &$filter
        ]);

        return $filter;
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

        $filter = $qb->filter()->bool()
            ->addMust($qb->filter()->terms()->setTerms('level', $featuredLevels))
            ->addMust($qb->filter()->term()->setTerm('status', true))
            ->addMustNot($qb->filter()->terms()->setTerms('_id', SearchBlock::$previousItems[self::$elasticType]))
            ->addShould($this->getNotExpiredEvents($qb));

        /* ModStores Hooks */
        HookFire("eventconfiguration_before_setup_featuredquery", [
            "that"           => &$this,
            "filter"         => &$filter,
            "query"          => &$query,
            "searchEvent"    => &$searchEvent,
            "featuredLevels" => &$featuredLevels
        ]);

        $finalQuery = $qb->query()->filtered($query, $filter);

        /* ModStores Hooks */
        HookFire("eventconfiguration_before_return_featuredquery", [
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
        /* Sets Event level information to be used while rendering the summary templates */
        $features[self::$elasticType] = EventLevelFeatures::getAllLevelsAndNormalize(
            $this->container->get('doctrine')
        );
    }

    /**
     * Gets filter to event has not expired.
     *
     * @param QueryBuilder $queryBuilder
     * @return \Elastica\Filter\Script
     */
    public function getNotExpiredEvents(QueryBuilder $queryBuilder)
    {
        return $queryBuilder->filter()->script(new Script('notHasExpired', ['field' => 'recurrent_date'], 'native'));
    }

    public function registerUpcoming(SearchEvent $searchEvent)
    {
        $this->register($searchEvent);

        $qb = SearchEngine::getElasticaQueryBuilder();

        $query = $qb->query()->match_all();

        $filter = $qb->filter()->bool()
            ->addMust($qb->filter()->type(self::$elasticType))
            ->addMust($qb->filter()->term()->setTerm('status', true));

        /* ModStores Hooks */
        HookFire("eventconfiguration_before_setup_recurringquery", [
            "that"        => &$this,
            "filter"      => &$filter,
            "query"       => &$query,
            "searchEvent" => &$searchEvent,
        ]);

        $finalQuery = $qb->query()->filtered($query, $filter);

        /* ModStores Hooks */
        HookFire("eventconfiguration_before_return_recurringquery", [
            "that"        => &$this,
            "searchEvent" => &$searchEvent,
            "finalQuery"  => &$finalQuery,
        ]);

        $this->setElasticaQuery($finalQuery);
    }

    /**
     * @param SearchEvent $searchEvent
     *
     * @throws \Exception
     */
    public function registerCard(SearchEvent $searchEvent)
    {
        $parameterInfo = $this->container->get('search.parameters');

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
        HookFire("eventconfiguration_before_setup_popularquery", [
            "that"        => &$this,
            "filter"      => &$filter,
            "query"       => &$mainQuery,
            "searchEvent" => &$searchEvent,
        ]);

        $mainQuery = $qb->query()->filtered($mainQuery, $filter);

        /* ModStores Hooks */
        HookFire("eventconfiguration_before_return_popularquery", [
            "that"        => &$this,
            "searchEvent" => &$searchEvent,
            "finalQuery"  => &$mainQuery,
        ]);

        $this->setElasticaQuery($mainQuery);
    }
}
