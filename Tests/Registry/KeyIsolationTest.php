<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Registry;

use PHPUnit\Framework\TestCase;

/**
 * Tests that registry and system keys are properly isolated:
 * - Different users don't see each other's keys
 * - Different key/name combinations don't collide
 * - Registry and system namespaces are independent
 */
class KeyIsolationTest extends TestCase
{
    private InMemoryRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new InMemoryRegistry();
    }

    public function testDifferentUsersAreIsolated(): void
    {
        $this->registry->registryWrite(1, 'app', 'theme', 's', 'dark');
        $this->registry->registryWrite(2, 'app', 'theme', 's', 'light');

        $this->assertSame('dark', $this->registry->registryRead(1, 'app', 'theme', 's'));
        $this->assertSame('light', $this->registry->registryRead(2, 'app', 'theme', 's'));
    }

    public function testDifferentKeysAreIsolated(): void
    {
        $this->registry->registryWrite(0, 'app1', 'setting', 'i', 1);
        $this->registry->registryWrite(0, 'app2', 'setting', 'i', 2);

        $this->assertSame(1, $this->registry->registryRead(0, 'app1', 'setting', 'i'));
        $this->assertSame(2, $this->registry->registryRead(0, 'app2', 'setting', 'i'));
    }

    public function testDifferentNamesAreIsolated(): void
    {
        $this->registry->registryWrite(0, 'app', 'width', 'i', 100);
        $this->registry->registryWrite(0, 'app', 'height', 'i', 200);

        $this->assertSame(100, $this->registry->registryRead(0, 'app', 'width', 'i'));
        $this->assertSame(200, $this->registry->registryRead(0, 'app', 'height', 'i'));
    }

    public function testDifferentTypesAreIsolated(): void
    {
        $this->registry->registryWrite(0, 'app', 'val', 'i', 42);
        $this->registry->registryWrite(0, 'app', 'val', 's', 'text');

        $this->assertSame(42, $this->registry->registryRead(0, 'app', 'val', 'i'));
        $this->assertSame('text', $this->registry->registryRead(0, 'app', 'val', 's'));
    }

    public function testRegistryAndSystemAreIndependent(): void
    {
        $this->registry->registryWrite(0, 'app', 'setting', 'i', 10);
        $this->registry->systemWrite('app', 'setting', 'i', 20);

        $this->assertSame(10, $this->registry->registryRead(0, 'app', 'setting', 'i'));
        $this->assertSame(20, $this->registry->systemRead('app', 'setting', 'i'));
    }

    public function testDeleteOneUserDoesNotAffectAnother(): void
    {
        $this->registry->registryWrite(1, 'app', 'val', 'i', 10);
        $this->registry->registryWrite(2, 'app', 'val', 'i', 20);

        $this->registry->registryDelete(1, 'app', 'val', 'i');

        $this->assertFalse($this->registry->registryExists(1, 'app', 'val', 'i'));
        $this->assertTrue($this->registry->registryExists(2, 'app', 'val', 'i'));
        $this->assertSame(20, $this->registry->registryRead(2, 'app', 'val', 'i'));
    }

    public function testSystemDeleteDoesNotAffectRegistryKey(): void
    {
        $this->registry->registryWrite(0, 'app', 'val', 'i', 10);
        $this->registry->systemWrite('app', 'val', 'i', 20);

        $this->registry->systemDelete('app', 'val', 'i');

        $this->assertFalse($this->registry->systemExists('app', 'val', 'i'));
        $this->assertTrue($this->registry->registryExists(0, 'app', 'val', 'i'));
    }

    public function testMultipleUsersWithDifferentKeys(): void
    {
        // Simulate a realistic scenario with multiple users
        for ($userId = 1; $userId <= 5; $userId++) {
            $this->registry->registryWrite($userId, 'prefs', 'page_size', 'i', $userId * 10);
            $this->registry->registryWrite($userId, 'prefs', 'theme', 's', "theme_$userId");
        }

        for ($userId = 1; $userId <= 5; $userId++) {
            $this->assertSame($userId * 10, $this->registry->registryRead($userId, 'prefs', 'page_size', 'i'));
            $this->assertSame("theme_$userId", $this->registry->registryRead($userId, 'prefs', 'theme', 's'));
        }
    }
}
