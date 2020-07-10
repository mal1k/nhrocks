<?php

namespace ArcaSolutions\ImportBundle\Services;

use ArcaSolutions\ClassifiedBundle\Entity\Classified;
use ArcaSolutions\ClassifiedBundle\Entity\ClassifiedLevel;
use ArcaSolutions\CoreBundle\Logic\FriendlyUrlLogic;
use ArcaSolutions\CoreBundle\Services\AccountHandler;
use ArcaSolutions\EventBundle\Entity\Event;
use ArcaSolutions\EventBundle\Entity\EventLevel;
use ArcaSolutions\ImportBundle\Entity\EventImport;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\ImportBundle\Entity\ListingImport;
use ArcaSolutions\ImportBundle\Exception\ImportNotFoundException;
use ArcaSolutions\ImportBundle\Logic\LocationLogic;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingLevel;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\WebBundle\Entity\Accountprofilecontact;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class ModuleImportService
 * @package ArcaSolutions\ImportBundle\Services
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @since 11.3.00
 */
abstract class ModuleImportService
{
    /* @var ContainerInterface */
    protected $container;

    /* @var DoctrineRegistry */
    protected $doctrine;

    /* @var EntityManager */
    protected $domainManager;

    /* @var Connection */
    protected $domainConnection;

    /* @var EntityManager */
    protected $mainManager;

    /* @var Connection */
    protected $mainConnection;

    /* @var ImportLog */
    protected $import;

    /* @var LocationLogic */
    protected $locationLogic;

    /* @var FriendlyUrlLogic */
    protected $friendlyUrlLogic;

    /**
     * @var AccountHandler
     */
    protected $accountHandler;

    /**
     * @var string
     */
    protected $dateFormat;
    /**
     * @var string
     */
    protected $timeFormat;

    /**
     * ModuleImportService constructor.
     * @param $container
     */
    function __construct($container)
    {
        $this->container = $container;
        $this->doctrine = $this->container->get("doctrine");

        $domain = $this->container->get('multi_domain.information')->getActiveHost();

        $this->domainManager = $this->container->get('orm.'.$domain);
        $this->domainConnection = $this->domainManager->getConnection();
        $this->domainConnection->getConfiguration()->setSQLLogger(null);
        $this->mainManager = $this->doctrine->getManager("main");
        $this->mainConnection = $this->mainManager->getConnection();
        $this->mainConnection->getConfiguration()->setSQLLogger(null);

        $this->locationLogic = new LocationLogic($this->container);
        $this->friendlyUrlLogic = new FriendlyUrlLogic($this->container);
        $this->accountHandler = $this->container->get('account.handler');

        $this->dateFormat = $this->container->get('settings')->getDomainSetting('date_format');
        $this->timeFormat = $this->container->get('settings')->getDomainSetting('clock_type');
    }

    /**
     * @param int $importId
     * @return $this
     * @throws ImportNotFoundException
     */
    public function setImportId($importId)
    {
        /* @var ImportLog $import */
        $import = $this->domainManager->getRepository(ImportLog::class)->find($importId);
        if ($import === null) {
            throw new ImportNotFoundException();
        }

        $this->setImport($import);

        return $this;
    }

    /**
     * @param ImportLog $import
     */
    public function setImport(ImportLog $import)
    {
        $this->import = $import;
        $this->locationLogic->setimport($this->import);
    }

    /**
     *
     * @param ListingImport|EventImport $moduleImport
     * @return bool True if success!
     */
    public function persistModuleInDatabase($moduleImport)
    {
        $retVal = false;
        $this->beginTransaction();
        try {
            $module = $this->buildModule($moduleImport);
            $this->domainManager->persist($module);
            $this->domainManager->flush($module);
            $this->commit();
            $retVal = true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->container->get('logger')->critical($e);
        }
        unset($moduleImport);

        return $retVal;
    }

    protected function beginTransaction()
    {
        if (!$this->mainConnection->isTransactionActive()) {
            $this->mainConnection->beginTransaction();
        }

        if (!$this->domainConnection->isTransactionActive()) {
            $this->domainConnection->beginTransaction();
        }
    }

    /**
     *
     * @param $moduleImport
     * @return Listing|Event|Classified
     */
    abstract protected function buildModule($moduleImport);

    protected function commit()
    {
        $this->mainConnection->commit();
        $this->domainConnection->commit();
    }

    protected function rollback()
    {
        $this->mainConnection->rollBack();
        $this->domainConnection->rollBack();
        $this->openEntityManagerIfNeed();
    }

    protected function openEntityManagerIfNeed()
    {
        if (!$this->mainManager->isOpen()) {
            $this->mainManager = $this->mainManager->create($this->mainConnection,
                $this->mainManager->getConfiguration());
        }
        if (!$this->domainManager->isOpen()) {
            $this->domainManager = $this->domainManager->create($this->domainConnection,
                $this->domainManager->getConfiguration());
        }
    }

