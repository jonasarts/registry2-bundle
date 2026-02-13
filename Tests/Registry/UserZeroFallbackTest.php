<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Registry;

use PHPUnit\Framework\TestCase;

/**
 * Tests the User-0 fallback mechanism:
 * - When a user-specific key doesn't exist, falls back to user 0
 * - When both exist, user-specific takes precedence
 * - After deleting user-specific, falls back to user 0
 * - The matching-value deletion optimization
 */
class UserZeroFallbackTest extends TestCase
{
    private InMemoryRegistry $registry;

    private const USER_ID = 5;

    protected function setUp(): void
    {
        $this->registry = new InMemoryRegistry();
    }

    public function testFallbackToUser0WhenUserKeyDoesNotExist(): void
    {
        // Write user-0 default
        $this->registry->registryWrite(0, 'settings', 'language', 's', 'en');

        // Read for user 5 - should fall back to user 0
        $result = $this->registry->registryReadDefault(self::USER_ID, 'settings', 'language', 's', 'fr');
        $this->assertSame('en', $result);
    }

    public function testUserSpecificOverridesUser0(): void
    {
        // Write user-0 default
        $this->registry->registryWrite(0, 'settings', 'language', 's', 'en');

        // Write user-specific value
        $this->registry->registryWrite(self::USER_ID, 'settings', 'language', 's', 'de');

        // Read for user 5 - should return user-specific
        $result = $this->registry->registryRead(self::USER_ID, 'settings', 'language', 's');
        $this->assertSame('de', $result);
    }

    public function testDeleteUserSpecificFallsBackToUser0(): void
    {
        // Write user-0 default
        $this->registry->registryWrite(0, 'settings', 'theme', 's', 'light');

        // Write user-specific value
        $this->registry->registryWrite(self::USER_ID, 'settings', 'theme', 's', 'dark');

        // Delete user-specific
        $this->registry->registryDelete(self::USER_ID, 'settings', 'theme', 's');

        // Should fall back to user 0
        $result = $this->registry->registryReadDefault(self::USER_ID, 'settings', 'theme', 's', 'default');
        $this->assertSame('light', $result);
    }

    public function testFallbackToProvidedDefaultWhenNoUser0(): void
    {
        // No user-0, no user-specific - should return the provided default
        $result = $this->registry->registryReadDefault(self::USER_ID, 'settings', 'missing', 's', 'fallback');
        $this->assertSame('fallback', $result);
    }

    public function testUser0FallbackForBoolean(): void
    {
        $this->registry->registryWrite(0, 'flags', 'active', 'b', true);

        $result = $this->registry->registryReadDefault(self::USER_ID, 'flags', 'active', 'b', false);
        $this->assertSame(true, $result);
    }

    public function testUser0FallbackForInteger(): void
    {
        $this->registry->registryWrite(0, 'settings', 'page_size', 'i', 25);

        $result = $this->registry->registryReadDefault(self::USER_ID, 'settings', 'page_size', 'i', 10);
        $this->assertSame(25, $result);
    }

    public function testUser0FallbackForFloat(): void
    {
        $this->registry->registryWrite(0, 'settings', 'ratio', 'f', 0.8);

        $result = $this->registry->registryReadDefault(self::USER_ID, 'settings', 'ratio', 'f', 0.5);
        $this->assertEqualsWithDelta(0.8, $result, 0.001);
    }

    public function testUser0FallbackForDate(): void
    {
        $ts = strtotime('2023-01-01');
        $this->registry->registryWrite(0, 'settings', 'start', 'd', $ts);

        $result = $this->registry->registryReadDefault(self::USER_ID, 'settings', 'start', 'd', strtotime('2000-01-01'));
        $this->assertSame($ts, $result);
    }

    // --- Matching value deletion ---

    public function testWriteMatchingUser0ValueDeletesUserKey(): void
    {
        // Write user-0 value
        $this->registry->registryWrite(0, 'key', 'name', 'i', 10);

        // Write different user value
        $this->registry->registryWrite(1, 'key', 'name', 'i', 11);

        // Verify user-specific key exists
        $this->assertTrue($this->registry->registryExists(1, 'key', 'name', 'i'));

        // Write the same value as user-0 -> should delete user key
        $this->registry->registryWrite(1, 'key', 'name', 'i', 10);

        // User-specific key should be deleted
        $this->assertFalse($this->registry->registryExists(1, 'key', 'name', 'i'));

        // But reading should still return user-0 value via fallback
        $result = $this->registry->registryReadDefault(1, 'key', 'name', 'i', 0);
        $this->assertSame(10, $result);
    }

    public function testWriteMatchingUser0ValueBooleanDeletesUserKey(): void
    {
        $this->registry->registryWrite(0, 'key', 'flag', 'b', true);
        $this->registry->registryWrite(1, 'key', 'flag', 'b', false);

        $this->assertTrue($this->registry->registryExists(1, 'key', 'flag', 'b'));

        // Write matching user-0 value
        $this->registry->registryWrite(1, 'key', 'flag', 'b', true);

        $this->assertFalse($this->registry->registryExists(1, 'key', 'flag', 'b'));
    }

    public function testWriteMatchingUser0ValueStringDeletesUserKey(): void
    {
        $this->registry->registryWrite(0, 'key', 'lang', 's', 'en');
        $this->registry->registryWrite(1, 'key', 'lang', 's', 'de');

        $this->assertTrue($this->registry->registryExists(1, 'key', 'lang', 's'));

        // Write matching user-0 value
        $this->registry->registryWrite(1, 'key', 'lang', 's', 'en');

        $this->assertFalse($this->registry->registryExists(1, 'key', 'lang', 's'));
    }

    public function testWriteDifferentValueDoesNotDeleteUserKey(): void
    {
        $this->registry->registryWrite(0, 'key', 'name', 'i', 10);
        $this->registry->registryWrite(1, 'key', 'name', 'i', 20);

        // Write a different value (not matching user-0)
        $this->registry->registryWrite(1, 'key', 'name', 'i', 30);

        // User key should still exist with new value
        $this->assertTrue($this->registry->registryExists(1, 'key', 'name', 'i'));
        $this->assertSame(30, $this->registry->registryRead(1, 'key', 'name', 'i'));
    }

    public function testUser0WriteDoesNotTriggerMatchingValueDeletion(): void
    {
        // Writing for user 0 should never trigger the matching-value deletion
        $this->registry->registryWrite(0, 'key', 'name', 'i', 10);

        // Overwrite user-0 with same value
        $this->registry->registryWrite(0, 'key', 'name', 'i', 10);

        // Key should still exist
        $this->assertTrue($this->registry->registryExists(0, 'key', 'name', 'i'));
        $this->assertSame(10, $this->registry->registryRead(0, 'key', 'name', 'i'));
    }

    public function testRegistryExistsDoesNotUseUser0Fallback(): void
    {
        // Write only for user-0
        $this->registry->registryWrite(0, 'key', 'name', 'i', 42);

        // registryExists should NOT use user-0 fallback
        $this->assertFalse($this->registry->registryExists(self::USER_ID, 'key', 'name', 'i'));
    }

    public function testRegistryDeleteDoesNotUseUser0Fallback(): void
    {
        // Write only for user-0
        $this->registry->registryWrite(0, 'key', 'name', 'i', 42);

        // registryDelete should NOT delete user-0 key when called with different user
        $result = $this->registry->registryDelete(self::USER_ID, 'key', 'name', 'i');
        $this->assertFalse($result);

        // User-0 key should still exist
        $this->assertTrue($this->registry->registryExists(0, 'key', 'name', 'i'));
    }
}
