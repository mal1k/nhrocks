<?php

namespace ArcaSolutions\ImportBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('import');

        $rootNode
            ->children()
                ->arrayNode('status')
                    ->isRequired()
                    ->useAttributeAsKey('value')
                    ->prototype('variable')->end()
                ->end()
                ->scalarNode('max_rows')->end()
                ->arrayNode('frontend')
                    ->children()
                        ->arrayNode('extensions')
                            ->prototype('variable')->end()
                        ->end()
                        ->scalarNode('size')
                            ->info('Max file size allowed in MB.')
                        ->end()
                        ->scalarNode('preview_length')
                            ->info('Number of lines that will be displayed on mapping preview step.')
                        ->end()
                        ->scalarNode('xlsx_max_rows')
                            ->info('Max lines allowed on xlsx files.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
