<?php

namespace ArcaSolutions\CoreBundle\Command;

use ArcaSolutions\CoreBundle\Entity\Domain;
use ArcaSolutions\MultiDomainBundle\Command\AbstractMultiDomainCommand;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Migration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class MigrateCommand
 * @package ArcaSolutions\UpgradeBundle\Command
 */
class MigrateCommand extends AbstractMultiDomainCommand
{
    protected function configure()
    {
        $this
            ->setName('edirectory:migrate')
            ->setDefinition([
                new InputOption(
                    'db-main',
                    'dbm',
                    InputOption::VALUE_NONE,
                    'Execute migrations for main database.'
                ),
                new InputOption(
                    'db-domain',
                    'dbd',
                    InputOption::VALUE_NONE,
                    'Execute migrations for one active domain database.'
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
                    'Execute migrations for all active domains.'
                ),
                new InputOption(
                    'all',
                    null,
                    InputOption::VALUE_NONE,
                    'Execute migrations for all databases (main and domain).'
                ),
            ])
            ->setDescription('Executes migrations from selected database(s).')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command will attempt to execute migrations of main and/or domain databases

  <info>php %command.full_name% --db-main</info> (Execute migrations for main database)
  <info>php %command.full_name% --db-domain</info> (Execute migrations for one active domain database)
  <info>php %command.full_name% --all-domains</info> (Execute migrations for all active domains)
  <info>php %command.full_name% --all</info> (Execute migrations for all databases (main and domain))
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<comment>Migrations Initiated</comment>");

        /* Executes main migrations if the option chosen is "main" or "all" */
        if ($mainOption = $input->getOption('db-main') || $allOption = $input->getOption('all')) {
            $this->executeMainMigrations($input, $output);
        }

        $domainsToMigrate = null;
        $domainRepository = $this->getContainer()->get("doctrine")->getRepository("CoreBundle:Domain", "main");

        /* Executes domain migrations according to chosen option */
        if ($allDomainsOption = $input->getOption('all-domains') || $allOption = $input->getOption('all')) {
            /* get all domains active */
            $domainsToMigrate = $domainRepository->findBy(["status" => "A"]);
            if (!$domainsToMigrate) {
                $output->writeln("\n<error>Error: No domain active found!</error>");
            }

        } elseif ($domainOption = $input->getOption('db-domain')) {
            $domains = $domainRepository->findBy(["status" => "A"]);

            /* Only one domain is available. Let's use it, no questions asked */
            if (count($domains) == 1) {
                $domainsToMigrate = $domains;
                $output->writeln("\nOnly one domain found ({$domains[0]->getUrl()}) . Using it");
            } else {
                /* More than one domain is available, let user decide */
                $output->writeln("\nThis eDirectory has more than one domain.");

                $options = [];

                /* Puts all available options human=readable into a string array, zero-indexed */
                for ($i = 0, $iMax = count($domains); $i < $iMax; $i++) {
                    /* @var $domains Domain[] */
                    $domainName = $domains[$i]->getName();
                    $domainUrl = $domains[$i]->getUrl();
                    $options[$i] = "{$domainName} ({$domainUrl})";
                }

                $question = new ChoiceQuestion(
                    "Which domain do you want execute the migrations? (Defaults to '{$options[0]}')",
                    $options,
                    $options[0]
                );

                $selectedDomain = $this->getHelper('question')->ask($input, $output, $question);

                $selectedIndex = array_search($selectedDomain, $options);

                if ($selectedIndex !== false and array_key_exists($selectedIndex, $domains)) {
                    $domainsToMigrate[] = $domains[$selectedIndex];
                } else {
                    /* Sanity check. This is unlikely to happen */
                    $output->writeln("\n<error>Error: Domain not found!</error>\n");
                }
            }
        }

        if ($domainsToMigrate) {
            foreach ($domainsToMigrate as $domain) {
                $this->executeDomainMigrations($input, $output, $domain);
            }
        }

        $output->writeln("\n<comment>Migrations Finished.</comment>");
    }

    private function executeMainMigrations(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("\n<info>Migrating main:</info>\n");

        /* Main Migration */
        $mainDbConnection = $this->getContainer()->get("doctrine.dbal.main_connection");

        $mainConfig = new Configuration($mainDbConnection);
        $mainConfig->setOutputWriter($this->getOutputWriter($output));
        $mainConfig->setMigrationsTableName('migration_versions');
        $mainConfig->setMigrationsNamespace("Application\\Migrations");
        $mainConfig->setMigrationsDirectory(__DIR__ . "/../../../../app/DoctrineMigrations/Main");

        $migration = new Migration($mainConfig);

        try {
            $migration->migrate();
            $output->writeln("\n<comment>Main migration executed successfully.</comment>");
        } catch (\Exception $e) {
            $output->writeln("<error>Error to execute Main migration.</error>");
            $output->writeln("<error>\"Error message: " . $e->getMessage() . "</error>");
        }
    }

    private function executeDomainMigrations(InputInterface $input, OutputInterface $output, Domain $domain)
    {
        if ($domain) {
            $output->writeln("\n<info>Migrating domain '" . $domain->getName() . "':</info>\n");

            /* Sets domain as active domain */
            $this->getContainer()->get("upgrade")->setDomain($domain);

            /* Domain Migration */
            $domainDbConnection = $this->getContainer()->get("database_connection");

            $domainConfig = new Configuration($domainDbConnection);
            $domainConfig->setOutputWriter($this->getOutputWriter($output));
            $domainConfig->setMigrationsTableName('migration_versions');
            $domainConfig->setMigrationsNamespace("Application\\Migrations");
            $domainConfig->setMigrationsDirectory(__DIR__ . "/../../../../app/DoctrineMigrations/Domain");

            $migration = new Migration($domainConfig);

            try {
                $migration->migrate();
                $output->writeln("\n<comment>Domain (" . $domain->getName() . ") migration executed successfully.</comment>");
            } catch (\Exception $e) {
                $output->writeln("<error>Error to execute Domain " . $domain->getName() . "  migration.</error>");
                $output->writeln("<error>\"Error message: " . $e->getMessage() . "</error>");
            }
        }
    }

    private function getOutputWriter(OutputInterface $output)
    {
        return new OutputWriter(function ($message) use ($output) {
            return $output->writeln($message);
        });
    }
}