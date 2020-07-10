<?php

namespace ArcaSolutions\SearchBundle\Services;

use ArcaSolutions\SearchBundle\Entity\Filters\DateFilter;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use Elastica\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SearchBlock
 *
 * @package ArcaSolutions\SearchBundle\Services
 */
final class SearchBlock
{
    /**
     * Stores any previous listing found in home pages
     *
     * @var array
     */
    static $previousItems
        = [
            'listing'    => [],
            'classified' => [],
            'event'      => [],
            'article'    => [],
            'deal'       => [],
            'blog'       => [],
        ];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Database name
     *
     * @var string
     */
    private $indexName;

    /**
     * @param ContainerInterface $containerInterface
     */
    public function __construct(ContainerInterface $containerInterface)
    {
        $this->container = $containerInterface;

        $this->indexName = $this->container->get("search.engine")->getElasticIndexName();

        /* ModStores Hooks */
        HookFire("searchblock_construct", [
            "that" => &$this
        ]);
    }

    /**
     * @param string $module
     * @param int $size
     * @param \stdClass $content
     *
     * @return \Elastica\Result[]
     */
    public function getCards($module, $size, $content)
    {
        $parameterInfo = $this->container->get('search.parameters');

        $isRandom = false;
        if($size && isset($content->custom) && ($content->custom->order1 === 'random' || $content->custom->order2 === 'random')) {
            $isRandom = true;
        }

        $searchEvent = new SearchEvent('keyword', $isRandom, (array)$content);

        if(isset($content->custom)) {
            // Necessary dependency for upcoming sorter
            if ($size && ($module === 'event') && ($content->custom->order1 === 'upcoming' || $content->custom->order2 === 'upcoming')) {
                $this->container->get('search.parameters')->addModule('event');
                $dateFilter = $this->container->get('filter.date');
                $searchEvent->addFilter($dateFilter, DateFilter::getName());
            }

            if (!empty($content->custom->categories)) {
                $categoryModule = $module === 'deal' ? 'L' : ucfirst($module[0]);
                foreach ($content->custom->categories as $category) {
                    $parameterInfo->addCategoryById($categoryModule.':'.$category);
                }
            }
        }

        $this->container->get('event_dispatcher')->dispatch($module.'.card', $searchEvent);

        /* ModStores Hooks */
        HookFire("searchblock_before_setup_featuredsearch", [
            "that"        => &$this,
            "module"      => &$module,
            "size"        => &$size,
            "searchEvent" => &$searchEvent,
        ]);

        $search = $this->container->get('search.engine')->search($searchEvent, $size);

        /* gets results */
        $result = $search->search()->getResults();

        /* adds item's id to exclude after */
        array_map(function ($item) {
            /* @var $item Result */
            self::$previousItems[$item->getType()][] = $item->getId();
        }, $result);

        $this->getCategories($result);

        if ('article' == $module) {
            $this->getAccount($result);
        }

        $parameterInfo->clearAllParameters();

        /* ModStores Hooks */
        HookFire("searchblock_before_return_featuredresult", [
            "that"   => &$this,
            "module" => &$module,
            "size"   => &$size,
            "result" => &$result,
        ]);

        return $result;
    }

    /**
     * Internally changes the items param, adding categories in each item
     *
     * @param $items
     */
    private function getCategories($items)
    {
        foreach ($items as $item) {
            /* gets categories */
            $categories = $this->container->get('search.engine')->categoryIdSearch(explode(' ', $item->categoryId));
            /* sets categories */
            $item->categories = $categories;
        }
    }

    /**
     * @param array $items
     */
    private function getAccount($items = [])
    {
        foreach ($items as $item) {
            if ($item->accountId) {
                $item->profile = $this->container->get('doctrine')->getRepository('WebBundle:Accountprofilecontact')
                    ->find($item->accountId);
            }
        }
    }

    /**
     * Gets featured items from elasticSearch using events from <module>Configuration
     *
     * @param string $type
     * @param int $size
     *
     * @return \Elastica\Result[]|null
     */
    public function getFeatured($type = '', $size = 1)
    {
        $searchEvent = new SearchEvent('keyword', true);

        /* e.g: featured.listing */
        $this->container->get('event_dispatcher')->dispatch('featured.'.$type, $searchEvent);

        $search = $this->container->get('search.engine')->search($searchEvent, $size);

        /* gets results */
        $result = $search->search()->getResults();

        /* adds item's id to exclude after */
        array_map(function ($item) {
            /* @var $item Result */
            self::$previousItems[$item->getType()][] = $item->getId();
        }, $result);

        $this->getCategories($result);

        return $result;
    }

