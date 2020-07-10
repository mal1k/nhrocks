<?php

namespace ArcaSolutions\EventBundle\Services\Synchronization;

use ArcaSolutions\CoreBundle\Entity\Location1;
use ArcaSolutions\CoreBundle\Entity\Location2;
use ArcaSolutions\CoreBundle\Entity\Location3;
use ArcaSolutions\CoreBundle\Entity\Location4;
use ArcaSolutions\CoreBundle\Entity\Location5;
use ArcaSolutions\CoreBundle\Services\Utility;
use ArcaSolutions\ElasticsearchBundle\Services\Synchronization\Modules\BaseSynchronizable;
use ArcaSolutions\ElasticsearchBundle\Services\Synchronization\Synchronization;
use ArcaSolutions\EventBundle\Entity\Event;
use ArcaSolutions\EventBundle\Entity\EventCategory;
use ArcaSolutions\EventBundle\Search\EventConfiguration;
use ArcaSolutions\EventBundle\Services\Recurring;
use ArcaSolutions\EventBundle\Twig\Extension\RecurringExtension;
use ArcaSolutions\ImageBundle\Entity\Image;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\ImportBundle\Services\ImportService;
use Doctrine\ORM\QueryBuilder;
use Elastica\Document;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSynchronizable extends BaseSynchronizable implements EventSubscriberInterface
{
    const PARTIAL_SETTING_KEY = 'event_partial_sync';

    /** @var Recurring */
    private $recurring;

    function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->configurationService = 'event.search';
        $this->databaseType = Synchronization::DATABASE_DOMAIN;
        $this->upsertFormat = static::DOCUMENT_UPSERT;
        $this->deleteFormat = static::DELETE_ID_RAW;
        $this->recurring = $this->container->get('event.recurring.service');
    }

    public static function getSubscribedEvents()
    {
        $events = [
            'edirectory.synchronization' => 'handleEvent',
        ];

        /* ModStores Hooks */
        HookFire("eventsynchronizable_before_return_subscribers", [
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
        $settings = $this->container->get('settings');

        $progressBar = null;
        $qB = $this->container->get('doctrine')->getRepository('EventBundle:Event')->createQueryBuilder('event');

        if ($output) {
            $totalCount = $qB->select('COUNT(event.id)')->where("event.status = 'A'")->getQuery()->getSingleScalarResult();

            $progressBar = new ProgressBar($output, $totalCount);

            $progressBar->start();
        }

        $this->container->get('search.engine')->clearType(EventConfiguration::$elasticType);

        $iteration = 0;
        $lastId = 0;

        $query = $qB->select('event.id')
            ->where('event.status = :eventStatus')
            ->setParameter('eventStatus', 'A');

        /* @var ImportLog $import */
        if (($import = $this->container->get('elasticsearch.synchronization')->getImport()) && ($import->getModule() == ImportService::MODULE_EVENT)) {
            $query->andWhere('event.import = :eventImport')
                ->setParameter('eventImport', $import);
        }

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
        $qB = $doctrine->getRepository('EventBundle:Event')->createQueryBuilder('event');

        if ($output) {
            $totalCount = $qB->select('COUNT(event.id)')->where("event.status = 'A'")->getQuery()->getSingleScalarResult();

            $countQuery = new \Elastica\Query([
                'query' => [
                    'type' => [
                        'value' => 'event'
                    ]
                ]
            ]);

            $currentCount = $this->container->get('search.engine')->getElasticaIndex()->count($countQuery);

            $progressBarCount = $totalCount - $currentCount < $partialTotalCount ? $totalCount - $currentCount : $partialTotalCount;

            $progressBar = new ProgressBar($output, $progressBarCount);

            $progressBar->start();
        }

        $query = $qB->select('event.id')
            ->where('event.status = :status')
            ->setParameter('status', 'A')
            ->orderBy('event.id', 'ASC');

        if ($lastId) {
            $query->andWhere('event.id > :last_id')->setParameter('last_id', $lastId);
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
            $elements = $this->container->get('doctrine')->getRepository('EventBundle:Event')->findBy(['id' => $ids]);

            while ($element = array_pop($elements)) {
                $result[] = $this->getUpsertDocument($element);
            }
        }

        return $result;
    }

    /**
     * @param Event $event
     * @return Document|null
     */
    public function getUpsertDocument($event)
    {
        $document = null;

        if ($event and is_object($event)) {
            $document = new Document(
                $event->getId(),
                $this->generateDocFromEntity($event),
                $this->container->get($this->getConfigurationService())->getElasticType(),
                $this->container->get('search.engine')->getElasticIndexName()
            );

            $document->setDocAsUpsert(true);
        }

        return $document;
    }

    /**
     * @param Event $element
     * @return string
     */
    public function generateDocFromEntity($element)
    {
        if ($categories = $element->getCategories()) {
            $categoryIds = [];

            /* @var $category EventCategory */
            while ($category = array_pop($categories)) {
                $categoryIds[] = $this->container->get('event.category.synchronization')
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
                    $parentCategoryIds[] = $this->container->get('event.category.synchronization')
                        ->normalizeId($element->$prop());
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

        $startDate = $element->getStartDate()->format('Y-m-d');

        if ($element->getRecurring() === 'Y') {
            $recurrentDate = [
                'start_date' => $startDate,
                'rrule'      => $this->recurring->getRRule_rfc2445($element),
            ];
        } else {
            $recurrentDate = [
                'start_date' => $startDate,
                'rrule'      => null,
            ];
            if (null != $element->getEndDate()) {
                $recurrentDate['end_date'] = $element->getEndDate()->format('Y-m-d');
            }
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
                'type'        => 'event',
                'id'          => $element->getId(),
            ],
            'weight'  => 100 - $element->getLevel(),
        ];

        /* @var $image Image */
        if ($element->getImageId() && $image = $this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($element->getImageId())) {
            $thumbnail = $this->container->get('imagehandler')->getPath($image);
        } else {
            $thumbnail = null;
        }

        if ($endDate = $element->getEndDate()) {
            $endDate = $endDate->format('Y-m-d');
        }

        if ($element->getUntilDate()) {
            $recurringUntil = $element->getUntilDate()->format('Y-m-d');
        } else {
            $recurringUntil = null;
        }

        $entered = $element->getEntered()->format('Y-m-d');
        $updated = $element->getUpdated()->format('Y-m-d');

        $document = [
            'address'          => [
                'location' => $element->getLocation(),
                'street'   => $element->getAddress(),
                'zipcode'  => $element->getZipCode(),
            ],
            'categoryId'       => $categoryId,
            'parentCategoryId' => implode(' ', array_unique($parentCategoryIds)) ?: null,
            'date'             => [
                'start' => $startDate == Utility::BAD_DATE_VALUE ? null : $startDate,
                'end'   => $endDate == Utility::BAD_DATE_VALUE ? null : $endDate,
            ],
            'time'             => [
                'start' => $element->getStartTime() ? $element->getStartTime()->format('H:i:s') : null,
                'end'   => $element->getEndTime() ? $element->getEndTime()->format('H:i:s') : null,
            ],
            'description'      => $element->getDescription(),
            'email'            => $element->getEmail(),
            'friendlyUrl'      => $element->getFriendlyUrl(),
            'geoLocation'      => $geoLocation,
            'level'            => $element->getLevel(),
            'entered'          => $entered == Utility::BAD_DATE_VALUE ? null : $entered,
            'updated'          => $updated == Utility::BAD_DATE_VALUE ? null : $updated,
            'locationId'       => $locationId,
            'phone'            => $element->getPhone(),
            'recurring'        => [
                'enabled' => $element->getRecurring() === 'Y',
                'until'   => $recurringUntil == Utility::BAD_DATE_VALUE ? null : $recurringUntil,
            ],
            'searchInfo'       => [
                'keyword'  => $element->getFulltextsearchKeyword(),
                'location' => $element->getFulltextsearchWhere(),
            ],
            'status'           => $element->getStatus() === 'A',
            'suggest'          => [
                'what'  => $suggest,
                'event' => $suggest
            ],
            'thumbnail'        => $thumbnail,
            'title'            => $element->getTitle(),
            'url'              => $element->getUrl(),
            'views'            => $element->getNumberViews(),
        ];

        if ($recurrentDate != null) {
            $document['recurrent_date'] = $recurrentDate;
        }

        /* ModStores Hooks */
        HookFire("eventsynchronizable_before_return_document", [
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
            'address'     => [
                'street'   => $info['address.street'],
                'zipcode'  => $info['address.zipcode'],
                'location' => $info['address.location'],
            ],
            'categoryId'  => $info['categoryId'],
            'date'        => [
                'end'   => $info['date.end'],
                'start' => $info['date.start'],
            ],
            'description' => $info['description'],
            'email'       => $info['email'],
            'friendlyUrl' => $info['friendlyUrl'],
            'geoLocation' => [
                'lat' => $info['geoLocation.lat'],
                'lon' => $info['geoLocation.lon'],
            ],
            '_id'         => $info['_id'],
            'level'       => $info['level'],
            'entered'     => $info['entered'],
            'updated'     => $info['updated'],
            'locationId'  => $info['locationId'],
            'phone'       => $info['phone'],
            'recurring'   => [
                'until'   => $info['recurring.until'],
                'enabled' => $info['recurring.enabled'],
            ],
            'searchInfo'  => [
                'keyword'  => $info['searchInfo.keyword'],
                'location' => $info['searchInfo.location'],
            ],
            'status'      => $info['status'],
            'thumbnail'   => $info['thumbnail'],
            'title'       => $info['title'],
            'url'         => $info['url'],
            'views'       => $info['views'],
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
        HookFire("eventsynchronizable_before_return_result", [
            "document" => &$document,
            "info"     => &$info
        ]);

        return $document;
    }
}
