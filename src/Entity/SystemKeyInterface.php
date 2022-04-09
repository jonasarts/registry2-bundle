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

/**
 * SystemKeyInterface.
 *
 * Interface to a system key; a system key stores a global/system value
 */
interface SystemKeyInterface
{
    public function getKey(): string;

    public function setKey(string $key);

    public function getName(): string;

    public function setName(string $name);

    public function getType(): string;

    public function setType(string $type);

    public function getValue(): string;

    public function setValue(string $value);

    public function serialize();

    public static function deserialize($string);
}
