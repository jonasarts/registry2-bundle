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
 * SystemKey.
 *
 * Stores a global/system value
 */
class SystemKey implements SystemKeyInterface, Stringable
{
    private string $key;

    private string $name;

    private RegistryKeyType $type;

    private string $value;

    /**
     * Entity to string.
     */
    public function __toString(): string
    {
        return $this->key.'/'.$this->name.' = '.$this->value.' ('.$this->type->name.')';
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
        $array = [];
        $array['key'] = $this->key;
        $array['name'] = $this->name;
        $array['type'] = $this->type->value;
        $array['value'] = $this->value;

        return json_encode($array, \JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public static function deserialize(string $string): self
    {
        /** @var array{key:string, name:string, type:string, value:string} $object */
        $object = json_decode($string, true, 512, \JSON_THROW_ON_ERROR);

        $system_key = new self();

        $system_key->key = $object['key'];
        $system_key->name = $object['name'];
        $system_key->type = RegistryKeyType::from($object['type']);
        $system_key->value = $object['value'];

        return $system_key;
    }

    /**
     * @param array{key:string, name:string, type:string, value:string} $array
     */
    public static function fromArray(array $array): self
    {
        $system_key = new self();

        $system_key->key = $array['key'];
        $system_key->name = $array['name'];
        $system_key->type = RegistryKeyType::from($array['type']);
        $system_key->value = $array['value'];

        return $system_key;
    }
}
