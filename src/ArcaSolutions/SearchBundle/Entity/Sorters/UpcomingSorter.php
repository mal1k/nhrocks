<?php

namespace ArcaSolutions\SearchBundle\Entity\Sorters;

use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\ParameterHandler;
use Elastica\Query;

class UpcomingSorter extends BaseSorter
{
    protected static $name = "upcoming";

    public static function getSubscribedEvents()
    {
        $events = [
            'search.global'        => 'register',
            'upcoming.event'       => 'register',
        ];

        /* ModStores Hooks */
        HookFire("upcomingsorter_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    public function register(SearchEvent $event)
    {
        if ($this->isSearchOnlyByEvent()) {
            $event->addSort($this, $this->translatedName);
        }
    }

    public function sort(Query $query)
    {
        $params = ["field" => "recurrent_date"];
        $parameterInfo = $this->container->get("search.parameters");
        if ($startDate = $parameterInfo->getStartDate()) {
            $startDate->setTime(0, 0, 0);
            $params["from"] = $startDate->format("Y-m-d");
        }

        $query->setSort([
            '_script' => [
                'type' => 'string',
                'script' => [
                    'script' => 'nextOccurrence',
                    'lang' => 'native',
                    'params' => $params
                ]
            ],
            'level' => ['order' => 'asc']
        ]);
    }

    private function isSearchOnlyByEvent()
    {
        $searchedModules = $this->container->get('search.parameters')->getModules();

        return (array_pop($searchedModules) == ParameterHandler::MODULE_EVENT and empty($searchedModules));
    }
}
