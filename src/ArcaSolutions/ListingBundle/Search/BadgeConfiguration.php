<?php

namespace ArcaSolutions\ListingBundle\Search;

use ArcaSolutions\CoreBundle\Search\BaseConfiguration;
use ArcaSolutions\SearchBundle\Services\SearchEngine;

class BadgeConfiguration extends BaseConfiguration
{
    /**
     * @var string|null
     */
    public static $elasticType = "badge";

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events = [
            'search.badge.id' => 'register'
        ];

        /* ModStores Hooks */
        HookFire("badgeconfiguration_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    /**
     * {@inheritdoc}
     */
    public function getElasticaQuery()
    {
        $returnValue = [];

        if ($termArray = $this->searchEvent->getKeyword()) {
            $queryBuilder = SearchEngine::getElasticaQueryBuilder();
            $returnValue[static::$elasticType] = $queryBuilder->query()->ids(static::$elasticType, $termArray);
        }

        /* ModStores Hooks */
        HookFire("badgeconfiguration_before_return_query", [
            "that"        => &$this,
            "returnValue" => &$returnValue,
        ]);

        return $returnValue;
    }
}
