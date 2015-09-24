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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RegistryExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // apply config globals
        $container->setParameter('registry.globals.engine', $config['globals']['engine']);
        $container->setParameter('registry.globals.defaultvalues', $config['globals']['defaultvalues']);
        $container->setParameter('registry.globals.delimiter', $config['globals']['delimiter']);

        if ('doctrine' === $config['globals']['engine']) {
            // apply config doctrine
        } elseif ('redis' === $config['globals']['engine']) {
            // apply config redis
            $container->setParameter('registry.redis.alias', $config['redis']['alias']);
            $container->setParameter('registry.redis.prefix', $config['redis']['prefix']);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
