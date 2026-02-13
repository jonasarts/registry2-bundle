<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Registry;

use PHPUnit\Framework\TestCase;

/**
 * Tests that all shorthand methods delegate correctly to their full counterparts.
 * This ensures the public API contract is maintained during migration.
 */
class ShorthandMethodsTest extends TestCase
{
    private InMemoryRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new InMemoryRegistry();
    }

    // --- Registry shorthands ---

    public function testRwWritesValue(): void
    {
        $this->assertTrue($this->registry->rw(0, 'key', 'name', 'i', 42));
        $this->assertSame(42, $this->registry->registryRead(0, 'key', 'name', 'i'));
    }

    public function testRrReadsValue(): void
    {
        $this->registry->registryWrite(0, 'key', 'name', 's', 'hello');
        $this->assertSame('hello', $this->registry->rr(0, 'key', 'name', 's'));
    }

    public function testRrdReadsDefault(): void
    {
        $result = $this->registry->rrd(0, 'key', 'name', 'i', 99);
        $this->assertSame(99, $result);
    }

    public function testReChecksExistence(): void
    {
        $this->assertFalse($this->registry->re(0, 'key', 'name', 'i'));
        $this->registry->registryWrite(0, 'key', 'name', 'i', 1);
        $this->assertTrue($this->registry->re(0, 'key', 'name', 'i'));
    }

    public function testRdDeletesKey(): void
    {
        $this->registry->registryWrite(0, 'key', 'name', 'i', 42);
        $this->assertTrue($this->registry->rd(0, 'key', 'name', 'i'));
        $this->assertFalse($this->registry->registryExists(0, 'key', 'name', 'i'));
    }

    public function testRroReadsOnce(): void
    {
        $this->registry->registryWrite(0, 'key', 'name', 'b', true);
        $this->assertSame(true, $this->registry->rro(0, 'key', 'name', 'b'));
        $this->assertFalse($this->registry->registryExists(0, 'key', 'name', 'b'));
    }

    // --- System shorthands ---

    public function testSwWritesValue(): void
    {
        $this->assertTrue($this->registry->sw('key', 'name', 'i', 42));
        $this->assertSame(42, $this->registry->systemRead('key', 'name', 'i'));
    }

    public function testSrReadsValue(): void
    {
        $this->registry->systemWrite('key', 'name', 's', 'world');
        $this->assertSame('world', $this->registry->sr('key', 'name', 's'));
    }

    public function testSrdReadsDefault(): void
    {
        $result = $this->registry->srd('key', 'name', 'i', 77);
        $this->assertSame(77, $result);
    }

    public function testSeChecksExistence(): void
    {
        $this->assertFalse($this->registry->se('key', 'name', 'i'));
        $this->registry->systemWrite('key', 'name', 'i', 1);
        $this->assertTrue($this->registry->se('key', 'name', 'i'));
    }

    public function testSdDeletesKey(): void
    {
        $this->registry->systemWrite('key', 'name', 'i', 42);
        $this->assertTrue($this->registry->sd('key', 'name', 'i'));
        $this->assertFalse($this->registry->systemExists('key', 'name', 'i'));
    }

    public function testSroReadsOnce(): void
    {
        $this->registry->systemWrite('key', 'name', 'b', true);
        $this->assertSame(true, $this->registry->sro('key', 'name', 'b'));
        $this->assertFalse($this->registry->systemExists('key', 'name', 'b'));
    }
}
