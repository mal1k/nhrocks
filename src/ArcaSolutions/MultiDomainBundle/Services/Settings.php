<?php

namespace ArcaSolutions\MultiDomainBundle\Services;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\MultiDomainBundle\Exception\MultiDomainException;
use Doctrine\DBAL\Connection;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class Settings
 *
 * Class responsible for retrieving information from the domain that is accessing the system.
 *
 * @package ArcaSolutions\MultiDomainBundle\Services
 */
class Settings
{
    /**
     * @var array
     */
    protected $hostConfig = [];

    /**
     * @var string|null
     */
    protected $activeHost = null;

    /**
     * @var string|null
     */
    protected $originalActiveHost = null;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Connection
     */
    protected $domainConnection;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * Settings constructor.
     *
     * @param KernelInterface $kernel
     * @param $hostsConfig
     * @param Logger $logger
     * @param Connection $connection
     * @throws \Exception
     */
    public function __construct(KernelInterface $kernel, $hostsConfig, Logger $logger, Connection $connection)
    {
        $this->setHostConfig($hostsConfig);
        $this->logger = $logger;
        $this->domainConnection = $connection;
        $this->kernel = $kernel;

        /* @var Kernel $kernel */
        $domain = $kernel->getDomain() and $this->setActiveHost($domain);
    }

    /**
     * @param array $hostConfig
     */
    protected function setHostConfig($hostConfig)
    {
        $this->hostConfig = $hostConfig;
    }

    /**
     * @return array
     */
    public function getHostConfig()
    {
        return $this->hostConfig;
    }

    /**
     * @return null
     */
    public function getActiveHost()
    {
        return $this->activeHost;
    }

    /**
     * @author Lucas Trentim <lucas.trentim@arcasolutions.com>
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.0.00
     * @param null $activeHost
     * @throws MultiDomainException
     */
    public function setActiveHost($activeHost)
    {
        $this->originalActiveHost = $activeHost;

        $activeHost = str_replace('-', '_', $activeHost);

        if (isset($this->hostConfig[$activeHost])) {
            $this->activeHost = $activeHost;
        } else {
            $this->logger->critical("[MultiDomain/Settings] - Unable to set Active Host.");
            throw new MultiDomainException(sprintf('Cannot find host %s for this eDirectory installation', $activeHost));
        }
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param int $domainId The domain id
     * @param bool $connection If true the system will perform the exchange of the connection with the database
     * @throws MultiDomainException
     */
    public function setActiveHostById($domainId, $connection=false)
    {
        foreach ($this->hostConfig as $domain => $data) {
            if ($data['id'] == $domainId) {
                $this->setActiveHost($domain);
                $connection and $this->createNewConnection();
                break;
            }
        }

    }

    /**
     * @return null
     */
    public function getOriginalActiveHost()
    {
        return $this->originalActiveHost;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getSetting("id");
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getSetting($name)
    {
        $returnValue = null;

        if ($this->hostConfig and $this->activeHost and isset($this->hostConfig[$this->activeHost][$name])) {
            $returnValue = $this->hostConfig[$this->activeHost][$name];
        }

        return $returnValue;
    }

    /**
     * @param bool $absolute
     * @return string
     */
    public function getPath($absolute = false)
    {
        if($absolute) {
            return $this->kernel->getRootDir() . '/../web/' . $this->getSetting('path');
        }

        return $this->getSetting('path');
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->getSetting("template");
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->getSetting("locale");
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->getSetting("database");
    }

    /**
     * @return string
     */
    public function getElastic()
    {
        return $this->getSetting("elastic");
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getSetting("title");
    }

    /**
     * @return string
     */
    public function getBranded()
    {
        return $this->getSetting("branded");
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @throws MultiDomainException
     */
    private function createNewConnection()
    {
        $connection = $this->domainConnection;
        $params = $connection->getParams();
        $dbname = $this->getDatabase();

        if ($dbname != $params['dbname']) {
            $params['dbname'] = $dbname;

            if ($connection->isConnected()) {
                $connection->close();
            }

            try {
                $connection->__construct($params, $connection->getDriver(), $connection->getConfiguration(),
                    $connection->getEventManager());
                $connection->connect();
            } catch (\Exception $e) {
                $this->logger->error('Error changing the database name');
                throw new MultiDomainException(sprintf('Cannot connect to database %s', $dbname));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveHostCacheDir()
    {
        $domain = str_replace(['_', 'www.'], ['-', ''], $this->getActiveHost());

        return $this->kernel->getRootDir().'/cache/'.$this->kernel->getEnvironment().'/'.$domain;
    }
}
