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

namespace jonasarts\Bundle\RegistryBundle;

use jonasarts\Bundle\RegistryBundle\Controller\RegistryController;
use jonasarts\Bundle\RegistryBundle\Controller\SystemController;
use jonasarts\Bundle\RegistryBundle\Registry\DoctrineRegistry;
use jonasarts\Bundle\RegistryBundle\Registry\RedisRegistry;
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;
use Override;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Single-class bundle (AbstractBundle): configuration tree and container
 * wiring live here; there is no separate Extension/Configuration class.
 */
class RegistryBundle extends AbstractBundle
{
    /**
     * Use the package root (next to config/ and templates/) as the bundle path.
     */
    #[Override]
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    #[Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                // persistence engine: doctrine (default) or redis
                ->enumNode('engine')
                    ->values(['doctrine', 'redis'])
                    ->defaultValue('doctrine')
                ->end()
                ->arrayNode('globals')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_values')->defaultNull()->end()
                        ->scalarNode('delimiter')->cannotBeEmpty()->defaultValue(':')->end()
                    ->end()
                ->end()
                ->arrayNode('redis')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('prefix')->defaultValue('registry')->end()
                        ->scalarNode('client_service')->cannotBeEmpty()->defaultValue('snc_redis.registry')->end()
                    ->end()
                ->end()
                ->arrayNode('ui')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('base_template')->cannotBeEmpty()->defaultValue('base.html.twig')->end()
                        ->scalarNode('role')->cannotBeEmpty()->defaultValue('ROLE_REGISTRY_ADMIN')->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array{
     *   engine: string,
     *   globals: array{default_values: ?string, delimiter: string},
     *   redis: array{prefix: string, client_service: string},
     *   ui: array{enabled: bool, base_template: string, role: string}
     * } $config
     */
    #[Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->setParameter('registry.globals.default_values', $config['globals']['default_values']);
        $builder->setParameter('registry.globals.delimiter', $config['globals']['delimiter']);
        $builder->setParameter('registry.redis.prefix', $config['redis']['prefix']);
        $builder->setParameter('registry.engine', $config['engine']);
        $builder->setParameter('registry.ui.enabled', $config['ui']['enabled']);

        new YamlFileLoader($builder, new FileLocator(\dirname(__DIR__).'/config'))->load('services.yaml');

        // Register exactly one registry implementation for the chosen engine and
        // bind RegistryInterface to it. Redis is only wired when selected, so
        // there is no hard dependency on a redis client (nor snc/redis-bundle).
        if ('redis' === $config['engine']) {
            $this->registerRedisEngine($builder, $config['redis']['client_service']);
        } else {
            $this->registerDoctrineEngine($builder);
        }

        // Built-in CRUD controllers are wired only when explicitly enabled.
        if ($config['ui']['enabled']) {
            $this->registerUiControllers($builder, $config['ui']['base_template'], $config['ui']['role']);
        }
    }

    private function registerDoctrineEngine(ContainerBuilder $container): void
    {
        $definition = new Definition(DoctrineRegistry::class);
        $definition->setAutowired(true);
        $definition->setAutoconfigured(true);
        $definition->setArgument('$em', new Reference('doctrine.orm.entity_manager'));
        $definition->setArgument('$default_values_filename', '%registry.globals.default_values%');

        $container->setDefinition(DoctrineRegistry::class, $definition);
        $container->setAlias(RegistryInterface::class, DoctrineRegistry::class);
    }

    private function registerRedisEngine(ContainerBuilder $container, string $clientService): void
    {
        $definition = new Definition(RedisRegistry::class);
        $definition->setAutowired(true);
        $definition->setAutoconfigured(true);
        $definition->setArgument('$redis', new Reference($clientService));
        $definition->setArgument('$registry_prefix', '%registry.redis.prefix%');
        $definition->setArgument('$registry_delimiter', '%registry.globals.delimiter%');
        $definition->setArgument('$default_values_filename', '%registry.globals.default_values%');

        $container->setDefinition(RedisRegistry::class, $definition);
        $container->setAlias(RegistryInterface::class, RedisRegistry::class);
    }

    private function registerUiControllers(ContainerBuilder $container, string $baseTemplate, string $adminRole): void
    {
        foreach ([RegistryController::class, SystemController::class] as $controllerClass) {
            $definition = new Definition($controllerClass);
            $definition->setAutowired(true);
            $definition->setAutoconfigured(true);
            $definition->setArgument('$registry', new Reference(RegistryInterface::class));
            $definition->setArgument('$baseTemplate', $baseTemplate);
            $definition->setArgument('$adminRole', $adminRole);

            $container->setDefinition($controllerClass, $definition);
        }
    }
}
