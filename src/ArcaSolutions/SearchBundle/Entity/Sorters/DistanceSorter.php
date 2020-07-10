<?php

namespace ArcaSolutions\SearchBundle\Entity\Sorters;

use ArcaSolutions\CoreBundle\Services\Utility;
use Elastica\Query;
use Elastica\Script\ScriptFile;

class DistanceSorter extends BaseSorter
{
    /**
     * @var string
     */
    protected static $name = 'distance';

    /**
     * @var null
     */
    private $userGeoLocation;

    /**
     * @var bool
     */
    private $initialized = false;

    public function __construct($container)
    {
        parent::__construct($container);

        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        $events = [
            'search.global' => 'register'
        ];

        /* ModStores Hooks */
        HookFire("distancesorter_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    public function needsGeoLocation()
    {
        return true;
    }

    public function sort(Query $query)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        if ($this->hasValidGeolocation()) {
            $query->setSort([
                '_geo_distance' => [
                    'geoLocation'   => $this->userGeoLocation,
                    'order'         => 'asc',
                    'unit'          => $this->container->get('translator')->trans('distance.unit', [], 'units'),
                    'distance_type' => 'plane',
                ],
            ]);
        }
    }

    private function initialize()
    {
        $this->userGeoLocation = [
            'lat' => $this->container->get('request')->get('lat'),
            'lon' => $this->container->get('request')->get('lng'),
        ];

        if (!$this->hasValidGeolocation()) {
            $this->userGeoLocation = Utility::extractGeoPoint(
                $this->container->get('request_stack')->getCurrentRequest()->cookies->get(
                    $this->container->get('search.engine')->getGeoLocationCookieName()
                )
            );
        }

        if ($this->hasValidGeolocation()) {
            $this->script = new ScriptFile('searchDistance', $this->userGeoLocation);
        }

        $this->initialized = true;
    }

    private function hasValidGeolocation()
    {
        return isset($this->userGeoLocation['lat'], $this->userGeoLocation['lat']) &&
            !empty($this->userGeoLocation['lat']) && !empty($this->userGeoLocation['lon']);
    }
}
