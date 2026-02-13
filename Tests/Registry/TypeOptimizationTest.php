<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Registry;

use PHPUnit\Framework\TestCase;

/**
 * Tests type alias normalization:
 * - All type aliases ('int', 'integer', 'i') map to the same storage key
 * - Unknown types default to string
 */
class TypeOptimizationTest extends TestCase
{
    private InMemoryRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new InMemoryRegistry();
    }

    // --- Integer aliases ---

    public function testIntegerTypeAliases(): void
    {
        $this->registry->registryWrite(0, 'key', 'val', 'i', 42);

        // All integer aliases should read the same stored value
        $this->assertSame(42, $this->registry->registryRead(0, 'key', 'val', 'i'));
        $this->assertSame(42, $this->registry->registryRead(0, 'key', 'val', 'int'));
        $this->assertSame(42, $this->registry->registryRead(0, 'key', 'val', 'integer'));
    }

    public function testIntegerExistsWithAliases(): void
    {
        $this->registry->registryWrite(0, 'key', 'val', 'integer', 10);

        $this->assertTrue($this->registry->registryExists(0, 'key', 'val', 'i'));
        $this->assertTrue($this->registry->registryExists(0, 'key', 'val', 'int'));
        $this->assertTrue($this->registry->registryExists(0, 'key', 'val', 'integer'));
    }

    // --- Boolean aliases ---

    public function testBooleanTypeAliases(): void
    {
        $this->registry->registryWrite(0, 'key', 'flag', 'b', true);

        $this->assertSame(true, $this->registry->registryRead(0, 'key', 'flag', 'b'));
        $this->assertSame(true, $this->registry->registryRead(0, 'key', 'flag', 'bln'));
        $this->assertSame(true, $this->registry->registryRead(0, 'key', 'flag', 'boolean'));
    }

    // --- String aliases ---

    public function testStringTypeAliases(): void
    {
        $this->registry->registryWrite(0, 'key', 'label', 's', 'test');

        $this->assertSame('test', $this->registry->registryRead(0, 'key', 'label', 's'));
        $this->assertSame('test', $this->registry->registryRead(0, 'key', 'label', 'str'));
        $this->assertSame('test', $this->registry->registryRead(0, 'key', 'label', 'string'));
    }

    // --- Float aliases ---

    public function testFloatTypeAliases(): void
    {
        $this->registry->registryWrite(0, 'key', 'ratio', 'f', 1.5);

        $this->assertEqualsWithDelta(1.5, $this->registry->registryRead(0, 'key', 'ratio', 'f'), 0.001);
        $this->assertEqualsWithDelta(1.5, $this->registry->registryRead(0, 'key', 'ratio', 'flt'), 0.001);
        $this->assertEqualsWithDelta(1.5, $this->registry->registryRead(0, 'key', 'ratio', 'float'), 0.001);
    }

    // --- Date aliases ---

    public function testDateTypeAliases(): void
    {
        $ts = strtotime('2023-01-01');
        $this->registry->registryWrite(0, 'key', 'created', 'd', $ts);

        $this->assertSame($ts, $this->registry->registryRead(0, 'key', 'created', 'd'));
        $this->assertSame($ts, $this->registry->registryRead(0, 'key', 'created', 'dat'));
        $this->assertSame($ts, $this->registry->registryRead(0, 'key', 'created', 'date'));
    }

    // --- Time aliases ---

    public function testTimeTypeAliases(): void
    {
        $ts = strtotime('2023-01-01 12:00:00');
        $this->registry->registryWrite(0, 'key', 'logged', 't', $ts);

        $this->assertSame($ts, $this->registry->registryRead(0, 'key', 'logged', 't'));
        $this->assertSame($ts, $this->registry->registryRead(0, 'key', 'logged', 'tim'));
        $this->assertSame($ts, $this->registry->registryRead(0, 'key', 'logged', 'time'));
    }

    // --- Unknown type defaults to string ---

    public function testUnknownTypeDefaultsToString(): void
    {
        $this->registry->registryWrite(0, 'key', 'val', 'unknown', 'test');

        $result = $this->registry->registryRead(0, 'key', 'val', 'unknown');
        $this->assertSame('test', $result);
    }

    // --- Type aliases work the same for system keys ---

    public function testSystemIntegerTypeAliases(): void
    {
        $this->registry->systemWrite('key', 'val', 'i', 42);

        $this->assertSame(42, $this->registry->systemRead('key', 'val', 'i'));
        $this->assertSame(42, $this->registry->systemRead('key', 'val', 'int'));
        $this->assertSame(42, $this->registry->systemRead('key', 'val', 'integer'));
    }

    public function testSystemBooleanTypeAliases(): void
    {
        $this->registry->systemWrite('key', 'flag', 'boolean', true);

        $this->assertSame(true, $this->registry->systemRead('key', 'flag', 'b'));
        $this->assertSame(true, $this->registry->systemRead('key', 'flag', 'bln'));
        $this->assertSame(true, $this->registry->systemRead('key', 'flag', 'boolean'));
    }

    // --- Type with whitespace is trimmed ---

    public function testTypeWithWhitespaceIsTrimmed(): void
    {
        $this->registry->registryWrite(0, 'key', 'val', ' i ', 42);

        $this->assertSame(42, $this->registry->registryRead(0, 'key', 'val', 'i'));
    }
}
