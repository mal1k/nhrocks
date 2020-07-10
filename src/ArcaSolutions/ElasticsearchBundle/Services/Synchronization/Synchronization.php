<?php

namespace ArcaSolutions\ElasticsearchBundle\Services\Synchronization;


use ArcaSolutions\CoreBundle\Search\BaseConfiguration;
use ArcaSolutions\ElasticsearchBundle\Services\Synchronization\Modules\BaseSynchronizable;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\ImportBundle\Services\ImportService;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Response;
use Elastica\Script\ScriptFile;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Synchronization
 *
 * Class that performs the synchronization of information stored in the mysql with elasticsearch.
 *
 * @author  Lucas Trentim <lucas.trentim@arcasolutions.com>
 * @author  Diego Mosela <diego.mosela@arcasolutions.com>
 *
 * @package ArcaSolutions\ElasticsearchBundle\Services\Synchronization
 */
class Synchronization
{
    /**
     * Enumerator for main database
     */
    const DATABASE_MAIN = 1;

    /**
     * Enumerator for domain database
     */
    const DATABASE_DOMAIN = 2;

    /**
     * Limit of items upon which a bulk will be sent
     */
    const BULK_THRESHOLD = 250;

    /**
     * Default limit to partial sync
     */
    const PARTIAL_TREHSHOLD = 10000;

    /**
     * Path to the index creation file
     * @var string
     */
    protected $indexMappingFilePath;

    /**
     * @var array
     */
    private $synchronizables = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ImportLog
     */
    private $import;

