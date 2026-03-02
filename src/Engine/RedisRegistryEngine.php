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
 * RedisRegistryEngine
 */
class RedisRegistryEngine implements RegistryEngineInterface
{
    private const REGISTRY_TYPE = 'registry';

    private const SYSTEM_TYPE = 'system';

    /**
     * @var object phpredis client or predis client
     */
    private object $redis;

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
     * @return string
     */
    private function getHashKey(string $key, ?int $user_id = null): string
    {
        $scope = $user_id === null ? 'system' : "registry{$this->delimiter}{$user_id}";

        return "{$this->prefix}{$this->delimiter}{$scope}{$this->delimiter}{$key}";

        /*
        if (is_null($user_id)) {
            return $this->prefix.$this->delimiter.'system'.$this->delimiter.$key;
        } else {
            return $this->prefix.$this->delimiter.'registry'.$this->delimiter.(string) $user_id.$this->delimiter.$key;
        }
        */
    }

    /**
     * Constructor.
     *
     * @param object $redis
     * @param string $registry_prefix
     * @param string $registry_delimiter
     */
    public function __construct(object $redis, string $registry_prefix, string $registry_delimiter)
    {
        if ($registry_delimiter === '') {
            throw new \InvalidArgumentException('registry_delimiter must be non-empty');
        }
        if (
            !method_exists($redis, 'hExists')
            || !method_exists($redis, 'hDel')
            || !method_exists($redis, 'hGet')
            || !method_exists($redis, 'hSet')
            || !method_exists($redis, 'hGetAll')
            || !method_exists($redis, 'keys')
        ) {
            throw new \InvalidArgumentException('Unsupported redis client');
        }

        $this->redis = $redis;
        $this->prefix = $registry_prefix;
        $this->delimiter = $registry_delimiter;
    }

    /**
     * exists
     *
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param RegistryKeyType $type
     * @return bool
     */
    public function registryExists(int $user_id, string $key, string $name, RegistryKeyType $type): bool
    {
        return $this->redis->hExists($this->getHashKey($key, $user_id), $name.$this->delimiter.$type) > 0;
    }

    /**
     * del
     *
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param RegistryKeyType $type
     * @return bool
     */
    public function registryDelete(int $user_id, string $key, string $name, RegistryKeyType $type): bool
    {
        // false if failure, 0 if doesnt exist, long number of deleted keys
        $r = $this->redis->hDel($this->getHashKey($key, $user_id), $name.$this->delimiter.$type);

        return ($r != false) && ($r > 0);
    }

    /**
     * get
     *
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param RegistryKeyType $type
     * @return bool|string
     */
    public function registryRead(int $user_id, string $key, string $name, RegistryKeyType $type): bool|string
    {
        $value = $this->redis->hGet($this->getHashKey($key, $user_id), $name.$this->delimiter.$type);

        return is_string($value) ? $value : false;
    }

    /**
     * set
     *
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param RegistryKeyType $type
     * @param mixed $value
     * @return bool
     * @throws \JsonException
     */
    public function registryWrite(int $user_id, string $key, string $name, RegistryKeyType $type, mixed $value): bool
    {
        return $this->redis->hSet($this->getHashKey($key, $user_id), $name.$this->delimiter.$type, $this->stringify($value)) !== false;
    }

    /**
     * @return array<int, RegKey>
     * @throws \RedisException
     */
    public function registryAll(): array
    {
        $prefix = $this->prefix;
        /** @var non-empty-string $delimiter */
        $delimiter = $this->delimiter;

        /** @var array<int, string> $keys */
        $keys = $this->redis->keys($prefix.$delimiter.'registry'.$delimiter.'*');

        $entities = [];

        foreach ($keys as $key) {
            /** @var array<string, string> $values */
            $values = $this->redis->hGetAll($key);
            foreach ($values as $name => $value) {
                $k = explode($delimiter, $key, 4);
                $n = explode($delimiter, $name);

                $array = [];
                $array['user_id'] = (int) $k[2];
                $array['key'] = $k[3];
                $array['name'] = $n[0];
                $array['type'] = $n[1];
                $array['value'] = $value;

                $entities[] = RegKey::fromArray($array);
            }
        }

        return $entities;
    }

    /** exists
     * @param string $key
     * @param string $name
     * @param RegistryKeyType $type
     * @return bool
     */
    public function systemExists(string $key, string $name, RegistryKeyType $type): bool
    {
        return $this->redis->hExists($this->getHashKey($key), $name.$this->delimiter.$type) > 0;
    }

    /**
     * del
     *
     * @param string $key
     * @param string $name
     * @param RegistryKeyType $type
     * @return bool
     */
    public function systemDelete(string $key, string $name, RegistryKeyType $type): bool
    {
        // false if failure, 0 if doesnt exist, long number of deleted keys
        $r = $this->redis->hDel($this->getHashKey($key), $name.$this->delimiter.$type);

        return ($r !== false) && ($r > 0);
    }

    /**
     * get
     *
     * @param string $key
     * @param string $name
     * @param RegistryKeyType $type
     * @return bool|string
     */
    public function systemRead(string $key, string $name, RegistryKeyType $type): bool|string
    {
        $value = $this->redis->hGet($this->getHashKey($key), $name.$this->delimiter.$type);

        return is_string($value) ? $value : false;
    }

    // set

    /**
     * @param string $key
     * @param string $name
     * @param RegistryKeyType $type
     * @param mixed $value
     * @return bool
     * @throws \JsonException
     */
    public function systemWrite(string $key, string $name, RegistryKeyType $type, mixed $value): bool
    {
        return $this->redis->hSet($this->getHashKey($key), $name.$this->delimiter.$type, $this->stringify($value)) !== false;
    }

    /**
     * @return array<int, SysKey>
     * @throws \RedisException
     */
    public function systemAll(): array
    {
        $prefix = $this->prefix;
        /** @var non-empty-string $delimiter */
        $delimiter = $this->delimiter;

        /** @var array<int, string> $keys */
        $keys = $this->redis->keys($prefix.$delimiter.'system'.$delimiter.'*');

        $entities = [];

        foreach ($keys as $key) {
            /** @var array<string, string> $values */
            $values = $this->redis->hGetAll($key);
            foreach ($values as $name => $value) {
                $k = explode($delimiter, $key, 3);
                $n = explode($delimiter, $name);

                $array = [];
                $array['key'] = $k[2];
                $array['name'] = $n[0];
                $array['type'] = $n[1];
                $array['value'] = $value;

                $entities[] = SysKey::fromArray($array);
            }
        }

        return $entities;
    }

    /**
     * @param mixed $value
     * @return string
     * @throws \JsonException
     */
    private function stringify(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value) || is_bool($value) || $value === null) {
            return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
