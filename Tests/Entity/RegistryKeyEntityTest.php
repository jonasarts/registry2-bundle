<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKeyEntity;
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKey;

/**
 * Tests RegistryKeyEntity (Doctrine-mapped entity):
 * - Getters/setters
 * - Serialization returns correct JSON
 * - Deserialization produces RegistryKey POPO (not entity)
 */
class RegistryKeyEntityTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $entity = new RegistryKeyEntity();
        $entity->setUserId(10);
        $entity->setKey('mykey');
        $entity->setName('myname');
        $entity->setType('s');
        $entity->setValue('myvalue');

        $this->assertSame(10, $entity->getUserId());
        $this->assertSame('mykey', $entity->getKey());
        $this->assertSame('myname', $entity->getName());
        $this->assertSame('s', $entity->getType());
        $this->assertSame('myvalue', $entity->getValue());
    }

    public function testSettersReturnSelf(): void
    {
        $entity = new RegistryKeyEntity();

        $this->assertSame($entity, $entity->setUserId(1));
        $this->assertSame($entity, $entity->setKey('k'));
        $this->assertSame($entity, $entity->setName('n'));
        $this->assertSame($entity, $entity->setType('s'));
        $this->assertSame($entity, $entity->setValue('v'));
    }

    public function testSerialize(): void
    {
        $entity = new RegistryKeyEntity();
        $entity->setUserId(3);
        $entity->setKey('app');
        $entity->setName('setting');
        $entity->setType('i');
        $entity->setValue('42');

        $json = $entity->serialize();
        $decoded = json_decode($json, true);

        $this->assertSame(3, $decoded['user_id']);
        $this->assertSame('app', $decoded['key']);
        $this->assertSame('setting', $decoded['name']);
        $this->assertSame('i', $decoded['type']);
        $this->assertSame('42', $decoded['value']);
    }

    public function testDeserializeReturnsRegistryKeyPopo(): void
    {
        $json = json_encode([
            'user_id' => 5,
            'key' => 'prefs',
            'name' => 'lang',
            'type' => 's',
            'value' => 'en',
        ]);

        $result = RegistryKeyEntity::deserialize($json);

        // Deserialize returns a RegistryKey POPO, not an Entity
        $this->assertInstanceOf(RegistryKey::class, $result);
        $this->assertSame(5, $result->getUserId());
        $this->assertSame('prefs', $result->getKey());
        $this->assertSame('lang', $result->getName());
        $this->assertSame('s', $result->getType());
        $this->assertSame('en', $result->getValue());
    }

    public function testToString(): void
    {
        $entity = new RegistryKeyEntity();
        $entity->setUserId(1);
        $entity->setKey('app');
        $entity->setName('val');
        $entity->setType('i');
        $entity->setValue('10');

        $str = (string) $entity;
        $this->assertStringContainsString('1', $str);
        $this->assertStringContainsString('app', $str);
        $this->assertStringContainsString('val', $str);
        $this->assertStringContainsString('10', $str);
    }

    public function testImplementsRegistryKeyInterface(): void
    {
        $entity = new RegistryKeyEntity();
        $this->assertInstanceOf(\jonasarts\Bundle\RegistryBundle\Entity\RegistryKeyInterface::class, $entity);
    }
}
