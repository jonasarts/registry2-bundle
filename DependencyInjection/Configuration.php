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
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * Constructor
     *
     * @param Boolean $debug Whether to use the debug mode
     */
    public function __construct($debug)
    {
        $this->debug = (Boolean) $debug;
    }
    
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
                        // default registry key-value file
                        ->scalarNode('default_values')
                            ->defaultNull()
                            //->defaultValue('%kernel.root_dir%/config/registry.yml')
                        ->end()
                        // field delimiter
                        ->scalarNode('delimiter')
                            ->cannotBeEmpty()
                            ->defaultValue(':')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('redis')
                    ->addDefaultsIfNotSet()
                    ->children()
                        // prefix
                        ->scalarNode('prefix')
                            ->isRequired()
                            ->defaultValue('registry')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
