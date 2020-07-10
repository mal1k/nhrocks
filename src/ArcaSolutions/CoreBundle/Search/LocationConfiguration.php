<?php

namespace ArcaSolutions\CoreBundle\Search;

use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\SearchEngine;

class LocationConfiguration extends BaseConfiguration
{
    /**
     * @var string|null
     */
    public static $elasticType = "location";

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events = [
            'search.location.friendlyurl' => 'registerFriendlyUrl',
            'search.location.id'          => 'registerId'
        ];

        /* ModStores Hooks */
        HookFire("locationconfiguration_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    public function registerFriendlyUrl(SearchEvent $event)
    {
        $this->register($event);

        if ($termArray = $this->searchEvent->getKeyword()) {
            $qB = SearchEngine::getElasticaQueryBuilder();

            $finalQuery = $qB->query()->terms("friendlyUrl", $termArray);

            /* ModStores Hooks */
            HookFire("locationconfiguration_before_return_friendlyurlquery", [
                "that"       => &$this,
                "finalQuery" => &$finalQuery,
            ]);

            $this->setElasticaQuery($finalQuery);
        }
    }

    public function registerId(SearchEvent $event)
    {
        $this->register($event);

        if ($termArray = $this->searchEvent->getKeyword()) {
            $qB = SearchEngine::getElasticaQueryBuilder();

            $finalQuery = $qB->query()->ids(static::$elasticType, $termArray);

            /* ModStores Hooks */
            HookFire("locationconfiguration_before_return_idquery", [
                "that"       => &$this,
                "finalQuery" => &$finalQuery,
            ]);

            $this->setElasticaQuery($finalQuery);
        }
    }
}
