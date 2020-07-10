<?php

namespace ArcaSolutions\ModStoresBundle\Command;

use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class BaseEdirectoryModstoresInstallCommand
 *
 * @package ArcaSolutions\ModStoresBundle\Kernel
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 * @author José Lourenção <jose.lourencao@arcasolutions.com>
 */
abstract class AbstractEdirectoryModstoresInstallCommand
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var AbstractPluginBundle
     */
    protected $bundle;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var bool
     */
    protected $hasBeforeWorkflow = false;

    /**
     * @var bool
     */
    protected $hasWorkflow = false;

    /**
     * @var bool
     */
    protected $hasAfterWorkflow = false;

    /**
     * @var array
     */
    protected $data = [
        'beforeCommand' => [],
        'afterCommand'  => [],
    ];

    /**
     * BaseEdirectoryModstoresInstallCommand constructor.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param AbstractPluginBundle $bundle
     */
    public function __construct(InputInterface $input, OutputInterface $output, AbstractPluginBundle $bundle)
    {
        $this->input = $input;
        $this->output = $output;
        $this->bundle = $bundle;

        $this->io = new SymfonyStyle($this->input, $this->output);
    }

    /**
     * Execute before workflow to command
     */
    public function executeBeforeCommand()
    {
        // check if command has enabled before workflow
        if ($this->hasBeforeWorkflow) {
            $this->beforeCommandWorkflow();
        }
    }

    /**
     * Default before workflow
     */
    protected function beforeCommandWorkflow()
    {
        // default method is empty
    }

    /**
     * Execute base workflow to command
     */
    public function executeCommand()
    {
        $metadata = $this->bundle->getComposerMetadata();

        // initializes installing process
        $this->getIo()->writeln(sprintf('<comment>Installing %s (%s)...</comment>', $metadata['description'],
            $metadata['version']));

        // check if command has enabled workflow
        if ($this->hasWorkflow) {
            $this->commandWorkflow();
        } else {
            $this->simpleLog('[x] Nothing to execute');
        }

        // finish plugin installation
        $this->getIo()->writeln(sprintf('<info>%s (v%s) Installed Successfully</info>', $metadata['description'],
            $metadata['version']));
        $this->getIo()->newLine();
    }

    /**
     * Retrieve base io interface
     */
    protected function getIo()
    {
        return $this->io;
    }

    /**
     * Default base workflow
     */
    protected function commandWorkflow()
    {
        // default method is empty
    }

    /**
     * Write on console basic text
     */
    protected function simpleLog($text)
    {
        $this->getIo()->writeln($text);
    }

    /**
     * Execute after workflow to command
     */
    public function executeAfterCommand()
    {
        // check if command has enabled after workflow
        if ($this->hasAfterWorkflow) {
            $this->afterCommandWorkflow();
        }
    }

    /**
     * Default after workflow
     */
    protected function afterCommandWorkflow()
    {
        // default method is empty
    }

    /**
     * Retrive all data or by key
     *
     * @param null $key
     * @return array|mixed
     */
    public function getData($key = null)
    {
        if (!is_null($key)) {
            return $this->data[$key];
        }

        return $this->data;
    }

    /**
     * Append value to data by key. Needs to match data schema
     *
     * @param $key
     * @param $value
     */
    protected function appendToData($key, $value)
    {
        if (isset($this->data[$key]) && !empty($value)) {

            // if value it is not a array, encapsulate it and set params as null
            if (!is_array($value)) {
                $value = [$value => null];
            }

            // merge with previous data value
            $this->data[$key] = array_merge_recursive($this->data[$key], $value);
        }
    }
}
