<?php

namespace ArcaSolutions\ImportBundle\DependencyInjection;


use ArcaSolutions\ImportBundle\Entity\ImportLog;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class ImportExtension
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\DependencyInjection
 * @since 11.3.00
 */
class ImportExtension extends Extension implements PrependExtensionInterface
{

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('import.config', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $defaultConfig = [
            'max_rows' => 10000,
            'status' => [
                ImportLog::STATUS_PENDING      => 'On Queue',
                ImportLog::STATUS_RUNNING      => 'In Progress',
                ImportLog::STATUS_DONE         => 'In Progress',
                ImportLog::STATUS_SYNC         => 'In Progress',
                ImportLog::STATUS_COMPLETED    => 'Completed',
                ImportLog::STATUS_ABORTED      => 'Aborted',
                ImportLog::STATUS_WAITROLLBACK => 'Waiting Rollback',
                ImportLog::STATUS_UNDONE       => 'Undone',
                ImportLog::STATUS_ERROR        => 'Error',
            ],
            'frontend' => [
                'extensions' => ['csv', 'xls', 'xlsx'],
                'size' => 5,
                'preview_length' => 5,
                'xlsx_max_rows' => 5000
            ]
        ];

        // Set status in the config of import
        $container->prependExtensionConfig($this->getAlias(), $defaultConfig);
    }
}
