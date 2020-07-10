<?php

namespace ArcaSolutions\ListingBundle\Services\Synchronization;

use ArcaSolutions\CoreBundle\Entity\Location1;
use ArcaSolutions\CoreBundle\Entity\Location2;
use ArcaSolutions\CoreBundle\Entity\Location3;
use ArcaSolutions\CoreBundle\Entity\Location4;
use ArcaSolutions\CoreBundle\Entity\Location5;
use ArcaSolutions\CoreBundle\Services\Utility;
use ArcaSolutions\ElasticsearchBundle\Services\Synchronization\Modules\BaseSynchronizable;
use ArcaSolutions\ElasticsearchBundle\Services\Synchronization\Synchronization;
use ArcaSolutions\ImageBundle\Entity\Image;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\ImportBundle\Services\ImportService;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingChoice;
use ArcaSolutions\ListingBundle\Search\ListingConfiguration;
use Doctrine\ORM\QueryBuilder;
use Elastica\Document;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ListingSynchronizable extends BaseSynchronizable implements EventSubscriberInterface
{
    const PARTIAL_SETTING_KEY = 'listing_partial_sync';

    function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->configurationService = 'listing.search';
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
        HookFire("listingsynchronizable_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    public function handleEvent($event, $eventName)
    {
        $this->generateAll();
    }

    /**
     * @param null $output
     * @param int $pageSize
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function generateAll($output = null, $pageSize = Synchronization::BULK_THRESHOLD)
    {
        $progressBar = null;
        $doctrine = $this->container->get('doctrine');
        $settings = $this->container->get('settings');

        /* @var $qB QueryBuilder */
        $qB = $doctrine->getRepository('ListingBundle:Listing')->createQueryBuilder('listing');

        if ($output) {
            $totalCount = $qB->select('COUNT(listing.id)')->where("listing.status = 'A'")->getQuery()->getSingleScalarResult();

            $progressBar = new ProgressBar($output, $totalCount);

            $progressBar->start();
        }

        $this->container->get('search.engine')->clearType(ListingConfiguration::$elasticType);

        $iteration = 0;
        $lastId = 0;

        $query = $qB->select('listing.id')
            ->where('listing.status = :listingStatus')
            ->setParameter('listingStatus', 'A');

        /* @var ImportLog $import */
        if (($import = $this->container->get('elasticsearch.synchronization')->getImport()) && ($import->getModule() == ImportService::MODULE_LISTING)) {
            $query->andWhere('listing.import = :listingImport')
                ->setParameter('listingImport', $import);
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
        $qB = $doctrine->getRepository('ListingBundle:Listing')->createQueryBuilder('listing');

        if ($output) {
            $totalCount = $qB->select('COUNT(listing.id)')->where("listing.status = 'A'")->getQuery()->getSingleScalarResult();

            $countQuery = new \Elastica\Query([
                'query' => [
                    'type' => [
                        'value' => 'listing'
                    ]
                ]
            ]);

            $currentCount = $this->container->get('search.engine')->getElasticaIndex()->count($countQuery);

            $progressBarCount = $totalCount - $currentCount < $partialTotalCount ? $totalCount - $currentCount : $partialTotalCount;

            $progressBar = new ProgressBar($output, $progressBarCount);

            $progressBar->start();
        }

        $query = $qB->select('listing.id')
            ->where('listing.status = :listingStatus')
            ->setParameter('listingStatus', 'A')
            ->orderBy('listing.id', 'ASC');

        if ($lastId) {
            $query->andWhere('listing.id > :last_id')->setParameter('last_id', $lastId);
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
            $doctrine = $this->container->get('doctrine');
            $elements = $doctrine->getRepository('ListingBundle:Listing')->findBy(['id' => $ids]);

            while ($element = array_pop($elements)) {
                $result[] = $this->getUpsertDocument($element);
            }

            $doctrine->getManager()->clear();

            $elements = null;
        }

        return $result;
    }

    /**
     * @param Listing $listing
     * @return Document|null
     */
    public function getUpsertDocument($listing)
    {
        $document = null;

        if ($listing and is_object($listing)) {
            $document = new Document(
                $listing->getId(),
                $this->generateDocFromEntity($listing),
                $this->container->get($this->getConfigurationService())->getElasticType(),
                $this->container->get('search.engine')->getElasticIndexName()
            );

            $document->setDocAsUpsert(true);
        }

        return $document;
    }

    /**
     * @param Listing $element
     * @return array
     */
    public function generateDocFromEntity($element)
    {
        $badges = array_map(function ($item) {
            /* @var $item ListingChoice */
            return $item->getEditorChoiceId();
        }, $element->getChoices()->toArray());

        if (count($badges) > 0) {
            $badgeIds = [];

            /* @var $badge ListingChoice */
            while ($badge = array_pop($badges)) {
                $badgeIds[] = $badge;
            }

            $badgeId = implode(' ', $badgeIds);
        } else {
            $badgeId = null;
        }

        $categoryIds = [];
        $parentCategoryIds = [];
        $syncService = $this->container->get('listing.category.synchronization');

        foreach ($element->getCategories() as $category) {
            $categoryIds[] = $syncService->normalizeId($category->getId());

            $parents = $category->getParentIds($category);

            for ($i = 0, $iMax = count($parents); $i < $iMax; $i++) {
                $parents[$i] = $syncService->normalizeId($parents[$i]);
            }

            $parentCategoryIds = array_merge($parentCategoryIds, $parents);
        }

        $claim = $element->getClaimDisable() != 'y' && $element->getAccountId() == 0;

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

        $doctrine = $this->container->get('doctrine');

        if ($reviewCount = $doctrine->getRepository('WebBundle:Review')->getTotalByItemId($element->getId(), 'listing')
        ) {
            is_array($reviewCount) and $reviewCount = array_pop($reviewCount);
        }

        $suggest = [
            'input'   => $element->getFulltextsearchKeyword(),
            'output'  => $element->getTitle(),
            'payload' => [
                'friendlyUrl' => $element->getFriendlyUrl(),
                'type'        => 'listing',
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
                'zipcode'    => $element->getZipcode(),
            ],
            'averageReview'    => $element->getAvgReview(),
            'badgeId'          => $badgeId,
            'categoryId'       => implode(' ', $categoryIds) ?: null,
            'parentCategoryId' => implode(' ', $parentCategoryIds) ?: null,
            'claim'            => $claim,
            'description'      => $element->getDescription(),
            'email'            => $element->getEmail(),
            'friendlyUrl'      => $element->getFriendlyUrl(),
            'geoLocation'      => $geoLocation,
            'level'            => $element->getLevel(),
            'entered'          => $entered == Utility::BAD_DATE_VALUE ? null : $entered,
            'updated'          => $updated == Utility::BAD_DATE_VALUE ? null : $updated,
            'locationId'       => $locationId,
            'phone'            => $element->getPhone(),
            'labelAdditionalPhone' => $element->getLabelAdditionalPhone(),
            'additionalPhone'  => $element->getAdditionalPhone(),
            'reviewCount'      => $reviewCount,
            'searchInfo'       => [
                'keyword'  => $element->getFulltextsearchKeyword(),
                'location' => $element->getFulltextsearchWhere(),
            ],
            'status'           => $element->getStatus() == 'A',
            'suggest'          => [
                'what'    => $suggest,
                'listing' => $suggest
            ],
            'thumbnail'        => $thumbnail,
            'title'            => $element->getTitle(),
            'url'              => $element->getUrl(),
            'views'            => $element->getNumberViews(),
            'hoursWork'        => $element->getHoursWork(),
        ];

        /* ModStores Hooks */
        HookFire("listingsynchronizable_before_return_document", [
            "document" => &$document,
            "element"  => &$element,
        ]);

        return $document;
    }

    public function extractFromResult($info)
    {
        $document = [
            'averageReview'    => $info['averageReview'],
            'address'          => [
                'complement' => $info['address.complement'],
                'street'     => $info['address.street'],
                'zipcode'    => $info['address.zipcode'],
            ],
            'badgeId'          => $info['badgeId'],
            'categoryId'       => $info['categoryId'],
            'parentCategoryId' => $info['parentCategoryId'],
            '_id'              => $info['_id'],
            'claim'            => $info['claim'],
            'description'      => $info['description'],
            'email'            => $info['email'],
            'friendlyUrl'      => $info['friendlyUrl'],
            'geoLocation'      => [
                'lat' => $info['geoLocation.lat'],
                'lon' => $info['geoLocation.lon'],
            ],
            'level'            => $info['level'],
            'entered'          => $info['entered'],
            'updated'          => $info['updated'],
            'locationId'       => $info['locationId'],
            'phone'            => $info['phone'],
            'labelAdditionalPhone' => $info['labelAdditionalPhone'],
            'additionalPhone'  => $info['additionalPhone'],
            'price'            => $info['price'],
            'reviewCount'      => $info['reviewCount'],
            'searchInfo'       => [
                'keyword'  => $info['searchInfo.keyword'],
                'location' => $info['searchInfo.location'],
            ],
            'status'           => $info['status'],
            'thumbnail'        => $info['thumbnail'],
            'title'            => $info['title'],
            'url'              => $info['url'],
            'views'            => $info['views'],
            'hoursWork'            => $info['hoursWork'],
            'suggest'          => [
                'what' => [
                    'input'   => $info['suggest.what.input'],
                    'output'  => $info['suggest.what.output'],
                    'payload' => $info['suggest.what.payload'],
                    'weight'  => $info['suggest.what.weight'],
                ],
            ],
        ];

        /* ModStores Hooks */
        HookFire("listingsynchronizable_before_return_result", [
            "document" => &$document,
            "info"     => &$info
        ]);

        return $document;
    }
}
