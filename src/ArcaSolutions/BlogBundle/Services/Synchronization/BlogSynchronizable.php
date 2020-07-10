<?php

namespace ArcaSolutions\BlogBundle\Services\Synchronization;

use ArcaSolutions\BlogBundle\Entity\Blogcategory;
use ArcaSolutions\BlogBundle\Entity\Post;
use ArcaSolutions\BlogBundle\Search\BlogConfiguration;
use ArcaSolutions\CoreBundle\Services\Utility;
use ArcaSolutions\ElasticsearchBundle\Services\Synchronization\Modules\BaseSynchronizable;
use ArcaSolutions\ElasticsearchBundle\Services\Synchronization\Synchronization;
use ArcaSolutions\ImageBundle\Entity\Image;
use Doctrine\ORM\QueryBuilder;
use Elastica\Document;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BlogSynchronizable extends BaseSynchronizable implements EventSubscriberInterface
{
    const PARTIAL_SETTING_KEY = 'blog_partial_sync';

    function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->configurationService = 'blog.search';
        $this->databaseType = Synchronization::DATABASE_DOMAIN;
        $this->upsertFormat = static::DOCUMENT_UPSERT;
        $this->deleteFormat = static::DELETE_ID_RAW;
    }

    public static function getSubscribedEvents()
    {
        $events = [
            'edirectory.synchronization' => 'handleEvent',
        ];

        /* ModStores Hooks */
        HookFire("blogsyncronizable_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    public function handleEvent($event, $eventName)
    {
        $this->generateAll();
    }

    public function generateAll($output = null, $pageSize = Synchronization::BULK_THRESHOLD)
    {
        $progressBar = null;
        $doctrine = $this->container->get('doctrine');
        $settings = $this->container->get('settings');

        $qB = $doctrine->getRepository('BlogBundle:Post')->createQueryBuilder('blog');

        if ($output) {
            $totalCount = $qB->select('COUNT(blog.id)')->where("blog.status = 'A'")->getQuery()->getSingleScalarResult();

            $progressBar = new ProgressBar($output, $totalCount);

            $progressBar->start();
        }

        $this->container->get('search.engine')->clearType(BlogConfiguration::$elasticType);

        $iteration = 0;
        $lastId = 0;

        $query = $qB->select('blog.id');

        do {
            $query->setMaxResults($pageSize)->setFirstResult($pageSize * $iteration++);

            $ids = $query->getQuery()->getArrayResult();

            if ($foundCount = count($ids)) {
                array_walk($ids, function (&$value) {
                    $value = $value['id'];
                });

                $this->addUpsert($ids);
                $progressBar and $progressBar->advance($foundCount);

                $lastId = isset($ids[count($ids) - 1]) ? $ids[count($ids) - 1] : 0;
            }

            $doctrine->getManager()->clear();
        } while ($foundCount);

        $settings->setSetting(self::PARTIAL_SETTING_KEY, $lastId);

        $progressBar and $progressBar->finish();
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param OutputInterface $output
     * @param int $partialTotalCount
     * @param int $pageSize
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function generatePartial(
        OutputInterface $output,
        $partialTotalCount = Synchronization::PARTIAL_TREHSHOLD,
        $pageSize = Synchronization::BULK_THRESHOLD
    ) {
        $currentCount = 0;
        $totalCount = 0;
        $progressBar = null;
        $doctrine = $this->container->get('doctrine');
        $settings = $this->container->get('settings');

        $lastId = $settings->getDomainSetting(self::PARTIAL_SETTING_KEY);

        /* @var $qB QueryBuilder */
        $qB = $doctrine->getRepository('BlogBundle:Post')->createQueryBuilder('blog');

        if ($output) {
            $totalCount = $qB->select('COUNT(blog.id)')->where("blog.status = 'A'")->getQuery()->getSingleScalarResult();

            $countQuery = new \Elastica\Query([
                'query' => [
                    'type' => [
                        'value' => 'blog'
                    ]
                ]
            ]);

            $currentCount = $this->container->get('search.engine')->getElasticaIndex()->count($countQuery);

            $progressBarCount = $totalCount - $currentCount < $partialTotalCount ? $totalCount - $currentCount : $partialTotalCount;

            $progressBar = new ProgressBar($output, $progressBarCount);

            $progressBar->start();
        }

        $query = $qB->select('blog.id')
            ->orderBy('blog.id', 'ASC');

        if ($lastId) {
            $query->andWhere('blog.id > :last_id')->setParameter('last_id', $lastId);
        }

        $iteration = 0;
        $totalSyncked = 0;

        while ($totalSyncked < $partialTotalCount) {
            $firstResult = $pageSize * $iteration++;
            $querySize = $pageSize * $iteration;

            if($querySize > $partialTotalCount) {
                $maxResults = $partialTotalCount - $totalSyncked;
            } else {
                $maxResults = $pageSize;
            }

            $query->setMaxResults($maxResults)->setFirstResult($firstResult);

            $ids = $query->getQuery()->getArrayResult();

            if ($foundCount = count($ids)) {
                array_walk($ids, function (&$value) {
                    $value = $value['id'];
                });

                $this->addUpsert($ids);
                $progressBar and $progressBar->advance($foundCount);

                $lastId = isset($ids[count($ids) - 1]) ? $ids[count($ids) - 1] : 0;
            } else {
                break;
            }

            $totalSyncked += $foundCount;

            $doctrine->getManager()->clear();
        }

        $currentCount += $totalSyncked;

        $settings->setSetting(self::PARTIAL_SETTING_KEY, $lastId);

        $progressBar and $progressBar->finish();

        $currentCount and $output->writeln(sprintf(PHP_EOL . PHP_EOL . 'Partial Synchronize %1$s/%2$s completed', $currentCount, $totalCount));
    }

    /**
     * {@inheritdoc}
     */
    public function getUpsertStash()
    {
        $result = [];

        if ($ids = parent::getUpsertStash()) {
            $elements = $this->container->get('doctrine')->getRepository('BlogBundle:Post')->findBy(['id' => $ids]);

            while ($element = array_pop($elements)) {
                $result[] = $this->getUpsertDocument($element);
            }
        }

        return $result;
    }

    /**
     * @param Post $blog
     * @return Document|null
     */
    public function getUpsertDocument($blog)
    {
        $document = null;

        if ($blog and is_object($blog)) {
            $document = new Document(
                $blog->getId(),
                $this->generateDocFromEntity($blog),
                $this->container->get($this->getConfigurationService())->getElasticType(),
                $this->container->get('search.engine')->getElasticIndexName()
            );

            $document->setDocAsUpsert(true);
        }

        return $document;
    }

    /**
     * @param Post $element
     * @return array
     */
    public function generateDocFromEntity($element)
    {
        $doctrine = $this->container->get('doctrine');

        /* @var $categories Blogcategory[] */
        $categories = array_map(function ($item) {
            return $item->getCategory();
        }, $element->getCategories()->getValues());

        $categoryIds = [];
        $parentCategoryIds = [];
        $syncService = $this->container->get('blog.category.synchronization');

        while ($category = array_pop($categories)) {
            $categoryIds[] = $syncService->normalizeId($category->getId());

            $parents = $category->getParentIds($category);

            for ($i = 0, $iMax = count($parents); $i < $iMax; $i++) {
                $parents[$i] = $syncService->normalizeId($parents[$i]);
            }

            $parentCategoryIds = array_merge($parentCategoryIds, $parents);
        }

        $commentCount = $doctrine->getRepository('BlogBundle:Comments')
            ->createQueryBuilder('comments')
            ->select('COUNT(comments.id)')
            ->where('comments.postId = :commentsPostId')
            ->setParameter('commentsPostId', $element->getId())
            ->getQuery()
            ->getSingleScalarResult();

        $suggest = [
            'input'   => $element->getFulltextsearchKeyword(),
            'output'  => $element->getTitle(),
            'payload' => [
                'friendlyUrl' => $element->getFriendlyUrl(),
                'type'        => 'blog',
                'id'          => $element->getId(),
            ],
            'weight'  => 90,
        ];

        /* @var $image Image */
        if ($element->getImageId() && $image = $doctrine->getRepository('ImageBundle:Image')->find($element->getImageId())) {
            $thumbnail = $this->container->get('imagehandler')->getPath($image);
        } else {
            $thumbnail = null;
        }

        $publicationDate = $element->getEntered()->format('Y-m-d');
        $entered = $element->getEntered()->format('Y-m-d');
        $updated = $element->getUpdated()->format('Y-m-d');

        $document = [
            'categoryId'       => implode(' ', $categoryIds) ?: null,
            'parentCategoryId' => implode(' ', array_unique($parentCategoryIds)) ?: null,
            'commentCount'     => $commentCount,
            'content'          => $element->getContent(),
            'friendlyUrl'      => $element->getFriendlyUrl(),
            'level'            => 10,
            'publicationDate'  => $publicationDate == Utility::BAD_DATE_VALUE ? null : $publicationDate,
            'entered'          => $entered == Utility::BAD_DATE_VALUE ? null : $entered,
            'updated'          => $updated == Utility::BAD_DATE_VALUE ? null : $updated,
            'searchInfo'       => [
                'keyword' => $element->getFulltextsearchKeyword(),
            ],
            'status'           => $element->getStatus() == 'A',
            'suggest'          => [
                'what' => $suggest,
                'blog' => $suggest
            ],
            'thumbnail'        => $thumbnail,
            'title'            => $element->getTitle(),
            'views'            => $element->getNumberViews(),
        ];

        /* ModStores Hooks */
        HookFire("blogsynchronizable_before_return_document", [
            "document" => &$document,
            "element"  => &$element
        ]);

        return $document;
    }

    /**
     * @inheritdoc
     */
    public function extractFromResult($info)
    {
        $document = [
            '_id'             => $info['_id'],
            'categoryId'      => $info['categoryId'],
            'thumbnail'       => $info['thumbnail'],
            'views'           => $info['views'],
            'friendlyUrl'     => $info['friendlyUrl'],
            'title'           => $info['title'],
            'content'         => $info['content'],
            'status'          => $info['status'],
            'searchInfo'      => [
                'keyword' => $info['searchInfo.keyword'],
            ],
            'publicationDate' => $info['publicationDate'],
            'entered'         => $info['entered'],
            'updated'         => $info['updated'],
            'commentCount'    => $info['commentCount'],
            'level'           => $info['level'],
            'suggest'         => [
                'what' => [
                    'input'   => $info['suggest.what.input'],
                    'output'  => $info['suggest.what.output'],
                    'payload' => $info['suggest.what.payload'],
                    'weight'  => $info['suggest.what.weight'],
                ],
            ],
        ];

        /* ModStores Hooks */
        HookFire("blogsynchronizable_before_return_result", [
            "document" => &$document,
            "info"     => &$info
        ]);

        return $document;
    }

    public function addAverageReviewUpdate($id, $value)
    {
    }
}
