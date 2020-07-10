<?php

namespace ArcaSolutions\SearchBundle\Entity\Filters;

use ArcaSolutions\SearchBundle\Entity\FilterMenuTreeNode;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\ParameterHandler;
use ArcaSolutions\SearchBundle\Services\SearchEngine;
use Elastica\Script\Script;

class DateFilter extends BaseFilter
{
    /**
     * {@inheritdoc}
     */
    protected static $name = 'DateFilter';
    /**
     * @var \DateTime
     */
    protected $startDate;
    /**
     * @var \DateTime
     */
    protected $endDate;
    /**
     * @var string
     */
    protected $urlDateFormat = 'm-d-Y';
    /**
     * @var string
     */
    protected $dateFormat = 'm/d/Y';
    /**
     * @var string
     */
    protected $queryDateFormat = 'Y-m-d';
    /**
     * @var string
     */
    protected $bootstrapDatepickerLanguage = 'en';
    /**
     * @var string
     */
    protected $bootstrapDatepickerDateFormat = 'mm/dd/yyyy';
    /**
     * @var string
     */
    protected $bootstrapDatepickerUrlDateFormat = '/mm-dd-yyyy';

    /**
     * @var FilterMenuTreeNode|null
     */
    protected $fromTodayFilterOption;
    /**
     * @var FilterMenuTreeNode|null
     */
    protected $anyDateFilterOption;
    /**
     * @var FilterMenuTreeNode|null
     */
    protected $todayFilterOption;
    /**
     * @var FilterMenuTreeNode|null
     */
    protected $weekFilterOption;
    /**
     * @var FilterMenuTreeNode|null
     */
    protected $weekendFilterOption;
    /**
     * @var FilterMenuTreeNode|null
     */
    protected $monthFilterOption;
    /**
     * @var FilterMenuTreeNode|null
     */
    protected $customDateFilterOption;

    function __construct($container)
    {
        parent::__construct($container);

        $languageHandler = $container->get('languagehandler');
        $this->dateFormat = $languageHandler->getDateFormat();
        $this->urlDateFormat = preg_replace("/[^\w]/", '-', $this->dateFormat);
        $locale = $languageHandler->getISOLang($container->get('multi_domain.information')->getLocale());
        $this->bootstrapDatepickerLanguage = $this->convertLocale($locale);
        $this->bootstrapDatepickerDateFormat = $this->convertToBootstrapDatepickerFormat($this->dateFormat);
        $this->bootstrapDatepickerUrlDateFormat = '/'. $this->convertToBootstrapDatepickerFormat($this->urlDateFormat);
    }

    public function convertLocale($locale)
    {
        switch ($locale) {
            case 'pt':
                $locale = 'pt-BR';
                break;
        }

        return $locale;
    }

