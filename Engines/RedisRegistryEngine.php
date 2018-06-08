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
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKey as RegKey;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKey as SysKey;
use jonasarts\Bundle\RegistryBundle\Registry\AbstractRegistryInterface;

/**
 * 
 */
class RedisRegistryEngine implements AbstractRegistryInterface
{
    // redis hash key part for registry keys
    const REGISTRY_TYPE = 'registry';
    
    // redis hash key part for system keys
    const SYSTEM_TYPE = 'system';

    /**
     * @var \Redis
     * 
     * phpredis client
     * or
     * predis client
     */
    private $redis;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @param string $key
     * @param int $user_id
     *
     * @return string
     */
    private function getHashKey($key, $user_id = null)
    {
        if (is_null($user_id)) {
            return $this->prefix.$this->delimiter.static::SYSTEM_TYPE.$this->delimiter.$key;
        } else {
            return $this->prefix.$this->delimiter.static::REGISTRY_TYPE.$this->delimiter.(string) $user_id.$this->delimiter.$key;
        }
    }

    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container, $redis)
    {
        $this->redis = $redis;
        $this->prefix = $container->getParameter('registry.redis.prefix');
        $this->delimiter = $container->getParameter('registry.globals.delimiter');
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

    /**
     * @return array
     */
    public function registryAll()
    {
        $prefix = $this->prefix;
        $delimiter = $this->delimiter;

        $keys = $this->redis->keys($prefix.$delimiter.'registry'.$delimiter.'*');

        $entities = array();

        foreach ($keys as $key) {
            $values = $this->redis->hGetAll($key);
            foreach ($values as $name => $value) {
                $k = explode($delimiter, $key, 4);
                $n = explode($delimiter, $name);

                $array = array();
                $array['user_id'] = $k[2];
                $array['key'] = $k[3];
                $array['name'] = $n[0];
                $array['type'] = $n[1];
                $array['value'] = $value;

                $entities[] = RegKey::fromArray($array);
            }
        }

        return $entities;
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

    /**
     * @return array
     */
    public function systemAll()
    {
        $prefix = $this->prefix;
        $delimiter = $this->delimiter;
        
        $keys = $this->redis->keys($prefix.$delimiter.'system'.$delimiter.'*');

        $entities = array();

        foreach ($keys as $key) {
            $values = $this->redis->hGetAll($key);
            foreach ($values as $name => $value) {
                $k = explode($delimiter, $key, 3);
                $n = explode($delimiter, $name);

                $array = array();
                $array['key'] = $k[2];
                $array['name'] = $n[0];
                $array['type'] = $n[1];
                $array['value'] = $value;

                $entities[] = SysKey::fromArray($array);
            }
        }

        return $entities;
    }
}
