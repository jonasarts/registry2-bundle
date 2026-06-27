<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests;

use jonasarts\Bundle\RegistryBundle\Controller\RegistryController;
use jonasarts\Bundle\RegistryBundle\Controller\SystemController;
use jonasarts\Bundle\RegistryBundle\Registry\DoctrineRegistry;
use jonasarts\Bundle\RegistryBundle\Registry\RedisRegistry;
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;
use jonasarts\Bundle\RegistryBundle\RegistryBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegistryExtensionTest extends TestCase
{
    /**
     * @param array<string, mixed> $config
     */
    private function load(array $config = []): ContainerBuilder
    {
        $container = new ContainerBuilder();
        new RegistryBundle()->getContainerExtension()->load([$config], $container);

        return $container;
    }

    public function testDefaultsToDoctrineEngine(): void
    {
        $container = $this->load();

        $this->assertTrue($container->hasDefinition(DoctrineRegistry::class));
        $this->assertFalse($container->hasDefinition(RedisRegistry::class));
        $this->assertSame('doctrine', $container->getParameter('registry.engine'));
    }

    public function testRegistryInterfaceIsAliasedToDoctrineByDefault(): void
    {
        $container = $this->load();

        $this->assertTrue($container->hasAlias(RegistryInterface::class));
        $this->assertSame(DoctrineRegistry::class, (string) $container->getAlias(RegistryInterface::class));
    }

    public function testRedisEngineIsWiredWithConfiguredClientService(): void
    {
        $container = $this->load(['engine' => 'redis', 'redis' => ['client_service' => 'my.redis.client']]);

        $this->assertTrue($container->hasDefinition(RedisRegistry::class));
        $this->assertFalse($container->hasDefinition(DoctrineRegistry::class));
        $this->assertSame(RedisRegistry::class, (string) $container->getAlias(RegistryInterface::class));

        $redisArg = $container->getDefinition(RedisRegistry::class)->getArgument('$redis');
        $this->assertInstanceOf(Reference::class, $redisArg);
        $this->assertSame('my.redis.client', (string) $redisArg);
    }

    public function testRedisClientServiceDefaultsToSncRedis(): void
    {
        $container = $this->load(['engine' => 'redis']);

        $redisArg = $container->getDefinition(RedisRegistry::class)->getArgument('$redis');
        $this->assertSame('snc_redis.registry', (string) $redisArg);
    }

    public function testUiControllersNotRegisteredByDefault(): void
    {
        $container = $this->load();

        $this->assertFalse($container->hasDefinition(RegistryController::class));
        $this->assertFalse($container->hasDefinition(SystemController::class));
        $this->assertFalse($container->getParameter('registry.ui.enabled'));
    }

    public function testUiControllersRegisteredWhenEnabled(): void
    {
        $container = $this->load(['ui' => ['enabled' => true]]);

        $this->assertTrue($container->hasDefinition(RegistryController::class));
        $this->assertTrue($container->hasDefinition(SystemController::class));

        $definition = $container->getDefinition(RegistryController::class);
        $this->assertSame('base.html.twig', $definition->getArgument('$baseTemplate'));
        $this->assertSame('ROLE_REGISTRY_ADMIN', $definition->getArgument('$adminRole'));
    }

    public function testUiRespectsCustomTemplateAndRole(): void
    {
        $container = $this->load([
            'ui' => ['enabled' => true, 'base_template' => 'layout.html.twig', 'role' => 'ROLE_ADMIN'],
        ]);

        $definition = $container->getDefinition(SystemController::class);
        $this->assertSame('layout.html.twig', $definition->getArgument('$baseTemplate'));
        $this->assertSame('ROLE_ADMIN', $definition->getArgument('$adminRole'));
    }

    public function testGlobalsParametersAreApplied(): void
    {
        $container = $this->load(['globals' => ['delimiter' => '/', 'default_values' => '/tmp/defaults.yaml']]);

        $this->assertSame('/', $container->getParameter('registry.globals.delimiter'));
        $this->assertSame('/tmp/defaults.yaml', $container->getParameter('registry.globals.default_values'));
    }
}
