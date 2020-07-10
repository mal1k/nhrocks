<?php

namespace ArcaSolutions\EventBundle\Services;

use ArcaSolutions\EventBundle\Entity\Event;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\SearchBundle\Entity\Filters\DateFilter;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\ParameterHandler;
use ArcaSolutions\SearchBundle\Services\SearchEngine;
use DateInterval;
use DateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;
use When\When;

class Recurring
{
    /**
     * It is the minimum quantity of events for API endpoint
     */
    const MINIMUM_EVENTS_FOR_API = 10;
    /**
     * Array of week name days
     *
     * @var array
     */
    protected static $weekdays = [
        1 => 'SU',
        2 => 'MO',
        3 => 'TU',
        4 => 'WE',
        5 => 'TH',
        6 => 'FR',
        7 => 'SA',
    ];
    /**
     * @var DoctrineRegistry
     */
    private $doctrine;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(DoctrineRegistry $doctrine, ContainerInterface $container)
    {
        $this->doctrine = $doctrine;
        $this->container = $container;
    }

    /**
     * Returns the next occurrence of the event recurring rule from today
     *
     * @param null $startDate
     * @param null $rrule
     * @return DateTime
     * @throws \Exception
     */
    public function getNextOccurrence($startDate = null, $untilDate = null, $rrule = null)
    {
        $r = new When();

        $r = $r->rrule($rrule);

        $date = new DateTime();

        if ($untilDate) {
            if ($startDate == $untilDate) {
                return $startDate;
            }
        }

        /*
         * Checks occurrence in one year
         */
        $count = 1;
        while ($count <= 365) {

            if ($date >= $startDate && $r->occursOn($date)) {
                return $date;
            }

            $date->add(new DateInterval('P1D'));

            $count++;
        }

        return null;
    }

    public function getRRule_rfc2445(Event $event)
    {

        $dayOfMonth = $event->getDay();
        $month = $event->getMonth();
        $until = $event->getUntilDate()->setTime(23, 59, 59);

        $daysOfWeek = null;
        // prepare days of week
        if (!empty($event->getDayofweek())) {
            $dayOfWeekMap = ['1' => 'SU', '2' => 'MO', '3' => 'TU', '4' => 'WE', '5' => 'TH', '6' => 'FR', '7' => 'SA'];
            $data = explode(',', $event->getDayofweek());
            // Convert days numbers to string format
            $daysOfWeekList = array_values(array_intersect_key($dayOfWeekMap, array_flip($data)));

            if (!empty($event->getWeek())) {
                $weekMap = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => -1];
                $data = explode(',', $event->getWeek());

                $weeksList = array_values(array_intersect_key($weekMap, array_flip($data)));
                $aux = [];
                foreach ($weeksList as $w) {
                    foreach ($daysOfWeekList as $d) {
                        $aux[] = $w.$d;
                    }
                }
                $daysOfWeekList = $aux;
            }

            $daysOfWeek = implode(',', $daysOfWeekList);
        }

        $rrule = [];

        if ($this->isYearly($event)) {
            $rrule[] = 'FREQ=YEARLY';
        } else {
            if ($this->isMonthly($event)) {
                $rrule[] = 'FREQ=MONTHLY';
            } else {
                if ($this->isWeekly($event)) {
                    $rrule[] = 'FREQ=WEEKLY';
                } else {
                    if ($this->isDaily($event)) {
                        $rrule[] = 'FREQ=DAILY';
                    }
                }
            }
        }

        if ($month > 0) {
            $rrule[] = 'BYMONTH='.$month;
        }

        if ($dayOfMonth > 0) {
            $rrule[] = 'BYMONTHDAY='.$dayOfMonth;
        }

        if ($daysOfWeek !== null) {
            $rrule[] = 'BYDAY='.$daysOfWeek;
        }

        if (null !== $until && $until->getTimestamp() > 0) {
            $rrule[] = 'UNTIL='.$until->format("Ymd\\THis\\Z");
        }

        $rrule[] = 'WKST=SU';