    public function convertToBootstrapDatepickerFormat($format)
    {
        return str_replace(['d', 'm', 'Y'], ['dd', 'mm', 'yyyy'], $format);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events = [
            'search.global'     => 'registerItem',
            'upcoming.event'    => 'registerItem',
            'event.card'        => 'registerItem'
        ];

        /* ModStores Hooks */
        HookFire("datefilter_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    /**
     * Provides the necessary elasticsearch queries and filters for a summary search
     * @param SearchEvent $searchEvent
     * @param $eventName
     * @throws \Exception
     */
    public function registerItem(SearchEvent $searchEvent, $eventName)
    {
        if ($this->isSearchOnlyByEvent()) {
            $this->register($searchEvent, $eventName);

            $parameterInfo = $this->container->get('search.parameters');

            $qB = SearchEngine::getElasticaQueryBuilder();

            $startDate = null;
            $endDate = null;

            if ($startDate = $parameterInfo->getStartDate()) {
                $this->startDate = $startDate;
                $this->startDate->setTime(0, 0, 0);
            }

            if ($endDate = $parameterInfo->getEndDate()) {
                $this->endDate = $endDate;
                $this->endDate->setTime(0, 0, 0);
            }

            if (null != $endDate && $endDate < $startDate) {
                $parameterInfo->clearStartDate();
                $parameterInfo->clearEndDate();
                return;
            }

            /*
             * Prepares filter script based in search parameters.
             */

            /* Exists parameters start and end date, then should filter events occurring in a range date. */
            if ((null != $startDate) && (null != $endDate)) {
                $script = new Script('hasAnyOccurrenceBetween', [
                    'field' => 'recurrent_date',
                    'start' => $startDate->format('Y-m-d'),
                    'end'   => $endDate->format('Y-m-d')
                ], 'native');

            /* Exists only start date, then should filter occurring in a specific date. */
            } else if (null != $startDate) {
                $script = new Script('hasOccurrencesAt', [
                    'field' => 'recurrent_date',
                    'date'  => $startDate->format('Y-m-d')
                ], 'native');

            /* No date parameter, then should filter not expired. */
            } else {
                $script = new Script('notHasExpired', [
                    'field' => 'recurrent_date'
                ], 'native');
            }

            $elasticFilter = $qB->filter()->script($script);

            if ($parameterInfo->hasKeywords()) {
                $this->addElasticaFilter($elasticFilter);
            } else {
                $this->addElasticaPostFilter($elasticFilter);
            }
        }
    }

    public function isSearchOnlyByEvent()
    {
        $searchedModules = $this->container->get('search.parameters')->getModules();

        return (array_pop($searchedModules) == ParameterHandler::MODULE_EVENT and empty($searchedModules));
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterView()
    {
        return $this->container->get('twig')->render('::blocks/filters/date.html.twig', ['dateFilter' => $this]);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param $title
     * @return FilterMenuTreeNode
     * @throws \Exception
     */
    public function getFilterOption($start, $end, $title)
    {
        $isSelected = ($start == $this->startDate && $end == $this->endDate);

        $searchPageUrl = $isSelected ? $this->getSearchPageUrl() : $this->getSearchPageUrl($start, $end);

        return new FilterMenuTreeNode(0, 0, $title, 0, 0, 0, $isSelected, $searchPageUrl, 0);
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return array
     * @throws \Exception
     */
    private function getSearchPageUrl($startDate = null, $endDate = null)
    {
        $searchParameters = clone $this->container->get('search.parameters');
        $searchParameters->clearModule();
        $searchParameters->clearEndDate();
        $searchParameters->clearStartDate();

        $searchParameters->addModule(ParameterHandler::MODULE_EVENT);

        $startDate and $searchParameters->setStartDate($startDate);
        $endDate and $searchParameters->setEndDate($endDate);

        return $searchParameters->buildUrl();
    }

    /**
     * @return FilterMenuTreeNode
     * @throws \Exception
     */
    public function getAnyDateFilterOption()
    {
        if ($this->anyDateFilterOption === null) {
            $this->anyDateFilterOption = $this->getFilterOption(null, null, 'anyDate');
        }

        return $this->anyDateFilterOption;
    }

    /**
     * @return FilterMenuTreeNode
     * @throws \Exception
     */
    public function getFromTodayFilterOption()
    {
        if ($this->fromTodayFilterOption === null) {
            $start = new \DateTime('today');
            $end = new \DateTime('+3 months');
            $this->fromTodayFilterOption = $this->getFilterOption($start, $end, 'fromToday');
        }

        return $this->fromTodayFilterOption;
    }

    /**
     * @return FilterMenuTreeNode
     * @throws \Exception
     */
    public function getTodayFilterOption()
    {
        if ($this->todayFilterOption === null) {
            $start = new \DateTime('today');
            $this->todayFilterOption = $this->getFilterOption($start, $start, 'today');
        }

        return $this->todayFilterOption;
    }

    /**
     * @return FilterMenuTreeNode
     * @throws \Exception
     */
    public function getWeekFilterOption()
    {
        if ($this->weekFilterOption === null) {
            $start = new \DateTime('today');
            $end = new \DateTime('next Saturday');
            $this->weekFilterOption = $this->getFilterOption($start, $end, 'week');
        }

        return $this->weekFilterOption;
    }

    /**
     * @return FilterMenuTreeNode
     * @throws \Exception
     */
    public function getWeekendFilterOption()
    {
        if ($this->weekendFilterOption === null) {
            $start = new \DateTime('next Saturday');
            $end = new \DateTime('next Sunday');
            $this->weekendFilterOption = $this->getFilterOption($start, $end, 'weekend');
        }

        return $this->weekendFilterOption;
    }

    /**
     * @return FilterMenuTreeNode
     * @throws \Exception
     */
    public function getMonthFilterOption()
    {
        if ($this->monthFilterOption === null) {
            $start = new \DateTime('today');
            $end = new \DateTime('last day of this month');
            $end->setTime(0, 0, 0);
            $this->monthFilterOption = $this->getFilterOption($start, $end, 'month');
        }

        return $this->monthFilterOption;
    }

    /**
     * @return FilterMenuTreeNode
     * @throws \Exception
     */
    public function getCustomDateFilterOption()
    {
        if ($this->customDateFilterOption === null) {
            $searchParameters = clone $this->container->get('search.parameters');

            $searchParameters->clearStartDate();
            $searchParameters->clearEndDate();
            $searchParameters->clearModule();

            $searchParameters->addModule(ParameterHandler::MODULE_EVENT);

            $overridingParameters = [
                ParameterHandler::SLUG_STARTDATE => 'STARTDATE',
                ParameterHandler::SLUG_ENDDATE => 'ENDDATE',
            ];

            $url = $searchParameters->buildUrl(1, $overridingParameters);
            $this->customDateFilterOption = new FilterMenuTreeNode(0, 0, 'custom', 0, 0, 0, false, $url, 0);
        }

        return $this->customDateFilterOption;
    }

    /**
     * {@inheritdoc}
     */
    public function processAggregationResults($aggregationResults)
    {
        /* This filter has no aggregations */
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return string
     */
    public function getStartDateString()
    {
        return $this->startDate ? $this->startDate->format($this->dateFormat) : '';
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @return string
     */
    public function getEndDateString()
    {
        return $this->endDate ? $this->endDate->format($this->dateFormat) : '';
    }

    /**
     * @return string
     */
    public function getUrlDateFormat()
    {
        return $this->urlDateFormat;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * @return string
     */
    public function getQueryDateFormat()
    {
        return $this->queryDateFormat;
    }

    /**
     * @return string
     */
    public function getBootstrapDatepickerLanguage()
    {
        return $this->bootstrapDatepickerLanguage;
    }

    /**
     * @return string
     */
    public function getBootstrapDatepickerUrlDateFormat()
    {
        return $this->bootstrapDatepickerUrlDateFormat;
    }

    /**
     * @return string
     */
    public function getBootstrapDatepickerDateFormat()
    {
        return $this->bootstrapDatepickerDateFormat;
    }

    /**
     * Returns the currently selected date filter option
     * @return FilterMenuTreeNode|null
     * @throws \Exception
     */
    public function getSelectedFilter()
    {
        $return = null;

        $filterOptions = [
            $this->getAnyDateFilterOption(),
            $this->getFromTodayFilterOption(),
            $this->getTodayFilterOption(),
            $this->getWeekFilterOption(),
            $this->getWeekendFilterOption(),
            $this->getMonthFilterOption(),
        ];

        /* @var $pointer FilterMenuTreeNode */
        while ($return === null and $pointer = array_pop($filterOptions)) {
            $pointer->isSelected and $return = $pointer;
        }

        if (!$return && ($this->startDate || $this->endDate)) {
            $return = $this->getCustomDateFilterOption();
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    protected function processAggregationBuckets($filterAggregationBuckets)
    {
        /* This filter has no buckets. */
    }
}
