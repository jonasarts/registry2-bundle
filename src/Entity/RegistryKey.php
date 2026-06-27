<?php

declare(strict_types=1);

/*
 * This file is part of the jonasarts Registry bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\RegistryBundle\Entity;

use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use JsonException;
use Stringable;

/**
 * RegistryKey.
 *
 * Stores a user value
 */
class RegistryKey implements RegistryKeyInterface, Stringable
{
    private int $user_id;

    private string $key;

    private string $name;

    private RegistryKeyType $type;

    private string $value;

    /**
     * Entity to string.
     */
    public function __toString(): string
    {
        return $this->user_id.' - '.$this->key.'/'.$this->name.' = '.$this->value.' ('.$this->type->name.')';
    }

    /**
     * Get user_id.
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * Set user_id.
     */
    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Get key.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Set key.
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set name.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get type.
     */
    public function getType(): RegistryKeyType
    {
        return $this->type;
    }

    /**
     * Set type.
     */
    public function setType(RegistryKeyType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get value.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Set value.
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @throws JsonException
     */
    public function serialize(): string
    {
        $a = [];
        $a['user_id'] = $this->user_id;
        $a['key'] = $this->key;
        $a['name'] = $this->name;
        $a['type'] = $this->type->value;
        $a['value'] = $this->value;

        return json_encode($a, \JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public static function deserialize(string $string): self
    {
        /** @var array{user_id:int, key:string, name:string, type:string, value:string} $object */
        $object = json_decode($string, true, 512, \JSON_THROW_ON_ERROR);

        $registry_key = new self();

        $registry_key->user_id = $object['user_id'];
        $registry_key->key = $object['key'];
        $registry_key->name = $object['name'];
        $registry_key->type = RegistryKeyType::from($object['type']);
        $registry_key->value = $object['value'];

        return $registry_key;
    }

    /**
     * @param array{user_id:int, key:string, name:string, type:string, value:string} $array
     */
    public static function fromArray(array $array): self
    {
        $registry_key = new self();

        $registry_key->user_id = $array['user_id'];
        $registry_key->key = $array['key'];
        $registry_key->name = $array['name'];
        $registry_key->type = RegistryKeyType::from($array['type']);
        $registry_key->value = $array['value'];

        return $registry_key;
    }
}
