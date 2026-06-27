<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests;

use jonasarts\Bundle\RegistryBundle\Engine\RegistryEngineInterface;
use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use jonasarts\Bundle\RegistryBundle\Registry\AbstractRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub as StubObject;
use PHPUnit\Framework\TestCase;

/**
 * Concrete subclass so we can instantiate AbstractRegistry.
 */
class TestableRegistry extends AbstractRegistry
{
    public function __construct(RegistryEngineInterface $engine, ?string $default_values_filename = null)
    {
        parent::__construct($default_values_filename);
        $this->engine = $engine;
    }
}

class AbstractRegistryTest extends TestCase
{
    private RegistryEngineInterface&StubObject $stubEngine;
    private TestableRegistry $registry;

    protected function setUp(): void
    {
        $this->stubEngine = $this->createStub(RegistryEngineInterface::class);
        $this->registry = new TestableRegistry($this->stubEngine);
    }

    /**
     * Creates a mock engine and rebuilds the registry with it.
     */
    private function useMockEngine(): RegistryEngineInterface&MockObject
    {
        $mock = $this->createMock(RegistryEngineInterface::class);
        $this->registry = new TestableRegistry($mock);
        return $mock;
    }

    // --- optimizeType via registryExists (the only way to observe it) ---

    #[DataProvider('typeAliasProvider')]
    public function testOptimizeTypeAliases(string $alias, RegistryKeyType $expected): void
    {
        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('registryExists')
            ->with(1, 'k', 'n', $expected)
            ->willReturn(true);

        $this->assertTrue($this->registry->registryExists(1, 'k', 'n', $alias));
    }

    /**
     * @return array<string, array{string, RegistryKeyType}>
     */
    public static function typeAliasProvider(): array
    {
        return [
            'i' => ['i', RegistryKeyType::INTEGER],
            'int' => ['int', RegistryKeyType::INTEGER],
            'integer' => ['integer', RegistryKeyType::INTEGER],
            'b' => ['b', RegistryKeyType::BOOLEAN],
            'bln' => ['bln', RegistryKeyType::BOOLEAN],
            'boolean' => ['boolean', RegistryKeyType::BOOLEAN],
            's' => ['s', RegistryKeyType::STRING],
            'str' => ['str', RegistryKeyType::STRING],
            'string' => ['string', RegistryKeyType::STRING],
            'f' => ['f', RegistryKeyType::FLOAT],
            'flt' => ['flt', RegistryKeyType::FLOAT],
            'float' => ['float', RegistryKeyType::FLOAT],
            'd' => ['d', RegistryKeyType::DATE],
            'dat' => ['dat', RegistryKeyType::DATE],
            'date' => ['date', RegistryKeyType::DATE],
            't' => ['t', RegistryKeyType::TIME],
            'tim' => ['tim', RegistryKeyType::TIME],
            'time' => ['time', RegistryKeyType::TIME],
            'a' => ['a', RegistryKeyType::ARRAY],
            'arr' => ['arr', RegistryKeyType::ARRAY],
            'array' => ['array', RegistryKeyType::ARRAY],
            'unknown defaults to s' => ['xyz', RegistryKeyType::STRING],
        ];
    }

    public function testOptimizeTypeAcceptsEnum(): void
    {
        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('registryExists')
            ->with(1, 'k', 'n', RegistryKeyType::INTEGER)
            ->willReturn(true);

        $this->assertTrue($this->registry->registryExists(1, 'k', 'n', RegistryKeyType::INTEGER));
    }

    public function testOptimizeTypeAcceptsAllEnumCases(): void
    {
        foreach (RegistryKeyType::cases() as $case) {
            $engine = $this->useMockEngine();
            $engine->expects($this->once())
                ->method('systemExists')
                ->with('k', 'n', $case)
                ->willReturn(true);

            $this->assertTrue($this->registry->systemExists('k', 'n', $case));
        }
    }

    // --- decodeTypedValue via registryReadDefault ---

    public function testDecodeTypedValueInteger(): void
    {
        $this->stubEngine->method('registryRead')->willReturn('42');

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'i', 0);

