<?php

namespace ArcaSolutions\ImportBundle\Services;

use ArcaSolutions\ImportBundle\Entity\ListingImport;
use ArcaSolutions\ImportBundle\Exception\ImportException;
use Elastica\Client;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;


/**
 * Class ElasticRepository
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Services
 * @since 11.3.00
 */
class ElasticRepository
{
    /**
     * @var array
     */
    private $elasticConfig;

    /**
     * @var Client
     */
    private $elasticClient;

    /**
     * @var string
     */
    private $elasticIndex;

    /**
     * @var int
     */
    private $bulkSize = 1000;

    /**
     * @var \ReflectionClass
     */
    private $reflectionClass;

    /**
     * @var \ReflectionProperty[]
     */
    private $properties;

    /**
     * @var string
     */
    private $docMappingType;

    /**
     * @var string
     */
    private $errorMappingType = "errors";

    /**
     * ElasticRepository constructor.
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string $elasticHost The ElasticSearch Host
     * @param string $elasticPort The ElasticSearch Port
     *
     * @throws ImportException
     */
    public function __construct($elasticHost, $elasticPort)
    {
        $this->elasticConfig = [
            'host' => $elasticHost,
            'port' => $elasticPort,
        ];

        $this->_initClient();
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string $indexName The elasticsearch index
     * @return ElasticRepository
     */
    public function setIndexName($indexName)
    {
        $this->elasticIndex = $indexName;

        return $this;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function getIndexName()
    {
        return $this->elasticIndex;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string $elasticHost The ElasticSearch Host
     * @return ElasticRepository
     */
    public function setHost($elasticHost)
    {
        $this->elasticConfig['host'] = $elasticHost;
        $this->elasticClient = null;

        return $this;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string $elasticPort The ElasticSearch Port
     * @return ElasticRepository
     * @throws ImportException
     */
    public function setPort($elasticPort)
    {
        if (!is_integer($elasticPort)) {
            throw new ImportException('Elastic Port value must be an integer', 500);
        }

        $this->elasticConfig['port'] = $elasticPort;
        $this->elasticClient = null;

        return $this;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string $classType The entity to work on the extractor (Listing|Event|Classified)
     * @return $this
     */
    public function setClassType($classType)
    {
        $this->reflectionClass = new \ReflectionClass($classType);
        $this->properties = $this->reflectionClass->getProperties();

        $this->docMappingType = strtolower($this->reflectionClass->getShortName());

        return $this;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function getDocMappingType()
    {
        return $this->docMappingType;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return string
     */
    public function getErrorMappingType()
    {
        return $this->errorMappingType;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param integer $value The value to bulk size
     * @return ElasticRepository
     * @throws ImportException
     */
    public function setBulkSize($value)
    {
        if (!is_integer($value)) {
            throw new ImportException('Bulk update value must be an integer', 500);
        }

        $this->bulkSize = $value;

        return $this;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param \ArrayIterator $items The items to persist
     * @param bool $cleanIndex
     */
    public function persistDataDoc(\ArrayIterator $items, $cleanIndex = false)
    {
        /* Remove the index */
        if ($cleanIndex) {
            $this->deleteIndex();
        }

        /* Get the elastic index */
        $index = $this->getIndex();

        $elasticDoc = [];
        foreach ($items as $id => $item) {
            $elasticDoc[] = $this->objectToDocument($item, $id+1, $this->docMappingType);

            /* Persist data in elasticSearch */
            if (count($elasticDoc) >= $this->bulkSize) {
                $this->addElasticDocInIndex($elasticDoc, $index);
                unset($elasticDoc);
                $elasticDoc = [];
            }
        }

        /* The remaining data */
        if (count($elasticDoc) > 0) {
            $this->addElasticDocInIndex($elasticDoc, $index);
            unset($elasticDoc);
        }

        unset($elasticDoc);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param \ArrayIterator $erros
     */
    public function persistErrorDoc(\ArrayIterator $erros)
    {
        $index = $this->getIndex();

        $errorDoc = [];
        foreach ($erros as $key => $erro) {
            $errorDoc[] = new Document($key, $erro, $this->errorMappingType);

            if (count($errorDoc) >= $this->bulkSize) {
                $this->addElasticDocInIndex($errorDoc, $index);
                unset($errorDoc);
                $errorDoc = [];
            }
        }

        if (count($errorDoc) > 0) {
            $this->addElasticDocInIndex($errorDoc, $index);
            unset($errorDoc);
        }
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return array
     */
    public function fetch10Documents()
    {
        $index = $this->getIndex();
        $type = $index->getType($this->docMappingType);

        /* @var $documents \Elastica\Document[] */
        $documents = $type->createSearch(new Query\Limit(10))
            ->search()
            ->getDocuments();

        $data = [];
        foreach ($documents as $document) {
            $data[] = $this->sourceToObject($document->getId(), $document->getData());
        }

        return $data;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return array
     */
    public function fetch100Documents()
    {
        $index = $this->getIndex();
        $type = $index->getType($this->docMappingType);

        /* @var $documents \Elastica\Document[] */
        $documents = $type->createSearch('', 100)
            ->search()
            ->getDocuments();

        $data = [];
        foreach ($documents as $document) {
            $data[] = $this->sourceToObject($document->getId(), $document->getData());
        }

        return $data;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $docId
     * @return bool
     */
    public function deleteDocument($docId)
    {
        $retVal = $this->getIndex()
            ->getType($this->docMappingType)
            ->deleteById($docId)
            ->isOk();
        $this->getIndex()->flush();

        return $retVal;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return int
     */
    public function getDocCount()
    {
        $type = $this->getIndex()->getType($this->getDocMappingType());

        return $type->count(new Query\MatchAll());
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @return int
     */
    public function getErrorCount()
    {
        $type = $this->getIndex()->getType($this->getErrorMappingType());

        return $type->count(new Query\MatchAll());
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return bool
     */
    public function deleteIndex()
    {
        try {
            $this->getIndex()->delete();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Making the connection to the elasticsearch
     *
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @throws ImportException
     */
    private function _initClient()
    {
        try {
            $this->elasticClient = new Client($this->elasticConfig);

            /* This line serves the sole purpose of testing if the connection was successful */
            $this->elasticClient->getStatus();
        } catch (\Exception $e) {
            throw new ImportException('Elasticsearch : Couldn\'t connect to server.', $e->getCode());
        }
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return \Elastica\Index
     */
    private function getIndex()
    {
        if ($this->elasticClient === null) {
            $this->_initClient();
        }

        return $this->elasticClient->getIndex($this->elasticIndex);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ListingImport $item The entity to persist
     * @param integer $id The document id
     * @param string $type The mapping type
     * @return Document
     */
    private function objectToDocument($item, $id = null, $type = null)
    {
        $document = new Document($id, $this->objectToArray($item), $type);
        $document->setDocAsUpsert(true);

        return $document;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ListingImport $item The entity to persist
     * @return array
     */
    private function objectToArray($item)
    {
        $array = [];
        /* @var \ReflectionProperty $property */
        foreach ($this->properties as $property) {
            $property->setAccessible(true);
            $array[$property->getName()] = $property->getValue($item);
            $property->setAccessible(false);
        }

        return array_filter($array, function ($v, $k) { return $v !== null; }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $id
     * @param $items
     * @return object
     */
    private function sourceToObject($id, $items)
    {
        $instance = $this->reflectionClass->newInstance();
        /* @var $property \ReflectionProperty */
        foreach ($this->properties as $property) {
            $property->setAccessible(true);
            if ($property->getName() == "id") {
                $property->setValue($instance, $id);
            } else {
                if (isset($items[$property->getName()])) {
                    $property->setValue($instance, $items[$property->name]);
                }
            }
            $property->setAccessible(false);
        }

        return $instance;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param array $documents
     * @param Index $index
     */
    private function addElasticDocInIndex(array $documents, $index)
    {
        $index->addDocuments($documents);
        $index->flush();
    }
}
