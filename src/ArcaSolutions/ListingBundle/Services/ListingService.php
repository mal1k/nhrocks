<?php

namespace ArcaSolutions\ListingBundle\Services;

use ArcaSolutions\EventBundle\Entity\Event;

use ArcaSolutions\SearchBundle\Entity\Filters\DateFilter;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\ParameterHandler;
use ArcaSolutions\SearchBundle\Services\SearchEngine;
use DateInterval;
use DateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;
use When\When;

class ListingService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $hoursWork string
     * @return array
     */
    public function formatHoursWork($hoursWork = '')
    {
        $hours = [];

        if(!empty($hoursWork)) {
            $hours = array_fill_keys([0, 1, 2, 3, 4, 5, 6], []);
            $hoursWork = json_decode($hoursWork, true);
            if (is_array($hoursWork)) {

                foreach ($hoursWork as $hourWork) {
                    $hours[$hourWork['weekday']][] = ['hours_start' => $hourWork['hours_start'], 'hours_end' => $hourWork['hours_end']];
                }

                // sort by weekday
                ksort($hours);

                // sort by hours_start
                foreach ($hours as &$hour) {
                    usort($hour, function ($a, $b) {
                        if ($a['hours_start'] === $b['hours_start']) {
                            return 0;
                        }

                        return $a['hours_start'] < $b['hours_start'] ? -1 : 1;
                    });
                }
                unset($hour);
            }
        }

        HookFire('listingservice_after_formathourswork', [
            'hours' => &$hours
        ]);

        return $hours;
    }

}
