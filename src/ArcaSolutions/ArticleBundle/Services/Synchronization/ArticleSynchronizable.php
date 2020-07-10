<?php

namespace ArcaSolutions\ArticleBundle\Services\Synchronization;

use ArcaSolutions\ArticleBundle\Entity\Article;
use ArcaSolutions\ArticleBundle\Entity\Articlecategory;
use ArcaSolutions\ArticleBundle\Search\ArticleConfiguration;
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

class ArticleSynchronizable extends BaseSynchronizable implements EventSubscriberInterface
{
    const PARTIAL_SETTING_KEY = 'article_partial_sync';

    function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->configurationService = 'article.search';
        $this->databaseType = Synchronization::DATABASE_DOMAIN;
        $this->upsertFormat = static::DOCUMENT_UPSERT;
        $this->deleteFormat = static::DELETE_ID_RAW;
    }

    public static function getSubscribedEvents()
    {
        $events =  [
            'edirectory.synchronization' => 'handleEvent',
        ];

        /* ModStores Hooks */
        HookFire("articlesyncronizable_before_return_subscribers", [
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

        $qB = $doctrine->getRepository('ArticleBundle:Article')->createQueryBuilder('article');

        if ($output) {
            $totalCount = $qB->select('COUNT(article.id)')->where("article.status = 'A'")->getQuery()->getSingleScalarResult();

            $progressBar = new ProgressBar($output, $totalCount);

            $progressBar->start();
        }

        $this->container->get('search.engine')->clearType(ArticleConfiguration::$elasticType);

        $iteration = 0;
        $lastId = 0;

        $query = $qB->select('article.id')
            ->where('article.status = :articleStatus')
            ->setParameter('articleStatus', 'A');

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
        $qB = $doctrine->getRepository('ArticleBundle:Article')->createQueryBuilder('article');

        if ($output) {
            $totalCount = $qB->select('COUNT(article.id)')->where("article.status = 'A'")->getQuery()->getSingleScalarResult();

            $countQuery = new \Elastica\Query([
                'query' => [
                    'type' => [
                        'value' => 'article'
                    ]
                ]
            ]);

            $currentCount = $this->container->get('search.engine')->getElasticaIndex()->count($countQuery);

            $progressBarCount = $totalCount - $currentCount < $partialTotalCount ? $totalCount - $currentCount : $partialTotalCount;

            $progressBar = new ProgressBar($output, $progressBarCount);

            $progressBar->start();
        }

        $query = $qB->select('article.id')
            ->where('article.status = :status')
            ->setParameter('status', 'A')
            ->orderBy('article.id', 'ASC');

        if ($lastId) {
            $query->andWhere('article.id > :last_id')->setParameter('last_id', $lastId);
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
            $elements = $this->container->get('doctrine')->getRepository('ArticleBundle:Article')->findBy(['id' => $ids]);

            while ($element = array_pop($elements)) {
                $result[] = $this->getUpsertDocument($element);
            }
        }

        return $result;
    }

    /**
     * @param Article $article
     * @return Document|null
     */
    public function getUpsertDocument($article)
    {
        $document = null;

        if ($article and is_object($article)) {
            $document = new Document(
                $article->getId(),
                $this->generateDocFromEntity($article),
                $this->container->get($this->getConfigurationService())->getElasticType(),
                $this->container->get('search.engine')->getElasticIndexName()
            );

            $document->setDocAsUpsert(true);
        }

        return $document;
    }

    /**
     * @param Article $element
     * @return array
     */
    public function generateDocFromEntity($element)
    {
        if ($categories = $element->getCategories()) {
            $categoryIds = [];

            /* @var $category Articlecategory */
            while ($category = array_pop($categories)) {
                $categoryIds[] = $this->container->get('article.category.synchronization')
                    ->normalizeId($category->getId());
            }

            $categoryId = implode(' ', $categoryIds);
        } else {
            $categoryId = null;
        }

        $parentCategoryIds = [];
        for ($i = 1; $i <= 5; $i++) {
            $prop = sprintf('getCat%dId', $i);

            if (!$element->$prop()) {
                continue;
            }

            for ($j = 1; $j <= 4; $j++) {
                $prop = sprintf('getParcat%dLevel%dId', $i, $j);
                if ($element->$prop() > 0) {
                    $parentCategoryIds[] = $this->container->get('article.category.synchronization')
                        ->normalizeId($element->$prop());
                }
            }
        }

        $suggest = [
            'input'   => $element->getFulltextsearchKeyword(),
            'output'  => $element->getTitle(),
            'payload' => [
                'friendlyUrl' => $element->getFriendlyUrl(),
                'type'        => 'article',
                'id'          => $element->getId(),
            ],
            'weight'  => 90,
        ];

        /* @var $image Image */
        if ($element->getImageId() && $image = $this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($element->getImageId())) {
            $thumbnail = $this->container->get('imagehandler')->getPath($image);
        } else {
            $thumbnail = null;
        }

        $publicationDate = $element->getPublicationDate()->format('Y-m-d');
        $entered = $element->getEntered()->format('Y-m-d');
        $updated = $element->getUpdated()->format('Y-m-d');

        $document =
            [
                'abstract'         => $element->getAbstract(),
                'accountId'        => $element->getAccountId(),
                'author'           => [
                    'name' => $element->getAuthor(),
                    'url'  => $element->getAuthorUrl(),
                ],
                'categoryId'       => $categoryId,
                'parentCategoryId' => implode(' ', array_unique($parentCategoryIds)) ?: null,
                'friendlyUrl'      => $element->getFriendlyUrl(),
                'level'            => $element->getLevel(),
                'publicationDate'  => $publicationDate == Utility::BAD_DATE_VALUE ? null : $publicationDate,
                'entered'          => $entered == Utility::BAD_DATE_VALUE ? null : $entered,
                'updated'          => $updated == Utility::BAD_DATE_VALUE ? null : $updated,
                'searchInfo'       => [
                    'keyword' => $element->getFulltextsearchKeyword(),
                ],
                'status'           => $element->getStatus() == 'A',
                'suggest'          => [
                    'what'    => $suggest,
                    'article' => $suggest
                ],
                'thumbnail'        => $thumbnail,
                'title'            => $element->getTitle(),
                'views'            => $element->getNumberViews(),
                'authorImageId'    => $element->getAuthorImageId()
            ];

        /* ModStores Hooks */
        HookFire("articlesynchronizable_before_return_document", [
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
            'abstract'        => $info['abstract'],
            'accountId'       => $info['accountId'],
            'author'          => [
                'name' => $info['author.name'],
                'url'  => $info['author.url'],
            ],
            'friendlyUrl'     => $info['friendlyUrl'],
            '_id'             => $info['_id'],
            'level'           => $info['level'],
            'publicationDate' => $info['publicationDate'],
            'entered'         => $info['entered'],
            'updated'         => $info['updated'],
            'searchInfo'      => [
                'keyword' => $info['searchInfo.keyword'],
            ],
            'status'          => $info['status'],
            'thumbnail'       => $info['thumbnail'],
            'title'           => $info['title'],
            'views'           => $info['views'],
            'authorImageId'   => $info['authorImageId'],
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
        HookFire("articlesynchronizable_before_return_result", [
            "document" => &$document,
            "info"     => &$info
        ]);

        return $document;
    }
}
