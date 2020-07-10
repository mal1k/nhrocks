<?php

namespace ArcaSolutions\ImportBundle\Controller;

use ArcaSolutions\AdminBundle\Controller\AbstractAdminController;
use ArcaSolutions\CoreBundle\Inflector;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ImportController
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Controller
 * @since 11.3.00
 */
class ImportController extends AbstractAdminController
{
    const SUPPORT_LINK = 'http://support.edirectory.com/customer/portal/articles/2911234-codification-of-import-file';

    const ABORTED_STATUSES = [
        ImportLog::STATUS_PENDING,
        ImportLog::STATUS_RUNNING,
        ImportLog::STATUS_DONE,
        ImportLog::STATUS_SYNC,
    ];

    const UNDONE_STATUSES = [
        ImportLog::STATUS_COMPLETED,
    ];

    /**
     * Import Homepage
     *
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param int $page
     * @return string
     */
    public function indexAction()
    {
        /* Loading Import Repository */
        $importLog = $this->container->get('doctrine')
            ->getRepository('ImportBundle:ImportLog')
            ->findBy([], ["createdAt" => "DESC"]);

        /* Creates the pagination to import logs */
        $paginator = $this->get('knp_paginator');

        /* @var $pagination SlidingPagination */
        $pagination = $paginator->paginate($importLog, 1, 10);
        $pagination->setTemplate('@Import/pagination.html.twig');

        $config = $this->getParameter('import.config');

        return $this->render('@Import/index.html.twig', [
            'logs'              => $pagination,
            'logStatus'         => $config['status'],
            'statusAborted'     => self::ABORTED_STATUSES,
            'statusUndone'      => self::UNDONE_STATUSES,
            'blockImport'       => system_blockListingCreation(),
            'paginate'          => false,
        ]);
    }