    /**
     * @param string $level
     * @return mixed
     */
    protected function getLevel($level)
    {
        if (!$level && $this->import->getLevelForItemsNotSpecified()) {
            return $this->getDefaultLevelForItemsNotSpecified($this->import->getModule(),
                $this->import->getLevelForItemsNotSpecified());
        }

        $defaultLevel = null;
        $moduleLevels = $this->getModuleLevels();
        foreach ($moduleLevels as $moduleLevel) {
            if (strtolower($level) == strtolower($moduleLevel->getName())) {
                return $moduleLevel;
            }

            if (is_null($defaultLevel) && $moduleLevel->getDefaultlevel() == 'y') {
                $defaultLevel = $moduleLevel;
            }
        }

        return $defaultLevel;
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since v11.4.00
     *
     * @param string $module
     * @param integer $level
     * @return null|ListingLevel|EventLevel
     */
    private function getDefaultLevelForItemsNotSpecified($module, $level)
    {
        switch ($module) {
            case 'listing':
                $class = ListingLevel::class;
                break;
            case 'event':
                $class = EventLevel::class;
                break;
        }

        return $this->domainManager->getRepository($class)->find($level);
    }

    /**
     * Return all the levels of the module to be imported
     *
     * @return ListingLevel[]|EventLevel[]|ClassifiedLevel[]
     */
    abstract protected function getModuleLevels();

    /**
     * @param string $importModuleStatus
     * @return string
     */
    protected function getStatus($importModuleStatus, $itemStatus)
    {
        $validStatus = ["active", "pending", "suspended", "expired"];

        if ($importModuleStatus && !in_array(strtolower($importModuleStatus), $validStatus)) {
            $importModuleStatus = "P";
        }

        $returnValue = $importModuleStatus ?: ($itemStatus ?: "P");

        if ($this->import->isImportedItemsActive()) {
            $returnValue = "A";
        }

        return strtoupper($returnValue[0]);
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ListingImport|EventImport $importData
     * @return Accountprofilecontact|null|object
     */
    protected function getAccount($importData)
    {
        if (!$account = $this->accountHandler->findOrCreateAccount($this->import->getAccountIdForAllItems())) {

            if ($importData->getAccountUsername()) {

                if (!$account = $this->accountHandler->findOrCreateAccount($importData->getAccountUsername())) {
                    $accountPost = [
                        'password'  => $importData->getAccountPassword(),
                        'firstname' => $importData->getAccountFirstName(),
                        'lastname'  => $importData->getAccountLastName(),
                        'email'     => $importData->getAccountUsername(),
                        'company'   => $importData->getAccountCompany(),
                        'address'   => $importData->getAccountAddress(120),
                        'address2'  => $importData->getAccountAddress2(120),
                        'country'   => $importData->getAccountCountry(),
                        'state'     => $importData->getAccountState(),
                        'city'      => $importData->getAccountCity(),
                        'zipcode'   => $importData->getAccountZipCode(),
                        'phone'     => $importData->getAccountPhone(),
                        'cemail'    => $importData->getAccountEmail(),
                        'url'       => $importData->getAccountUrl(),
                        'sponsor'   => 'y',
                    ];

                    $accounts = $this->accountHandler->saveAccount($accountPost);
                    list($account) = $accounts;
                    $this->accountHandler->setImportListing($account, $this->import);
                }
            }
        }

        if ($account) {
            return $this->domainManager->getRepository('WebBundle:Accountprofilecontact')->find($account->getId());
        }

        return null;
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string $itemTitle
     * @param integer $itemDdId
     * @param string $class
     * @return null|string
     */
    protected function getFriendlyUrl($itemTitle, $itemDdId, $class)
    {
        $returnValue = null;

        if (!$this->import->isUpdateExistingData() or !$itemDdId or ($this->import->isUpdateExistingData() && $this->import->isUpdateFriendlyUrl())) {
            $returnValue = $this->friendlyUrlLogic->buildUniqueModuleFriendlyUrl($itemTitle, $class);
        }

        return $returnValue;
    }

    /**
     * @param $moduleId
     * @param string $class
     * @return null|object
     */
    protected function findOneModuleById($moduleId, $class)
    {
        $retVal = null;
        if ($this->import->isUpdateExistingData() && $moduleId) {
            $retVal = $this->domainManager->getRepository($class)->find($moduleId);
        }
        unset($moduleId);

        return $retVal;
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ListingImport $moduleImport
     * @param array $moduleCategories
     * @return array
     */
    abstract protected function getFullTextSearchKeyword($moduleImport, array $moduleCategories);

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ListingImport $moduleImport
     * @param Listing $module
     * @return array
     */
    abstract protected function getFullTextSearchWhere($moduleImport, $module);
}
