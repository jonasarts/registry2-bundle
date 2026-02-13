<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKey;

/**
 * Tests SystemKey POPO:
 * - Getters/setters
 * - Serialization/deserialization
 * - fromArray factory
 * - __toString
 */
class SystemKeyTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $key = new SystemKey();
        $key->setKey('config');
        $key->setName('app_name');
        $key->setType('s');
        $key->setValue('MyApp');

        $this->assertSame('config', $key->getKey());
        $this->assertSame('app_name', $key->getName());
        $this->assertSame('s', $key->getType());
        $this->assertSame('MyApp', $key->getValue());
    }

    public function testSettersReturnSelf(): void
    {
        $key = new SystemKey();

        $this->assertSame($key, $key->setKey('k'));
        $this->assertSame($key, $key->setName('n'));
        $this->assertSame($key, $key->setType('s'));
        $this->assertSame($key, $key->setValue('v'));
    }

    public function testSerializeAndDeserialize(): void
    {
        $key = new SystemKey();
        $key->setKey('global');
        $key->setName('version');
        $key->setType('i');
        $key->setValue('3');

        $serialized = $key->serialize();
        $this->assertIsString($serialized);

        $decoded = json_decode($serialized, true);
        $this->assertSame('global', $decoded['key']);
        $this->assertSame('version', $decoded['name']);
        $this->assertSame('i', $decoded['type']);
        $this->assertSame('3', $decoded['value']);

        $restored = SystemKey::deserialize($serialized);
        $this->assertInstanceOf(SystemKey::class, $restored);
        $this->assertSame('global', $restored->getKey());
        $this->assertSame('version', $restored->getName());
        $this->assertSame('i', $restored->getType());
        $this->assertSame('3', $restored->getValue());
    }

    public function testFromArray(): void
    {
        $array = [
            'key' => 'settings',
            'name' => 'debug',
            'type' => 'b',
            'value' => '1',
        ];

        $key = SystemKey::fromArray($array);

        $this->assertInstanceOf(SystemKey::class, $key);
        $this->assertSame('settings', $key->getKey());
        $this->assertSame('debug', $key->getName());
        $this->assertSame('b', $key->getType());
        $this->assertSame('1', $key->getValue());
    }

    public function testToString(): void
    {
        $key = new SystemKey();
        $key->setKey('config');
        $key->setName('mode');
        $key->setType('s');
        $key->setValue('production');

        $str = (string) $key;
        $this->assertStringContainsString('config', $str);
        $this->assertStringContainsString('mode', $str);
        $this->assertStringContainsString('production', $str);
        $this->assertStringContainsString('s', $str);
    }

    public function testImplementsSystemKeyInterface(): void
    {
        $key = new SystemKey();
        $this->assertInstanceOf(\jonasarts\Bundle\RegistryBundle\Entity\SystemKeyInterface::class, $key);
    }
}
