<?php

/*
 * This file is part of the jonasarts Registry bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\RegistryBundle\Factory;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * RegistryEngineFactory.
 */
class RegistryEngineFactory
{
    /**
     * @param string             $engine_type
     * @param ContainerInterface $container
     * @return RegistryEngineInterface
     * @throws Exception
     */
    public static function build($engine_type, ContainerInterface $container)
    {
        $engine_class = 'jonasarts\\Bundle\\RegistryBundle\\Engines\\'.ucwords($engine_type).'RegistryEngine';

        if (class_exists($engine_class)) {
            return new $engine_class($container);
        } else {
            throw new \Exception(sprintf('Invalid engine type given. (%s)', $engine_class));
        }
    }
}
