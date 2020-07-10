<?php

namespace ArcaSolutions\EventBundle\Controller;


use ArcaSolutions\EventBundle\Search\EventConfiguration;
use Elastica\Aggregation\Terms;
use Elastica\Client;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\Range;
use Elastica\QueryBuilder;
use Elastica\Script\Script as Script;
use Elastica\Query\Script as QueryScript;
use Elastica\Search;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CalendarController
 *
 * @package ArcaSolutions\EventBundle\Controller
 */
class CalendarController extends Controller
{
    /**
     * Get all dates that have an event
     *
     * @param null $day
     * @param null $month
     * @param null $year
     * @return JsonResponse
     * @throws \Exception
     */
    public function eventsAction($day = null, $month = null, $year = null)
    {
        /* Creates date */
        $startDate = \DateTime::createFromFormat('Y-m-d', sprintf('%s-%s-%s', $year, $month, $day));
        $startDate->setTime(00, 00, 00);
        $endDate = clone $startDate;
        $endDate->setTime(23, 59, 59);
        $endDate->add(new \DateInterval('P30D'));

        $scriptParams = [
            'field' => 'recurrent_date',
            'start' => $startDate->format('Y-m-d'),
            'end'   => $endDate->format('Y-m-d'),
        ];

        // Aggregation
        $termsAgg = new Terms('doc_count');
        $termsAgg->setSize(0);
        $termsAgg->setOrder('_term', 'asc');
        $termsAgg->setScript(new Script('occurrencesBetween', $scriptParams, 'native'));


        // Creates query filter
        $builder = new QueryBuilder();
        $query = new BoolQuery();
        $query->addShould(
            $builder->query()->bool()
                ->addMustNot(new Exists('recurrent_date.rrule'))
                ->addMust(new Range('recurrent_date.start_date', ['gte' => $startDate->format('Y-m-d')]))
        );
        $query->addShould(
            $builder->query()->bool()
                ->addMust(new Exists('recurrent_date.rrule'))
                ->addFilter(new QueryScript(new Script('hasAnyOccurrenceBetween', $scriptParams, 'native')))
        );

        /* ModStores Hooks */
        HookFire('calendarcontroller_before_setup_calendarquery', [
            'that'        => &$this,
            'query'       => &$query,
        ]);

        // Creates query to be sent to elasticsearch
        $finalQuery = new Query();
        $finalQuery->setQuery($query);
        $finalQuery->addAggregation($termsAgg);

        $esConfig = $this->getParameter('search.config');
        $client = new Client($esConfig['elasticsearch']);
        $search = new Search($client);

        $indexName = $this->container->get('search.engine')->getElasticIndexName();
        $search->addIndex($indexName);

        $search->addType(EventConfiguration::$elasticType);
        $buckets = $search->search($finalQuery)->getAggregation('doc_count');

        $dates = [];
        foreach ($buckets['buckets'] as $bucket) {
            $eventDate = new \DateTime($bucket['key']);
            $eventDate->sub(new \DateInterval('PT21H'));

            $dates[] = [
                'id'    => count($dates),
                'start' => $eventDate->format('Y-m-d')
            ];
        }

        return JsonResponse::create(['success' => 1, 'result' => $dates]);
    }
}
