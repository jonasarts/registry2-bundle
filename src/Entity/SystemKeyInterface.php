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

    /** @return self */
    public function setKey(string $key);

    public function getName(): string;

    /** @return self */
    public function setName(string $name);

    public function getType(): string;

    /** @return self */
    public function setType(string $type);

    public function getValue(): string;

    /** @return self */
    public function setValue(string $value);

    /** @return string */
    public function serialize();

    /**
     * @param string $string
     * @return self
     */
    public static function deserialize($string);
}
