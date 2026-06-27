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

namespace jonasarts\Bundle\RegistryBundle\Engine;

use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;

/**
 * RegistryEngineInterface.
 *
 * Interface to the registry service
 */
interface RegistryEngineInterface
{
    /**
     * Registry Key Methods.
     */
    /**
     * exists.
     */
    public function registryExists(int $user_id, string $key, string $name, RegistryKeyType $type): bool;

    /**
     * del.
     */
    public function registryDelete(int $user_id, string $key, string $name, RegistryKeyType $type): bool;

    /**
     * get - must return any value as string!
     */
    public function registryRead(int $user_id, string $key, string $name, RegistryKeyType $type): mixed;

    /**
     * set.
     */
    public function registryWrite(int $user_id, string $key, string $name, RegistryKeyType $type, mixed $value): bool;

    /**
     * all registry keys.
     *
     * @return array<int, mixed>
     */
    public function registryAll(): array;

    /**
     * System Key Methods.
     */
    /**
     * exists.
     */
    public function systemExists(string $key, string $name, RegistryKeyType $type): bool;

    /**
     * del.
     */
    public function systemDelete(string $key, string $name, RegistryKeyType $type): bool;

    /**
     * get - must return any value as string!
     */
    public function systemRead(string $key, string $name, RegistryKeyType $type): mixed;

    /**
     * set.
     */
    public function systemWrite(string $key, string $name, RegistryKeyType $type, mixed $value): bool;

    /**
     * all system keys.
     *
     * @return array<int, mixed>
     */
    public function systemAll(): array;
}
