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
#[ORM\Table(name: '`system`')]
#[ORM\UniqueConstraint(name: 'uix_key_name', columns: ['systemkey', 'name'])]
#[UniqueEntity(fields: ['name', 'key'])]
class SystemKeyEntity implements SystemKeyInterface, Stringable
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id = 0;

    #[ORM\Column(name: 'systemkey', type: 'string', length: 255, nullable: false)]
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
     * Entitiy to string.
     */
    public function __toString(): string
    {
        return $this->key.'/'.$this->name.' => '.$this->value.' ('.$this->type.')';
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
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
        $array = [];
        $array['key'] = $this->key;
        $array['name'] = $this->name;
        $array['type'] = $this->type;
        $array['value'] = $this->value;

        return json_encode($array, \JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public static function deserialize(string $string): SystemKey
    {
        /** @var array{key:string, name:string, type:string, value:string} $object */
        $object = json_decode($string, true, 512, \JSON_THROW_ON_ERROR);

        $system_key = new SystemKey();

        $system_key->setKey($object['key']);
        $system_key->setName($object['name']);
        $system_key->setType(RegistryKeyType::from($object['type']));
        $system_key->setValue($object['value']);

        return $system_key;
    }
}
