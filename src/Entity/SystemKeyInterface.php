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

/**
 * SystemKeyInterface.
 *
 * Interface to a system key; a system key stores a global/system value
 */
interface SystemKeyInterface
{
    public function getKey(): string;

    public function setKey(string $key): self;

    public function getName(): string;

    public function setName(string $name): self;

    public function getType(): RegistryKeyType;

    public function setType(RegistryKeyType $type): self;

    public function getValue(): string;

    public function setValue(string $value): self;

    public function serialize(): string;

    public static function deserialize(string $string): self;
}
