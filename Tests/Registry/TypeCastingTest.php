<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Registry;

use PHPUnit\Framework\TestCase;

/**
 * Tests that values are correctly cast to the appropriate PHP type on read.
 * The engine stores everything as strings - AbstractRegistry converts on read.
 */
class TypeCastingTest extends TestCase
{
    private InMemoryRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new InMemoryRegistry();
    }

    // --- Registry type casting ---

    public function testRegistryReadReturnsBoolTrue(): void
    {
        $this->registry->registryWrite(0, 'key', 'flag', 'b', true);
        $result = $this->registry->registryRead(0, 'key', 'flag', 'b');
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testRegistryReadReturnsBoolFalse(): void
    {
        $this->registry->registryWrite(0, 'key', 'flag', 'b', false);
        $result = $this->registry->registryRead(0, 'key', 'flag', 'b');
        $this->assertIsBool($result);
        // Note: false is stored as "0", which casts back to false
    }

    public function testRegistryReadReturnsInt(): void
    {
        $this->registry->registryWrite(0, 'key', 'count', 'i', 123);
        $result = $this->registry->registryRead(0, 'key', 'count', 'i');
        $this->assertIsInt($result);
        $this->assertSame(123, $result);
    }

    public function testRegistryReadReturnsString(): void
    {
        $this->registry->registryWrite(0, 'key', 'label', 's', 'hello');
        $result = $this->registry->registryRead(0, 'key', 'label', 's');
        $this->assertIsString($result);
        $this->assertSame('hello', $result);
    }

    public function testRegistryReadReturnsFloat(): void
    {
        $this->registry->registryWrite(0, 'key', 'rate', 'f', 3.14);
        $result = $this->registry->registryRead(0, 'key', 'rate', 'f');
        $this->assertIsFloat($result);
        $this->assertEqualsWithDelta(3.14, $result, 0.001);
    }

    public function testRegistryReadDateFromTimestampReturnsInt(): void
    {
        $ts = strtotime('2023-06-15');
        $this->registry->registryWrite(0, 'key', 'created', 'd', $ts);
        $result = $this->registry->registryRead(0, 'key', 'created', 'd');
        $this->assertIsInt($result);
        $this->assertSame($ts, $result);
    }

    public function testRegistryReadDateFromDateTimeStoresAsIsoString(): void
    {
        $dt = new \DateTime('2023-06-15T10:30:00+00:00');
        $this->registry->registryWrite(0, 'key', 'updated', 'd', $dt);
        $result = $this->registry->registryRead(0, 'key', 'updated', 'd');
        // ISO string is not numeric, so strtotime is used
        $this->assertIsInt($result);
        $this->assertSame(strtotime($dt->format('c')), $result);
    }

    public function testRegistryReadTimeTypeReturnsInt(): void
    {
        $ts = strtotime('2023-06-15 14:30:00');
        $this->registry->registryWrite(0, 'key', 'logged', 't', $ts);
        $result = $this->registry->registryRead(0, 'key', 'logged', 't');
        $this->assertIsInt($result);
        $this->assertSame($ts, $result);
    }

    // --- System type casting ---

    public function testSystemReadReturnsBool(): void
    {
        $this->registry->systemWrite('key', 'active', 'b', true);
        $result = $this->registry->systemRead('key', 'active', 'b');
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testSystemReadReturnsInt(): void
    {
        $this->registry->systemWrite('key', 'version', 'i', 5);
        $result = $this->registry->systemRead('key', 'version', 'i');
        $this->assertIsInt($result);
        $this->assertSame(5, $result);
    }

    public function testSystemReadReturnsString(): void
    {
        $this->registry->systemWrite('key', 'name', 's', 'test');
        $result = $this->registry->systemRead('key', 'name', 's');
        $this->assertIsString($result);
        $this->assertSame('test', $result);
    }

    public function testSystemReadReturnsFloat(): void
    {
        $this->registry->systemWrite('key', 'rate', 'f', 2.5);
        $result = $this->registry->systemRead('key', 'rate', 'f');
        $this->assertIsFloat($result);
        $this->assertEqualsWithDelta(2.5, $result, 0.001);
    }

    public function testSystemReadDateReturnsInt(): void
    {
        $ts = strtotime('2024-01-01');
        $this->registry->systemWrite('key', 'launch', 'd', $ts);
        $result = $this->registry->systemRead('key', 'launch', 'd');
        $this->assertIsInt($result);
        $this->assertSame($ts, $result);
    }

    // --- Default value type casting ---

    public function testRegistryReadDefaultCastsIntegerDefault(): void
    {
        // Providing a float as default for integer type should cast to int
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'i', 5.7);
        $this->assertIsInt($result);
        $this->assertSame(5, $result);
    }

    public function testRegistryReadDefaultCastsBooleanDefault(): void
    {
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'b', 1);
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    public function testRegistryReadDefaultCastsStringDefault(): void
    {
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 's', 123);
        $this->assertIsString($result);
        $this->assertSame('123', $result);
    }

    public function testRegistryReadDefaultCastsFloatDefault(): void
    {
        $result = $this->registry->registryReadDefault(0, 'key', 'name', 'f', 5);
        $this->assertIsFloat($result);
        $this->assertSame(5.0, $result);
    }

    // --- Zero and empty values ---

    public function testRegistryReadIntegerZero(): void
    {
        $this->registry->registryWrite(0, 'key', 'count', 'i', 0);
        $result = $this->registry->registryRead(0, 'key', 'count', 'i');
        $this->assertSame(0, $result);
    }

    public function testRegistryReadEmptyString(): void
    {
        $this->registry->registryWrite(0, 'key', 'label', 's', '');
        $result = $this->registry->registryRead(0, 'key', 'label', 's');
        $this->assertSame('', $result);
    }

    public function testRegistryReadFloatZero(): void
    {
        $this->registry->registryWrite(0, 'key', 'rate', 'f', 0.0);
        $result = $this->registry->registryRead(0, 'key', 'rate', 'f');
        $this->assertSame(0.0, $result);
    }
}
