<?php

declare(strict_types=1);

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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('registry');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                // persistence engine: doctrine (default) or redis
                ->enumNode('engine')
                    ->values(['doctrine', 'redis'])
                    ->defaultValue('doctrine')
                ->end()
                ->arrayNode('globals')
                    ->addDefaultsIfNotSet()
                    ->children()
                        // default registry key-value file
                        ->scalarNode('default_values')
                            ->defaultNull()
                            // ->defaultValue('%kernel.root_dir%/config/registry.yml')
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
                            ->defaultValue('registry')
                        ->end()
                        // service id of the redis client to inject (native \Redis,
                        // Predis\Client, a symfony/cache redis adapter, or the
                        // snc_redis.registry service). Only used when engine = redis.
                        ->scalarNode('client_service')
                            ->cannotBeEmpty()
                            ->defaultValue('snc_redis.registry')
                        ->end()
                    ->end()
                ->end()
                // built-in CRUD controllers (default off, hardened in P5)
                ->arrayNode('ui')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
