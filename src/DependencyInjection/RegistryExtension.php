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

use Exception;
use jonasarts\Bundle\RegistryBundle\Registry\DoctrineRegistry;
use jonasarts\Bundle\RegistryBundle\Registry\RedisRegistry;
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;
use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration.
 */
class RegistryExtension extends Extension
{
    /**
     * @param array<int, array<string, mixed>> $configs
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        /** @var array{
         *   engine: string,
         *   globals: array{default_values: ?string, delimiter: string},
         *   redis: array{prefix: string, client_service: string},
         *   ui: array{enabled: bool}
         * } $config
         */
        $config = $this->processConfiguration($configuration, $configs);

        // apply config globals
        $container->setParameter('registry.globals.default_values', $config['globals']['default_values']);
        $container->setParameter('registry.globals.delimiter', $config['globals']['delimiter']);

        // apply config redis
        $container->setParameter('registry.redis.prefix', $config['redis']['prefix']);

        // expose engine + ui flag for downstream wiring
        $container->setParameter('registry.engine', $config['engine']);
        $container->setParameter('registry.ui.enabled', $config['ui']['enabled']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        // Register exactly one registry implementation depending on the chosen
        // engine and bind RegistryInterface to it. The Redis engine is only wired
        // when explicitly selected, so there is no hard dependency on a redis
        // client (and none on snc/redis-bundle).
        if ('redis' === $config['engine']) {
            $this->registerRedisEngine($container, $config['redis']['client_service']);
        } else {
            $this->registerDoctrineEngine($container);
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

    /**
     * Define a custom bundle_alias.
     *
     * {@inheritdoc}
     */
    #[Override]
    public function getAlias(): string
    {
        return 'registry';
    }
}
