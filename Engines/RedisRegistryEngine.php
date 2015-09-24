<?php

/*
 * This file is part of the jonasarts Registry bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\RegistryBundle\Engines;

use Symfony\Component\DependencyInjection\ContainerInterface;
use jonasarts\Bundle\RegistryBundle\Interfaces\AbstractRegistryInterface;

class RedisRegistryEngine implements AbstractRegistryInterface
{
    // redis hash key part for registry keys
    const REGISTRY_TYPE = 'registry';
    // redis hash key part for system keys
    const SYSTEM_TYPE = 'system';

    // phpredis client
    private $redis;

    private $redis_prefix;

    private $delimiter;

    /**
     * @param string $key
     * @param int    $user_id optional
     *
     * @return string
     */
    private function getHashKey($key, $user_id = null)
    {
        if (is_null($user_id)) {
            return $this->redis_prefix.static::SYSTEM_TYPE.$this->delimiter.$key;
        } else {
            return $this->redis_prefix.static::REGISTRY_TYPE.$this->delimiter.(string) $user_id.$this->delimiter.$key;
        }
    }

    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->delimiter = $container->getParameter('registry.globals.delimiter');
        $alias = $container->getParameter('registry.redis.alias');

        // get redis
        $this->redis = $container->get('snc_redis.'.$alias);

        $this->redis_prefix = $container->getParameter('registry.redis.prefix');

        // append delimiter to prefix
        $this->redis_prefix .= $this->delimiter;
    }

    // exists
    public function registryExists($user_id, $key, $name, $type)
    {
        return $this->redis->hExists($this->getHashKey($key, $user_id), $name.$this->delimiter.$type) > 0;
    }

    // del
    public function registryDelete($user_id, $key, $name, $type)
    {
        return $this->redis->hDel($this->getHashKey($key, $user_id), $name.$this->delimiter.$type) > 0;
    }

    // get
    public function registryRead($user_id, $key, $name, $type)
    {
        return $this->redis->hGet($this->getHashKey($key, $user_id), $name.$this->delimiter.$type);
    }

    // set
    public function registryWrite($user_id, $key, $name, $type, $value)
    {
        return $this->redis->hSet($this->getHashKey($key, $user_id), $name.$this->delimiter.$type, $value) !== false;
    }

    // exists
    public function systemExists($systemkey, $name, $type)
    {
        return $this->redis->hExists($this->getHashKey($systemkey), $name.$this->delimiter.$type) > 0;
    }

    // del
    public function systemDelete($systemkey, $name, $type)
    {
        return $this->redis->hDel($this->getHashKey($systemkey), $name.$this->delimiter.$type) > 0;
    }

    // get
    public function systemRead($systemkey, $name, $type)
    {
        return $this->redis->hGet($this->getHashKey($systemkey), $name.$this->delimiter.$type);
    }

    // set
    public function systemWrite($systemkey, $name, $type, $value)
    {
        return $this->redis->hSet($this->getHashKey($systemkey), $name.$this->delimiter.$type, $value) !== false;
    }
}
