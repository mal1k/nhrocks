<?php

namespace ArcaSolutions\ModStoresBundle\Command;

use ArcaSolutions\MultiDomainBundle\Command\AbstractMultiDomainCommand;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class EdirectoryModstoresInstallCommand
 *
 * @package ArcaSolutions\ModStoresBundle\Kernel
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 * @author José Lourenção <jose.lourencao@arcasolutions.com>
 *
 * Errors Debug Troubleshot
 *
 * [1001] - Edirectory version does not match with Kernel requirements
 * [1002] - Kernel version does not match with plugin requirements
 */
class EdirectoryModstoresInstallCommand extends AbstractMultiDomainCommand
{
    /**
     * @var int
     */
    private $countInstalled = 0;

    /**
     * @var array
     */
    private $requiredList = [
        'beforeCommand' => [],
        'afterCommand'  => [],
    ];

    /**
     * Edirectory ModStore Core Installation Command base configuration
     */
    protected function configure()
    {
        $this->setName('edirectory:plugin:install')
            ->setDescription('Install eDirectory Plugin')
            ->setDefinition([
                new InputOption(
                    'execute-required',
                    'r',
                    InputOption::VALUE_NONE,
                    'Execute all required commands after installation.'
                ),
                new InputOption(
                    'upgrade',
                    'u',
                    InputOption::VALUE_NONE,
                    'For ModStore upgrade if available on bundle.'
                ),
                new InputOption(
                    'force-install',
                    'f',
                    InputOption::VALUE_NONE,
                    'For ModStore to force re-install on bundle if at least same version.'
                ),
                new InputOption(
                    'lang',
                    'l',
                    InputOption::VALUE_OPTIONAL,
                    'Set up project language.'
                ),
            ])
            ->setAliases([
                'edirectory:modstores:install',
            ]);

        parent::configure();
    }

    /**
     * Edirectory ModStore Core Installation Command base execute method
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // writes nice command header
        $io->block('ModsStore Installation', null, 'fg=black;bg=green', ' ', true);

        // validates if kernel matches with edirectory version
        if (!$this->getContainer()->get('modstore.versioncontrol.service')->isValidModStoreVersion()) {

            $io->writeln(sprintf('<error>[1001] Invalid %s version</error>',
                $this->getContainer()->get('kernel.modstore')->getName()));
            $io->newLine();

            exit;
        }

        // continue with activated plugins
        if (($activated = $this->getContainer()->get('kernel.modstore')->getKernel()->getActivated()) && is_array($activated)) {

            // gets current installed ModStores
            $installed = [];

            // run all base commands for all plugins
            foreach ($activated as $plugin) {

                if ($this->getContainer()->get('modstore.versioncontrol.service')->isValidPluginVersion($plugin)) {

                    if (!$this->isInstalledPlugin($plugin) || $this->hasUpgrade($input, $plugin)) {

                        // add ModStore as installed and increments counter
                        $installed[] = $plugin;
                        $this->incrementInstalledCounter();

                        $command = $plugin->getInstallCommand($input, $output);

                        // execute needed method from command and get before command instructions
                        $command->executeBeforeCommand();
                        $this->addRequired('beforeCommand', $command->getData('beforeCommand'));

                        // execute needed method from command
                        $command->executeCommand();

                        // execute needed method from command and get after command instructions
                        $command->executeAfterCommand();
                        $this->addRequired('afterCommand', $command->getData('afterCommand'));
                    }

                } else {

                    $io->writeln(sprintf('<error>[1002] Invalid %s require version</error>',
                        $plugin->getComposerMetadata('description')));
                    $io->newLine();

                }

            }

            // saves installed ModStores
            $this->updateInstalled($installed);

            // run after installation commands
            $this->executeAfterRequired($input, $output);
        }

        // alert if any ModStores were installed or not
        if ($this->getInstalledCounter()) {

            $io->writeln(sprintf('<comment>Installation Finished</comment>'));
            $io->newLine();
            exit;

        }

        $io->writeln(sprintf('<comment>No ModStores to install</comment>'));
        $io->newLine();
    }

    /**
     * Verify if plugins is installed
     *
     * @param $plugin
     * @return bool
     */
    private function isInstalledPlugin($plugin)
    {
        return in_array($plugin->getQualifiedNamespace(),
            $this->getContainer()->get('kernel.modstore')->getKernel()->getInstalled(true));
    }

    /**
     * Compare versions for upgrade need verification
     *
     * @param $input
     * @param $plugin
     * @return bool|mixed
     */
    private function hasUpgrade(InputInterface $input, $plugin)
    {
        $installed = $this->getContainer()->get('kernel.modstore')->getKernel()->getVersionLock();

        if (!array_key_exists($plugin->getName(), $installed)) {
            return false;
        }

        if ($input->getOption('force-install')) {

            return $this->getContainer()->get('modstore.versioncontrol.service')
                ->compareAGreaterEqual($plugin->getComposerMetadata('version'),
                    $installed[$plugin->getName()]['version']);

        } else if ($input->getOption('upgrade')) {

            return $this->getContainer()->get('modstore.versioncontrol.service')
                ->compareAGreater($plugin->getComposerMetadata('version'),
                    $installed[$plugin->getName()]['version']);

        }
    }

    /**
     * Increment total installed
     */
    private function incrementInstalledCounter()
    {
        $this->countInstalled++;
    }

