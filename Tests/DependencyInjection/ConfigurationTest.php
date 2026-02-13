<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use jonasarts\Bundle\RegistryBundle\DependencyInjection\Configuration;

/**
 * Tests the bundle Configuration tree:
 * - Default values
 * - Custom values
 * - Required fields
 */
class ConfigurationTest extends TestCase
{
    private function processConfiguration(array $configs): array
    {
        $processor = new Processor();
        return $processor->processConfiguration(new Configuration(false), $configs);
    }

    public function testDefaultConfiguration(): void
    {
        $config = $this->processConfiguration([]);

        $this->assertNull($config['globals']['default_values']);
        $this->assertSame(':', $config['globals']['delimiter']);
        $this->assertSame('registry', $config['redis']['prefix']);
    }

    public function testCustomGlobalsConfiguration(): void
    {
        $config = $this->processConfiguration([
            [
                'globals' => [
                    'default_values' => '/path/to/registry.yml',
                    'delimiter' => '/',
                ],
            ],
        ]);

        $this->assertSame('/path/to/registry.yml', $config['globals']['default_values']);
        $this->assertSame('/', $config['globals']['delimiter']);
    }

    public function testCustomRedisPrefix(): void
    {
        $config = $this->processConfiguration([
            [
                'redis' => [
                    'prefix' => 'myapp',
                ],
            ],
        ]);

        $this->assertSame('myapp', $config['redis']['prefix']);
    }

    public function testFullCustomConfiguration(): void
    {
        $config = $this->processConfiguration([
            [
                'globals' => [
                    'default_values' => '/etc/registry.yml',
                    'delimiter' => '|',
                ],
                'redis' => [
                    'prefix' => 'custom_prefix',
                ],
            ],
        ]);

        $this->assertSame('/etc/registry.yml', $config['globals']['default_values']);
        $this->assertSame('|', $config['globals']['delimiter']);
        $this->assertSame('custom_prefix', $config['redis']['prefix']);
    }

    public function testTreeBuilderName(): void
    {
        $configuration = new Configuration(false);
        $treeBuilder = $configuration->getConfigTreeBuilder();

        // Verify the root node name matches the bundle alias
        $this->assertSame('registry', $treeBuilder->buildTree()->getName());
    }
}
