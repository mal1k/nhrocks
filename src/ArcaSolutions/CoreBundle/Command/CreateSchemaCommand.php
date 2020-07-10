<?php

namespace ArcaSolutions\CoreBundle\Command;

use ArcaSolutions\MultiDomainBundle\Command\AbstractMultiDomainCommand;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateSchemaCommand extends AbstractMultiDomainCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('edirectory:schema:create')
            ->setDescription('Generate schema for a specific domain, or all domains.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);

        $this->getMultiDomain($input);

        $domains = $this->multiDomain->getHostConfig();

        if ($domain = $input->getOption('domain')) {
            $domains = isset($domains[$domain]) ? [$domain => $domains[$domain]] : [];
        }

        $progressBar = new ProgressBar($output, count($domains));
        $style->writeln('');
        foreach ($domains as $domain => $config) {
            $style->writeln(sprintf("Creating schema for %s domain\n", $domain));
            $progressBar->start();

            $this->multiDomain->setActiveHost($domain);
            $this->getConnection();

            $em = $this->getContainer()->get('doctrine.orm.domain_entity_manager');

            $dcmf = new DisconnectedClassMetadataFactory();
            $dcmf->setEntityManager($em);

            $st = new SchemaTool($em);

            $st->createSchema($dcmf->getAllMetadata());

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln("\n");
    }
}
