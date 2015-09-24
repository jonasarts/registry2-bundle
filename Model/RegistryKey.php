<?php

/*
 * This file is part of the jonasarts Registry bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\RegistryBundle\Model;

use jonasarts\Bundle\RegistryBundle\Interfaces\RegistryKeyInterface;

/**
 * RegistryKey.
 * 
 * Stores a user value
 */
class RegistryKey implements RegistryKeyInterface
{
    /**
     * @var int;
     */
    private $user_id;

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
     * Entitiy to string.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->user_id.' - '.$this->key.'/'.$this->name.' = '.$this->value.' ('.$this->type.')';
    }

    /**
     * Get user_id.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set user_id.
     *
     * @param int $user_id
     *
     * @return RegistryKey
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set key.
     *
     * @param string $key
     *
     * @return RegistryKey
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return RegistryKey
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return RegistryKey
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value.
     *
     * @param string $value
     *
     * @return RegistryKey
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        $a = array();
        $a['user_id'] = $this->user_id;
        $a['key'] = $this->key;
        $a['name'] = $this->name;
        $a['type'] = $this->type;
        $a['value'] = $this->value;

        return json_encode($a);
    }

    /**
     * @param string $string
     *
     * @return RegistryKey
     */
    public static function deserialize($string)
    {
        $object = json_decode($string);

        $registry_key = new self();

        $registry_key->user_id = $object->user_id;
        $registry_key->key = $object->key;
        $registry_key->name = $object->name;
        $registry_key->type = $object->type;
        $registry_key->value = $object->value;

        return $registry_key;
    }

    /**
     * @param array $array
     *
     * @return RegistryKey
     */
    public static function fromArray($array)
    {
        $registry_key = new self();

        $registry_key->user_id = $array['user_id'];
        $registry_key->key = $array['key'];
        $registry_key->name = $array['name'];
        $registry_key->type = $array['type'];
        $registry_key->value = $array['value'];

        return $registry_key;
    }
}
