<?php

namespace ArcaSolutions\ModStoresBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class VersionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('version')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
            ->setAliases([
                'edirectory:modstores:version',
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $domainFile = Yaml::parse(file_get_contents('app/config/domain.yml'));
        $hosts = array_keys($domainFile['multi_domain']['hosts']);

        foreach ($hosts as $domain) {
            $databaseDomain = $domainFile['multi_domain']['hosts'][$domain]['database'];

            $databaseFile = Yaml::parse(file_get_contents('app/config/database.yml'));
            $database = $databaseFile['doctrine']['dbal']['connections']['domain']['dbname'];

            $databaseFile['doctrine']['dbal']['connections']['domain']['dbname'] = $databaseDomain;
            $yaml = Yaml::dump($databaseFile, 99);

            file_put_contents('app/config/database.yml', $yaml);

            $output->writeln("Domain : {$domain}");
            $output_shel = shell_exec("php -d disable_functions='' -d memory_limit=-1 app/console app/console doctrine:migrations:version --add --all");

        }
    }
}