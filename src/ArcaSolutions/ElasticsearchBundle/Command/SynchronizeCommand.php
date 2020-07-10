<?php

namespace ArcaSolutions\ElasticsearchBundle\Command;

use ArcaSolutions\CoreBundle\Entity\Domain;
use ArcaSolutions\CoreBundle\Inflector;
use ArcaSolutions\CoreBundle\Mailer\Mailer;
use ArcaSolutions\CoreBundle\Search\CategoryConfiguration;
use ArcaSolutions\ElasticsearchBundle\Services\Synchronization\Synchronization;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\ImportBundle\Services\ImportService;
use ArcaSolutions\WebBundle\Entity\Setting;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SynchronizeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('edirectory:synchronize')
            ->setDefinition([
                new InputOption(
                    'recreate-index',
                    'r',
                    InputOption::VALUE_NONE,
                    'Recreates the entire index from scratch.
                    If this option is not provided, the index will not be rebuilt unless missing.'
                ),
                new InputOption(
                    'force-domain',
                    'f',
                    InputOption::VALUE_REQUIRED,
                    'Defines which domain will be synchronized.
                    Use either the domain url (ie: demodirectory.com)
                    or the domain id (ie: 1)

                    If this option is not provided, you will be prompted'
                ),
                new InputOption(
                    'bulk-size',
                    'b',
                    InputOption::VALUE_REQUIRED,
                    'Defines how many items will be updated in a cycle.
                    Defaults to '.Synchronization::BULK_THRESHOLD,
                    Synchronization::BULK_THRESHOLD
                ),
                new InputOption(
                    'module',
                    'm',
                    InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                    'Defines which modules will be synchronized. Available options are:

                    - article
                    - blog
                    - classified
                    - deal
                    - event
                    - listing
                    - articleCategory
                    - blogCategory
                    - classifiedCategory
                    - eventCategory
                    - listingCategory
                    - badge
                    - location

                    If this option is not provided, all modules will be synchronized',
                    []
                ),
                new InputOption(
                    'domain',
                    'd',
                    InputOption::VALUE_OPTIONAL,
                    'The domain that will execute the command'
                ),
                new InputOption(
                    'all-domains',
                    'a',
                    InputOption::VALUE_NONE,
                    'Execute the synchronize for all domains.'
                ),
                new InputOption(
                    'import-only',
                    'i',
                    InputOption::VALUE_NONE,
                    'Execute the synchronize only for imported data.'
                ),
                new InputOption(
                    'partial',
                    'p',
                    InputOption::VALUE_REQUIRED,
                    'Execute the synchronize process by batches to avoid memory limit overflow.'
                ),
            ])
            ->setDescription('Synchronizes MySQL and Elasticsearch.')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command will attempt to synchronize all modules, categories, locations and badges, pulling data from the MySQL database into elasticsearch

  <info>php %command.full_name% --recreate-index</info> (Forces index deletion and recreation)
  <info>php %command.full_name% --force-domain=1</info> (Forces a specific domain (By it's id) and prevents domain prompt)
  <info>php %command.full_name% --force-domain=demodirectory.com</info> (Forces a specific domain (By it's URL) and prevents domain prompt)
  <info>php %command.full_name% --bulk-size=250</info> (Forces a specific amount of items per synchronization cycle)
  <info>php %command.full_name% --module=listing</info> (If provided, only selected module (listing) will be syncronized)
  <info>php %command.full_name% --module=listing --module=location</info> (If provided, only selected modules (listing and locations) will be syncronized)
  <info>php %command.full_name% --all-domains</info> (Synchronize all domains)
  <info>php %command.full_name% --import-only</info> (Synchronize only imported data)
  <info>php %command.full_name% --partial=10000 --module=listing</info> (Synchronize the first 10k listings and save the last id to continue the sync later)
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Synchronization Initiated</comment>');

        if (!defined('SELECTED_DOMAIN_URL') && (!$input->getOption('all-domains') && !$input->getOption('domain'))) {
            throw new \InvalidArgumentException(
                '<error>You MUST provide a valid domain url using the --domain=demodirectory.com option</error>'
            );
        }

        try {
            $domainsToSynchronize = $this->getDomainsToSynchronize($input, $output);
        } catch(\Exception $e){
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return;
        }

        if (is_array($domainsToSynchronize)) {
            foreach ($domainsToSynchronize as $domain) {
                $this->synchronizeDomain($domain, $input, $output);
            }
        } else {
            $this->synchronizeDomain($domainsToSynchronize, $input, $output);
        }

        $output->writeln("<comment>Synchronization Finished.</comment>\n\n");
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return Domain|Domain[]
     */
    private function getDomainsToSynchronize(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $domainRepository = $doctrine->getRepository('CoreBundle:Domain', 'main');

        if ($forcedDomain = $input->getOption('force-domain')) {
            $domain = $domainRepository->findOneByIdOrUrl($forcedDomain);

            if (!$domain) {
                throw new \InvalidArgumentException('The domain provided did not match any available domain.');
            }

            return $domain;
        }

        if ($allDomainsOption = $input->getOption('all-domains')) {
            $domains = $domainRepository->findBy(['status' => 'A']);

            if (!$domains) {
                throw new \InvalidArgumentException('Error: No domain active found!');
            }

            return $domains;
        }

        if ($domains = $domainRepository->findBy(['status' => 'A'])) {
            if (count($domains) === 1) {
                return array_pop($domains);
            }

            $output->writeln('This eDirectory has more than one domain: ');

            $options = [];
            foreach ($domains as $domain) {
                $options[] = sprintf('%s (%s)', $domain->getName(), $domain->getUrl());
            }

            $question = new ChoiceQuestion(
                "Which domain do you want to Synchronize? (Defaults to '{$options[0]}')",
                $options,
                $options[0]
            );

            $selectedDomain = $this->getHelper('question')->ask($input, $output, $question);

            $selectedIndex = array_search($selectedDomain, $options, true);

            if ($selectedIndex === false && !array_key_exists($selectedIndex, $domains)) {
                return [];
            }

            return $domains[$selectedIndex];
        }

        return [];
    }

    /**
     * Synchronizes one domain
     *
     * @param Domain $domain
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \ArcaSolutions\ImportBundle\Exception\InvalidModuleException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function synchronizeDomain(Domain $domain, InputInterface $input, OutputInterface $output)
    {
        if (!$domain) {
            return;
        }

        $output->writeln("\n<info>Synchronizing domain '".$domain->getName()."':</info>");

        /* Sets domain as active domain */
        $this->getContainer()->get('upgrade')->setDomain($domain);

        $searchEngine = $this->getContainer()->get('search.engine');

        $import = null;
        if ($input->getOption('import-only')) {
            $importRepository = $this->getContainer()->get('doctrine')->getRepository('ImportBundle:ImportLog');

            $import = $importRepository->findOneBy(
                ['status' => ImportLog::STATUS_DONE],
                ['status' => 'desc', 'updatedAt' => 'asc']
            );

            if (!$import) {
                return;
            }

            $import->setStatus(ImportLog::STATUS_SYNC);

            /* Changes the import status to SYNC */
            $em = $this->getContainer()->get('doctrine.orm.domain_entity_manager');
            $em->flush();

            $syncService = $this->getContainer()->get('elasticsearch.synchronization');
            $syncService->setImport($import);
        }

        /* Recreates index if option is set */
        if ($input->getOption('recreate-index')) {
            $this->getContainer()->get('elasticsearch.synchronization')->createIndex();

            $settings = $this->getContainer()->get('settings');

            $settings->setSetting('listing_partial_sync', 0);
            $settings->setSetting('event_partial_sync', 0);
            $settings->setSetting('blog_partial_sync', 0);
            $settings->setSetting('classified_partial_sync', 0);
            $settings->setSetting('article_partial_sync', 0);
            $settings->setSetting('deal_partial_sync', 0);

            $output->writeln("\n<info>Recreating Index...</info>");
            $progressBar = new ProgressBar($output, 10);
            $progressBar->start();

            for ($i = 0; $i < 10; $i++) {
                sleep(0.6);
                $progressBar->advance(1);
            }

            $progressBar->finish();
            $output->writeln("\n<info>Index Recreated.</info>");
        }

        $availableModules = [
            'article'            => 1,
            'blog'               => 1 << 1,
            'classified'         => 1 << 2,
            'deal'               => 1 << 3,
            'event'              => 1 << 4,
            'listing'            => 1 << 5,
            'articleCategory'    => 1 << 6,
            'blogCategory'       => 1 << 7,
            'classifiedCategory' => 1 << 8,
            'eventCategory'      => 1 << 9,
            'listingCategory'    => 1 << 10,
            'badge'              => 1 << 11,
            'location'           => 1 << 12,
        ];

        /* ModStores Hooks */
        HookFire("synchronizacommand_after_setup_availablemodules", [
            "availableModules" => &$availableModules
        ]);

        $moduleFlags = 0;

        if ($selectedModules = $input->getOption('module')) {
            while ($module = array_pop($selectedModules)) {
                if (array_key_exists($module, $availableModules)) {
                    $moduleFlags |= $availableModules[$module];
                }
            }
            if(empty($moduleFlags)) {
                $output->writeln("\n<error>Please select an available module</error>\n" .
                                "\n<comment>############### Available Modules ###############</comment>\n" .
                                "\n<info> -m article</info>\n" .
                                "<info> -m blog</info>\n" .
                                "<info> -m classified</info>\n" .
                                "<info> -m deal</info>\n" .
                                "<info> -m event</info>\n" .
                                "<info> -m listing</info>\n" .
                                "<info> -m articleCategory</info>\n" .
                                "<info> -m blogCategory</info>\n" .
                                "<info> -m classifiedCategory</info>\n" .
                                "<info> -m eventCategory</info>\n" .
                                "<info> -m listingCategory</info>\n" .
                                "<info> -m badge</info>\n" .
                                "<info> -m location</info>\n");

                return;
            }
        }

        $output->writeln("\n<comment>############### Modules ###############</comment>");

        if (($moduleFlags === 0 or $moduleFlags & $availableModules['article']) && (!$import)) {
            $output->writeln("\n<info>========= Article =========</info>");
            $this->synchronize($input, $output, 'article.synchronization');
        }

        if (($moduleFlags === 0 or $moduleFlags & $availableModules['blog']) && (!$import)) {
            $output->writeln("\n<info>========= Blog =========</info>");
            $this->synchronize($input, $output, 'blog.synchronization');
        }

        if (($moduleFlags === 0 or $moduleFlags & $availableModules['classified']) && (!$import)) {
            $output->writeln("\n<info>========= Classified =========</info>");
            $this->synchronize($input, $output, 'classified.synchronization');
        }

        if (($moduleFlags === 0 or $moduleFlags & $availableModules['deal']) && (!$import)) {
            $output->writeln("\n<info>========= Deal =========</info>");
            $this->synchronize($input, $output, 'deal.synchronization');
        }

        if (($moduleFlags === 0 or $moduleFlags & $availableModules['event']) && (!$import || $import->getModule() == ImportService::MODULE_EVENT)) {
            $output->writeln("\n<info>========= Event =========</info>");
            $this->synchronize($input, $output, 'event.synchronization');
        }

        if (($moduleFlags === 0 or $moduleFlags & $availableModules['listing']) && (!$import || $import->getModule() == ImportService::MODULE_LISTING)) {
            $output->writeln("\n<info>========= Listing =========</info>");
            $this->synchronize($input, $output, 'listing.synchronization');
        }

        /* ModStores Hooks */
        HookFire("synchronizacommand_after_modules_synchronize", [
            "availableModules" => &$availableModules,
            "moduleFlags"      => &$moduleFlags,
            "output"           => &$output,
            "import"           => &$import,
        ]);

        $output->writeln("\n<comment>############### Categories ###############</comment>");

        $categoryFlags = $availableModules['articleCategory'] | $availableModules['blogCategory'] | $availableModules['classifiedCategory'] | $availableModules['eventCategory'] | $availableModules['listingCategory'];

        if ($moduleFlags === 0 or $moduleFlags & $categoryFlags) {
            $searchEngine->clearType(CategoryConfiguration::$elasticType);
        }

        if (($moduleFlags === 0 or $moduleFlags & $availableModules['articleCategory']) && (!$import)) {
            $output->writeln("\n<info>========= Article Category =========</info>");
            $this->synchronize($input, $output, 'article.category.synchronization');
        }

        if (($moduleFlags === 0 or $moduleFlags & $availableModules['blogCategory']) && (!$import)) {
            $output->writeln("\n<info>========= Blog Category =========</info>");
            $this->synchronize($input, $output, 'blog.category.synchronization');
        }

        if (($moduleFlags === 0 or $moduleFlags & $availableModules['classifiedCategory']) && (!$import)) {
            $output->writeln("\n<info>========= Classified Category =========</info>");
            $this->synchronize($input, $output, 'classified.category.synchronization');
        }

        if (($moduleFlags === 0 or $moduleFlags & $availableModules['eventCategory']) && (!$import || $import->getModule() == ImportService::MODULE_EVENT)) {
            $output->writeln("\n<info>========= Event Category =========</info>");
            $this->synchronize($input, $output, 'event.category.synchronization');
        }

        if (($moduleFlags === 0 or $moduleFlags & $availableModules['listingCategory']) && (!$import || $import->getModule() == ImportService::MODULE_LISTING)) {
            $output->writeln("\n<info>========= Listing Category =========</info>");
            $this->synchronize($input, $output, 'listing.category.synchronization');
        }

        /* ModStores Hooks */
        HookFire("synchronizacommand_after_categories_synchronize", [
            "availableModules" => &$availableModules,
            "moduleFlags"      => &$moduleFlags,
            "output"           => &$output,
            "import"           => &$import,
        ]);

        if (($moduleFlags === 0 or $moduleFlags & $availableModules['badge']) && (!$import)) {
            $output->writeln("\n<comment>############### Badges ###############</comment>\n");
            $this->synchronize($input, $output, 'badge.synchronization');
        }

        if ($moduleFlags === 0 or $moduleFlags & $availableModules['location']) {
            $output->writeln("\n<comment>############### Locations ###############</comment>\n");
            $this->synchronize($input, $output, 'location.synchronization');
        }

        /* ModStores Hooks */
        HookFire("synchronizacommand_after_additional_synchronize", [
            "availableModules" => &$availableModules,
            "moduleFlags"      => &$moduleFlags,
            "output"           => &$output,
            "import"           => &$import,
        ]);

        if ($index = $searchEngine->getElasticaIndex() and $response = $index->flush()) {
            if ($response->getError()) {
                $output->writeln("\n<error>Elasticsearch flush failed! Is the server dead?</error>");
            } else {
                $output->writeln("\nFlushing all bulk actions for the selected index....\n");
            }
        }

        /* Changes the import status to COMPLETED */
        if ($import) {
            $import->setStatus(ImportLog::STATUS_COMPLETED);

            $em->merge($import);
            $em->flush();

            //Multi domain issue (Kernel->getDomain())
//            $this->sendNotification($import, $domain);
            $this->updateImportFlag($import);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $synchronizable
     */
    protected function synchronize(InputInterface $input, OutputInterface $output, $synchronizable)
    {
        $start = microtime(true);

        if($total = $input->getOption('partial')) {
            if ($input->getOption('module')) {
                $this->getContainer()->get($synchronizable)->generatePartial($output, $total,
                    $input->getOption('bulk-size'));
                $this->getContainer()->get('elasticsearch.synchronization')->synchronize();
                $output->writeln(sprintf("\n\nOperation took %d seconds", microtime(true) - $start));

                return;
            }

            $output->writeln("\n<error>Module Parameter is required for Partial Synchronization</error>");
            exit;

        }

        $this->getContainer()->get($synchronizable)->generateAll($output, $input->getOption('bulk-size'));
        $this->getContainer()->get('elasticsearch.synchronization')->synchronize();
        $output->writeln(sprintf("\n\nOperation took %d seconds", microtime(true) - $start));
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ImportLog $import
     * @param Domain $domain
     * @throws \Exception
     */
    private function sendNotification(ImportLog $import, Domain $domain)
    {
        /* Send Mail for Admins */
        $sendTo = explode(',', $this->getContainer()->get('doctrine')
            ->getRepository('WebBundle:Setting')
            ->getSetting('sitemgr_import_email'));

        $translator = $this->getContainer()->get('translator');
        $subject = '['.$domain->getName().'] '.$translator->trans('Import Completed', [], 'administrator');

        try {
            $context = $this->getContainer()->get('router')->getContext();
            $context->setHost($this->getContainer()->get('kernel')->getDomain());

            $this->getContainer()->get('core.mailer')->newMail()
                ->setSubject($subject)
                ->setTo($sendTo, null, true)
                ->setBody(
                    $this->getContainer()->get('twig')->render('@Import/mailer/completed.html.twig', [
                        'module'    => $translator->trans(/** @Ignore */
                            Inflector::pluralize(ucfirst($import->getModule())), [],
                            'administrator'),
                        'import_id' => $import->getId(),
                    ]),
                    'text/html'
                )
                ->send();
        } catch (\Exception $e) {
            $this->getContainer()->get('logger')->addError('Import send notification: '.$e->getMessage());
        }
    }

    /**
     * @param ImportLog $import
     * @throws \ArcaSolutions\ImportBundle\Exception\InvalidModuleException
     */
    private function updateImportFlag($import)
    {
        $flagName = ImportService::getImportSettingFlagName($import->getModule());

        $setting = $this->getContainer()->get('doctrine')->getRepository('WebBundle:Setting')->findOneBy([
            'name' => $flagName,
        ]);

        if (!$setting) {
            $setting = new Setting();
            $setting->setName($flagName);
        }

        $setting->setValue(date('Y-m-d H:i:s'));

        $em = $this->getContainer()->get('doctrine.orm.domain_entity_manager');
        $em->persist($setting);
        $em->flush();
    }

}
