<?php

namespace ArcaSolutions\CoreBundle\Search;

use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\SearchEngine;

class CategoryConfiguration extends BaseConfiguration
{
    /**
     * @var string|null
     */
    public static $elasticType = "category";

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events = [
            'search.category.friendlyurl' => 'registerFriendlyUrl',
            'search.category.id'          => 'registerId',
        ];

        /* ModStores Hooks */
        HookFire("categoryconfiguration_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    public function registerFriendlyUrl(SearchEvent $searchEvent)
    {
        $this->register($searchEvent);

        if ($termArray = $this->searchEvent->getKeyword()) {
            $qB = SearchEngine::getElasticaQueryBuilder();

            $finalQuery = $qB->query()->terms("friendlyUrl", $termArray);

            /* ModStores Hooks */
            HookFire("categoryconfiguration_before_return_friendlyurlquery", [
                "that"        => &$this,
                "searchEvent" => &$searchEvent,
                "finalQuery"  => &$finalQuery,
            ]);

            $this->setElasticaQuery($finalQuery);
        }
    }

    public function registerId(SearchEvent $searchEvent)
    {
        $this->register($searchEvent);

        if ($termArray = $this->searchEvent->getKeyword()) {
            $qB = SearchEngine::getElasticaQueryBuilder();

            $finalQuery = $qB->query()->ids(static::$elasticType, $termArray);

            /* ModStores Hooks */
            HookFire("categoryconfiguration_before_return_idquery", [
                "that"        => &$this,
                "searchEvent" => &$searchEvent,
                "finalQuery"  => &$finalQuery,
            ]);

            $this->setElasticaQuery($finalQuery);
        }
    }
}