    /**
     * Add command(s) to queue
     *
     * @param $queue
     * @param $commands
     */
    private function addRequired($queue, $commands)
    {
        // wrap commands to an array if it is not a list of items
        !is_array($commands) and $commands = [$commands => null];

        foreach ($commands as $command => $params) {

            // copy reference of determined queue command to a pointer
            $itemQueue = &$this->requiredList[$queue][$command];

            // set item is not set, initialize it as null
            !isset($itemQueue) and $itemQueue = null;

            if (!empty($params)) {

                // wrap params to an array if it is not a list of items
                !is_array($params) and $params = [$params => null];

                // initialize item params it not set
                empty($itemQueue) and $itemQueue = [];

                $itemQueue = array_merge_recursive($itemQueue, $params);
            }

        }
    }

    /**
     * Update installed cache
     *
     * @param $installed
     */
    private function updateInstalled($installed)
    {
        if ($this->getInstalledCounter()) {

            $this->getContainer()->get('modstore.autoloader.cache.service')->saveInstalled($installed);

        }
    }

    /**
     * Return total installed
     *
     * @return int
     */
    private function getInstalledCounter()
    {
        return $this->countInstalled;
    }

    /**
     * Execute required commands
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     */
    private function executeAfterRequired(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('execute-required')) {
            return;
        }

        // execute defined commands
        foreach ($this->getRequiredCommandsWorkflow() as $command => $params) {

            // initialize process builder
            $processBuilder = $this->getBaseCommand($input, $output);

            switch ($command) {

                case 'gulp-frontend':
                    $processBuilder = new ProcessBuilder();
                    $processBuilder->enableOutput()
                        ->setTimeout(null)
                        ->add('npm')
                        ->add('run')
                        ->add('gulp-frontend');
                    break;

                case 'assets:install':
                    $processBuilder->add('assets:install');
                    $processBuilder->add('--symlink');
                    $processBuilder->add('--relative');
                    break;

                case 'cache:clear':
                    $processBuilder->add('cache:clear');
                    break;

                case 'edirectory:sync':
                    $processBuilder->add('edirectory:synchronize');
                    $processBuilder->add('--recreate-index');
                    $processBuilder->add('--all-domains');
                    if ($this->getMultiDomain()->getOriginalActiveHost()) {
                        $processBuilder->add('--domain='.$this->getMultiDomain()->getOriginalActiveHost());
                    }
                    break;

                case 'migrate:main':
                    $processBuilder->add('edirectory:migrate');
                    $processBuilder->add('--db-main');
                    if ($this->getMultiDomain()->getOriginalActiveHost()) {
                        $processBuilder->add('--domain='.$this->getMultiDomain()->getOriginalActiveHost());
                    }
                    break;

                case 'migrate:domain':
                    $processBuilder->add('edirectory:migrate');
                    $processBuilder->add('--all-domains');
                    if ($this->getMultiDomain()->getOriginalActiveHost()) {
                        $processBuilder->add('--domain='.$this->getMultiDomain()->getOriginalActiveHost());
                    }
                    break;

                case 'fixture:main':
                    if (is_array($params['fixtures'])) {
                        $processBuilder->add('edirectory:fixture');
                        $processBuilder->add('--db-main');
                        foreach ($params['fixtures'] as $fixture) {
                            $processBuilder->add('--fixtures='.$fixture);
                        }
                        if ($this->getMultiDomain()->getOriginalActiveHost()) {
                            $processBuilder->add('--domain='.$this->getMultiDomain()->getOriginalActiveHost());
                        }
                    }
                    break;

                case 'fixture:domain':
                    if (is_array($params['fixtures'])) {
                        $processBuilder->add('edirectory:fixture');
                        $processBuilder->add('--all-domains');
                        foreach ($params['fixtures'] as $fixture) {
                            $processBuilder->add('--fixtures='.$fixture);
                        }

                        if ($this->getMultiDomain()->getOriginalActiveHost()) {
                            $processBuilder->add('--domain='.$this->getMultiDomain()->getOriginalActiveHost());
                        }
                    }
                    break;
            }

            // run built command
            $processBuilder
                ->add('--env='.$input->getOption('env'))
                ->getProcess()
                ->run(function ($type, $buffer) {
                    echo $buffer;
                });

        }
    }

    /**
     * Returns array of priority of sub-commands workflow
     *
     * @return array
     */
    private function getRequiredCommandsWorkflow()
    {
        $queue = [];

        // setup command list
        $list = $this->requiredList['afterCommand'];

        // sorter commands as they depend of each other
        array_key_exists('cache:clear', $list) and $queue['cache:clear'] = $list['cache:clear'];
        array_key_exists('migrate:main', $list) and $queue['migrate:main'] = $list['migrate:main'];
        array_key_exists('migrate:domain', $list) and $queue['migrate:domain'] = $list['migrate:domain'];
        array_key_exists('fixture:main', $list) and $queue['fixture:main'] = $list['fixture:main'];
        array_key_exists('fixture:domain', $list) and $queue['fixture:domain'] = $list['fixture:domain'];
        array_key_exists('edirectory:sync', $list) and $queue['edirectory:sync'] = $list['edirectory:sync'];
        array_key_exists('assets:install', $list) and $queue['assets:install'] = $list['assets:install'];
        array_key_exists('gulp-frontend', $list) and $queue['gulp-frontend'] = $list['gulp-frontend'];

        return $queue;
    }

    /**
     * Create base ProcessBuilder
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return ProcessBuilder
     */
    private function getBaseCommand(InputInterface $input, OutputInterface $output)
    {
        $processBuilder = new ProcessBuilder();

        return $processBuilder
            ->enableOutput()
            ->setPrefix('app/console')
            ->setTimeout(null);
    }
}