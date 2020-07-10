<?php

namespace ArcaSolutions\ImportBundle\Command;


use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\ImportBundle\Entity\ListingImport;
use ArcaSolutions\ImportBundle\Exception\InvalidModuleException;
use ArcaSolutions\ImportBundle\Services\ElasticRepository;
use ArcaSolutions\ImportBundle\Services\EventImportService;
use ArcaSolutions\ImportBundle\Services\ImportService;
use ArcaSolutions\ImportBundle\Services\ListingImportService;
use ArcaSolutions\MultiDomainBundle\Command\AbstractMultiDomainCommand;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Elastica\Exception\ResponseException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * Class ImportDataCommand
 *
 * @author Roberto Silva (roberto.silva@arcasolutions.com)
 * @package ArcaSolutions\ImportBundle\Command
 * @since 11.3.00
 */
class ImportDataCommand extends AbstractMultiDomainCommand
{

    const COMMAND_NAME = "edirectory:import";
    const COMMAND_DESCRIPTION = "Import data into database";
    const COMMAND_HELP = "This command allows you to import data into database.";

    const OPTION_DURATION = "duration";
    const OPTION_LINE = "line";
    const OPTION_MODULE = "module";

    /**
     * @var integer
     */
    private $limitDuration;

    /**
     * @var integer
     */
    private $limitLine;

    /**
     * @var string
     */
    private $module;

    /**
     * @var \DateTime
     */
    private $startTime;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(self::COMMAND_DESCRIPTION)
            ->setHelp(self::COMMAND_HELP)
            ->addOption(self::OPTION_MODULE,
                null,
                InputOption::VALUE_OPTIONAL,
                "Select the module to be imported first.")
            ->addOption(self::OPTION_DURATION,
                null,
                InputOption::VALUE_OPTIONAL,
                "Maximum number of minutes this command can take. [default=20]")
            ->addOption(self::OPTION_LINE,
                null,
                InputOption::VALUE_OPTIONAL,
                "Maximum number of lines this command can read. [default=5000]");

        parent::configure();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->startTime = new \DateTime();

        $this->limitDuration = $input->getOption(self::OPTION_DURATION) ?: 19;
        $this->limitLine = $input->getOption(self::OPTION_LINE) ?: 5000;

        parent::initialize($input, $output);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->kernel = $this->getContainer()->get('kernel');
        $this->module = $input->getOption(self::OPTION_MODULE);