    /**
     * Synchronization constructor.
     * @param ContainerInterface $container
     */
    function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $s = DIRECTORY_SEPARATOR;
        $this->indexMappingFilePath = $container->get("kernel")->getRootDir()."{$s}..{$s}ElasticConfigs{$s}IndexCreation.json";
    }

    function __destruct()
    {
        $this->synchronize();
    }

    /**
     * @param $module BaseSynchronizable
     * Inserts or updates itens into elasticsearch
     */
    public function upsertES($module)
    {
        $logger = $this->container->get("logger");

        try {
            if ($ids = $module->getUpsertStash()) {
                $elasticaClient = $this->container->get("search.engine")->getElasticaClient();

                if ($response = $elasticaClient->updateDocuments($ids) and $response->hasError()) {
                    $logger->critical("Elasticsearch Request Error", ["error" => $response->getError()]);
                }

                $module->clearUpsertStash();
            }
        } catch (\Exception $e) {
            $logger->critical("Elasticsearch Synchronization Failure", ["exception" => $e]);
        }
    }

    /**
     * @param $module BaseSynchronizable
     * Removes items from elasticsearch
     */
    public function deleteES($module)
    {
        try {
            if ($ids = $module->getDeleteStash()) {

                /* @var BaseConfiguration $configuration */
                $configuration = $this->container->get($module->getConfigurationService());
                $elasticaClient = $this->container->get("search.engine")->getElasticaClient();
                $indexName = $this->container->get("search.engine")->getElasticIndexName();

                $elasticaClient->getIndex($indexName)->getType($configuration->getElasticType())->deleteIds($ids);

                $module->clearDeleteStash();
            }

        } catch (\Exception $e) {
            $this->container->get("logger")->critical("Elasticsearch Synchronization Failure", ["exception" => $e]);
        }
    }

    /**
     * @param $module BaseSynchronizable
     * Updates the view count of items in Elasticsearch
     */
    public function updateViewsES($module)
    {
        try {
            if ($ids = $module->getViewUpdateStash()) {
                /* @var BaseConfiguration $configuration */
                $configuration = $this->container->get($module->getConfigurationService());
                $indexName = $this->container->get("search.engine")->getElasticIndexName();
                $elasticaClient = $this->container->get("search.engine")->getElasticaClient();

                $documentsToUpdate = [];
                $data = new ScriptFile("updateView");

                foreach ($ids as $id) {
                    $documentsToUpdate[] = new Document(
                        $id,
                        $data,
                        $configuration->getElasticType(),
                        $indexName
                    );
                }

                $documentsToUpdate and $elasticaClient->updateDocuments($documentsToUpdate);

                unset($documentsToUpdate);

                $module->clearViewUpdateStash();
            }

        } catch (\Exception $e) {
        }
    }

    /**
     * @param $module BaseSynchronizable
     * Sets the average review for items in elasticsearch
     */
    public function updateAverageReviewES($module)
    {
        try {
            if ($ids = $module->getAverageReviewUpdateStash()) {
                /* @var BaseConfiguration $configuration */
                $configuration = $this->container->get($module->getConfigurationService());
                $indexName = $this->container->get("search.engine")->getElasticIndexName();
                $elasticaClient = $this->container->get("search.engine")->getElasticaClient();

                $documentsToUpdate = [];

                foreach ($ids as $id => $reviewValue) {
                    $data = ["averageReview" => $reviewValue];

                    $documentsToUpdate[] = new Document(
                        $id,
                        $data,
                        $configuration->getElasticType(),
                        $indexName
                    );
                }

                $documentsToUpdate and $elasticaClient->updateDocuments($documentsToUpdate);

                unset($documentsToUpdate);

                $module->clearAverageReviewUpdateStash();
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * @param string $item
     */
    public function addItem($item)
    {
        if (!in_array($item, $this->synchronizables)) {
            $this->synchronizables[] = $item;
        }
    }

    /**
     * Will synchronize every available module's MySQL data in elasticsearch
     * @return bool
     */
    public function synchronizeAll()
    {
        $return = false;

        $index = $this->container->get("search.engine")->getElasticaIndex();

        if ($index->exists()) {
            try {
                $this->container->get("event_dispatcher")->dispatch('edirectory.synchronization');
                $index->flush();
                $return = true;
            } catch (\Exception $e) {
                $this->container->get("logger")->critical("An error occurred during elasticsearch synchronization.",
                    ["Exception" => $e]);
            }
        }

        return $return;
    }

    /**
     * Creates an elasticsearch index or re-creates it, erasing everything if it already exists
     * If no $indexName is provided, the method will attempt to use the current domain's index name.
     * If no $language is provided, the method will attempt to use the current domain's language.
     *
     * @param string $language
     * @param string $indexName
     * @return bool
     */
    public function createIndex($language = null, $indexName = null)
    {
        $return = false;

        $searchEngine = $this->container->get("search.engine");

        if (file_exists($this->indexMappingFilePath) and $ESConfigPathContent = file_get_contents($this->indexMappingFilePath)) {
            $indexMapping = json_decode($ESConfigPathContent, true);

            $locale = $language ?: $this->container->get("multi_domain.information")->getLocale();
            $indexMapping['settings']['analysis']['analyzer']['text']['type'] = $searchEngine->getAnalyzerForLanguage($locale);

            /* Re-creates index, deleting it beforehand if already exists */
            $index = $indexName ? $searchEngine->getElasticaClient()->getIndex($indexName) : $searchEngine->getElasticaIndex();
            /* @var $response Response */
            if ($response = $index->create($indexMapping, true)) {
                $return = !$response->hasError();
            }
        } else {
            $logger = $this->container->get("logger");
            $logger->critical("Elasticsearch Index Creation Failed.");
        }

        return $return;
    }

    /**
     * Removes Elasticsearch index and all its data. Returns true on success
     * If no $indexName is provided, the method will attempt to use the current domain's index name.
     *
     * @param string $indexName
     * @return bool
     */
    public function deleteIndex($indexName = null)
    {
        try {
            $searchEngine = $this->container->get("search.engine");
            $index = $indexName ? $searchEngine->getElasticaClient()->getIndex($indexName) : $searchEngine->getElasticaIndex();
            $response = $index->delete();
            $worked = false == $response->hasError();
        } catch (ResponseException $e) {
            $worked = false !== strpos($e->getMessage(), "IndexMissingException");
        } catch (\Exception $e) {
            $worked = false;
        }

        return $worked;
    }

    /**
     * Returns the path to the JSON containing elasticsearch index mapping information
     * @return string
     */
    public function getIndexMappingFilePath()
    {
        return $this->indexMappingFilePath;
    }

    /**
     * Performs stored operations on Elasticsearch.
     */
    public function synchronize()
    {
        foreach ($this->synchronizables as $synchronizable) {
            $this->upsertES($synchronizable);
            $this->deleteES($synchronizable);
            $this->updateViewsES($synchronizable);
            $this->updateAverageReviewES($synchronizable);
        }

        $this->container->get("search.engine")->getElasticaIndex()->flush();
    }

    /**
     * @return ImportLog
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * @param ImportLog $import
     */
    public function setImport($import)
    {
        $this->import = $import;
    }
}
