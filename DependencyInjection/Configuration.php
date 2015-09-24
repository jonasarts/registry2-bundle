<?php

/*
 * This file is part of the jonasarts Registry bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\RegistryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('registry');

        $rootNode
            ->children()
                ->arrayNode('globals')
                    ->addDefaultsIfNotSet()
                    ->children()
                        // engine
                        ->enumNode('engine')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->values(array('redis', 'doctrine'))
                            ->defaultValue('redis')
                        ->end()
                        // default registry key-value file
                        ->scalarNode('defaultvalues')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('%kernel.root_dir%/config/registry.yml')
                        ->end()
                        // field delimiter
                        ->scalarNode('delimiter')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue(':')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('redis')
                    ->addDefaultsIfNotSet()
                    ->children()
                        // alias
                        ->scalarNode('alias')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('registry')
                        ->end()
                        // prefix
                        ->scalarNode('prefix')
                            ->defaultValue('registry')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
