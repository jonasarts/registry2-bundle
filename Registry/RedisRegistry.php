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

namespace jonasarts\Bundle\RegistryBundle\Registry;

use jonasarts\Bundle\RegistryBundle\Engine\RedisRegistryEngine;
use jonasarts\Bundle\RegistryBundle\Registry\AbstractRegistry;

/**
 * RedisRegistry.
 *
 * Implementation of AbstractRegistry using redis for persistence.
 */
class RedisRegistry extends AbstractRegistry implements RedisRegistryInterface
{
    /**
     * Constructor
     */
    public function __construct($redis, string $default_values_filename = null)
    {
        parent::__construct($default_values_filename);

        // create the engine
        $this->engine = new RedisRegistryEngine($container, $redis);
    }
}
