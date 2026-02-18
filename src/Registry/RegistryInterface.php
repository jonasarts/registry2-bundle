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

/**
 * RegistryInterface.
 *
 * Interface to the registry service
 */
interface RegistryInterface
{
    public function registryExists(int $user_id, string $key, string $name, string $type): bool;
    public function re(int $uid, string $k, string $n, string $t): bool;
    public function registryDelete(int $user_id, string $key, string $name, string $type): bool;
    public function rd(int $uid, string $k, string $n, string $t): bool;

    /**
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @param mixed $default
     * @return mixed
     */
    public function registryReadDefault(int $user_id, string $key, string $name, string $type, mixed $default): mixed;

    /**
     * @param int $uid
     * @param string $k
     * @param string $n
     * @param string $t
     * @param mixed $d
     * @return mixed
     */
    public function rrd(int $uid, string $k, string $n, string $t, mixed $d): mixed;

    /**
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @return mixed
     */
    public function registryRead(int $user_id, string $key, string $name, string $type): mixed;

    /**
     * @param int $uid
     * @param string $k
     * @param string $n
     * @param string $t
     * @return mixed
     */
    public function rr(int $uid, string $k, string $n, string $t): mixed;

    /**
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @return mixed
     */
    public function registryReadOnce(int $user_id, string $key, string $name, string $type): mixed;

    /**
     * @param int $uid
     * @param string $k
     * @param string $n
     * @param string $t
     * @return mixed
     */
    public function rro(int $uid, string $k, string $n, string $t): mixed;

    /**
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @param mixed $value
     * @return bool
     */
    public function registryWrite(int $user_id, string $key, string $name, string $type, mixed $value): bool;

    /**
     * @param int $uid
     * @param string $k
     * @param string $n
     * @param string $t
     * @param mixed $v
     * @return bool
     */
    public function rw(int $uid, string $k, string $n, string $t, mixed $v): bool;


    public function systemExists(string $key, string $name, string $type): bool;
    public function se(string $k, string $n, string $t): bool;
    public function systemDelete(string $key, string $name, string $type): bool;
    public function sd(string $k, string $n, string $t): bool;

    /**
     * @param string $key
     * @param string $name
     * @param string $type
     * @param mixed $default
     * @return mixed
     */
    public function systemReadDefault(string $key, string $name, string $type, mixed $default): mixed;

    /**
     * @param string $k
     * @param string $n
     * @param string $t
     * @param mixed $d
     * @return mixed
     */
    public function srd(string $k, string $n, string $t, mixed $d): mixed;

    /**
     * @param string $key
     * @param string $name
     * @param string $type
     * @return mixed
     */
    public function systemRead(string $key, string $name, string $type): mixed;

    /**
     * @param string $k
     * @param string $n
     * @param string $t
     * @return mixed
     */
    public function sr(string $k, string $n, string $t): mixed;

    /**
     * @param string $key
     * @param string $name
     * @param string $type
     * @return mixed
     */
    public function systemReadOnce(string $key, string $name, string $type): mixed;

    /**
     * @param string $k
     * @param string $n
     * @param string $t
     * @return mixed
     */
    public function sro(string $k, string $n, string $t): mixed;

    /**
     * @param string $key
     * @param string $name
     * @param string $type
     * @param mixed $value
     * @return bool
     */
    public function systemWrite(string $key, string $name, string $type, mixed $value): bool;

    /**
     * @param string $k
     * @param string $n
     * @param string $t
     * @param mixed $v
     * @return bool
     */
    public function sw(string $k, string $n, string $t, mixed $v): bool;

    /** @return array<int, mixed> */
    public function registryAll(): array;
}