        return 'RRULE:'.implode(';', $rrule);
    }

    /**
     * Checks if a event is yearly
     *
     * @param Event $event
     *
     * @return bool
     */
    public function isYearly(Event $event)
    {
        return 0 != $event->getMonth();
    }

    /**
     * Checks if a event is monthly
     *
     * Note:
     * It is used to cover the possibility of a monthly recurrence in a certain day
     * The flags used in DB is different of a normal monthly recurrence,
     * so it is needed verify other things
     *
     * @param Event $event
     *
     * @return bool
     */
    public function isMonthly(Event $event)
    {
        return (0 == $event->getMonth() && '' != $event->getWeek())
            || (!$this->isDaily($event) && !$this->isWeekly($event) && !$this->isYearly($event));
    }

    /**
     * Checks if a event is daily
     *
     * @param Event $event
     *
     * @return bool
     */
    public function isDaily(Event $event)
    {
        return 0 == $event->getMonth() && '' == $event->getWeek() && '' == $event->getDayofweek()
            && 0 == $event->getDay();
    }

    /**
     * Checks if a event is weekly
     *
     * @param Event $event
     *
     * @return bool
     */
    public function isWeekly(Event $event)
    {
        return 0 == $event->getMonth() && '' == $event->getWeek() && '' != $event->getDayofweek();
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param null $wholeMonth
     * @param int $limit
     * @return Event[]
     * @since v11.4.00
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     */
    public function getUpcomingEvents(\DateTime $startDate, \DateTime $endDate, $wholeMonth = null, $limit = 50)
    {
        $events = $this->getRecurringEventsUsingES($startDate, $endDate, $limit);

        return $this->filterEvents($events, $startDate, $endDate, $wholeMonth);
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param int $limit
     * @return array
     */
    private function getRecurringEventsUsingES(\DateTime $startDate, \DateTime $endDate = null, $limit = 50)
    {
        /* @var $searchEngine SearchEngine */
        $searchEngine = $this->container->get('search.engine');

        /* @var $parameterHandler ParameterHandler */
        $parameterHandler = $this->container->get('search.parameters');
        $parameterHandler->setStartDate($startDate);
        $parameterHandler->setEndDate($endDate ?: $startDate);
        $parameterHandler->addModule(ParameterHandler::MODULE_EVENT);

        /* @var $dateFilter DateFilter */
        $dateFilter = $this->container->get('filter.date');

        $searchEvent = new SearchEvent('keyword');

        $searchEvent->addFilter($dateFilter, DateFilter::getName());

        $searchEvent->setDefaultSorter($this->container->get('sorter.upcoming'));

        $this->container->get('event_dispatcher')->dispatch('upcoming.event', $searchEvent);

        $search = $searchEngine->search($searchEvent, $limit);
        $result = $search->search();

        $eventRepo = $this->doctrine->getRepository('EventBundle:Event');

        $documents = $result->getDocuments();

        $ids = [];
        foreach ($documents as $doc) {
            $ids[] = $doc->getId();
        }

        return $eventRepo->findBy(['id' => $ids]);
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since v11.4.00
     * @param Event[] $events
     * @param DateTime $startDate
     * @param DateTime|null $endDate
     * @return Event[]
     */
    private function filterEvents(array $events, \DateTime $startDate, \DateTime $endDate = null, $wholeMonth = null)
    {
        $filtered = [];

        foreach ($events as $event) {
            if ($event === null) {
                continue;
            }

            $isInTheFuture = $event->getStartDate() >= $startDate;
            $isRecurring = $event->getRecurring() === 'Y';
            $endDateIsNullOrIsInTheFuture = $event->getEndDate() === null ||
                $event->getEndDate()->getTimestamp() <= 0 ||
                $event->getEndDate() >= $startDate;
            $untilDateIsNullOrIsInTheFuture = $event->getUntilDate() === null ||
                $event->getUntilDate()->getTimestamp() <= 0 ||
                $event->getUntilDate() >= $startDate;

            if ((!$wholeMonth || $isInTheFuture || $isRecurring) && $endDateIsNullOrIsInTheFuture && $untilDateIsNullOrIsInTheFuture) {
                $filtered[] = $event;
            }
        }

        return $filtered;
    }

}