    /**
     * @param string $type
     * @param int $size
     *
     * @return \Elastica\Result[]
     */
    public function getRecent($type = '', $size = 1)
    {
        $searchEvent = new SearchEvent('keyword');

        /* e.g: featured.listing */
        $this->container->get('event_dispatcher')->dispatch('recent.'.$type, $searchEvent);

        /* ModStores Hooks */
        HookFire("searchblock_before_setup_recentsearch", [
            "that"        => &$this,
            "type"        => &$type,
            "size"        => &$size,
            "searchEvent" => &$searchEvent,
        ]);

        $search = $this->container->get('search.engine')->search($searchEvent, $size);

        /* gets results */
        $result = $search->search()->getResults();

        /* adds item's id to exclude after */
        array_map(function ($item) {
            /* @var $item Result */
            self::$previousItems[$item->getType()][] = $item->getId();
        }, $result);

        $this->getCategories($result);

        /* ModStores Hooks */
        HookFire("searchblock_before_return_recentresult", [
            "that"   => &$this,
            "type"   => &$type,
            "size"   => &$size,
            "result" => &$result,
        ]);

        return $result;
    }

    /**
     * @param string $type
     * @param int $size
     *
     * @param bool $repeatableItem
     * @return \Elastica\Result[]
     */
    public function getPopular($type = '', $size = 1, $repeatableItem = false)
    {
        $searchEvent = new SearchEvent('keyword');

        /* e.g: featured.listing */
        $this->container->get('event_dispatcher')->dispatch('popular.'.$type, $searchEvent, $repeatableItem);

        /* ModStores Hooks */
        HookFire("searchblock_before_setup_popularsearch", [
            "that"        => &$this,
            "type"        => &$type,
            "size"        => &$size,
            "searchEvent" => &$searchEvent,
        ]);

        $search = $this->container->get('search.engine')->search($searchEvent, $size);

        /* gets results */
        $result = $search->search()->getResults();

        if(!$repeatableItem) {
            /* adds item's id to exclude after */
            array_map(function ($item) {
                /* @var $item Result */
                self::$previousItems[$item->getType()][] = $item->getId();
            }, $result);
        }

        $this->getCategories($result);

        if ('article' == $type) {
            $this->getAccount($result);
        }

        /* ModStores Hooks */
        HookFire("searchblock_before_return_popularresult", [
            "that"   => &$this,
            "type"   => &$type,
            "size"   => &$size,
            "result" => &$result,
        ]);

        return $result;
    }

    /**
     * @param string $type
     * @param int $size
     * @param int|array $category_ids
     *
     * @return \Elastica\Result[]
     * @throws \Exception When it's not passed a int ID
     */
    public function getBestOf($type = '', $size = 1, $category_ids)
    {
        $searchEvent = new SearchEvent('keyword', null, ['category_ids' => $category_ids]);

        /* e.g: featured.listing */
        $this->container->get('event_dispatcher')->dispatch('bestof.'.$type, $searchEvent);

        /* ModStores Hooks */
        HookFire("searchblock_before_setup_bestofsearch", [
            "that"        => &$this,
            "type"        => &$type,
            "size"        => &$size,
            "searchEvent" => &$searchEvent,
        ]);

        $search = $this->container->get('search.engine')->search($searchEvent, $size);

        /* gets results */
        $result = $search->search()->getResults();

        /* adds item's id to exclude after */
        array_map(function ($item) {
            /* @var $item Result */
            self::$previousItems[$item->getType()][] = $item->getId();
        }, $result);

        $this->getCategories($result);
        $this->getReviews($result, $type);

        /* ModStores Hooks */
        HookFire("searchblock_before_return_bestofresult", [
            "that"   => &$this,
            "type"   => &$type,
            "size"   => &$size,
            "result" => &$result,
        ]);

        return $result;
    }

    /**
     * @param array $items
     * @param string $type
     *
     * @internal param string|'listing'|'classified'|'event' $type
     */
    private function getReviews($items = [], $type = '')
    {
        if ($items) {
            foreach ($items as $item) {
                /* @var $item Result */
                /* gets a review */
                $item->review = $this->container->get('doctrine')
                    ->getRepository('WebBundle:Review')
                    ->getOneGoodReview($item->getId(), $type);
                /* gets total of reviews */
                $item->reviewTotal = current($this->container->get('doctrine')
                    ->getRepository('WebBundle:Review')
                    ->getTotalByItemId($item->getId(), $type));
            }
        }
    }
}
