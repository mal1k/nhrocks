<?php

namespace ArcaSolutions\SearchBundle\Entity\Filters;

use ArcaSolutions\SearchBundle\Entity\FilterMenuTreeNode;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\SearchEngine;

class RatingFilter extends BaseTranslatableUrlFilter
{
    /**
     * {@inheritdoc}
     */
    protected static $name = "RatingFilter";
    /**
     * {@inheritdoc}
     */
    protected static $filterUrlName = "rating";

    private $aggregationInfo;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events = [
            'search.global' => 'registerItem'
        ];

        /* ModStores Hooks */
        HookFire("ratingfilter_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    public function registerMapItem(SearchEvent $searchEvent, $eventName)
    {
        $listingReview = $this->container->get("settings")->getDomainSetting("review_listing_enabled");

        //Only will register the filter if has review active for listing
        if ($listingReview) {
            $this->register($searchEvent, $eventName);

            if ($ratingInfo = $this->container->get("search.parameters")->getQueryParameter($this->translatedName)) {
                $this->addElasticaFilter(
                    SearchEngine::getElasticaQueryBuilder()->filter()->terms("averageReview", $ratingInfo)
                );
            }
        }
    }

    public function registerItem(SearchEvent $searchEvent, $eventName)
    {
        $listingReview = $this->container->get("settings")->getDomainSetting("review_listing_enabled");

        //Only will register the filter if has review active for listing
        if ($listingReview) {
            $this->register($searchEvent, $eventName);

            $qb = SearchEngine::getElasticaQueryBuilder();

            if ($ratingInfo = $this->container->get("search.parameters")->getQueryParameter($this->translatedName)) {
                $this->addElasticaPostFilter(
                    $qb->filter()->terms("averageReview", $ratingInfo)
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterView()
    {
        $return = null;

        if ($this->aggregationInfo) {
            $menuNodes = $this->getRatingTree();

            $return = $this->container->get("twig")->render(
                "::blocks/filters/rating.html.twig",
                ["menuNodes" => $menuNodes]
            );
        }


        return $return;
    }

    /**
     * @param $rating
     * @return array
     */
    private function getSearchPageUrl($rating)
    {
        $result = null;

        if ($searchParameters = clone $this->container->get("search.parameters")) {
            $searchParameters->toggleQueryParameter($this->translatedName, $rating);
            $result = $searchParameters->buildUrl();
        }

        return $result;
    }

    public function getElasticaAggregations()
    {
        $subscribedEvents = self::getSubscribedEvents();

        switch ($subscribedEvents[$this->eventName]) {
            case 'registerItem' :
                $qb = SearchEngine::getElasticaQueryBuilder();

                $aggregation = $qb->aggregation()->terms(static::$name)
                    ->setField("averageReview")->addAggregation(
                        $qb->aggregation()->terms(static::$name)
                            ->setField("_type"))
                    ->setSize($this->searchConfig['settings']['aggregationSize']);

                $filters = $this->searchEvent->getElasticaPostFilters();
//                unset ($filters[static::$name]);

                if ($filters) {
                    $aggregation->addAggregation(
                        $qb->aggregation()->filter(
                            "filtered",
                            $qb->filter()
                                ->bool()
                                ->addMust($filters)
                        )
                    );
                }

                $this->addElasticaAggregation($aggregation);

                break;
        }

        return $this->elasticaAggregations;
    }

    /**
     * {@inheritdoc}
     */
    protected function processAggregationBuckets($filterAggregationBuckets)
    {
        $this->aggregationInfo = null;
        $listingReview = $this->container->get("settings")->getDomainSetting("review_listing_enabled");

        foreach ($filterAggregationBuckets as $bucket) {
            if ($bucket['key'] > 0 and $documentCount = isset($bucket['filtered']) ? $bucket['filtered']['doc_count'] : $bucket['doc_count']) {
                $listingKey = array_search('listing', array_column($bucket['RatingFilter']['buckets'], 'key'));

                //This if below will take care of the review situation (on or off for listing module)
                if ($listingReview && $listingKey !== false) {
                    //Get the count for each situation
                    if ($listingReview) {
                        $documentCountAux = $bucket['RatingFilter']['buckets'][$listingKey]['doc_count'];
                    }

                    $this->aggregationInfo[$bucket['key']] = ["numberOfItems" => $documentCountAux];
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getAggregationInfo()
    {
        return $this->aggregationInfo;
    }

    public function getRatingTree()
    {
        $ratingTree = [];

        if ($this->aggregationInfo) {

            $requestFilterRatings = $this->container->get("search.parameters")->getQueryParameter($this->translatedName);

            krsort($this->aggregationInfo, SORT_NUMERIC);

            foreach ($this->aggregationInfo as $key => $value) {
                $ratingTree[$key] = new FilterMenuTreeNode(
                    null,
                    null,
                    $key,
                    $key,
                    null,
                    $key,
                    in_array($key, $requestFilterRatings),
                    $this->getSearchPageUrl($key),
                    $value["numberOfItems"],
                    null
                );
            }
        }
        return $ratingTree;
    }

}
