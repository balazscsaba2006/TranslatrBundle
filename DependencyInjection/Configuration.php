<?php

namespace Evozon\TranslatrBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @package   Evozon\TranslatrBundle\DependencyInjection
 * @author    Balazs Csaba <csaba.balazs@evozon.com>
 * @copyright 2016 Evozon (https://www.evozon.com)
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('evozon_translatr');

        $rootNode
            ->children()
                ->scalarNode('api_key')
                    ->isRequired()
                ->end()
                ->scalarNode('secret')
                    ->isRequired()
                ->end()
                ->scalarNode('project')
                    ->isRequired()
                ->end()
                ->arrayNode('locale_format')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('parts')
                            ->prototype('scalar')
                                ->validate()
                                    ->ifNotInArray(['code', 'locale', 'region'])
                                    ->thenInvalid('Invalid locale format part "%s"')
                                ->end()
                            ->end()
                            ->isRequired()
                            ->defaultValue(['locale'])
                        ->end()
                        ->scalarNode('separator')
                            ->validate()
                                ->ifNotInArray(['_', '-'])
                                ->thenInvalid('Invalid locale format separator "%s"')
                            ->end()
                            ->defaultValue('_')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('mappings')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('sources')
                                ->prototype('scalar')
                                ->end()
                            ->end()
                            ->arrayNode('locales')
                                ->prototype('scalar')
                                ->end()
                            ->end()
                            ->scalarNode('output')
                                ->defaultValue(EvozonTranslatrExtension::DEFAULT_OUTPUT)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
