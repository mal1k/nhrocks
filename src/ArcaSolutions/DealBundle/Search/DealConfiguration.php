<?php

namespace ArcaSolutions\DealBundle\Search;

use ArcaSolutions\CoreBundle\Search\BaseConfiguration;
use ArcaSolutions\ElasticsearchBundle\Entity\DecayFunction;
use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use ArcaSolutions\ListingBundle\Search\ListingConfiguration;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\SearchBlock;
use ArcaSolutions\SearchBundle\Services\SearchEngine;
use Elastica\Query\FunctionScore;

/**
 * Class DealConfiguration
 *
 * Lucas Trentim <lucas.trentim@arcasolutions.com>
 * @since 11.0.00
 * @package ArcaSolutions\DealBundle\Search
 */
class DealConfiguration extends BaseConfiguration
{
    /**
     * @var string|null
     */
    public static $elasticType = 'deal';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events = [
            'search.global'     => 'registerItem',
            'popular.deal'      => 'registerPopular',
            'deal.card'         => 'registerCard'
        ];

        /* ModStores Hooks */
        HookFire("dealconfiguration_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    public function registerItem(SearchEvent $searchEvent)
    {
        /* @todo After 'promotion' has been changed to 'deal', change the line below */
        if (array_key_exists('promotion', $this->searchEngine->getActiveModules())) {

            $this->register($searchEvent);
            $qb = SearchEngine::getElasticaQueryBuilder();

            $query = $this->createDefaultSearchQuery();

            $filter = [
                $qb->filter()->type(self::$elasticType),
                $qb->filter()->term()->setTerm('status', true),
                $qb->filter()->range('date.start', ['lte' => 'now']),
                $qb->filter()->range('date.end', ['gte' => 'now/d']),
                $qb->filter()->range('amount', ['gt' => 0]),
                $qb->filter()->exists('listing.friendlyUrl'),
            ];

            /* ModStores Hooks */
            HookFire("dealconfiguration_before_setup_itemquery", [
                "that"        => &$this,
                "filter"      => &$filter,
                "query"       => &$query,
                "searchEvent" => &$searchEvent,
            ]);

            $finalQuery = $qb->query()->filtered($query, $qb->filter()->bool_and($filter));

            /* ModStores Hooks */
            HookFire("dealconfiguration_before_return_itemquery", [
                "that"        => &$this,
                "searchEvent" => &$searchEvent,
                "finalQuery"  => &$finalQuery,
            ]);

            $this->setElasticaQuery($finalQuery);

        }
    }

    /**
     * @param SearchEvent $searchEvent
     * @param bool $repeatableItem
     * @throws \Exception
     */
    public function registerPopular(SearchEvent $searchEvent, $repeatableItem = false)
    {
        /* registers this event */
        $this->register($searchEvent);

        $qb = SearchEngine::getElasticaQueryBuilder();

        $searchEvent->setDefaultSorter($this->container->get('sorter.view'));

        $query = $qb->query()->match_all();

        $filter = $qb->filter()->bool()
            ->addMust($qb->filter()->term()->setTerm('status', true))
            ->addMust($qb->filter()->range('date.start', ['lte' => 'now']))
            ->addMust($qb->filter()->range('date.end', ['gte' => 'now/d']))
            ->addMust($qb->filter()->exists('listing.friendlyUrl'))
            ->addMustNot($qb->filter()->terms()->setTerms('_id', SearchBlock::$previousItems[self::$elasticType]));

        /* ModStores Hooks */
        HookFire("dealconfiguration_before_setup_popularquery", [
            "that"        => &$this,
            "filter"      => &$filter,
            "query"       => &$query,
            "searchEvent" => &$searchEvent,
        ]);

        $finalQuery = $qb->query()->filtered($query, $filter);

        /* ModStores Hooks */
        HookFire("dealconfiguration_before_return_popularquery", [
            "that"        => &$this,
            "searchEvent" => &$searchEvent,
            "finalQuery"  => &$finalQuery,
        ]);

        $this->setElasticaQuery($finalQuery);
    }

    /**
     * {@inheritdoc}
     */
    public function getLevelFeatures(&$features)
    {
        if (empty($features[ListingConfiguration::$elasticType])) {
            /* Sets Listing level information to be used while rendering the summary templates */
            $features[ListingConfiguration::$elasticType] = ListingLevelFeatures::getAllLevelsAndNormalize(
                $this->container->get('doctrine')
            );
        }
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

        $filter->addMust($qb->filter()->term()->setTerm('status', true))
            ->addMust($qb->filter()->range('date.start', ['lte' => 'now']))
            ->addMust($qb->filter()->range('date.end', ['gte' => 'now/d']))
            ->addMust($qb->filter()->exists('listing.friendlyUrl'));

        /* ModStores Hooks */
        HookFire("dealconfiguration_before_setup_recentquery", [
            "that"        => &$this,
            "filter"      => &$filter,
            "query"       => &$mainQuery,
            "searchEvent" => &$searchEvent,
        ]);

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

        $mainQuery = $qb->query()->filtered($mainQuery, $filter);

        /* ModStores Hooks */
        HookFire("dealconfiguration_before_return_recentquery", [
            "that"        => &$this,
            "searchEvent" => &$searchEvent,
            "finalQuery"  => &$mainQuery,
        ]);

        $this->setElasticaQuery($mainQuery);
    }
}
