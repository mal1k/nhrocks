<?php

namespace ArcaSolutions\CoreBundle\Command;

use ArcaSolutions\CoreBundle\Entity\Domain;
use ArcaSolutions\MultiDomainBundle\Command\AbstractMultiDomainCommand;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use InvalidArgumentException;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as DataFixturesLoader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class FixtureCommand
 * @package ArcaSolutions\CoreBundle\Command
 */
class FixtureCommand extends AbstractMultiDomainCommand
{
    protected function configure()
    {
        $this
            ->setName('edirectory:fixture')
            ->setDefinition([
                new InputOption(
                    'fixtures',
                    null,
                    InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                    'The directory to load data fixtures from.'
                ),
                new InputOption(
                    'db-main',
                    'dbm',
                    InputOption::VALUE_NONE,
                    'Execute fixtures for main database.'
                ),
                new InputOption(
                    'db-domain',
                    'dbd',
                    InputOption::VALUE_NONE,
                    'Execute fixtures for one active domain database.'
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
                    'Execute fixtures for all active domains.'
                ),
            ])
            ->setDescription('Executes fixtures from selected database(s).')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command will attempt to execute fixtures of main and/or domain databases

  <info>php %command.full_name% --db-main</info> (Execute fixtures for main database)
  <info>php %command.full_name% --db-domain</info> (Execute fixtures for one active domain database)
  <info>php %command.full_name% --all-domains</info> (Execute fixtures for all active domains)
  <info>php %command.full_name% --all</info> (Execute fixtures for all databases (main and domain))
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<comment>Fixtures Initiated</comment>");

        $dirOrFile = $input->getOption('fixtures');
        if ($dirOrFile) {
            $paths = is_array($dirOrFile) ? $dirOrFile : array($dirOrFile);
        } else {
            /** @var $kernel \Symfony\Component\HttpKernel\KernelInterface */
            $kernel = $this->getApplication()->getKernel();
            $paths = array($kernel->getRootDir() . '/DataFixtures/ORM');
            foreach ($kernel->getBundles() as $bundle) {
                $paths[] = $bundle->getPath() . '/DataFixtures/ORM';
            }
        }

        $loader = new DataFixturesLoader($this->getContainer());
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $loader->loadFromDirectory($path);
            } elseif (is_file($path)) {
                $loader->loadFromFile($path);
            }
        }

        $fixtures = $loader->getFixtures();

        if (!$fixtures) {
            throw new InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- " . implode("\n- ", $paths))
            );
        }

        if ($mainOption = $input->getOption('db-main')) {
            $this->executeMainFixtures($input, $output, $fixtures);
        }

        if ((!$input->getOption('all-domains') && !$input->getOption('domain')) && !defined("SELECTED_DOMAIN_URL")) {
            $output->writeln("<error> You MUST provide a valid domain url using the --domain=demodirectory.com option</error>");
            exit();
        }

        $domainsToMigrate = null;
        $domainRepository = $this->getContainer()->get("doctrine")->getRepository("CoreBundle:Domain", "main");

        if ($allDomainsOption = $input->getOption('all-domains')) {
            $domainsToMigrate = $domainRepository->findBy(["status" => "A"]);
            if (!$domainsToMigrate) {
                $output->writeln("\n<error>Error: No domain active found!</error>");
            }

        } elseif ($domainOption = $input->getOption('db-domain')) {
            $domains = $domainRepository->findBy(["status" => "A"]);

            if (count($domains) == 1) {
                $domainsToMigrate = $domains;
                $output->writeln("\nOnly one domain found ({$domains[0]->getUrl()}) . Using it");
            } else {
                $output->writeln("\nThis eDirectory has more than one domain.");

                $options = [];

                for ($i = 0, $iMax = count($domains); $i < $iMax; $i++) {
                    /* @var $domains Domain[] */
                    $domainName = $domains[$i]->getName();
                    $domainUrl = $domains[$i]->getUrl();
                    $options[$i] = "{$domainName} ({$domainUrl})";
                }

                $question = new ChoiceQuestion(
                    "Which domain do you want execute the fixtures? (Defaults to '{$options[0]}')",
                    $options,
                    $options[0]
                );

                $selectedDomain = $this->getHelper('question')->ask($input, $output, $question);

                $selectedIndex = array_search($selectedDomain, $options);

                if ($selectedIndex !== false and array_key_exists($selectedIndex, $domains)) {
                    $domainsToMigrate[] = $domains[$selectedIndex];
                } else {
                    $output->writeln("\n<error>Error: Domain not found!</error>");
                }
            }
        }

        if ($domainsToMigrate) {
            foreach ($domainsToMigrate as $domain) {
                $this->executeDomainFixtures($input, $output, $fixtures, $domain);
            }
        }

        $output->writeln("\n<comment>Fixtures Finished.</comment>");
    }

    private function executeMainFixtures(InputInterface $input, OutputInterface $output, $fixtures)
    {
        $output->writeln("\n<info>Loading fixtures for main:</info>\n");

        $em = $this->getContainer()->get("doctrine")->getManager('main');

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->setLogger(function ($message) use ($output) {
            $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
        });

        try {
            $executor->execute($fixtures, true);
        } catch (\Exception $e) {
            $output->writeln("<error>Error to execute Main fixture.</error>");
            $output->writeln("<error>\"Error message: " . $e->getMessage() . "</error>");
        }

    }

    private function executeDomainFixtures(InputInterface $input, OutputInterface $output, $fixtures, Domain $domain)
    {
        if ($domain) {
            $output->writeln("\n<info>Loading fixtures for domain '" . $domain->getName() . "':</info>\n");

            $em = $this->getEntityManagerByDomain($domain->getUrl());

            $purger = new ORMPurger($em);
            $executor = new ORMExecutor($em, $purger);
            $executor->setLogger(function ($message) use ($output) {
                $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
            });

            try {
                $executor->execute($fixtures, true);
            } catch (\Exception $e) {
                $output->writeln("<error>Error to execute Domain " . $domain->getName() . "  fixture.</error>");
                $output->writeln("<error>\"Error message: " . $e->getMessage() . "</error>");
            }
        }
    }
}
