<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKey;

/**
 * Tests RegistryKey POPO:
 * - Getters/setters
 * - Serialization/deserialization
 * - fromArray factory
 * - __toString
 */
class RegistryKeyTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $key = new RegistryKey();
        $key->setUserId(5);
        $key->setKey('app');
        $key->setName('theme');
        $key->setType('s');
        $key->setValue('dark');

        $this->assertSame(5, $key->getUserId());
        $this->assertSame('app', $key->getKey());
        $this->assertSame('theme', $key->getName());
        $this->assertSame('s', $key->getType());
        $this->assertSame('dark', $key->getValue());
    }

    public function testSettersReturnSelf(): void
    {
        $key = new RegistryKey();

        $this->assertSame($key, $key->setUserId(1));
        $this->assertSame($key, $key->setKey('k'));
        $this->assertSame($key, $key->setName('n'));
        $this->assertSame($key, $key->setType('s'));
        $this->assertSame($key, $key->setValue('v'));
    }

    public function testSerializeAndDeserialize(): void
    {
        $key = new RegistryKey();
        $key->setUserId(3);
        $key->setKey('settings');
        $key->setName('language');
        $key->setType('s');
        $key->setValue('en');

        $serialized = $key->serialize();
        $this->assertIsString($serialized);

        $decoded = json_decode($serialized, true);
        $this->assertSame(3, $decoded['user_id']);
        $this->assertSame('settings', $decoded['key']);
        $this->assertSame('language', $decoded['name']);
        $this->assertSame('s', $decoded['type']);
        $this->assertSame('en', $decoded['value']);

        $restored = RegistryKey::deserialize($serialized);
        $this->assertInstanceOf(RegistryKey::class, $restored);
        $this->assertSame(3, $restored->getUserId());
        $this->assertSame('settings', $restored->getKey());
        $this->assertSame('language', $restored->getName());
        $this->assertSame('s', $restored->getType());
        $this->assertSame('en', $restored->getValue());
    }

    public function testFromArray(): void
    {
        $array = [
            'user_id' => 7,
            'key' => 'prefs',
            'name' => 'font_size',
            'type' => 'i',
            'value' => '14',
        ];

        $key = RegistryKey::fromArray($array);

        $this->assertInstanceOf(RegistryKey::class, $key);
        $this->assertSame(7, $key->getUserId());
        $this->assertSame('prefs', $key->getKey());
        $this->assertSame('font_size', $key->getName());
        $this->assertSame('i', $key->getType());
        $this->assertSame('14', $key->getValue());
    }

    public function testToString(): void
    {
        $key = new RegistryKey();
        $key->setUserId(1);
        $key->setKey('app');
        $key->setName('setting');
        $key->setType('s');
        $key->setValue('value');

        $str = (string) $key;
        $this->assertStringContainsString('1', $str);
        $this->assertStringContainsString('app', $str);
        $this->assertStringContainsString('setting', $str);
        $this->assertStringContainsString('value', $str);
        $this->assertStringContainsString('s', $str);
    }

    public function testImplementsRegistryKeyInterface(): void
    {
        $key = new RegistryKey();
        $this->assertInstanceOf(\jonasarts\Bundle\RegistryBundle\Entity\RegistryKeyInterface::class, $key);
    }

    public function testImplementsSystemKeyInterface(): void
    {
        // RegistryKeyInterface extends SystemKeyInterface
        $key = new RegistryKey();
        $this->assertInstanceOf(\jonasarts\Bundle\RegistryBundle\Entity\SystemKeyInterface::class, $key);
    }
}
