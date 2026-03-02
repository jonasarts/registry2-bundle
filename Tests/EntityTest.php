<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests;

use jonasarts\Bundle\RegistryBundle\Entity\RegistryKey;
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKeyEntity;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKey;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKeyEntity;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    // --- RegistryKey ---

    public function testRegistryKeySerializeDeserializeRoundTrip(): void
    {
        $key = new RegistryKey();
        $key->setUserId(42);
        $key->setKey('app');
        $key->setName('theme');
        $key->setType('s');
        $key->setValue('dark');

        $json = $key->serialize();
        $restored = RegistryKey::deserialize($json);

        $this->assertSame(42, $restored->getUserId());
        $this->assertSame('app', $restored->getKey());
        $this->assertSame('theme', $restored->getName());
        $this->assertSame('s', $restored->getType());
        $this->assertSame('dark', $restored->getValue());
    }

    public function testRegistryKeyFromArray(): void
    {
        $data = [
            'user_id' => 7,
            'key' => 'settings',
            'name' => 'lang',
            'type' => 's',
            'value' => 'en',
        ];

        $key = RegistryKey::fromArray($data);

        $this->assertSame(7, $key->getUserId());
        $this->assertSame('settings', $key->getKey());
        $this->assertSame('lang', $key->getName());
        $this->assertSame('s', $key->getType());
        $this->assertSame('en', $key->getValue());
    }

    public function testRegistryKeyToString(): void
    {
        $key = new RegistryKey();
        $key->setUserId(1);
        $key->setKey('k');
        $key->setName('n');
        $key->setType('i');
        $key->setValue('99');

        $this->assertSame('1 - k/n = 99 (i)', (string) $key);
    }

    public function testRegistryKeySettersReturnSelf(): void
    {
        $key = new RegistryKey();

        $this->assertSame($key, $key->setUserId(1));
        $this->assertSame($key, $key->setKey('k'));
        $this->assertSame($key, $key->setName('n'));
        $this->assertSame($key, $key->setType('s'));
        $this->assertSame($key, $key->setValue('v'));
    }

    // --- SystemKey ---

    public function testSystemKeySerializeDeserializeRoundTrip(): void
    {
        $key = new SystemKey();
        $key->setKey('global');
        $key->setName('version');
        $key->setType('s');
        $key->setValue('1.0.0');

        $json = $key->serialize();
        $restored = SystemKey::deserialize($json);

        $this->assertSame('global', $restored->getKey());
        $this->assertSame('version', $restored->getName());
        $this->assertSame('s', $restored->getType());
        $this->assertSame('1.0.0', $restored->getValue());
    }

    public function testSystemKeyFromArray(): void
    {
        $data = [
            'key' => 'config',
            'name' => 'debug',
            'type' => 'b',
            'value' => '1',
        ];

        $key = SystemKey::fromArray($data);

        $this->assertSame('config', $key->getKey());
        $this->assertSame('debug', $key->getName());
        $this->assertSame('b', $key->getType());
        $this->assertSame('1', $key->getValue());
    }

    public function testSystemKeyToString(): void
    {
        $key = new SystemKey();
        $key->setKey('sys');
        $key->setName('flag');
        $key->setType('b');
        $key->setValue('1');

        $this->assertSame('sys/flag = 1 (b)', (string) $key);
    }

    public function testSystemKeySettersReturnSelf(): void
    {
        $key = new SystemKey();

        $this->assertSame($key, $key->setKey('k'));
        $this->assertSame($key, $key->setName('n'));
        $this->assertSame($key, $key->setType('s'));
        $this->assertSame($key, $key->setValue('v'));
    }

    // --- RegistryKeyEntity ---

    public function testRegistryKeyEntitySerialize(): void
    {
        $entity = new RegistryKeyEntity();
        $entity->setUserId(10);
        $entity->setKey('app');
        $entity->setName('color');
        $entity->setType('s');
        $entity->setValue('blue');

        $json = $entity->serialize();
        $data = json_decode($json, true);

        $this->assertSame(10, $data['user_id']);
        $this->assertSame('app', $data['key']);
        $this->assertSame('color', $data['name']);
        $this->assertSame('s', $data['type']);
        $this->assertSame('blue', $data['value']);
    }

    public function testRegistryKeyEntityDeserializeReturnsDto(): void
    {
        $entity = new RegistryKeyEntity();
        $entity->setUserId(5);
        $entity->setKey('k');
        $entity->setName('n');
        $entity->setType('i');
        $entity->setValue('42');

        $json = $entity->serialize();
        $dto = RegistryKeyEntity::deserialize($json);

        // deserialize returns RegistryKey (DTO), not RegistryKeyEntity
        $this->assertInstanceOf(RegistryKey::class, $dto);
        $this->assertSame(5, $dto->getUserId());
        $this->assertSame('42', $dto->getValue());
    }

    public function testRegistryKeyEntityToString(): void
    {
        $entity = new RegistryKeyEntity();
        $entity->setUserId(3);
        $entity->setKey('k');
        $entity->setName('n');
        $entity->setType('f');
        $entity->setValue('3.14');

        $this->assertSame('3 - k/n = 3.14 (f)', (string) $entity);
    }

    public function testRegistryKeyEntityIdDefaultsToZero(): void
    {
        $entity = new RegistryKeyEntity();

        $this->assertSame(0, $entity->getId());
    }

    // --- SystemKeyEntity ---

    public function testSystemKeyEntitySerialize(): void
    {
        $entity = new SystemKeyEntity();
        $entity->setKey('sys');
        $entity->setName('timeout');
        $entity->setType('i');
        $entity->setValue('30');

        $json = $entity->serialize();
        $data = json_decode($json, true);

        $this->assertSame('sys', $data['key']);
        $this->assertSame('timeout', $data['name']);
        $this->assertSame('i', $data['type']);
        $this->assertSame('30', $data['value']);
    }

    public function testSystemKeyEntityDeserializeReturnsDto(): void
    {
        $entity = new SystemKeyEntity();
        $entity->setKey('sys');
        $entity->setName('flag');
        $entity->setType('b');
        $entity->setValue('1');

        $json = $entity->serialize();
        $dto = SystemKeyEntity::deserialize($json);

        // deserialize returns SystemKey (DTO), not SystemKeyEntity
        $this->assertInstanceOf(SystemKey::class, $dto);
        $this->assertSame('sys', $dto->getKey());
        $this->assertSame('1', $dto->getValue());
    }

    public function testSystemKeyEntityToString(): void
    {
        $entity = new SystemKeyEntity();
        $entity->setKey('cfg');
        $entity->setName('rate');
        $entity->setType('f');
        $entity->setValue('0.75');

        // SystemKeyEntity uses '=>' not '='
        $this->assertSame('cfg/rate => 0.75 (f)', (string) $entity);
    }

    public function testSystemKeyEntityIdDefaultsToZero(): void
    {
        $entity = new SystemKeyEntity();

        $this->assertSame(0, $entity->getId());
    }
}
