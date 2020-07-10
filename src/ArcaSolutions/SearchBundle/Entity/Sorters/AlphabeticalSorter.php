<?php

namespace ArcaSolutions\SearchBundle\Entity\Sorters;

use Elastica\Query;

class AlphabeticalSorter extends BaseSorter
{
    protected static $name = "alphabetical";

    public static function getSubscribedEvents()
    {
        $events = [
            'search.global' => 'register',
        ];

        /* ModStores Hooks */
        HookFire("alphabeticalsorter_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    public function sort(Query $query)
    {
        $query->setSort([
            'title.raw' => ['order' => 'asc']
        ]);
    }
}