        $import = $this->getImport();
        if ($import != null) {
            $this->processImport($import);
        }
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return ImportLog|null
     */
    private function getImport()
    {
        if ($domain = $this->multiDomain->getActiveHost()) {
            return $this->findImportByDomainUrl($domain);
        }

        $domains = $this->getMultiDomain()->getHostConfig();
        foreach ($domains as $domain => $domainInfo) {
            $import = $this->findImportByDomainUrl($domain);
            if ($import) {
                return $import;
            }
        }

        return null;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $domainUrl
     * @return mixed
     */
    private function findImportByDomainUrl($domainUrl)
    {
        // Sets domain as active
        $this->multiDomain->setActiveHost($domainUrl);

        /* @var $repository EntityRepository */
        $repository = $this->getDoctrine()->getRepository(ImportLog::class);

        $criteria = ["status" => [ImportLog::STATUS_RUNNING, ImportLog::STATUS_PENDING]];
        $this->module AND $criteria["module"] = $this->module;

        $imports = $repository->findBy(
            $criteria,
            ["status" => "desc", "updatedAt" => "asc"],
            1
        );

        return array_pop($imports);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return EntityManager|object
     */
    private function getDoctrine()
    {
        if (!$this->getContainer()->has('orm.'.$this->multiDomain->getActiveHost())) {
            $this->getContainer()->set('orm.'.$this->multiDomain->getActiveHost(),
                $this->getEntityManagerByDomain($this->multiDomain->getActiveHost()));
        }

        return $this->getContainer()->get('orm.'.$this->multiDomain->getActiveHost());
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ImportLog $import
     */
    private function processImport($import)
    {
        $this->printMem();

        /* @var $worker ImportService */
        $worker = $this->getContainer()->get("import.worker");

        /* @var $elastic ElasticRepository */
        $elastic = $worker->getElastic($import->getId());

        try {
            $total = $elastic->getDocCount();
        } catch (ResponseException $e) {
            $import->setStatus(ImportLog::STATUS_ERROR);
            $this->saveImport($import);
            printf("Error while executing import #".$import->getId()."\n");
            exit;
        }

        printf("Total docs ".$total." to be imported.\n");
        $this->printMem();

        try {
            $service = $this->getImportServiceByModule($import->getModule());
            $service->setImport($import);

            $import->setStatus(ImportLog::STATUS_RUNNING);
            $this->saveImport($import);

            $counter = 0;
            $rowCounter = 0;

            do {
                $documents = $elastic->fetch100Documents();
                $docsCount = count($documents);

                $counter += $docsCount;

                /* Verifies that import status has changed */
                $importStatus = $this->getDoctrine()->getRepository(ImportLog::class)
                    ->getImportLogStatus($import->getId());

                /* @var ListingImport $document */
                foreach ($documents as $document) {
                    /* Checks status change */
                    if ($importStatus !== ImportLog::STATUS_RUNNING) {
                        $docsCount = 0;
                        break;
                    }

                    /* Checks execution limits */
                    if ($rowCounter > $this->limitLine or $this->limitRunningTime()) {
                        printf("The line [".$this->limitLine."] or time [".$this->limitDuration."] limit has been 
                    exceeded. Importation will continue on the next run.");
                        return true;
                    }

                    $docId = $document->getId();
                    $service->persistModuleInDatabase($document);
                    if ($docId > 0) {
                        $elastic->deleteDocument($docId);
                    }

                    /* Verifies that import status has changed */
                    $importStatus = $this->getDoctrine()->getRepository(ImportLog::class)
                        ->getImportLogStatus($import->getId());

                    $rowCounter++;
                }

                unset($documents);

                $this->printCounter($counter, $total);

            } while ($docsCount == 100);

            $elastic->deleteIndex();

            if ($importStatus == ImportLog::STATUS_RUNNING) {
                $import->setStatus(ImportLog::STATUS_DONE);
                $this->saveImport($import);
            }
        } catch (InvalidModuleException $exception) {
            $this->getContainer()->get('monolog.logger_prototype')->error(
                sprintf('Module not implemented [%s].', $import->getModule()), ['Import Command']);
        }
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     */
    private function printMem()
    {
        if ($this->kernel->isDebug()) {
            printf("%.2f mb\n", $this->getMemoryUsageInMB());
        }
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return float
     */
    private function getMemoryUsageInMB()
    {
        return (memory_get_usage() / 1024 / 1024);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ImportLog $import
     */
    private function saveImport(ImportLog $import)
    {
        $em = $this->getDoctrine();
        $em->persist($import);
        $em->flush();
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
        switch ($module) {
            case ImportService::MODULE_LISTING:
                return $this->getContainer()->get("import.listing_import");
            case ImportService::MODULE_EVENT:
                return $this->getContainer()->get("import.event_import");
            default:
                throw new InvalidModuleException(sprintf('Module %s not supported', $module));
        }
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $counter
     * @param $total
     */
    private function printCounter($counter, $total)
    {
        if ($this->kernel->isDebug()) {
            $now = new \DateTime();
            $diff = $now->diff($this->startTime);
            printf("%d/%d | mem: %.2fMB | time: %s\n", $counter, $total, $this->getMemoryUsageInMB(),
                $diff->format("%I:%S"));
            unset($now, $diff);
        }
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @return bool
     */
    private function limitRunningTime()
    {
        $start = clone $this->startTime;
        $start->add(new \DateInterval('PT'.$this->limitDuration.'M'));
        $now = new \DateTime();

        $return = $now >= $start;
        unset($start, $now);

        return $return;
    }
}
