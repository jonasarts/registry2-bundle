<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Registry;

use PHPUnit\Framework\TestCase;

/**
 * Tests full CRUD lifecycle for registry and system keys across all data types.
 * Each test is independent (fresh registry per test).
 */
class RegistryCrudTest extends TestCase
{
    private InMemoryRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new InMemoryRegistry();
    }

    // --- Registry: Write + Read + Exists + Delete for each type ---

    public function testRegistryBooleanCrud(): void
    {
        // Write
        $this->assertTrue($this->registry->registryWrite(0, 'app', 'enabled', 'b', true));

        // Exists
        $this->assertTrue($this->registry->registryExists(0, 'app', 'enabled', 'b'));

        // Read
        $result = $this->registry->registryRead(0, 'app', 'enabled', 'b');
        $this->assertSame(true, $result);

        // Delete
        $this->assertTrue($this->registry->registryDelete(0, 'app', 'enabled', 'b'));

        // Verify gone
        $this->assertFalse($this->registry->registryExists(0, 'app', 'enabled', 'b'));
    }

    public function testRegistryIntegerCrud(): void
    {
        $this->assertTrue($this->registry->registryWrite(0, 'app', 'count', 'i', 42));
        $this->assertTrue($this->registry->registryExists(0, 'app', 'count', 'i'));

        $result = $this->registry->registryRead(0, 'app', 'count', 'i');
        $this->assertSame(42, $result);

        $this->assertTrue($this->registry->registryDelete(0, 'app', 'count', 'i'));
        $this->assertFalse($this->registry->registryExists(0, 'app', 'count', 'i'));
    }

    public function testRegistryStringCrud(): void
    {
        $this->assertTrue($this->registry->registryWrite(0, 'app', 'label', 's', 'hello world'));
        $this->assertTrue($this->registry->registryExists(0, 'app', 'label', 's'));

        $result = $this->registry->registryRead(0, 'app', 'label', 's');
        $this->assertSame('hello world', $result);

        $this->assertTrue($this->registry->registryDelete(0, 'app', 'label', 's'));
        $this->assertFalse($this->registry->registryExists(0, 'app', 'label', 's'));
    }

    public function testRegistryFloatCrud(): void
    {
        $this->assertTrue($this->registry->registryWrite(0, 'app', 'ratio', 'f', 0.75));
        $this->assertTrue($this->registry->registryExists(0, 'app', 'ratio', 'f'));

        $result = $this->registry->registryRead(0, 'app', 'ratio', 'f');
        $this->assertEqualsWithDelta(0.75, $result, 0.001);

        $this->assertTrue($this->registry->registryDelete(0, 'app', 'ratio', 'f'));
        $this->assertFalse($this->registry->registryExists(0, 'app', 'ratio', 'f'));
    }

    public function testRegistryDateCrud(): void
    {
        $ts = strtotime('2023-06-15');
        $this->assertTrue($this->registry->registryWrite(0, 'app', 'created', 'd', $ts));
        $this->assertTrue($this->registry->registryExists(0, 'app', 'created', 'd'));

        $result = $this->registry->registryRead(0, 'app', 'created', 'd');
        $this->assertSame($ts, $result);

        $this->assertTrue($this->registry->registryDelete(0, 'app', 'created', 'd'));
        $this->assertFalse($this->registry->registryExists(0, 'app', 'created', 'd'));
    }

    public function testRegistryDateTimeCrud(): void
    {
        $dt = new \DateTime('2023-06-15T12:00:00');
        $this->assertTrue($this->registry->registryWrite(0, 'app', 'updated', 'd', $dt));
        $this->assertTrue($this->registry->registryExists(0, 'app', 'updated', 'd'));

        $result = $this->registry->registryRead(0, 'app', 'updated', 'd');
        // DateTime is stored as ISO string, read back as strtotime result
        $this->assertIsInt($result);

        $this->assertTrue($this->registry->registryDelete(0, 'app', 'updated', 'd'));
    }

    // --- Registry: Overwrite existing value ---

    public function testRegistryWriteOverwritesExistingValue(): void
    {
        $this->registry->registryWrite(0, 'app', 'val', 'i', 10);
        $this->assertSame(10, $this->registry->registryRead(0, 'app', 'val', 'i'));

        $this->registry->registryWrite(0, 'app', 'val', 'i', 20);
        $this->assertSame(20, $this->registry->registryRead(0, 'app', 'val', 'i'));
    }

    // --- Registry: Delete non-existent key ---

    public function testRegistryDeleteNonExistentReturnsFalse(): void
    {
        $result = $this->registry->registryDelete(0, 'nonexistent', 'name', 'i');
        $this->assertFalse($result);
    }

    // --- Registry: Read non-existent key returns null ---

    public function testRegistryReadNonExistentReturnsNull(): void
    {
        $result = $this->registry->registryRead(0, 'nonexistent', 'name', 's');
        $this->assertNull($result);
    }

    // --- Registry: Exists for non-existent key ---

    public function testRegistryExistsNonExistentReturnsFalse(): void
    {
        $this->assertFalse($this->registry->registryExists(0, 'nonexistent', 'name', 'i'));
    }

    // --- System: CRUD for each type ---

    public function testSystemBooleanCrud(): void
    {
        $this->assertTrue($this->registry->systemWrite('app', 'feature', 'b', true));
        $this->assertTrue($this->registry->systemExists('app', 'feature', 'b'));

        $result = $this->registry->systemRead('app', 'feature', 'b');
        $this->assertSame(true, $result);

        $this->assertTrue($this->registry->systemDelete('app', 'feature', 'b'));
        $this->assertFalse($this->registry->systemExists('app', 'feature', 'b'));
    }

    public function testSystemIntegerCrud(): void
    {
        $this->assertTrue($this->registry->systemWrite('app', 'version', 'i', 3));
        $this->assertTrue($this->registry->systemExists('app', 'version', 'i'));

        $this->assertSame(3, $this->registry->systemRead('app', 'version', 'i'));

        $this->assertTrue($this->registry->systemDelete('app', 'version', 'i'));
        $this->assertFalse($this->registry->systemExists('app', 'version', 'i'));
    }

    public function testSystemStringCrud(): void
    {
        $this->assertTrue($this->registry->systemWrite('app', 'name', 's', 'MyApp'));
        $this->assertTrue($this->registry->systemExists('app', 'name', 's'));

        $this->assertSame('MyApp', $this->registry->systemRead('app', 'name', 's'));

        $this->assertTrue($this->registry->systemDelete('app', 'name', 's'));
        $this->assertFalse($this->registry->systemExists('app', 'name', 's'));
    }

    public function testSystemFloatCrud(): void
    {
        $this->assertTrue($this->registry->systemWrite('app', 'rate', 'f', 0.95));
        $this->assertTrue($this->registry->systemExists('app', 'rate', 'f'));

        $this->assertEqualsWithDelta(0.95, $this->registry->systemRead('app', 'rate', 'f'), 0.001);

        $this->assertTrue($this->registry->systemDelete('app', 'rate', 'f'));
    }

    public function testSystemDateCrud(): void
    {
        $ts = strtotime('2024-01-01');
        $this->assertTrue($this->registry->systemWrite('app', 'launch', 'd', $ts));
        $this->assertTrue($this->registry->systemExists('app', 'launch', 'd'));

        $this->assertSame($ts, $this->registry->systemRead('app', 'launch', 'd'));

        $this->assertTrue($this->registry->systemDelete('app', 'launch', 'd'));
    }

    public function testSystemWriteOverwritesExistingValue(): void
    {
        $this->registry->systemWrite('app', 'val', 'i', 10);
        $this->assertSame(10, $this->registry->systemRead('app', 'val', 'i'));

        $this->registry->systemWrite('app', 'val', 'i', 20);
        $this->assertSame(20, $this->registry->systemRead('app', 'val', 'i'));
    }

    public function testSystemDeleteNonExistentReturnsFalse(): void
    {
        $this->assertFalse($this->registry->systemDelete('nonexistent', 'name', 'i'));
    }

    public function testSystemReadNonExistentReturnsNull(): void
    {
        $this->assertNull($this->registry->systemRead('nonexistent', 'name', 's'));
    }
}