        $this->assertSame(42, $result);
    }

    public function testDecodeTypedValueBoolean(): void
    {
        $this->stubEngine->method('registryRead')->willReturn('1');

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'b', false);

        $this->assertTrue($result);
    }

    public function testDecodeTypedValueBooleanFalse(): void
    {
        $this->stubEngine->method('registryRead')->willReturn('');

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'b', true);

        $this->assertFalse($result);
    }

    public function testDecodeTypedValueString(): void
    {
        $this->stubEngine->method('registryRead')->willReturn('hello');

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 's', '');

        $this->assertSame('hello', $result);
    }

    public function testDecodeTypedValueFloat(): void
    {
        $this->stubEngine->method('registryRead')->willReturn('3.14');

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'f', 0.0);

        $this->assertSame(3.14, $result);
    }

    public function testDecodeTypedValueDateNumeric(): void
    {
        $ts = (string) strtotime('2024-01-01');
        $this->stubEngine->method('registryRead')->willReturn($ts);

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'd', 0);

        $this->assertSame((int) $ts, $result);
    }

    public function testDecodeTypedValueDateString(): void
    {
        $this->stubEngine->method('registryRead')->willReturn('2024-01-15');

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'd', 0);

        $this->assertSame(strtotime('2024-01-15'), $result);
    }

    public function testDecodeTypedValueTime(): void
    {
        $ts = (string) strtotime('12:30:00');
        $this->stubEngine->method('registryRead')->willReturn($ts);

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 't', 0);

        $this->assertSame((int) $ts, $result);
    }

    public function testDecodeTypedValueArray(): void
    {
        $arr = ['a' => 1, 'b' => 'two'];
        $this->stubEngine->method('registryRead')->willReturn(json_encode($arr));

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'a', []);

        $this->assertSame($arr, $result);
    }

    // --- normalizeDefaultValue (engine returns false = not found) ---

    public function testNormalizeDefaultValueInteger(): void
    {
        $this->stubEngine->method('registryRead')->willReturn(false);

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'i', 99);

        $this->assertSame(99, $result);
    }

    public function testNormalizeDefaultValueIntegerNonNumeric(): void
    {
        $this->stubEngine->method('registryRead')->willReturn(false);

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'i', 'not-a-number');

        $this->assertSame(0, $result);
    }

    public function testNormalizeDefaultValueBoolean(): void
    {
        $this->stubEngine->method('registryRead')->willReturn(false);

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'b', 1);

        $this->assertTrue($result);
    }

    public function testNormalizeDefaultValueString(): void
    {
        $this->stubEngine->method('registryRead')->willReturn(false);

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 's', 'default');

        $this->assertSame('default', $result);
    }

    public function testNormalizeDefaultValueStringNonScalar(): void
    {
        $this->stubEngine->method('registryRead')->willReturn(false);

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 's', ['not', 'scalar']);

        $this->assertSame('', $result);
    }

    public function testNormalizeDefaultValueFloat(): void
    {
        $this->stubEngine->method('registryRead')->willReturn(false);

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'f', 2.5);

        $this->assertSame(2.5, $result);
    }

    public function testNormalizeDefaultValueFloatNonNumeric(): void
    {
        $this->stubEngine->method('registryRead')->willReturn(false);

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'f', 'nope');

        $this->assertSame(0.0, $result);
    }

    public function testNormalizeDefaultValueDateTimeInterface(): void
    {
        $this->stubEngine->method('registryRead')->willReturn(false);

        $dt = new \DateTimeImmutable('2024-06-15');
        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'd', $dt);

        $this->assertSame($dt, $result);
    }

    public function testNormalizeDefaultValueDateInteger(): void
    {
        $this->stubEngine->method('registryRead')->willReturn(false);

        $ts = strtotime('2024-01-01');
        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'd', $ts);

        $this->assertSame($ts, $result);
    }

    public function testNormalizeDefaultValueDateString(): void
    {
        $this->stubEngine->method('registryRead')->willReturn(false);

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'd', '2024-03-01');

        $this->assertSame(strtotime('2024-03-01'), $result);
    }

    public function testNormalizeDefaultValueArray(): void
    {
        $this->stubEngine->method('registryRead')->willReturn(false);

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'a', ['x' => 1]);

        $this->assertSame(['x' => 1], $result);
    }

    public function testNormalizeDefaultValueArrayNonArray(): void
    {
        $this->stubEngine->method('registryRead')->willReturn(false);

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'a', 'not-array');

        $this->assertSame([], $result);
    }

    public function testNormalizeDefaultValueNull(): void
    {
        $this->stubEngine->method('registryRead')->willReturn(false);

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'i', null);

        $this->assertNull($result);
    }

    // --- Error paths ---

    public function testRegistryWriteThrowsOnDelimiterInName(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('delimiter is not allowed in name');

        $this->registry->registryWrite(1, 'k', 'has:colon', 's', 'v');
    }

    public function testSystemWriteThrowsOnDelimiterInName(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('delimiter is not allowed in name');

        $this->registry->systemWrite('k', 'has:colon', 's', 'v');
    }

    // --- Smart override: user value matching user-0 deletes user entry ---

    public function testRegistryWriteDeletesUserKeyWhenMatchingDefault(): void
    {
        $engine = $this->useMockEngine();

        $engine->method('registryRead')
            ->willReturnCallback(function (int $uid, string $k, string $n, RegistryKeyType $t): bool|string {
                if ($uid === 0) {
                    return '10';
                }
                return false;
            });

        $engine->expects($this->once())
            ->method('registryDelete')
            ->with(1, 'k', 'n', RegistryKeyType::INTEGER)
            ->willReturn(true);

        $result = $this->registry->registryWrite(1, 'k', 'n', 'i', 10);

        $this->assertTrue($result);
    }

    public function testRegistryWriteUser0DoesNotTriggerSmartOverride(): void
    {
        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('registryWrite')
            ->with(0, 'k', 'n', RegistryKeyType::INTEGER, 10)
            ->willReturn(true);

        $result = $this->registry->registryWrite(0, 'k', 'n', 'i', 10);

        $this->assertTrue($result);
    }

    // --- Fallback: user read falls back to user-0 ---

    public function testRegistryReadDefaultFallsBackToUser0(): void
    {
        $callCount = 0;
        $this->stubEngine->method('registryRead')
            ->willReturnCallback(function (int $uid) use (&$callCount): bool|string {
                $callCount++;
                if ($uid === 1) {
                    return false;
                }
                return '42';
            });

        $result = $this->registry->registryReadDefault(1, 'k', 'n', 'i', 0);

        $this->assertSame(42, $result);
        $this->assertSame(2, $callCount, 'Should have called registryRead twice (user, then user-0)');
    }

    // --- Enum acceptance in all method families ---

    public function testRegistryReadWithEnum(): void
    {
        $this->stubEngine->method('registryRead')->willReturn('hello');

        $result = $this->registry->registryRead(1, 'k', 'n', RegistryKeyType::STRING);

        $this->assertSame('hello', $result);
    }

    public function testRegistryDeleteWithEnum(): void
    {
        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('registryDelete')
            ->with(1, 'k', 'n', RegistryKeyType::BOOLEAN)
            ->willReturn(true);

        $this->assertTrue($this->registry->registryDelete(1, 'k', 'n', RegistryKeyType::BOOLEAN));
    }

    public function testSystemReadWithEnum(): void
    {
        $this->stubEngine->method('systemRead')->willReturn('99');

        $result = $this->registry->systemRead('k', 'n', RegistryKeyType::INTEGER);

        $this->assertSame(99, $result);
    }

    public function testSystemWriteWithEnum(): void
    {
        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('systemWrite')
            ->with('k', 'n', RegistryKeyType::FLOAT, 1.5)
            ->willReturn(true);

        $this->assertTrue($this->registry->systemWrite('k', 'n', RegistryKeyType::FLOAT, 1.5));
    }

    public function testSystemDeleteWithEnum(): void
    {
        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('systemDelete')
            ->with('k', 'n', RegistryKeyType::DATE)
            ->willReturn(true);

        $this->assertTrue($this->registry->systemDelete('k', 'n', RegistryKeyType::DATE));
    }

    public function testSystemExistsWithEnum(): void
    {
        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('systemExists')
            ->with('k', 'n', RegistryKeyType::TIME)
            ->willReturn(true);

        $this->assertTrue($this->registry->systemExists('k', 'n', RegistryKeyType::TIME));
    }

    // --- Shortcut aliases delegate correctly ---

    public function testShortcutRe(): void
    {
        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('registryExists')
            ->with(1, 'k', 'n', RegistryKeyType::INTEGER)
            ->willReturn(true);

        $this->assertTrue($this->registry->re(1, 'k', 'n', 'i'));
    }

    public function testShortcutRd(): void
    {
        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('registryDelete')
            ->with(1, 'k', 'n', RegistryKeyType::STRING)
            ->willReturn(true);

        $this->assertTrue($this->registry->rd(1, 'k', 'n', 's'));
    }

    public function testShortcutSe(): void
    {
        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('systemExists')
            ->with('k', 'n', RegistryKeyType::BOOLEAN)
            ->willReturn(false);

        $this->assertFalse($this->registry->se('k', 'n', 'b'));
    }

    public function testShortcutSd(): void
    {
        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('systemDelete')
            ->with('k', 'n', RegistryKeyType::FLOAT)
            ->willReturn(true);

        $this->assertTrue($this->registry->sd('k', 'n', 'f'));
    }

    // --- registryWrite with DateTimeInterface ---

    public function testRegistryWriteDateTimeConvertsToIso(): void
    {
        $dt = new \DateTimeImmutable('2024-06-15T10:30:00+00:00');

        $engine = $this->useMockEngine();
        $engine->method('registryRead')->willReturn(false);
        $engine->expects($this->once())
            ->method('registryWrite')
            ->with(0, 'k', 'n', RegistryKeyType::DATE, $dt->format('c'))
            ->willReturn(true);

        $this->assertTrue($this->registry->registryWrite(0, 'k', 'n', 'd', $dt));
    }

    public function testSystemWriteDateTimeConvertsToIso(): void
    {
        $dt = new \DateTimeImmutable('2024-06-15T10:30:00+00:00');

        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('systemWrite')
            ->with('k', 'n', RegistryKeyType::TIME, $dt->format('c'))
            ->willReturn(true);

        $this->assertTrue($this->registry->systemWrite('k', 'n', 't', $dt));
    }

    // --- registryReadOnce / systemReadOnce ---

    public function testRegistryReadOnceDeletesAfterRead(): void
    {
        $engine = $this->useMockEngine();
        $engine->method('registryRead')->willReturn('val');
        $engine->expects($this->once())
            ->method('registryDelete')
            ->with(1, 'k', 'n', RegistryKeyType::STRING)
            ->willReturn(true);

        $result = $this->registry->registryReadOnce(1, 'k', 'n', 's');

        $this->assertSame('val', $result);
    }

    public function testSystemReadOnceDeletesAfterRead(): void
    {
        $engine = $this->useMockEngine();
        $engine->method('systemRead')->willReturn('val');
        $engine->expects($this->once())
            ->method('systemDelete')
            ->with('k', 'n', RegistryKeyType::STRING)
            ->willReturn(true);

        $result = $this->registry->systemReadOnce('k', 'n', 's');

        $this->assertSame('val', $result);
    }

    // --- registryAll / systemAll ---

    public function testRegistryAllDelegates(): void
    {
        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('registryAll')
            ->willReturn(['a', 'b']);

        $this->assertSame(['a', 'b'], $this->registry->registryAll());
    }

    public function testSystemAllDelegates(): void
    {
        $engine = $this->useMockEngine();
        $engine->expects($this->once())
            ->method('systemAll')
            ->willReturn([]);

        $this->assertSame([], $this->registry->systemAll());
    }
}
