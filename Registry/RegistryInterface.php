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
    public function registryReadDefault(int $user_id, string $key, string $name, string $type, $default);
    public function rrd(int $uid, string $k, string $n, string $t, $d);
    public function registryRead(int $user_id, string $key, string $name, string $type);
    public function rr(int $uid, string $k, string $n, string $t);
    public function registryReadOnce(int $user_id, string $key, string $name, string $type);
    public function rro(int $uid, string $k, string $n, string $t);
    public function registryWrite(int $user_id, string $key, string $name, string $type, $value): bool;
    public function rw(int $uid, string $k, string $n, string $t, $v): bool;


    public function systemExists(string $key, string $name, string $type): bool;
    public function se(string $k, string $n, string $t): bool;
    public function systemDelete(string $key, string $name, string $type): bool;
    public function sd(string $k, string $n, string $t): bool;
    public function systemReadDefault(string $key, string $name, string $type, $default);
    public function srd(string $k, string $n, string $t, $d);
    public function systemRead(string $key, string $name, string $type);
    public function sr(string $k, string $n, string $t);
    public function systemReadOnce(string $key, string $name, string $type);
    public function sro(string $k, string $n, string $t);
    public function systemWrite(string $key, string $name, string $type, $value): bool;
    public function sw(string $k, string $n, string $t, $v): bool;
    
}
