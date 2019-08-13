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
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKeyInterface;

/**
 * RegistryKeyEntity.
 *
 * Doctrine-mapped RegistryKey entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="registry",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="uix_userid_key_name", columns={"userid", "registrykey", "name"})}
 * )
 * @UniqueEntity({"name", "registrykey", "userid"})
 */
class RegistryKeyEntity implements RegistryKeyInterface
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
     * @var int;
     * @ORM\Column(name="userid", type="integer")
     */
    private $user_id;

    /**
     * @var string
     *
     * @ORM\Column(name="registrykey", type="string", length=255, nullable=false)
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
     * @Assert\Length(min = 1, max = 1)
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
        return $this->user_id.' - '.$this->key.'/'.$this->name.' = '.$this->value.' ('.$this->type.')';
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
     * Set user_id.
     *
     * @param int $user_id
     * @return Registry
     */
    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Get user_id.
     *
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * Set key.
     *
     * @param string $key
     * @return Registry
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
     * @return Registry
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
     * @return Registry
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
     * @return Registry
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
     * @return RegistryKey
     */
    public static function deserialize($string)
    {
        $object = json_decode($string);

        $registry_key = new RegistryKey();

        $registry_key->user_id = $object->user_id;
        $registry_key->key = $object->key;
        $registry_key->name = $object->name;
        $registry_key->type = $object->type;
        $registry_key->value = $object->value;

        return $registry_key;
    }
}
