<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Registry;

use PHPUnit\Framework\TestCase;

/**
 * Tests registryReadOnce() and systemReadOnce() behavior:
 * - Reads the value
 * - Deletes the key after reading
 * - Subsequent reads return null / exists returns false
 */
class RegistryReadOnceTest extends TestCase
{
    private InMemoryRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new InMemoryRegistry();
    }

    public function testRegistryReadOnceReturnsValueAndDeletesKey(): void
    {
        $this->registry->registryWrite(0, 'flash', 'message', 'b', true);

        $result = $this->registry->registryReadOnce(0, 'flash', 'message', 'b');
        $this->assertSame(true, $result);

        $this->assertFalse($this->registry->registryExists(0, 'flash', 'message', 'b'));
    }

    public function testRegistryReadOnceIntegerReturnsValueAndDeletesKey(): void
    {
        $this->registry->registryWrite(0, 'flash', 'count', 'i', 7);

        $result = $this->registry->registryReadOnce(0, 'flash', 'count', 'i');
        $this->assertSame(7, $result);

        $this->assertFalse($this->registry->registryExists(0, 'flash', 'count', 'i'));
    }

    public function testRegistryReadOnceStringReturnsValueAndDeletesKey(): void
    {
        $this->registry->registryWrite(0, 'flash', 'msg', 's', 'temporary');

        $result = $this->registry->registryReadOnce(0, 'flash', 'msg', 's');
        $this->assertSame('temporary', $result);

        $this->assertFalse($this->registry->registryExists(0, 'flash', 'msg', 's'));
    }

    public function testRegistryReadOnceNonExistentReturnsNull(): void
    {
        $result = $this->registry->registryReadOnce(0, 'nonexistent', 'name', 's');
        $this->assertNull($result);
    }

    public function testSystemReadOnceReturnsValueAndDeletesKey(): void
    {
        $this->registry->systemWrite('flash', 'message', 'b', true);

        $result = $this->registry->systemReadOnce('flash', 'message', 'b');
        $this->assertSame(true, $result);

        $this->assertFalse($this->registry->systemExists('flash', 'message', 'b'));
    }

    public function testSystemReadOnceIntegerReturnsValueAndDeletesKey(): void
    {
        $this->registry->systemWrite('flash', 'count', 'i', 99);

        $result = $this->registry->systemReadOnce('flash', 'count', 'i');
        $this->assertSame(99, $result);

        $this->assertFalse($this->registry->systemExists('flash', 'count', 'i'));
    }

    public function testSystemReadOnceNonExistentReturnsNull(): void
    {
        $result = $this->registry->systemReadOnce('nonexistent', 'name', 's');
        $this->assertNull($result);
    }
}
