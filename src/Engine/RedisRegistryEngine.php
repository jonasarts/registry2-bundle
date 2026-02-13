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

namespace jonasarts\Bundle\RegistryBundle\Engine;

use jonasarts\Bundle\RegistryBundle\Entity\RegistryKey as RegKey;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKey as SysKey;

/**
 *
 */
class RedisRegistryEngine implements RegistryEngineInterface
{
    // redis hash key part for registry keys
    const REGISTRY_TYPE = 'registry';

    // redis hash key part for system keys
    const SYSTEM_TYPE = 'system';

    /**
     * @var \Redis|\Predis
     *
     * phpredis client
     * or
     * predis client
     */
    private $redis;

    /**
     * @var string
     */
    private string $prefix;

    /**
     * @var string
     */
    private string $delimiter;

    /**
     * @param string $key
     * @param int|null $user_id
     *
     * @return string
     */
    private function getHashKey(string $key, ?int $user_id = null): string
    {
        if (is_null($user_id)) {
            return $this->prefix.$this->delimiter.static::SYSTEM_TYPE.$this->delimiter.$key;
        } else {
            return $this->prefix.$this->delimiter.static::REGISTRY_TYPE.$this->delimiter.(string)$user_id.$this->delimiter.$key;
        }
    }

    /**
     * Constructor.
     *
     * @var $redis \Redis|\Predis
     */
    public function __construct($redis, string $registry_prefix, string $registry_delimiter)
    {
        $this->redis = $redis;
        $this->prefix = $registry_prefix;
        $this->delimiter = $registry_delimiter;
    }

    // exists
    public function registryExists(int $user_id, string $key, string $name, string $type): bool
    {
        return $this->redis->hExists($this->getHashKey($key, $user_id), $name.$this->delimiter.$type) > 0;
    }

    // del
    public function registryDelete(int $user_id, string $key, string $name, string $type): bool
    {
        // false if failure, 0 if doesnt exist, long number of deleted keys
        $r = $this->redis->hDel($this->getHashKey($key, $user_id), $name.$this->delimiter.$type);

        return ($r != false) && ($r > 0);
    }

    // get
    public function registryRead(int $user_id, string $key, string $name, string $type)
    {
        return $this->redis->hGet($this->getHashKey($key, $user_id), $name.$this->delimiter.$type);
    }

    // set
    public function registryWrite(int $user_id, string $key, string $name, string $type, $value): bool
    {
        return $this->redis->hSet($this->getHashKey($key, $user_id), $name.$this->delimiter.$type, $value) !== false;
    }

    /**
     * @return array
     * @throws \RedisException
     */
    public function registryAll(): array
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
    public function systemExists(string $systemkey, string $name, string $type): bool
    {
        return $this->redis->hExists($this->getHashKey($systemkey), $name.$this->delimiter.$type) > 0;
    }

    // del
    public function systemDelete(string $systemkey, string $name, string $type): bool
    {
        // false if failure, 0 if doesnt exist, long number of deleted keys
        $r = $this->redis->hDel($this->getHashKey($systemkey), $name.$this->delimiter.$type) > 0;

        return ($r != false) && ($r > 0);
    }

    // get
    public function systemRead(string $systemkey, string $name, string $type)
    {
        return $this->redis->hGet($this->getHashKey($systemkey), $name.$this->delimiter.$type);
    }

    // set
    public function systemWrite(string $systemkey, string $name, string $type, $value): bool
    {
        return $this->redis->hSet($this->getHashKey($systemkey), $name.$this->delimiter.$type, $value) !== false;
    }

    /**
     * @return array
     * @throws \RedisException
     */
    public function systemAll(): array
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
