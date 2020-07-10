<?php

namespace ArcaSolutions\ModStoresBundle\Command;

use ArcaSolutions\MultiDomainBundle\Command\AbstractMultiDomainCommand;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;


class EdirectoryPluginsMarkExistingMigrationsCommand extends AbstractMultiDomainCommand
{


    /**
     * Edirectory ModStore Core Installation Command base configuration
     */
    protected function configure()
    {
        $this->setName('edirectory:plugin:mark:migrations')
            ->setDescription('Copy existing migrations')
            ->setDefinition([
                new InputOption(
                    'db-main',
                    'dbm',
                    InputOption::VALUE_NONE,
                    'Execute the migrations copy for main database.'
                ),
                new InputOption(
                    'db-domain',
                    'dbd',
                    InputOption::VALUE_NONE,
                    'Execute the migrations copy for one active domain database.'
                ),
                new InputOption(
                    'domain',
                    'd',
                    InputOption::VALUE_OPTIONAL,
                    'The domain that will execute the command'
                ),
                new InputOption(
                    'all-domains',
                    null,
                    InputOption::VALUE_NONE,
                    'Execute the migrations copy for all active domains.'
                ),
                new InputOption(
                    'all',
                    null,
                    InputOption::VALUE_NONE,
                    'Execute the migrations copy for all databases (main and domain).'
                ),
            ])
            ->setDescription('Executes the migrations copy from selected database(s).')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command will attempt to execute migrations of main and/or domain databases

  <info>php %command.full_name% --db-main</info> (Execute the migrations copy for main database)
  <info>php %command.full_name% --db-domain</info> (Execute the migrations copy for one active domain database)
  <info>php %command.full_name% --all-domains</info> (Execute the migrations copy for all active domains)
  <info>php %command.full_name% --all</info> (Execute the migrations copy for all databases (main and domain))
EOF
            );
    }

    /**
     * Edirectory ModStore Core Installation Command base execute method
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        // writes nice command header
        $io->block('Mark Migrations', null, 'fg=black;bg=green', ' ', true);

        $this->markMigrations('domain', $input, $output);
        $this->markMigrations('main', $input, $output);

        $io->writeln(sprintf('<comment>Marked all default migrations as executed</comment>'));
        $io->newLine();
    }

    protected function markMigrations($database = 'domain', $input, $output)
    {

        if ($database === 'domain') {

            $domainFile = Yaml::parse(file_get_contents('app/config/domain.yml'));
            $domains = array_keys($domainFile['multi_domain']['hosts']);

            foreach ($domains as $domain) {

                $databaseDomain = $domainFile['multi_domain']['hosts'][$domain]['database'];

                $databaseFile = Yaml::parse(file_get_contents('app/config/database.yml'));

                $databaseFile['doctrine']['dbal']['connections']['domain']['dbname'] = $databaseDomain;
                $yaml = Yaml::dump($databaseFile, 99);

                file_put_contents('app/config/database.yml', $yaml);

                $output->writeln("Change to domain: {$domain}");


                $config = $this->getMigrationConfiguration('domain');

                $versions = $config->getAvailableVersions();
                foreach ($versions as $versionNumber) {

                    $version = $config->getVersion($versionNumber);

                    if ($versionNumber <= 20181004162236 && !$version->isMigrated()) {
                        $version->markMigrated();
                        $output->writeln("Mark Domain migration {$versionNumber} as executed");
                    }
                }
            }

            $databaseFile = Yaml::parse(file_get_contents('app/config/database.yml'));

            $databaseFile['doctrine']['dbal']['connections']['domain']['dbname'] = '~';
            $yaml = Yaml::dump($databaseFile, 99);

            file_put_contents('app/config/database.yml', $yaml);
        } else {
            $config = $this->getMigrationConfiguration('main');

            $versions = $config->getAvailableVersions();

            foreach ($versions as $versionNumber) {
                $version = $config->getVersion($versionNumber);
                if ($versionNumber <= 20171124121025 && !$version->isMigrated()) {
                    $version->markMigrated();
                    $output->writeln("Mark Main migration {$versionNumber} as executed");
                }
            }
        }
    }

    /**
     * @param $database
     * @return Configuration
     */
    protected function getMigrationConfiguration($database, $domain = null)
    {
        /** @var Connection $conn */
        $conn = $this->getContainer()
            ->get(sprintf('doctrine.dbal.%s_connection', $database));

        if ($database === 'domain' && !empty($domain)) {
            $params = $conn->getParams();


            if ($domain != $params['dbname']) {
                $params['dbname'] = $domain;
                if ($conn->isConnected()) {
                    $conn->close();
                }

                $conn->__construct(
                    $params,
                    $conn->getDriver(),
                    $conn->getConfiguration(),
                    $conn->getEventManager()
                );

                try {
                    $conn->connect();
                } catch (Exception $e) {
                    $this->logText('<error>Could not instantiate domain connection.</error>');
                }
            }
        }

        $migrationDir = $this->getContainer()->get('kernel')->getRootDir().'/DoctrineMigrations/%s';

        $config = new Configuration($conn);
        $config->setMigrationsTableName('migration_versions');
        $config->setMigrationsNamespace("Application\\Migrations");
        $config->setMigrationsDirectory(sprintf($migrationDir, ucfirst($database)));

        return $config;
    }
}