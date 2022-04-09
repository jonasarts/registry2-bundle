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
 * RegistryKeyInterface.
 *
 * Interface for a registry key; a registry key stores a value for an user
 */
interface RegistryKeyInterface extends SystemKeyInterface
{
    public function getUserId(): int;

    public function setUserId(int $user_id);
}
