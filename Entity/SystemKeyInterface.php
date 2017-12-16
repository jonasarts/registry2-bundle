<?php

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
    public function getKey();

    public function setKey($key);

    public function getName();

    public function setName($name);

    public function getType();

    public function setType($type);

    public function getValue();

    public function setValue($value);

    public function serialize();

    public static function deserialize($string);
}
