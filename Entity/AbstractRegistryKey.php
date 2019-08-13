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

use jonasarts\Bundle\RegistryBundle\Entity\RegistryKeyInterface;

/**
 * AbstractRegistryKey.
 *
 * Stores a user value
 */
abstract class AbstractRegistryKey
{
    // this class is 'empty' because the mapping annotation for doctrine
    // would need to reimplement all fields in the doctrine-version again
    // or use yaml / xml for doctrine entity definition

    // /**
    //  * @var string
    //  */
    // private $key;

    // /**
    //  * @var string
    //  */
    // private $name;

    // /**
    //  * @var string
    //  */
    // private $type;

    // /**
    //  * @var string
    //  */
    // private $value;

    // /**
    //  * Get id.
    //  *
    //  * @return int
    //  */
    // public function getId()
    // {
    //     return $this->id;
    // }

    // /**
    //  * Set key.
    //  *
    //  * @param string $key
    //  * @return System
    //  */
    // public function setKey($key)
    // {
    //     $this->key = $key;

    //     return $this;
    // }

    // /**
    //  * Get key.
    //  *
    //  * @return string
    //  */
    // public function getKey()
    // {
    //     return $this->key;
    // }

    // /**
    //  * Set name.
    //  *
    //  * @param string $name
    //  * @return System
    //  */
    // public function setName($name)
    // {
    //     $this->name = $name;

    //     return $this;
    // }

    // /**
    //  * Get name.
    //  *
    //  * @return string
    //  */
    // public function getName()
    // {
    //     return $this->name;
    // }

    // /**
    //  * Set type.
    //  *
    //  * @param string $type
    //  * @return System
    //  */
    // public function setType($type)
    // {
    //     $this->type = $type;

    //     return $this;
    // }

    // /**
    //  * Get type.
    //  *
    //  * @return string
    //  */
    // public function getType()
    // {
    //     return $this->type;
    // }

    // /**
    //  * Set value.
    //  *
    //  * @param string $value
    //  * @return System
    //  */
    // public function setValue($value)
    // {
    //     $this->value = $value;

    //     return $this;
    // }

    // /**
    //  * Get value.
    //  *
    //  * @return string
    //  */
    // public function getValue()
    // {
    //     return $this->value;
    // }

}
