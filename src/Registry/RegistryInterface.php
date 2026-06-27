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

namespace jonasarts\Bundle\RegistryBundle\Registry;

use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;

/**
 * RegistryInterface.
 *
 * Interface to the registry service
 */
interface RegistryInterface
{
    public function registryExists(int $user_id, string $key, string $name, string|RegistryKeyType $type): bool;

    public function re(int $uid, string $k, string $n, string|RegistryKeyType $t): bool;

    public function registryDelete(int $user_id, string $key, string $name, string|RegistryKeyType $type): bool;

    public function rd(int $uid, string $k, string $n, string|RegistryKeyType $t): bool;

    public function registryReadDefault(int $user_id, string $key, string $name, string|RegistryKeyType $type, mixed $default): mixed;

    public function rrd(int $uid, string $k, string $n, string|RegistryKeyType $t, mixed $d): mixed;

    public function registryRead(int $user_id, string $key, string $name, string|RegistryKeyType $type): mixed;

    public function rr(int $uid, string $k, string $n, string|RegistryKeyType $t): mixed;

    public function registryReadOnce(int $user_id, string $key, string $name, string|RegistryKeyType $type): mixed;

    public function rro(int $uid, string $k, string $n, string|RegistryKeyType $t): mixed;

    public function registryWrite(int $user_id, string $key, string $name, string|RegistryKeyType $type, mixed $value): bool;

    public function rw(int $uid, string $k, string $n, string|RegistryKeyType $t, mixed $v): bool;

    public function systemExists(string $key, string $name, string|RegistryKeyType $type): bool;

    public function se(string $k, string $n, string|RegistryKeyType $t): bool;

    public function systemDelete(string $key, string $name, string|RegistryKeyType $type): bool;

    public function sd(string $k, string $n, string|RegistryKeyType $t): bool;

    public function systemReadDefault(string $key, string $name, string|RegistryKeyType $type, mixed $default): mixed;

    public function srd(string $k, string $n, string|RegistryKeyType $t, mixed $d): mixed;

    public function systemRead(string $key, string $name, string|RegistryKeyType $type): mixed;

    public function sr(string $k, string $n, string|RegistryKeyType $t): mixed;

    public function systemReadOnce(string $key, string $name, string|RegistryKeyType $type): mixed;

    public function sro(string $k, string $n, string|RegistryKeyType $t): mixed;

    public function systemWrite(string $key, string $name, string|RegistryKeyType $type, mixed $value): bool;

    public function sw(string $k, string $n, string|RegistryKeyType $t, mixed $v): bool;

    /**
     * @return array<int, mixed>
     */
    public function registryAll(): array;
}
