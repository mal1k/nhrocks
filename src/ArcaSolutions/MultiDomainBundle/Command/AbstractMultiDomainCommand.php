<?php

namespace ArcaSolutions\MultiDomainBundle\Command;

use ArcaSolutions\MultiDomainBundle\Exception\NotActiveHostException;
use ArcaSolutions\MultiDomainBundle\Services\Settings;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AbstractMultiDomainCommand
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @package ArcaSolutions\MultiDomainBundle\Command
 */
abstract class AbstractMultiDomainCommand extends ContainerAwareCommand
{
    /** Main database */
    const DATABASE_MAIN = 'main';

    /** Domain database */
    const DATABASE_DOMAIN = 'domain';

    /**
     * @var Settings
     */
    protected $multiDomain;

    protected function configure()
    {
        $this->addOption('all-domains', 'all', InputOption::VALUE_NONE, 'Execute for all domains.');
        $this->addOption('domain', 'd', InputOption::VALUE_OPTIONAL, 'The domain that will execute the command');
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        /* @todo Required constants checks due to the --domain parameter being in the app/console. After refactoring all the commands to use this class will no longer be necessary */

        if ((!$input->getOption('all-domains') && !$input->getOption('domain')) && !defined("SELECTED_DOMAIN_URL")) {
            throw new \InvalidArgumentException('You MUST provide a valid domain url using the --domain=demodirectory.com option or use the --all-domains to run the command for all domains.');
        }

        /* Get MultiDomain Service */
        $this->multiDomain = $this->getContainer()->get('multi_domain.information');

        /* Set domain informed in --domain parameter */
        if (defined("SELECTED_DOMAIN_URL") and SELECTED_DOMAIN_URL) {
            $this->multiDomain->setActiveHost(SELECTED_DOMAIN_URL);
        }
    }

    /**
     * MultiDomain Command Header
     *
     * @param SymfonyStyle $style
     * @param OutputInterface $output
     */
    protected function outputHeader(SymfonyStyle $style, OutputInterface $output)
    {
        $text = $this->multiDomain->getActiveHost() ? $this->multiDomain->getOriginalActiveHost() : "all domains";
        $style->section("MultiDomain - Running: ".$text);
    }

    /**
     * Gets the domain information with the information in the parameters
     *
     * @return Settings|array
     */
    protected function getMultiDomain()
    {
        return $this->multiDomain;
    }

    /**
     * Sets the new domain
     *
     * @param $domain
     */
    protected function setDomain($domain)
    {
        $this->multiDomain->setActiveHost($domain);
    }

    /**
     * Gets the domain active
     *
     * @return Settings
     * @throws NotActiveHostException
     */
    protected function getDomain()
    {
        if (is_null($this->multiDomain->getActiveHost())) {
            throw new NotActiveHostException();
        }

        return $this->multiDomain;
    }

    /**
     * @return object
     * @throws ConnectionException
     */
    protected function getConnection()
    {
        $connection = $this->getContainer()->get('doctrine.dbal.domain_connection');
        $params = $connection->getParams();
        $dbname = $this->multiDomain->getDatabase();

        if ($dbname == $params['dbname']) {
            return $connection;
        }

        $params['dbname'] = $dbname;
        if ($connection->isConnected()) {
            $connection->close();
        }

        $connection->__construct(
            $params,
            $connection->getDriver(),
            $connection->getConfiguration(),
            $connection->getEventManager()
        );

        try {
            $connection->connect();

            return $connection;
        } catch (\Exception $e) {
            throw new ConnectionException('Could not instantiate domain connection.');
        }
    }

    /**
     * Get entity manager for the given domain.
     * @param string $domain
     * @return EntityManager
     */
    public function getEntityManagerByDomain($domain) {
        $this->setDomain($domain);
        $em = $this->getContainer()->get('doctrine.orm.domain_entity_manager');

        return EntityManager::create($this->getConnection(), $em->getConfiguration(), $em->getEventManager());
    }

}
