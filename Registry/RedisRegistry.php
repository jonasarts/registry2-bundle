<?php

/*
 * This file is part of the jonasarts Registry bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\RegistryBundle\Registry;

use Symfony\Component\DependencyInjection\ContainerInterface;
use jonasarts\Bundle\RegistryBundle\Engines\RedisRegistryEngine;
use jonasarts\Bundle\RegistryBundle\Registry\AbstractRegistry;
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;

/**
 * RedisRegistry.
 * 
 * Implementation of AbstractRegistry using redis for persistence.
 */
class RedisRegistry extends AbstractRegistry implements RegistryInterface
{
    /**
     * Constructor
     */
    public function __construct(ContainerInterface $container, $redis)
    {
        parent::__construct($container);

        // create the engine
        $this->engine = new RedisRegistryEngine($container, $redis);
    }
}
