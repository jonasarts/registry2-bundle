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

use InvalidArgumentException;
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKey as RegKey;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKey as SysKey;
use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use JsonException;
use RedisException;

/**
 * RedisRegistryEngine.
 */
class RedisRegistryEngine implements RegistryEngineInterface
{
    use StringifyValue;

    /**
     * @var object phpredis client or predis client
     */
    private readonly object $redis;

    private readonly string $delimiter;

    private function getHashKey(string $key, ?int $user_id = null): string
    {
        $scope = null === $user_id ? 'system' : sprintf('registry%s%d', $this->delimiter, $user_id);

        return $this->prefix.$this->delimiter.$scope.$this->delimiter.$key;
    }

    /**
     * Constructor.
     */
    public function __construct(object $redis, private readonly string $prefix, string $registry_delimiter)
    {
        if ('' === $registry_delimiter) {
            throw new InvalidArgumentException('registry_delimiter must be non-empty');
        }

        if (
            !method_exists($redis, 'hExists')
            || !method_exists($redis, 'hDel')
            || !method_exists($redis, 'hGet')
            || !method_exists($redis, 'hSet')
            || !method_exists($redis, 'hGetAll')
            || !method_exists($redis, 'keys')
        ) {
            throw new InvalidArgumentException('Unsupported redis client');
        }

        $this->redis = $redis;
        $this->delimiter = $registry_delimiter;
    }

    /**
     * exists.
     */
    public function registryExists(int $user_id, string $key, string $name, RegistryKeyType $type): bool
    {
        return $this->redis->hExists($this->getHashKey($key, $user_id), $name.$this->delimiter.$type->value) > 0;
    }

    /**
     * del.
     */
    public function registryDelete(int $user_id, string $key, string $name, RegistryKeyType $type): bool
    {
        // false if failure, 0 if doesn't exist, long number of deleted keys
        $r = $this->redis->hDel($this->getHashKey($key, $user_id), $name.$this->delimiter.$type->value);

        return (false != $r) && ($r > 0);
    }

    /**
     * get.
     */
    public function registryRead(int $user_id, string $key, string $name, RegistryKeyType $type): bool|string
    {
        $value = $this->redis->hGet($this->getHashKey($key, $user_id), $name.$this->delimiter.$type->value);

        return is_string($value) ? $value : false;
    }

    /**
     * set.
     *
     * @throws JsonException
     */
    public function registryWrite(int $user_id, string $key, string $name, RegistryKeyType $type, mixed $value): bool
    {
        return false !== $this->redis->hSet($this->getHashKey($key, $user_id), $name.$this->delimiter.$type->value, $this->stringify($value));
    }

    /**
     * @return array<int, RegKey>
     *
     * @throws RedisException
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

    /** exists.
     */
    public function systemExists(string $key, string $name, RegistryKeyType $type): bool
    {
        return $this->redis->hExists($this->getHashKey($key), $name.$this->delimiter.$type->value) > 0;
    }

    /**
     * del.
     */
    public function systemDelete(string $key, string $name, RegistryKeyType $type): bool
    {
        // false if failure, 0 if doesn't exist, long number of deleted keys
        $r = $this->redis->hDel($this->getHashKey($key), $name.$this->delimiter.$type->value);

        return (false !== $r) && ($r > 0);
    }

    /**
     * get.
     */
    public function systemRead(string $key, string $name, RegistryKeyType $type): bool|string
    {
        $value = $this->redis->hGet($this->getHashKey($key), $name.$this->delimiter.$type->value);

        return is_string($value) ? $value : false;
    }

    // set
    /**
     * @throws JsonException
     */
    public function systemWrite(string $key, string $name, RegistryKeyType $type, mixed $value): bool
    {
        return false !== $this->redis->hSet($this->getHashKey($key), $name.$this->delimiter.$type->value, $this->stringify($value));
    }

    /**
     * @return array<int, SysKey>
     *
     * @throws RedisException
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
}
