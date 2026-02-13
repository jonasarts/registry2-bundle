<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Registry;

use PHPUnit\Framework\TestCase;

/**
 * Tests delimiter validation:
 * - Delimiter in name throws exception for both registry and system
 * - Delimiter in key is allowed (the code has a comment "why not?")
 * - Custom delimiters work correctly
 */
class DelimiterValidationTest extends TestCase
{
    public function testRegistryWriteThrowsExceptionWhenNameContainsDefaultDelimiter(): void
    {
        $registry = new InMemoryRegistry();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('delimiter is not allowed in name');

        $registry->registryWrite(0, 'key', 'name:invalid', 'i', 42);
    }

    public function testSystemWriteThrowsExceptionWhenNameContainsDefaultDelimiter(): void
    {
        $registry = new InMemoryRegistry();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('delimiter is not allowed in name');

        $registry->systemWrite('key', 'name:invalid', 'i', 42);
    }

    public function testRegistryWriteAllowsDelimiterInKey(): void
    {
        $registry = new InMemoryRegistry();

        // Delimiter in key is allowed (current behavior)
        $result = $registry->registryWrite(0, 'key:with:delimiters', 'name', 's', 'value');
        $this->assertTrue($result);

        $this->assertSame('value', $registry->registryRead(0, 'key:with:delimiters', 'name', 's'));
    }

    public function testSystemWriteAllowsDelimiterInKey(): void
    {
        $registry = new InMemoryRegistry();

        $result = $registry->systemWrite('key:with:delimiters', 'name', 's', 'value');
        $this->assertTrue($result);

        $this->assertSame('value', $registry->systemRead('key:with:delimiters', 'name', 's'));
    }

    public function testCustomDelimiterValidation(): void
    {
        $registry = new InMemoryRegistry(null, '/');

        // Slash in name should throw with custom delimiter
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('delimiter is not allowed in name');

        $registry->registryWrite(0, 'key', 'name/invalid', 'i', 42);
    }

    public function testCustomDelimiterAllowsDefaultDelimiterInName(): void
    {
        $registry = new InMemoryRegistry(null, '/');

        // Default delimiter (:) should be allowed when custom delimiter is /
        $result = $registry->registryWrite(0, 'key', 'name:valid', 'i', 42);
        $this->assertTrue($result);
    }
}
