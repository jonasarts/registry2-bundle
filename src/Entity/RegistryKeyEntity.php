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
use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use JsonException;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'registry')]
#[ORM\UniqueConstraint(name: 'uix_userid_key_name', columns: ['userid', 'registrykey', 'name'])]
#[UniqueEntity(fields: ['name', 'key', 'user_id'])]
class RegistryKeyEntity implements RegistryKeyInterface, Stringable
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id = 0;

    #[ORM\Column(name: 'userid', type: 'integer')]
    private int $user_id;

    #[ORM\Column(name: 'registrykey', type: 'string', length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $key;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name;

    #[ORM\Column(name: 'type', type: 'string', length: 1, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 1)]
    private string $type;

    #[ORM\Column(name: 'value', type: 'text', nullable: true)]
    private string $value;

    /**
     * Entity to string.
     */
    public function __toString(): string
    {
        return $this->user_id.' - '.$this->key.'/'.$this->name.' = '.$this->value.' ('.$this->type.')';
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
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
     * Get user_id.
     */
    public function getUserId(): int
    {
        return $this->user_id;
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
     * Get key.
     */
    public function getKey(): string
    {
        return $this->key;
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
     * Get name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set type.
     */
    public function setType(RegistryKeyType $type): self
    {
        $this->type = $type->value;

        return $this;
    }

    /**
     * Get type.
     */
    public function getType(): RegistryKeyType
    {
        return RegistryKeyType::from($this->type);
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
     * Get value.
     */
    public function getValue(): string
    {
        return $this->value;
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
        $a['type'] = $this->type;
        $a['value'] = $this->value;

        return json_encode($a, \JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public static function deserialize(string $string): RegistryKey
    {
        /** @var array{user_id:int, key:string, name:string, type:string, value:string} $object */
        $object = json_decode($string, true, 512, \JSON_THROW_ON_ERROR);

        $registry_key = new RegistryKey();

        $registry_key->setUserId($object['user_id']);
        $registry_key->setKey($object['key']);
        $registry_key->setName($object['name']);
        $registry_key->setType(RegistryKeyType::from($object['type']));
        $registry_key->setValue($object['value']);

        return $registry_key;
    }
}
