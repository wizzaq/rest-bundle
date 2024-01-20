<?php

declare(strict_types=1);

namespace Wizzaq\RestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wizzaq_rest');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('use_resolvers')->defaultTrue()->end()
                ->booleanNode('use_protocols')->defaultTrue()->end()
                ->scalarNode('default_protocol')->defaultNull()->end()
                ->arrayNode('protocols')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('rest')->defaultTrue()->end()
                    ->end()
                ->end()
                ->scalarNode('default_response_section')->defaultNull()->end()
                ->scalarNode('serializer')->defaultNull()->end()
            ->end()->end();

        return $treeBuilder;
    }
}
