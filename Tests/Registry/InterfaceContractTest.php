<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Registry;

use PHPUnit\Framework\TestCase;
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;
use jonasarts\Bundle\RegistryBundle\Registry\AbstractRegistry;

/**
 * Tests that the public API contract (RegistryInterface) is properly implemented.
 * This ensures consumers' type-hinted code will continue to work after migration.
 */
class InterfaceContractTest extends TestCase
{
    public function testInMemoryRegistryImplementsRegistryInterface(): void
    {
        $registry = new InMemoryRegistry();
        $this->assertInstanceOf(RegistryInterface::class, $registry);
    }

    public function testInMemoryRegistryExtendsAbstractRegistry(): void
    {
        $registry = new InMemoryRegistry();
        $this->assertInstanceOf(AbstractRegistry::class, $registry);
    }

    public function testRegistryInterfaceDefinesAllRequiredMethods(): void
    {
        $interface = new \ReflectionClass(RegistryInterface::class);
        $methods = array_map(fn(\ReflectionMethod $m) => $m->getName(), $interface->getMethods());

        // Registry methods
        $this->assertContains('registryExists', $methods);
        $this->assertContains('registryDelete', $methods);
        $this->assertContains('registryReadDefault', $methods);
        $this->assertContains('registryRead', $methods);
        $this->assertContains('registryReadOnce', $methods);
        $this->assertContains('registryWrite', $methods);

        // Registry shorthands
        $this->assertContains('re', $methods);
        $this->assertContains('rd', $methods);
        $this->assertContains('rrd', $methods);
        $this->assertContains('rr', $methods);
        $this->assertContains('rro', $methods);
        $this->assertContains('rw', $methods);

        // System methods
        $this->assertContains('systemExists', $methods);
        $this->assertContains('systemDelete', $methods);
        $this->assertContains('systemReadDefault', $methods);
        $this->assertContains('systemRead', $methods);
        $this->assertContains('systemReadOnce', $methods);
        $this->assertContains('systemWrite', $methods);

        // System shorthands
        $this->assertContains('se', $methods);
        $this->assertContains('sd', $methods);
        $this->assertContains('srd', $methods);
        $this->assertContains('sr', $methods);
        $this->assertContains('sro', $methods);
        $this->assertContains('sw', $methods);
    }

    public function testRegistryExistsSignature(): void
    {
        $method = new \ReflectionMethod(RegistryInterface::class, 'registryExists');
        $params = $method->getParameters();

        $this->assertCount(4, $params);
        $this->assertSame('int', $params[0]->getType()->getName());    // user_id
        $this->assertSame('string', $params[1]->getType()->getName()); // key
        $this->assertSame('string', $params[2]->getType()->getName()); // name
        $this->assertSame('string', $params[3]->getType()->getName()); // type

        $this->assertSame('bool', $method->getReturnType()->getName());
    }

    public function testRegistryWriteSignature(): void
    {
        $method = new \ReflectionMethod(RegistryInterface::class, 'registryWrite');
        $params = $method->getParameters();

        $this->assertCount(5, $params);
        $this->assertSame('int', $params[0]->getType()->getName());    // user_id
        $this->assertSame('string', $params[1]->getType()->getName()); // key
        $this->assertSame('string', $params[2]->getType()->getName()); // name
        $this->assertSame('string', $params[3]->getType()->getName()); // type
        $this->assertNull($params[4]->getType());                       // value (mixed)

        $this->assertSame('bool', $method->getReturnType()->getName());
    }

    public function testSystemExistsSignature(): void
    {
        $method = new \ReflectionMethod(RegistryInterface::class, 'systemExists');
        $params = $method->getParameters();

        $this->assertCount(3, $params);
        $this->assertSame('string', $params[0]->getType()->getName()); // key
        $this->assertSame('string', $params[1]->getType()->getName()); // name
        $this->assertSame('string', $params[2]->getType()->getName()); // type

        $this->assertSame('bool', $method->getReturnType()->getName());
    }

    public function testSystemWriteSignature(): void
    {
        $method = new \ReflectionMethod(RegistryInterface::class, 'systemWrite');
        $params = $method->getParameters();

        $this->assertCount(4, $params);
        $this->assertSame('string', $params[0]->getType()->getName()); // key
        $this->assertSame('string', $params[1]->getType()->getName()); // name
        $this->assertSame('string', $params[2]->getType()->getName()); // type
        $this->assertNull($params[3]->getType());                       // value (mixed)

        $this->assertSame('bool', $method->getReturnType()->getName());
    }

    public function testEngineInterfaceDefinesAllRequiredMethods(): void
    {
        $interface = new \ReflectionClass(\jonasarts\Bundle\RegistryBundle\Engine\RegistryEngineInterface::class);
        $methods = array_map(fn(\ReflectionMethod $m) => $m->getName(), $interface->getMethods());

        $this->assertContains('registryExists', $methods);
        $this->assertContains('registryDelete', $methods);
        $this->assertContains('registryRead', $methods);
        $this->assertContains('registryWrite', $methods);
        $this->assertContains('registryAll', $methods);

        $this->assertContains('systemExists', $methods);
        $this->assertContains('systemDelete', $methods);
        $this->assertContains('systemRead', $methods);
        $this->assertContains('systemWrite', $methods);
        $this->assertContains('systemAll', $methods);
    }
}
