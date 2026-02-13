<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKeyEntity;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKey;

/**
 * Tests SystemKeyEntity (Doctrine-mapped entity):
 * - Getters/setters
 * - Serialization returns correct JSON
 * - Deserialization produces SystemKey POPO (not entity)
 */
class SystemKeyEntityTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $entity = new SystemKeyEntity();
        $entity->setKey('config');
        $entity->setName('app_name');
        $entity->setType('s');
        $entity->setValue('TestApp');

        $this->assertSame('config', $entity->getKey());
        $this->assertSame('app_name', $entity->getName());
        $this->assertSame('s', $entity->getType());
        $this->assertSame('TestApp', $entity->getValue());
    }

    public function testSettersReturnSelf(): void
    {
        $entity = new SystemKeyEntity();

        $this->assertSame($entity, $entity->setKey('k'));
        $this->assertSame($entity, $entity->setName('n'));
        $this->assertSame($entity, $entity->setType('s'));
        $this->assertSame($entity, $entity->setValue('v'));
    }

    public function testSerialize(): void
    {
        $entity = new SystemKeyEntity();
        $entity->setKey('global');
        $entity->setName('debug');
        $entity->setType('b');
        $entity->setValue('1');

        $json = $entity->serialize();
        $decoded = json_decode($json, true);

        $this->assertSame('global', $decoded['key']);
        $this->assertSame('debug', $decoded['name']);
        $this->assertSame('b', $decoded['type']);
        $this->assertSame('1', $decoded['value']);
    }

    public function testDeserializeReturnsSystemKeyPopo(): void
    {
        $json = json_encode([
            'key' => 'settings',
            'name' => 'mode',
            'type' => 's',
            'value' => 'prod',
        ]);

        $result = SystemKeyEntity::deserialize($json);

        $this->assertInstanceOf(SystemKey::class, $result);
        $this->assertSame('settings', $result->getKey());
        $this->assertSame('mode', $result->getName());
        $this->assertSame('s', $result->getType());
        $this->assertSame('prod', $result->getValue());
    }

    public function testToString(): void
    {
        $entity = new SystemKeyEntity();
        $entity->setKey('config');
        $entity->setName('mode');
        $entity->setType('s');
        $entity->setValue('test');

        $str = (string) $entity;
        $this->assertStringContainsString('config', $str);
        $this->assertStringContainsString('mode', $str);
        $this->assertStringContainsString('test', $str);
    }

    public function testImplementsSystemKeyInterface(): void
    {
        $entity = new SystemKeyEntity();
        $this->assertInstanceOf(\jonasarts\Bundle\RegistryBundle\Entity\SystemKeyInterface::class, $entity);
    }
}
