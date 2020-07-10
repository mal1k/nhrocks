<?php

namespace ArcaSolutions\ArticleBundle\Search;

use ArcaSolutions\CoreBundle\Search\BaseConfiguration;
use ArcaSolutions\CoreBundle\Services\Utility;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\SearchBlock;
use ArcaSolutions\SearchBundle\Services\SearchEngine;

/**
 * Class ArticleConfiguration
 *
 * @author Lucas Trentim <lucas.trentim@arcasolutions.com>
 * @since 11.0.00
 * @package ArcaSolutions\ArticleBundle\Search
 */
class ArticleConfiguration extends BaseConfiguration
{
    /**
     * @var string|null
     */
    public static $elasticType = 'article';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events = [
            'search.global'   => 'registerItem',
            'recent.article'  => 'registerRecent',
            'article.card'    => 'registerCard'
        ];

        return $events;
    }

    public function registerItem(SearchEvent $searchEvent)
    {
        if (!$where = $searchEvent->getWhere() and array_key_exists(self::$elasticType,
                $this->searchEngine->getActiveModules())) {

            $this->register($searchEvent);
            $qB = SearchEngine::getElasticaQueryBuilder();

            if (!$where and $keyword = Utility::convertArrayToString($this->searchEvent->getKeyword())) {
                $query = $qB->query()->multi_match()
                    ->setQuery($keyword)
                    ->setTieBreaker(0.3)
                    ->setOperator('and')
                    ->setFields([
                        'friendlyUrl^200',
                        'title.raw^200',
                        'title.analyzed^10',
                        'abstract^5',
                        'searchInfo.keyword^1',
                    ]);
            } else {
                $query = $qB->query()->match_all();
            }

            $filter = $qB->filter()->bool()
                ->addMust($qB->filter()->type(self::$elasticType))
                ->addMust($qB->filter()->term()->setTerm('status', true));

            /* ModStores Hooks */
            HookFire("articleconfiguration_before_setup_itemquery", [
                "that"        => &$this,
                "filter"      => &$filter,
                "query"       => &$query,
                "searchEvent" => &$searchEvent,
            ]);

            $finalQuery = $qB->query()->filtered($query, $filter);

            /* ModStores Hooks */
            HookFire("articleconfiguration_before_return_itemquery", [
                "that"        => &$this,
                "searchEvent" => &$searchEvent,
                "finalQuery"  => &$finalQuery,
            ]);

            $this->setElasticaQuery($finalQuery);
        }
    }

    /**
     * @param SearchEvent $searchEvent
     * @throws \Exception
     */
    public function registerRecent(SearchEvent $searchEvent)
    {
        /* registers this event */
        $this->register($searchEvent);

        $qb = SearchEngine::getElasticaQueryBuilder();

        $searchEvent->setDefaultSorter($this->container->get('sorter.publicationdate'));

        $query = $qb->query()->match_all();

        $filter = $qb->filter()->bool()
            ->addMust($qb->filter()->term()->setTerm('status', true))
            ->addMustNot($qb->filter()->terms()->setTerms('_id', SearchBlock::$previousItems[self::$elasticType]));

        /* ModStores Hooks */
        HookFire("articleconfiguration_before_setup_recentquery", [
            "that"        => &$this,
            "filter"      => &$filter,
            "query"       => &$query,
            "searchEvent" => &$searchEvent,
        ]);

        $finalQuery = $qb->query()->filtered($query, $filter);

        /* ModStores Hooks */
        HookFire("articleconfiguration_before_return_recentquery", [
            "that"        => &$this,
            "searchEvent" => &$searchEvent,
            "finalQuery"  => &$finalQuery,
        ]);

        $this->setElasticaQuery($finalQuery);
    }

    /**
     * @param SearchEvent $searchEvent
     *
     * @throws \Exception
     */
    public function registerCard(SearchEvent $searchEvent)
    {
        /* registers this event */
        $this->register($searchEvent);

        $qb = SearchEngine::getElasticaQueryBuilder();

        $options = $searchEvent->getOptions();

        $mainQuery = $qb->query()->match_all();

        $filter =  $qb->filter()->bool();

        $filter->addMust($qb->filter()->term()->setTerm('status', true));

        /* ModStores Hooks */
        HookFire("articleconfiguration_before_setup_popularquery", [
            "that"        => &$this,
            "filter"      => &$filter,
            "query"       => &$mainQuery,
            "searchEvent" => &$searchEvent,
        ]);

        if(empty($options['items'])) {
            if (!empty($options['custom']->level)) {
                $filter->addMust($qb->filter()->terms()->setTerms('level', (array)$options['custom']->level));
            }

            $filter->addMustNot($qb->filter()->terms()->setTerms('_id',
                    SearchBlock::$previousItems[self::$elasticType]));

            $searchEvent->setCardSorter([$options['custom']->order1, $options['custom']->order2]);

        } else {
            $filter->addMust($qb->filter()->terms()->setTerms('_id',$options['items']));
        }

        $mainQuery = $qb->query()->filtered($mainQuery, $filter);

        /* ModStores Hooks */
        HookFire("articleconfiguration_before_setup_popularquery", [
            "that"        => &$this,
            "searchEvent" => &$searchEvent,
            "finalQuery"  => &$mainQuery,
        ]);

        $this->setElasticaQuery($mainQuery);
    }
}
