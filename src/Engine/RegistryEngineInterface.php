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
     * exists
     *
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool
     */
    public function registryExists(int $user_id, string $key, string $name, string $type): bool;

    /**
     * del
     *
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool
     */
    public function registryDelete(int $user_id, string $key, string $name, string $type): bool;

    /**
     * get - must return any value as string!
     *
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @return mixed
     */
    public function registryRead(int $user_id, string $key, string $name, string $type): mixed;

    /**
     * set
     *
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @param mixed $value
     * @return bool
     */
    public function registryWrite(int $user_id, string $key, string $name, string $type, mixed $value): bool;

    /**
     * all registry keys
     * @return array<int, mixed>
     */
    public function registryAll(): array;

    /**
     * System Key Methods.
     */

    /**
     * exists
     *
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool
     */
    public function systemExists(string $key, string $name, string $type): bool;

    /**
     * del
     *
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool
     */
    public function systemDelete(string $key, string $name, string $type): bool;

    /**
     * get - must return any value as string!
     *
     * @param string $key
     * @param string $name
     * @param string $type
     * @return mixed
     */
    public function systemRead(string $key, string $name, string $type): mixed;

    /**
     * set
     *
     * @param string $key
     * @param string $name
     * @param string $type
     * @param mixed $value
     * @return bool
     */
    public function systemWrite(string $key, string $name, string $type, mixed $value): bool;

    /**
     * all system keys
     *
     * @return array<int, mixed>
     */
    public function systemAll(): array;
}
