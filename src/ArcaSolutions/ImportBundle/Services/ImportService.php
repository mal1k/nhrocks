<?php

namespace ArcaSolutions\ImportBundle\Services;


use ArcaSolutions\ImportBundle\Entity\EventImport;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\ImportBundle\Entity\ListingImport;
use ArcaSolutions\ImportBundle\Exception\ImportNotFoundException;
use ArcaSolutions\ImportBundle\Exception\InvalidContentTypeException;
use ArcaSolutions\ImportBundle\Exception\InvalidModuleException;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\MultiDomainBundle\Services\Settings;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImportService
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Services
 * @since 11.3.00
 */
class ImportService
{
    const CONTENT_TYPE_CSV = "text/csv";
    const CONTENT_TYPE_XLSX = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";

    const TYPE_CSV = 'csv';
    const TYPE_XLS = 'xls';
    const TYPE_XLSX = 'xlsx';

    const MODULE_LISTING = "listing";
    const MODULE_EVENT = "event";

    const CONTENT_TYPES = [self::CONTENT_TYPE_CSV, self::CONTENT_TYPE_XLSX];
    const MODULES = [self::MODULE_LISTING, self::MODULE_EVENT];


    /**
     * @var DoctrineRegistry
     */
    private $doctrine;

    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * @var Extractor
     */
    private $extractor;

    /**
     * @var ElasticRepository
     */
    private $elasticRepository;

    /**
     * @var string;
     */
    private $filePath;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ImportService constructor.
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param RegistryInterface $doctrine
     * @param Extractor $extractor
     * @param ElasticRepository $elasticRepository
     * @param Settings $settings
     */
    public function __construct(
        RegistryInterface $doctrine,
        Extractor $extractor,
        ElasticRepository $elasticRepository,
        Settings $settings,
        ContainerInterface $container
    ) {
        $this->doctrine = $doctrine;
        $this->entityManager = $this->doctrine->getManager();
        $this->repository = $this->doctrine->getRepository(ImportLog::class);

        $this->extractor = $extractor;
        $this->elasticRepository = $elasticRepository;
        $this->settings = $settings;
        $this->container = $container;

        if (!$this->container->has('orm.'.$this->container->get('multi_domain.information')->getActiveHost())) {
            $this->container->set('orm.'.$this->container->get('multi_domain.information')->getActiveHost(),
                $this->entityManager);
        }
    }

