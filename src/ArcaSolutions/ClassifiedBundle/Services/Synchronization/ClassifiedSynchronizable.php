<?php

namespace ArcaSolutions\ClassifiedBundle\Services\Synchronization;

use ArcaSolutions\ClassifiedBundle\Entity\Classified;
use ArcaSolutions\ClassifiedBundle\Entity\Classifiedcategory;
use ArcaSolutions\ClassifiedBundle\Search\ClassifiedConfiguration;
use ArcaSolutions\CoreBundle\Entity\Location1;
use ArcaSolutions\CoreBundle\Entity\Location2;
use ArcaSolutions\CoreBundle\Entity\Location3;
use ArcaSolutions\CoreBundle\Entity\Location4;
use ArcaSolutions\CoreBundle\Entity\Location5;
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

class ClassifiedSynchronizable extends BaseSynchronizable implements EventSubscriberInterface
{
    const PARTIAL_SETTING_KEY = 'classified_partial_sync';

    function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->configurationService = 'classified.search';
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
        HookFire("classifiedsynchronizable_before_return_subscribers", [
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

        $qB = $doctrine->getRepository('ClassifiedBundle:Classified')->createQueryBuilder('classified');

        if ($output) {
            $totalCount = $qB->select('COUNT(classified.id)')->where("classified.status = 'A'")->getQuery()->getSingleScalarResult();

            $progressBar = new ProgressBar($output, $totalCount);

            $progressBar->start();
        }

        $this->container->get('search.engine')->clearType(ClassifiedConfiguration::$elasticType);

        $iteration = 0;
        $lastId = 0;

        $query = $qB->select('classified.id');

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
        $qB = $doctrine->getRepository('ClassifiedBundle:Classified')->createQueryBuilder('classified');

        if ($output) {
            $totalCount = $qB->select('COUNT(classified.id)')->where("classified.status = 'A'")->getQuery()->getSingleScalarResult();

            $countQuery = new \Elastica\Query([
                'query' => [
                    'type' => [
                        'value' => 'classified'
                    ]
                ]
            ]);

            $currentCount = $this->container->get('search.engine')->getElasticaIndex()->count($countQuery);

            $progressBarCount = $totalCount - $currentCount < $partialTotalCount ? $totalCount - $currentCount : $partialTotalCount;

            $progressBar = new ProgressBar($output, $progressBarCount);

            $progressBar->start();
        }

        $query = $qB->select('classified.id')
            ->orderBy('classified.id', 'ASC');

        if ($lastId) {
            $query->andWhere('classified.id > :last_id')->setParameter('last_id', $lastId);
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
            $elements = $this->container->get('doctrine')->getRepository('ClassifiedBundle:Classified')->findBy(['id' => $ids]);

            while ($element = array_pop($elements)) {
                $result[] = $this->getUpsertDocument($element);
            }
        }

        return $result;
    }

    /**
     * @param Classified $classified
     * @return Document|null
     */
    public function getUpsertDocument($classified)
    {
        $document = null;

        if ($classified and is_object($classified)) {
            $document = new Document(
                $classified->getId(),
                $this->generateDocFromEntity($classified),
                $this->container->get($this->getConfigurationService())->getElasticType(),
                $this->container->get('search.engine')->getElasticIndexName()
            );

            $document->setDocAsUpsert(true);
        }

        return $document;
    }

    /**
     * @param Classified $element
     * @return string
     */
    public function generateDocFromEntity($element)
    {
        $doctrine = $this->container->get('doctrine');

        if ($categories = $element->getCategories()) {
            $categoryIds = [];

            /* @var $category Classifiedcategory */
            while ($category = array_pop($categories)) {
                $categoryIds[] = $this->container->get('classified.category.synchronization')
                    ->normalizeId($category->getId());
            }

            $categoryId = implode(' ', $categoryIds);
        } else {
            $categoryId = null;
        }

        $syncService = $this->container->get('classified.category.synchronization');
        $parentCategoryIds = [];
        for ($i = 1; $i <= 5; $i++) {
            $prop = sprintf('getCat%dId', $i);

            if (!$element->$prop()) {
                continue;
            }

            for ($j = 1; $j <= 4; $j++) {
                $prop = sprintf('getParcat%dLevel%dId', $i, $j);
                if ($element->$prop() > 0) {
                    $parentCategoryIds[] = $syncService->normalizeId($element->$prop());
                }
            }
        }

        if ($latitude = $element->getLatitude() and $longitude = $element->getLongitude()) {
            $geoLocation = [
                'lat' => $latitude,
                'lon' => $longitude,
            ];
        } else {
            $geoLocation = null;
        }

        $locationIds = [];
        $locationSynchronizable = $this->container->get('location.synchronization');

        /* @var $location1 Location1 */
        if ($location1 = $element->getLocation1()) {
            $locationIds[] = $locationSynchronizable->formatId($location1, 1);
        }

        /* @var $location2 Location2 */
        if ($location2 = $element->getLocation2()) {
            $locationIds[] = $locationSynchronizable->formatId($location2, 2);
        }

        /* @var $location3 Location3 */
        if ($location3 = $element->getLocation3()) {
            $locationIds[] = $locationSynchronizable->formatId($location3, 3);
        }

        /* @var $location4 Location4 */
        if ($location4 = $element->getLocation4()) {
            $locationIds[] = $locationSynchronizable->formatId($location4, 4);
        }

        /* @var $location5 Location5 */
        if ($location5 = $element->getLocation5()) {
            $locationIds[] = $locationSynchronizable->formatId($location5, 5);
        }

        $locationId = implode(' ', $locationIds);

        $suggest = [
            'input'   => $element->getFulltextsearchKeyword(),
            'output'  => $element->getTitle(),
            'payload' => [
                'friendlyUrl' => $element->getFriendlyUrl(),
                'type'        => 'classified',
                'id'          => $element->getId(),
            ],
            'weight'  => 100 - $element->getLevel(),
        ];

        /* @var $image Image */
        if ($element->getImageId() && $image = $doctrine->getRepository('ImageBundle:Image')->find($element->getImageId())) {
            $thumbnail = $this->container->get('imagehandler')->getPath($image);
        } else {
            $thumbnail = null;
        }

        $entered = $element->getEntered()->format('Y-m-d');
        $updated = $element->getUpdated()->format('Y-m-d');

        $document = [
            'address'          => [
                'complement' => $element->getAddress2(),
                'street'     => $element->getAddress(),
            ],
            'categoryId'       => $categoryId,
            'parentCategoryId' => implode(' ', array_unique($parentCategoryIds)) ?: null,
            'contactName'      => $element->getContactname(),
            'description'      => $element->getSummarydesc(),
            'email'            => $element->getEmail(),
            'friendlyUrl'      => $element->getFriendlyUrl(),
            'geoLocation'      => $geoLocation,
            'level'            => $element->getLevel(),
            'entered'          => $entered == Utility::BAD_DATE_VALUE ? null : $entered,
            'updated'          => $updated == Utility::BAD_DATE_VALUE ? null : $updated,
            'locationId'       => $locationId,
            'phone'            => $element->getPhone(),
            'price'            => $element->getClassifiedPrice(),
            'searchInfo'       => [
                'keyword'  => $element->getFulltextsearchKeyword(),
                'location' => $element->getFulltextsearchWhere(),
            ],
            'status'           => $element->getStatus() == 'A',
            'suggest'          => [
                'what'       => $suggest,
                'classified' => $suggest
            ],
            'thumbnail'        => $thumbnail,
            'title'            => $element->getTitle(),
            'url'              => $element->getUrl(),
            'views'            => $element->getNumberViews(),
        ];

        /* ModStores Hooks */
        HookFire("classifiedsynchronizable_before_return_document", [
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
        return [
            '_id'         => $info['_id'],
            'locationId'  => $info['locationId'],
            'categoryId'  => $info['categoryId'],
            'thumbnail'   => $info['thumbnail'],
            'title'       => $info['title'],
            'friendlyUrl' => $info['friendlyUrl'],
            'description' => $info['description'],
            'searchInfo'  => [
                'keyword'  => $info['searchInfo.keyword'],
                'location' => $info['searchInfo.location'],
            ],
            'geoLocation' => [
                'lat' => $info['geoLocation.lat'],
                'lon' => $info['geoLocation.lon'],
            ],
            'level'       => $info['level'],
            'entered'     => $info['entered'],
            'updated'     => $info['updated'],
            'status'      => $info['status'],
            'views'       => $info['views'],
            'price'       => $info['price'],
            'address'     => [
                'street'     => $info['address.street'],
                'complement' => $info['address.complement'],
            ],
            'url'         => $info['url'],
            'suggest'     => [
                'what' => [
                    'input'   => $info['suggest.what.input'],
                    'output'  => $info['suggest.what.output'],
                    'payload' => $info['suggest.what.payload'],
                    'weight'  => $info['suggest.what.weight'],
                ],
            ],
        ];

        /* ModStores Hooks */
        HookFire("classifiedsynchronizable_before_return_result", [
            "document" => &$document,
            "info"     => &$info
        ]);

        return $document;
    }

    public function addAverageReviewUpdate($id, $value)
    {
    }
}