    /**
     * Import Homepage
     *
     * @author João P. Schias <joao.schias@arcasolutions.com>
     *
     * @param int $page
     * @return string
     */
    public function paginateAction($page = 1)
    {
        /* Loading Import Repository */
        $importLog = $this->container->get('doctrine')
            ->getRepository('ImportBundle:ImportLog')
            ->findBy([], ["createdAt" => "DESC"]);

        /* Creates the pagination to import logs */
        $paginator = $this->get('knp_paginator');

        /* @var $pagination SlidingPagination */
        $pagination = $paginator->paginate($importLog, $page, 10);
        $pagination->setTemplate('@Import/pagination.html.twig');

        $config = $this->getParameter('import.config');

        return $this->render('@Import/items.html.twig', [
            'logs'              => $pagination,
            'logStatus'         => $config['status'],
            'statusAborted'     => self::ABORTED_STATUSES,
            'statusUndone'      => self::UNDONE_STATUSES,
            'paginate'          => true,

        ]);
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStatusAction(Request $request)
    {
        $response = ['status' => false];

        $importServices = $this->get('import.worker');
        if ($importServices->setImportStatus($request->get('importId'),
            $request->get('statusValue'))) {
            $response['status'] = true;
        }

        return new JsonResponse($response);
    }

    /**
     * Guides user through the import process.
     *
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since 11.3.00
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function wizardAction(Request $request)
    {
        if (system_blockListingCreation()) {
            return $this->render('@Web/upgrade_plan_banner.html.twig', []);
        }

        $type = $request->get('type');
        $finder = (new Finder())
            ->depth(0)
            ->name('*.csv')
            ->name('*.xls')
            ->name('*.xlsx');

        $dateFormat = $this->get('settings')->getDomainSetting('date_format') ?: 'Y-m-d';
        $clockType = $this->get('settings')->getDomainSetting('clock_type') ?: '24';

        $clockType = $clockType == '24' ? 'H:i:s' : 'h:i:s A';

        $path = $this->get('import.file_handler')->getImportFolderPath();
        $files = [];

        foreach ($finder->in($path) as $file) {
            $modDate = new \DateTime();
            $modDate->setTimestamp($file->getMTime());

            $files[] = [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'date' => $modDate->format(sprintf('%s %s', $dateFormat, $clockType)),
                'path' => $this->get('import.file_handler')->getImportFolderUri().'/'.$file->getFilename(),
            ];
        }

        usort($files, function ($a, $b) {
            if ($a['date'] == $b['date']) {
                return 0;
            }

            return $a['date'] > $b['date'] ? -1 : 1;
        });

        $importClass = $this->get('import.worker')
            ->getClassTypeByString($type);

        $headers = $this->get('import.extractor')
            ->setClassType($importClass)
            ->getClassPropertiesAlias();

        $config = $this->getParameter('import.config');
        $config['frontend']['block_import'] = (PAYMENTSYSTEM_FEATURE === 'off' && setting_get('listing_limit_count'));
        $config['frontend']['listing_limit'] = setting_get('listing_limit_count');
        $config['frontend']['available_items'] = system_getAvailableListingsItems();

        $accounts = $this->get('doctrine.orm.main_entity_manager')
            ->getRepository('CoreBundle:Account')
            ->findBy(['isSponsor' => 'y']);

        return $this->render('@Import/wizard.html.twig', [
            'files'            => $files,
            'config'           => $config['frontend'],
            'headers'          => $headers,
            'accounts'         => $accounts,
            'levels'           => $this->getModuleLevels($type),
            'requiredMappings' => $this->get('import.mapping')->getRequiredFields($type),
        ]);
    }

    /**
     * Get module levels
     *
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since 11.3.00
     * @param $type
     * @return \ArcaSolutions\EventBundle\Entity\EventLevel[]|\ArcaSolutions\ListingBundle\Entity\ListingLevel[]|array
     * @throws \Exception
     */
    private function getModuleLevels($type)
    {
        switch ($type) {
            case 'listing':
                return $this->get('doctrine')->getRepository('ListingBundle:ListingLevel')
                    ->findBy(['active' => 'y']);
            case 'event':
                return $this->get('doctrine')->getRepository('EventBundle:EventLevel')
                    ->findBy(['active' => 'y']);
        }

        throw new \Exception($this->get('translator')->trans('Module level not found', [], 'administrator'));
    }

    /**
     * Stores and validate import file.
     *
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since 11.3.00
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        try {
            $file = null;
            $files = $request->files->all();
            $importFileHandler = $this->get('import.file_handler');
            $mapping = json_decode($request->get('mapping'), JSON_OBJECT_AS_ARRAY);
            $hasHeader = $request->get('hasHeader', 0) == 1;
            $separator = $request->get('separator', ',');
            $type = $request->get('type');
            $importClass = $this->get('import.worker')->getClassTypeByString($type);
            $config = $this->getParameter('import.config');

            if (count($files) > 0) {
                $file = array_pop($files);
                $file = $importFileHandler->upload($file);
            }

            if ($fileName = $request->get('file', false)) {
                $importFileHandler->getImportFolderPath();
                $pathParts = explode('/', $fileName);
                $file = new File($importFileHandler->getImportFolderPath().'/'.$pathParts[count($pathParts) - 1]);
            }

            $analyser = $this->get('import.file_analyser')
                ->setHasHeader($hasHeader)
                ->setSeparator($separator);

            $analyser->configure(new \SplFileObject($file->getRealPath()), $mapping, $importClass);

            if ($analyser->getTotalItens() > $config['max_rows']) {
                return new JsonResponse([
                    'status'  => 'critical',
                    'message' => $this->get('translator')->trans('Import files are limited to %count% lines. Please split your file in smaller ones',
                        ['%count%' => $config['max_rows']], 'administrator'),
                ]);
            }

            $analyser->analyse();
        } catch (\Exception $e) {
            $this->get('logger')->critical('Error reading import file.', ['exception' => $e]);

            return new JsonResponse([
                'status'  => 'critical',
                'message' => $this->get('translator')->trans('Error parsing import file', [], 'administrator'),
            ]);
        }

        return new JsonResponse([
            'status'              => $analyser->getResult(),
            'file'                => $file->getFilename(),
            'errors'              => $analyser->getErrors(),
            'total_itens'         => $analyser->getTotalItens(),
            'total_valid_itens'   => $analyser->getTotalValidItens(),
            'total_invalid_itens' => $analyser->getTotalInvalidItens(),
        ]);
    }

    /**
     * Insert import on import queue
     *
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since 11.3.00
     * @param Request $request
     * @return JsonResponse
     */
    public function finishAction(Request $request)
    {
        $fileName = $request->get('file');
        $type = $request->get('type');
        $mapping = json_decode($request->get('mapping'), JSON_OBJECT_AS_ARRAY);
        $filePath = $this->get('import.file_handler')->getImportFolderPath().'/';
        $errors = json_decode($request->get('errors'), JSON_OBJECT_AS_ARRAY);

        $em = $this->getDoctrine()->getManager("domain");
        $em->getConnection()->beginTransaction();

        try {
            $file = new File($filePath.$fileName);
            $numberLines = count(file($file, FILE_SKIP_EMPTY_LINES)) - 1;
            $response = ['status' => 'pending'];

            $importLog = new ImportLog();
            $importLog
                ->setHasHeader($request->get('hasHeader') == 1)
                ->setDelimiter($request->get('csvSeparator', ','))
                ->setModule($type)
                ->setStatus(ImportLog::STATUS_PENDING)
                ->setFilename($file->getFilename())
                ->setContentType($file->getExtension())
                ->setUpdateExistingData($request->get('overwrite') == 1)
                ->setUpdateFriendlyUrl($request->get('updateUrl') == 1)
                ->setAccountIdForAllItems((int)$request->get('account'))
                ->setNewCategoriesFeatured($request->get('featuredCategories') == 1)
                ->setImportedItemsActive($request->get('active') == 1)
                ->setLevelForItemsNotSpecified($request->get('level'))
                ->setErrorLines((int)$request->get('total_errors'))
                ->setTotalLines((int)$request->get('total_itens'))
                ->setErrors($errors);

            $em->persist($importLog);
            $em->flush();

            $this->get('import.worker')
                ->setFilePath($filePath)
                ->parseFileWithMapping($importLog->getId(), $mapping);

            $em->getConnection()->commit();

            if($numberLines <= 500){
                $this->container->get('import.worker')->doImport($importLog);
                $this->container->get('import.worker')->doSync($importLog);

                $response['status'] = 'completed';
            }

            /* Removes file after save importLog */
            unlink($filePath.$fileName);

            return new JsonResponse($response);
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();
            $this->get('logger')->critical('Error persisting data in elasticsearch.', ['exception' => $e]);

            $return = [
                'message' => $this->getFormattedSupportLink(),
                'status'  => 'error',
            ];
        }

        return new JsonResponse($return, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Formats the error message with the support link
     *
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since 11.3.00
     * @return string
     */
    private function getFormattedSupportLink()
    {
        $message = $this->get('translator')->trans(
            'Something went wrong while reading your file. [Click here] to learn more.',
            [],
            'administrator');

        preg_match("/\[(.+)\]/", $message, $matches);

        $styles = 'cursor: pointer; padding: 0; display: inline;';

        $link = sprintf(
            "<a href='%s' target='_blank' style='%s'>%s</a>",
            self::SUPPORT_LINK,
            $styles,
            $matches[1]
        );

        return preg_replace("/\[.+\]/", $link, $message);
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param Request $request
     * @return JsonResponse
     */
    public function statusAction(Request $request)
    {
        $ids = $request->get('importIds');

        $imports = $this->getDoctrine()->getRepository('ImportBundle:ImportLog')->findBy([
            'id' => $ids,
        ]);

        $config = $this->getParameter('import.config');
        $trans = $this->get('translator');
        $response = [];

        foreach ($imports as $import) {
            $status = $config['status'][$import->getStatus()];
            $options = [];

            if (in_array($import->getStatus(), self::UNDONE_STATUSES, true)) {
                $options[] = [
                    'value' => ImportLog::STATUS_WAITROLLBACK,
                    'label' => $trans->trans('Undo import', [], 'administrator'),
                ];
            }

            if(in_array($import->getStatus(), self::ABORTED_STATUSES, true)) {
                $options[] = [
                    'value' => ImportLog::STATUS_ABORTED,
                    'label' => $trans->trans('Abort import', [], 'administrator'),
                ];
            }

            $response[] = [
                'id'           => $import->getId(),
                'status'       => $trans->trans(/** @Ignore */
                    $status, [], 'import_status'),
                'status_style' => Inflector::friendly_title(strtolower($status)),
                'options' => $options
            ];
        }

        return new JsonResponse($response);
    }
}
