<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use jonasarts\Bundle\RegistryBundle\DependencyInjection\RegistryExtension;

/**
 * Tests the RegistryExtension:
 * - Custom alias
 */
class RegistryExtensionTest extends TestCase
{
    public function testGetAlias(): void
    {
        $extension = new RegistryExtension();
        $this->assertSame('registry', $extension->getAlias());
    }
}
