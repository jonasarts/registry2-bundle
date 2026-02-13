<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Registry;

use PHPUnit\Framework\TestCase;

/**
 * Tests registryReadDefault() and systemReadDefault() behavior:
 * - Returns the default value when key does not exist
 * - Correctly casts return values to the requested type
 * - Handles null defaults
 */
class RegistryReadDefaultTest extends TestCase
{
    private InMemoryRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new InMemoryRegistry();
    }

    // --- Registry ReadDefault (no key stored, returns default) ---

    public function testRegistryReadDefaultBooleanReturnsDefault(): void
    {
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'b', true);
        $this->assertSame(true, $result);
    }

    public function testRegistryReadDefaultBooleanFalseReturnsDefault(): void
    {
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'b', false);
        $this->assertSame(false, $result);
    }

    public function testRegistryReadDefaultIntegerReturnsDefault(): void
    {
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'i', 42);
        $this->assertSame(42, $result);
    }

    public function testRegistryReadDefaultStringReturnsDefault(): void
    {
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 's', 'hello');
        $this->assertSame('hello', $result);
    }

    public function testRegistryReadDefaultFloatReturnsDefault(): void
    {
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'f', 3.14);
        $this->assertSame(3.14, $result);
    }

    public function testRegistryReadDefaultDateReturnsTimestamp(): void
    {
        $ts = strtotime('2023-06-15');
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'd', $ts);
        $this->assertSame($ts, $result);
    }

    public function testRegistryReadDefaultDateStringReturnsTimestamp(): void
    {
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'd', '2023-06-15');
        $this->assertSame(strtotime('2023-06-15'), $result);
    }

    public function testRegistryReadDefaultDateTimeReturnsDateTime(): void
    {
        $dt = new \DateTime('2023-06-15');
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'd', $dt);
        $this->assertInstanceOf(\DateTime::class, $result);
        $this->assertEquals($dt, $result);
    }

    public function testRegistryReadDefaultNullReturnsNull(): void
    {
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'i', null);
        $this->assertNull($result);
    }

    public function testRegistryReadDefaultNullForAllTypes(): void
    {
        foreach (['i', 'b', 's', 'f', 'd'] as $type) {
            $result = $this->registry->registryReadDefault(0, 'key', 'name_' . $type, $type, null);
            $this->assertNull($result, "Expected null for type '$type'");
        }
    }

    // --- Registry ReadDefault (key exists, returns stored value cast to type) ---

    public function testRegistryReadDefaultBooleanReturnsStoredValue(): void
    {
        $this->registry->registryWrite(0, 'key', 'name', 'b', true);
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'b', false);
        $this->assertSame(true, $result);
    }

    public function testRegistryReadDefaultIntegerReturnsStoredValue(): void
    {
        $this->registry->registryWrite(0, 'key', 'name', 'i', 99);
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'i', 0);
        $this->assertSame(99, $result);
    }

    public function testRegistryReadDefaultStringReturnsStoredValue(): void
    {
        $this->registry->registryWrite(0, 'key', 'name', 's', 'stored');
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 's', 'default');
        $this->assertSame('stored', $result);
    }

    public function testRegistryReadDefaultFloatReturnsStoredValue(): void
    {
        $this->registry->registryWrite(0, 'key', 'name', 'f', 2.718);
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'f', 0.0);
        $this->assertEqualsWithDelta(2.718, $result, 0.001);
    }

    public function testRegistryReadDefaultDateReturnsStoredTimestamp(): void
    {
        $ts = strtotime('2023-01-01');
        $this->registry->registryWrite(0, 'key', 'name', 'd', $ts);
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'd', strtotime('2000-01-01'));
        $this->assertSame($ts, $result);
    }

    // --- System ReadDefault ---

    public function testSystemReadDefaultBooleanReturnsDefault(): void
    {
        $result = $this->registry->systemReadDefault('key', 'name', 'b', true);
        $this->assertSame(true, $result);
    }

    public function testSystemReadDefaultIntegerReturnsDefault(): void
    {
        $result = $this->registry->systemReadDefault('key', 'name', 'i', 5);
        $this->assertSame(5, $result);
    }

    public function testSystemReadDefaultStringReturnsDefault(): void
    {
        $result = $this->registry->systemReadDefault('key', 'name', 's', 'test');
        $this->assertSame('test', $result);
    }

    public function testSystemReadDefaultFloatReturnsDefault(): void
    {
        $result = $this->registry->systemReadDefault('key', 'name', 'f', 5.5);
        $this->assertSame(5.5, $result);
    }

    public function testSystemReadDefaultDateReturnsDefault(): void
    {
        $ts = strtotime('2013-10-16');
        $result = $this->registry->systemReadDefault('key', 'name', 'd', $ts);
        $this->assertSame($ts, $result);
    }

    public function testSystemReadDefaultNullReturnsNull(): void
    {
        $result = $this->registry->systemReadDefault('key', 'name', 'i', null);
        $this->assertNull($result);
    }

    public function testSystemReadDefaultReturnsStoredValue(): void
    {
        $this->registry->systemWrite('key', 'name', 'i', 77);
        $result = $this->registry->systemReadDefault('key', 'name', 'i', 0);
        $this->assertSame(77, $result);
    }
}
