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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKeyInterface;

/**
 * SystemKeyEntity.
 *
 * Doctrine-mapped SystemKey entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="system",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="uix_key_name", columns={"systemkey", "name"})}
 * )
 * @UniqueEntity({"name", "systemkey"})
 */
class SystemKeyEntity implements SystemKeyInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="systemkey", type="string", length=255, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(max = 255)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(max = 255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=1, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(min = 1, max = 1))
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    private $value;

    /**
     * Entitiy to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->key.'/'.$this->name.' => '.$this->value.' ('.$this->type.')';
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set key.
     *
     * @param string $key
     * @return System
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
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
     * Set name.
     *
     * @param string $name
     * @return System
     */
    public function setName(string $name): self
    {
        $this->name = $name;

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
     * Set type.
     *
     * @param string $type
     * @return System
     */
    public function setType(string $type): self
    {
        $this->type = $type;

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
     * Set value.
     *
     * @param string $value
     * @return System
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

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

        $system_key = new SystemKey();

        $system_key->key = $object->key;
        $system_key->name = $object->name;
        $system_key->type = $object->type;
        $system_key->value = $object->value;

        return $system_key;
    }
}
