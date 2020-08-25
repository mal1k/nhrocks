<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing\Command;

use ArcaSolutions\ModStoresBundle\Command\AbstractEdirectoryModstoresInstallCommand;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle;
use ArcaSolutions\ModStoresBundle\Traits\WorkflowMethodsTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EdirectoryModstoresInstallCommand extends AbstractEdirectoryModstoresInstallCommand
{
    use WorkflowMethodsTrait;

    /**
     * EdirectoryModstoresInstallCommand constructor.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param AbstractPluginBundle $bundle
     */
    public function __construct(InputInterface $input, OutputInterface $output, AbstractPluginBundle $bundle)
    {
        parent::__construct($input, $output, $bundle);

        // enable workflow
        $this->hasWorkflow = true;
        $this->hasAfterWorkflow = true;
    }

    public function commandWorkflow()
    {
        $this->simpleLog('[-] Add elastic search index');
        $this->overwriteIndexCreation(['mappings', 'event', 'properties', 'listingId'], ['type' => 'integer']);

        $this->simpleLog('[-] Copy migrates files');
        $this->copyMigrations($this->bundle->getName());

        $this->simpleLog('[-] Copy SASS files');
        $this->copySass($this->bundle->getName());

        $this->simpleLog('[-] Copy Stub files');
        $this->copyStub($this->bundle->getName(), 'event-tab.php', 'web/sitemgr/content/listing/event.php');
    }

    public function afterCommandWorkflow()
    {
        $this->appendToData('afterCommand', 'cache:clear');
        $this->appendToData('afterCommand', 'gulp-frontend');
        $this->appendToData('afterCommand', 'edirectory:sync');
        $this->appendToData('afterCommand', 'migrate:domain');
        $this->appendToData('afterCommand', [
            'fixture:domain' => [
                'fixtures' => [
                    'src/ArcaSolutions/ModStoresBundle/Plugins/EventAssociationListing/DataFixtures/ORM/Common',
                ],
            ],
        ]);
    }
}
