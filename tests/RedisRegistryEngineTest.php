<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests;

use InvalidArgumentException;
use jonasarts\Bundle\RegistryBundle\Engine\RedisRegistryEngine;
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKey;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKey;
use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Interface matching the Redis methods required by RedisRegistryEngine.
 * Used solely for mocking in tests.
 */
interface TestRedisClient
{
    public function hExists(string $key, string $field): int|bool;

    public function hDel(string $key, string ...$fields): int|false;

    public function hGet(string $key, string $field): string|false;

    public function hSet(string $key, string $field, string $value): int|false;

    public function hGetAll(string $key): array;

    public function keys(string $pattern): array;
}

class RedisRegistryEngineTest extends TestCase
{
    private const string PREFIX = 'reg';

    private const string DELIMITER = ':';

    private function createEngine(TestRedisClient&MockObject $redis): RedisRegistryEngine
    {
        return new RedisRegistryEngine($redis, self::PREFIX, self::DELIMITER);
    }

    // --- Constructor validation ---

    public function testConstructorRejectsEmptyDelimiter(): void
    {
        $redis = $this->createStub(TestRedisClient::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('registry_delimiter must be non-empty');

        new RedisRegistryEngine($redis, 'prefix', '');
    }

    public function testConstructorRejectsUnsupportedClient(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported redis client');

        new RedisRegistryEngine(new stdClass(), 'prefix', ':');
    }

    // --- Registry: type->value used in field names ---

    public function testRegistryExistsUsesTypeValue(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hExists')
            ->with('reg:registry:1:app', 'theme:s')
            ->willReturn(1);

        $engine = $this->createEngine($redis);

        $this->assertTrue($engine->registryExists(1, 'app', 'theme', RegistryKeyType::STRING));
    }

    public function testRegistryExistsReturnsFalse(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hExists')
            ->with('reg:registry:1:app', 'theme:s')
            ->willReturn(0);

        $engine = $this->createEngine($redis);

        $this->assertFalse($engine->registryExists(1, 'app', 'theme', RegistryKeyType::STRING));
    }

    public function testRegistryReadUsesTypeValue(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hGet')
            ->with('reg:registry:1:app', 'theme:s')
            ->willReturn('dark');

        $engine = $this->createEngine($redis);

        $this->assertSame('dark', $engine->registryRead(1, 'app', 'theme', RegistryKeyType::STRING));
    }

    public function testRegistryReadReturnsFalseWhenNotFound(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hGet')
            ->with('reg:registry:5:k', 'n:i')
            ->willReturn(false);

        $engine = $this->createEngine($redis);

        $this->assertFalse($engine->registryRead(5, 'k', 'n', RegistryKeyType::INTEGER));
    }

    public function testRegistryWriteUsesTypeValue(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hSet')
            ->with('reg:registry:1:app', 'theme:s', 'dark')
            ->willReturn(1);

        $engine = $this->createEngine($redis);

        $this->assertTrue($engine->registryWrite(1, 'app', 'theme', RegistryKeyType::STRING, 'dark'));
    }

    public function testRegistryWriteReturnsFalseOnFailure(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hSet')
            ->willReturn(false);

        $engine = $this->createEngine($redis);

        $this->assertFalse($engine->registryWrite(1, 'k', 'n', RegistryKeyType::STRING, 'v'));
    }

    public function testRegistryDeleteUsesTypeValue(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hDel')
            ->with('reg:registry:1:app', 'theme:s')
            ->willReturn(1);

        $engine = $this->createEngine($redis);

        $this->assertTrue($engine->registryDelete(1, 'app', 'theme', RegistryKeyType::STRING));
    }

    public function testRegistryDeleteReturnsFalseWhenNotFound(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hDel')
            ->willReturn(0);

        $engine = $this->createEngine($redis);

        $this->assertFalse($engine->registryDelete(1, 'k', 'n', RegistryKeyType::STRING));
    }

    public function testRegistryDeleteReturnsFalseOnFailure(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hDel')
            ->willReturn(false);

        $engine = $this->createEngine($redis);

        $this->assertFalse($engine->registryDelete(1, 'k', 'n', RegistryKeyType::STRING));
    }

    // --- Registry: all types use type->value ---

    public function testRegistryReadWithIntegerType(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hGet')
            ->with('reg:registry:1:k', 'n:i')
            ->willReturn('42');

        $engine = $this->createEngine($redis);

        $this->assertSame('42', $engine->registryRead(1, 'k', 'n', RegistryKeyType::INTEGER));
    }

    public function testRegistryReadWithBooleanType(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hGet')
            ->with('reg:registry:1:k', 'n:b')
            ->willReturn('1');

        $engine = $this->createEngine($redis);

        $this->assertSame('1', $engine->registryRead(1, 'k', 'n', RegistryKeyType::BOOLEAN));
    }

    public function testRegistryReadWithFloatType(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hGet')
            ->with('reg:registry:1:k', 'n:f')
            ->willReturn('3.14');

        $engine = $this->createEngine($redis);

        $this->assertSame('3.14', $engine->registryRead(1, 'k', 'n', RegistryKeyType::FLOAT));
    }

    public function testRegistryReadWithDateType(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hGet')
            ->with('reg:registry:1:k', 'n:d')
            ->willReturn('1700000000');

        $engine = $this->createEngine($redis);

        $this->assertSame('1700000000', $engine->registryRead(1, 'k', 'n', RegistryKeyType::DATE));
    }

    public function testRegistryReadWithTimeType(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hGet')
            ->with('reg:registry:1:k', 'n:t')
            ->willReturn('45000');

        $engine = $this->createEngine($redis);

        $this->assertSame('45000', $engine->registryRead(1, 'k', 'n', RegistryKeyType::TIME));
    }

    public function testRegistryReadWithArrayType(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hGet')
            ->with('reg:registry:1:k', 'n:a')
            ->willReturn('{"x":1}');

        $engine = $this->createEngine($redis);

        $this->assertSame('{"x":1}', $engine->registryRead(1, 'k', 'n', RegistryKeyType::ARRAY));
    }

    // --- Registry: stringify ---

    public function testRegistryWriteStringifiesInt(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hSet')
            ->with('reg:registry:1:k', 'n:i', '42')
            ->willReturn(1);

        $engine = $this->createEngine($redis);

        $this->assertTrue($engine->registryWrite(1, 'k', 'n', RegistryKeyType::INTEGER, 42));
    }

    public function testRegistryWriteJsonEncodesArray(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hSet')
            ->with('reg:registry:1:k', 'n:a', '{"x":1}')
            ->willReturn(1);

        $engine = $this->createEngine($redis);

        $this->assertTrue($engine->registryWrite(1, 'k', 'n', RegistryKeyType::ARRAY, ['x' => 1]));
    }

    public function testRegistryWriteStringifiesBool(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hSet')
            ->with('reg:registry:1:k', 'n:b', '1')
            ->willReturn(1);

        $engine = $this->createEngine($redis);

        $this->assertTrue($engine->registryWrite(1, 'k', 'n', RegistryKeyType::BOOLEAN, true));
    }

    public function testRegistryWriteStringifiesNull(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hSet')
            ->with('reg:registry:1:k', 'n:s', '')
            ->willReturn(1);

        $engine = $this->createEngine($redis);

        $this->assertTrue($engine->registryWrite(1, 'k', 'n', RegistryKeyType::STRING, null));
    }

    // --- Registry All ---

    public function testRegistryAllReturnsEntities(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('keys')
            ->with('reg:registry:*')
            ->willReturn(['reg:registry:1:app']);
        $redis->expects($this->once())
            ->method('hGetAll')
            ->with('reg:registry:1:app')
            ->willReturn(['theme:s' => 'dark', 'count:i' => '5']);

        $engine = $this->createEngine($redis);
        $result = $engine->registryAll();

        $this->assertCount(2, $result);
        $this->assertInstanceOf(RegistryKey::class, $result[0]);
        $this->assertSame(1, $result[0]->getUserId());
        $this->assertSame('app', $result[0]->getKey());
        $this->assertSame('theme', $result[0]->getName());
        $this->assertSame(RegistryKeyType::STRING, $result[0]->getType());
        $this->assertSame('dark', $result[0]->getValue());
    }

    public function testRegistryAllReturnsEmptyArray(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('keys')
            ->willReturn([]);

        $engine = $this->createEngine($redis);

        $this->assertSame([], $engine->registryAll());
    }

    // --- System: type->value used in field names ---

    public function testSystemExistsUsesTypeValue(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hExists')
            ->with('reg:system:config', 'debug:b')
            ->willReturn(1);

        $engine = $this->createEngine($redis);

        $this->assertTrue($engine->systemExists('config', 'debug', RegistryKeyType::BOOLEAN));
    }

    public function testSystemExistsReturnsFalse(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hExists')
            ->with('reg:system:config', 'debug:b')
            ->willReturn(0);

        $engine = $this->createEngine($redis);

        $this->assertFalse($engine->systemExists('config', 'debug', RegistryKeyType::BOOLEAN));
    }

    public function testSystemReadUsesTypeValue(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hGet')
            ->with('reg:system:config', 'version:s')
            ->willReturn('2.0');

        $engine = $this->createEngine($redis);

        $this->assertSame('2.0', $engine->systemRead('config', 'version', RegistryKeyType::STRING));
    }

    public function testSystemReadReturnsFalseWhenNotFound(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hGet')
            ->willReturn(false);

        $engine = $this->createEngine($redis);

        $this->assertFalse($engine->systemRead('k', 'n', RegistryKeyType::STRING));
    }

    public function testSystemWriteUsesTypeValue(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hSet')
            ->with('reg:system:config', 'version:s', '2.0')
            ->willReturn(1);

        $engine = $this->createEngine($redis);

        $this->assertTrue($engine->systemWrite('config', 'version', RegistryKeyType::STRING, '2.0'));
    }

    public function testSystemWriteReturnsFalseOnFailure(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hSet')
            ->willReturn(false);

        $engine = $this->createEngine($redis);

        $this->assertFalse($engine->systemWrite('k', 'n', RegistryKeyType::STRING, 'v'));
    }

    public function testSystemDeleteUsesTypeValue(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hDel')
            ->with('reg:system:config', 'debug:b')
            ->willReturn(1);

        $engine = $this->createEngine($redis);

        $this->assertTrue($engine->systemDelete('config', 'debug', RegistryKeyType::BOOLEAN));
    }

    public function testSystemDeleteReturnsFalseWhenNotFound(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hDel')
            ->willReturn(0);

        $engine = $this->createEngine($redis);

        $this->assertFalse($engine->systemDelete('k', 'n', RegistryKeyType::STRING));
    }

    public function testSystemDeleteReturnsFalseOnFailure(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hDel')
            ->willReturn(false);

        $engine = $this->createEngine($redis);

        $this->assertFalse($engine->systemDelete('k', 'n', RegistryKeyType::STRING));
    }

    // --- System All ---

    public function testSystemAllReturnsEntities(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('keys')
            ->with('reg:system:*')
            ->willReturn(['reg:system:config']);
        $redis->expects($this->once())
            ->method('hGetAll')
            ->with('reg:system:config')
            ->willReturn(['version:s' => '2.0', 'timeout:i' => '30']);

        $engine = $this->createEngine($redis);
        $result = $engine->systemAll();

        $this->assertCount(2, $result);
        $this->assertInstanceOf(SystemKey::class, $result[0]);
        $this->assertSame('config', $result[0]->getKey());
        $this->assertSame('version', $result[0]->getName());
        $this->assertSame(RegistryKeyType::STRING, $result[0]->getType());
        $this->assertSame('2.0', $result[0]->getValue());
    }

    public function testSystemAllReturnsEmptyArray(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('keys')
            ->willReturn([]);

        $engine = $this->createEngine($redis);

        $this->assertSame([], $engine->systemAll());
    }

    // --- Hash key format ---

    public function testRegistryHashKeyIncludesUserId(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hGet')
            ->with('reg:registry:42:mykey', 'myname:s')
            ->willReturn('val');

        $engine = $this->createEngine($redis);

        $engine->registryRead(42, 'mykey', 'myname', RegistryKeyType::STRING);
    }

    public function testSystemHashKeyOmitsUserId(): void
    {
        $redis = $this->createMock(TestRedisClient::class);
        $redis->expects($this->once())
            ->method('hGet')
            ->with('reg:system:mykey', 'myname:s')
            ->willReturn('val');

        $engine = $this->createEngine($redis);

        $engine->systemRead('mykey', 'myname', RegistryKeyType::STRING);
    }
}
