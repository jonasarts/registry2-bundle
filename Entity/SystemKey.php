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

use jonasarts\Bundle\RegistryBundle\Entity\SystemKeyInterface;

/**
 * SystemKey.
 *
 * Stores a global/system value
 */
class SystemKey extends AbstractRegistryKey implements SystemKeyInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $value;

    /**
     * Entity to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->key.'/'.$this->name.' = '.$this->value.' ('.$this->type.')';
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Set key.
     *
     * @param string $key
     * @return SystemKey
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     * @return SystemKey
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param string $type
     * @return SystemKey
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Set value.
     *
     * @param string $value
     * @return SystemKey
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        $array = array();
        $array['key'] = $this->key;
        $array['name'] = $this->name;
        $array['type'] = $this->type;
        $array['value'] = $this->value;

        return json_encode($array);
    }

    /**
     * @param string $string
     * @return SystemKey
     */
    public static function deserialize($string)
    {
        $object = json_decode($string);

        $system_key = new self();

        $system_key->key = $object->key;
        $system_key->name = $object->name;
        $system_key->type = $object->type;
        $system_key->value = $object->value;

        return $system_key;
    }

    /**
     * @param array $array
     * @return SystemKey
     */
    public static function fromArray($array)
    {
        $system_key = new self();

        $system_key->key = $array['key'];
        $system_key->name = $array['name'];
        $system_key->type = $array['type'];
        $system_key->value = $array['value'];

        return $system_key;
    }
}
