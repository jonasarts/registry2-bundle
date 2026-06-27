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

namespace jonasarts\Bundle\RegistryBundle\Tests;

use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use jonasarts\Bundle\RegistryBundle\Registry\RedisRegistry;
use PHPUnit\Framework\TestCase;
use Redis;
use RedisException;

/**
 * End-to-end integration test for RedisRegistry against a real redis server.
 *
 * Part of the `integration` test suite. It is self-contained (no ordering
 * dependencies) and skips automatically when the phpredis extension is missing
 * or no redis server is reachable. Connection is configured via the REDIS_HOST
 * and REDIS_PORT environment variables (defaults: 127.0.0.1:6379).
 */
class RegistryTest extends TestCase
{
    private const string PREFIX = 'bundle-test';

    private const string DELIMITER = '/';

    private const int USER = 2;

    private Redis $redis;

    private RedisRegistry $registry;

    protected function setUp(): void
    {
        if (!\extension_loaded('redis')) {
            $this->markTestSkipped('phpredis extension not available');
        }

        $host = getenv('REDIS_HOST') ?: '127.0.0.1';
        $port = (int) (getenv('REDIS_PORT') ?: 6379);

        $redis = new Redis();
        try {
            if (!@$redis->connect($host, $port, 1.0)) {
                $this->markTestSkipped(sprintf('redis not reachable at %s:%d', $host, $port));
            }
        } catch (RedisException) {
            $this->markTestSkipped(sprintf('redis not reachable at %s:%d', $host, $port));
        }

        $this->redis = $redis;
        $this->flush();
        $this->registry = new RedisRegistry($redis, self::PREFIX, self::DELIMITER);
    }

    protected function tearDown(): void
    {
        if (isset($this->redis)) {
            $this->flush();
        }
    }

    private function flush(): void
    {
        /** @var array<int, string> $keys */
        $keys = $this->redis->keys(self::PREFIX.self::DELIMITER.'*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }

    public function testStringRoundTrip(): void
    {
        $this->registry->registryWrite(self::USER, 'key', 'str', RegistryKeyType::STRING, 'hello');

        $this->assertTrue($this->registry->registryExists(self::USER, 'key', 'str', RegistryKeyType::STRING));
        $this->assertSame('hello', $this->registry->registryRead(self::USER, 'key', 'str', RegistryKeyType::STRING));
    }

    public function testIntegerRoundTrip(): void
    {
        $this->registry->registryWrite(self::USER, 'key', 'int', RegistryKeyType::INTEGER, 42);

        $this->assertSame(42, $this->registry->registryRead(self::USER, 'key', 'int', RegistryKeyType::INTEGER));
    }

    public function testBooleanRoundTrip(): void
    {
        $this->registry->registryWrite(self::USER, 'key', 'bln_t', RegistryKeyType::BOOLEAN, true);
        $this->registry->registryWrite(self::USER, 'key', 'bln_f', RegistryKeyType::BOOLEAN, false);

        $this->assertTrue($this->registry->registryRead(self::USER, 'key', 'bln_t', RegistryKeyType::BOOLEAN));
        $this->assertFalse($this->registry->registryRead(self::USER, 'key', 'bln_f', RegistryKeyType::BOOLEAN));
    }

    public function testFloatRoundTrip(): void
    {
        $this->registry->registryWrite(self::USER, 'key', 'flt', RegistryKeyType::FLOAT, 0.5);

        $this->assertSame(0.5, $this->registry->registryRead(self::USER, 'key', 'flt', RegistryKeyType::FLOAT));
    }

    public function testArrayRoundTrip(): void
    {
        $data = ['a' => 'b', 'c' => 1];
        $this->registry->registryWrite(self::USER, 'key', 'arr', RegistryKeyType::ARRAY, $data);

        $this->assertSame($data, $this->registry->registryRead(self::USER, 'key', 'arr', RegistryKeyType::ARRAY));
    }

    public function testDateRoundTrip(): void
    {
        $this->registry->registryWrite(self::USER, 'key', 'dat', RegistryKeyType::DATE, '2013-10-16');

        $this->assertSame(
            strtotime('2013-10-16'),
            $this->registry->registryRead(self::USER, 'key', 'dat', RegistryKeyType::DATE),
        );
    }

    public function testUser0FallbackForRegistry(): void
    {
        // value only present for user 0; reading as USER must fall back to it
        $this->registry->registryWrite(0, 'key', 'shared', RegistryKeyType::STRING, 'global');

        $this->assertSame('global', $this->registry->registryRead(self::USER, 'key', 'shared', RegistryKeyType::STRING));
    }

    public function testSystemRoundTrip(): void
    {
        $this->registry->systemWrite('key', 'str', RegistryKeyType::STRING, 'sys');

        $this->assertTrue($this->registry->systemExists('key', 'str', RegistryKeyType::STRING));
        $this->assertSame('sys', $this->registry->systemRead('key', 'str', RegistryKeyType::STRING));
    }

    public function testDeleteRemovesKey(): void
    {
        $this->registry->registryWrite(self::USER, 'key', 'tmp', RegistryKeyType::STRING, 'x');
        $this->assertTrue($this->registry->registryExists(self::USER, 'key', 'tmp', RegistryKeyType::STRING));

        $this->registry->registryDelete(self::USER, 'key', 'tmp', RegistryKeyType::STRING);
        $this->assertFalse($this->registry->registryExists(self::USER, 'key', 'tmp', RegistryKeyType::STRING));
    }
}