    /**
     * @param Settings $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * Creates a new log record and returns its ID
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param \SplFileObject $file
     * @param string $contentType
     * @param string $module
     * @param string $status
     * @param int $errorLines
     * @param int $totalLines
     * @param bool $hasHeader
     * @param string $separator
     * @return int
     * @throws InvalidContentTypeException
     * @throws InvalidModuleException
     */
    public function create(
        \SplFileObject $file,
        $contentType,
        $module,
        $status,
        $errorLines,
        $totalLines,
        $hasHeader = false,
        $separator = ';'
    ) {
        if (!in_array($contentType, self::CONTENT_TYPES)) {
            throw new InvalidContentTypeException();
        }

        $module = strtolower($module);
        if (!in_array($module, self::MODULES)) {
            throw new InvalidModuleException();
        }

        $import = new ImportLog();
        $import->setFilename($file->getFilename());
        $import->setContentType($contentType);
        $import->setModule($module);
        $import->setHasHeader($hasHeader);
        $import->setDelimiter($separator);
        $import->setStatus($status);
        $import->setErrorLines($errorLines);
        $import->setTotalLines($totalLines);
        $this->entityManager->persist($import);
        $this->entityManager->flush();

        return $import->getId();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param null $id
     * @return array
     */
    public function getPropertiesAlias($id = null)
    {
        $extractor = $this->getExtractorByImportId($id);

        return $extractor->getClassPropertiesAlias();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param int $importId
     * @return Extractor
     */
    private function getExtractorByImportId($importId)
    {
        $import = $this->findImport($importId);

        return $this->getExtractorByImport($import);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param int $id
     * @return ImportLog
     * @throws ImportNotFoundException
     */
    private function findImport($id)
    {
        /* @var $import ImportLog */
        $import = $this->repository->find($id);

        if ($import === null) {
            throw new ImportNotFoundException();
        }

        return $import;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ImportLog $import
     * @return Extractor
     * @throws InvalidModuleException
     * @throws InvalidContentTypeException
     */
    private function getExtractorByImport($import)
    {
        $file = new \SplFileObject($this->filePath.$import->getFilename());

        switch ($file->getExtension()) {
            case self::TYPE_CSV:
                $this->extractor->fromCsvFile($file, $import->hasHeader(), $import->getDelimiter())
                    ->setClassType($this->getClassTypeByString($import->getModule()));
                break;
            case self::TYPE_XLS:
            case self::TYPE_XLSX:
                $this->extractor->fromXlsFile($file, $import->hasHeader())
                    ->setClassType($this->getClassTypeByString($import->getModule()));
                break;
            default:
                throw new InvalidContentTypeException(sprintf('Content type %s not supported', $file->getExtension()));
        }

        return $this->extractor;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $module
     * @return string
     * @throws InvalidModuleException
     */
    public function getClassTypeByString($module)
    {
        switch ($module) {
            case self::MODULE_LISTING:
                return ListingImport::class;
            case self::MODULE_EVENT:
                return EventImport::class;
            default:
                throw new InvalidModuleException(sprintf('Module %s not supported', $module));
        }
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param null $id
     * @return null
     */
    public function getColumnHeaders($id = null)
    {
        $import = $this->findImport($id);

        if ($import->hasHeader()) {
            return $this->getExtractorByImport($import)
                ->getColumnHeaders();
        }

        return null;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $id
     * @param array $mapping
     * @return array
     */
    public function parseFileWithMapping($id, $mapping)
    {
        $extractor = $this->getExtractorByImportId($id);
        $extractor->setMapping($mapping);

        $items = $extractor->getExtractItems();
        $errors = $extractor->getExtractErrors();

        $elastic = $this->getElastic($id, $extractor->getClassType());
        $elastic->persistDataDoc($items);
        $elastic->persistErrorDoc($errors);

        return ["errors" => $errors];
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $id
     * @param null $classType
     * @return ElasticRepository
     */
    public function getElastic($id, $classType = null)
    {
        if ($classType == null) {
            $import = $this->findImport($id);
            $classType = $this->getClassTypeByString($import->getModule());
        }

        $this->elasticRepository->setIndexName($this->getIndexName($id))
            ->setClassType($classType);

        return $this->elasticRepository;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param int $id
     * @return string
     */
    private function getIndexName($id)
    {
        return $this->settings->getElastic()."_import_$id";
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param int $id
     * @param string $levelForItemsNotSpecified
     * @param bool $importedItemsActive
     * @param bool $newCategoriesAsFeatured
     * @param bool $updateExistingData
     * @param bool $updateFriendlyUrl
     * @param int $accountIdForAllItems
     * @return bool
     */
    public function setImport(
        $id,
        $levelForItemsNotSpecified,
        $importedItemsActive,
        $newCategoriesAsFeatured,
        $updateExistingData,
        $updateFriendlyUrl,
        $accountIdForAllItems = null
    ) {
        try {
            $import = $this->findImport($id);
            $import->setLevelForItemsNotSpecified($levelForItemsNotSpecified);
            $import->setImportedItemsActive($importedItemsActive);
            $import->setNewCategoriesFeatured($newCategoriesAsFeatured);
            $import->setUpdateExistingData($updateExistingData);
            $import->setUpdateFriendlyUrl($updateFriendlyUrl);
            $import->setAccountIdForAllItems($accountIdForAllItems);

            $this->entityManager->persist($import);
            $this->entityManager->flush();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string $path
     * @return $this
     */
    public function setFilePath($path)
    {
        $this->filePath = $path;

        return $this;
    }

    /**
     * Updates import status
     *
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param int $id The import id
     * @param string $status The import status
     * @return bool
     */
    public function setImportStatus($id, $status)
    {
        $return = true;

        try {
            $import = $this->findImport($id);
            $importStatus = $import->getStatus();
            $import->setStatus($status);
            $this->entityManager->flush();

            if ($importStatus !== ImportLog::STATUS_RUNNING) {
                $this->getElastic($import->getId())->deleteIndex();
            }
        } catch (ImportNotFoundException $e) {
            $return = false;
        }

        return $return;
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $module
     * @return string
     * @throws InvalidModuleException
     */
    public static function getImportSettingFlagName($module)
    {
        switch ($module) {
            case self::MODULE_LISTING:
                return 'last_datetime_import';
            case self::MODULE_EVENT:
                return 'last_datetime_import_events';
            default:
                throw new InvalidModuleException(sprintf('Module %s not supported', $module));
        }
    }

    public function doImport(ImportLog $importLog)
    {
        $service = $this->getImportServiceByModule($importLog->getModule());
        $service->setImport($importLog);

        $importLog->setStatus(ImportLog::STATUS_RUNNING);
        $this->saveImport($importLog);

        $counter = 0;
        $rowCounter = 0;

        do {
            $documents = $this->getElastic($importLog->getId())->fetch100Documents();
            $docsCount = count($documents);

            $counter += $docsCount;

            /* Verifies that import status has changed */
            $importStatus = $this->entityManager->getRepository(ImportLog::class)
                ->getImportLogStatus($importLog->getId());

            /* @var ListingImport $document */
            foreach ($documents as $document) {
                /* Checks status change */
                if ($importStatus !== ImportLog::STATUS_RUNNING) {
                    $docsCount = 0;
                    break;
                }

                $docId = $document->getId();
                $service->persistModuleInDatabase($document);
                if ($docId > 0) {
                    $this->getElastic($importLog->getId())->deleteDocument($docId);
                }

                /* Verifies that import status has changed */
                $importStatus = $this->entityManager->getRepository(ImportLog::class)
                    ->getImportLogStatus($importLog->getId());

                $rowCounter++;
            }

            unset($documents);

        } while ($docsCount == 100);

        $this->getElastic($importLog->getId())->deleteIndex();

        if ($importStatus == ImportLog::STATUS_RUNNING) {
            $importLog->setStatus(ImportLog::STATUS_DONE);
            $this->saveImport($importLog);
        }
    }

    public function doSync(ImportLog $importLog)
    {
        $importLog->setStatus(ImportLog::STATUS_SYNC);

        $this->saveImport($importLog);

        $syncService = $this->container->get('elasticsearch.synchronization');
        $syncService->setImport($importLog);

        $synchronizable = $importLog->getModule() === 'listing' ? 'listing.synchronization' : 'event.synchronization';

        $this->container->get($synchronizable)->generateAll();
        $this->container->get('elasticsearch.synchronization')->synchronize();

        $importLog->setStatus(ImportLog::STATUS_COMPLETED);

        $this->mergeImport($importLog);
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $module
     * @return EventImportService|ListingImportService
     * @throws InvalidModuleException
     */
    private function getImportServiceByModule($module)
    {
        if (!$this->container->has('orm.'.$this->container->get('multi_domain.information')->getActiveHost())) {
            $this->container->set('orm.'.$this->container->get('multi_domain.information')->getActiveHost(),
            $this->entityManager);
        }

        switch ($module) {
            case ImportService::MODULE_LISTING:
                return $this->container->get("import.listing_import");
            case ImportService::MODULE_EVENT:
                return $this->container->get("import.event_import");
            default:
                throw new InvalidModuleException(sprintf('Module %s not supported', $module));
        }
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ImportLog $import
     */
    private function saveImport(ImportLog $import)
    {
        $em = $this->entityManager;
        $em->persist($import);
        $em->flush();
    }

    /**
     * @author João P. Schias <joao.schias@arcasolutions.com>
     * @since 11.5.00
     *
     * @param ImportLog $import
     */
    private function mergeImport(ImportLog $import)
    {
        $em = $this->entityManager;
        $em->merge($import);
        $em->flush();
    }

}
