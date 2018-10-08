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

    // exists
    public function registryExists(int $userid, string $key, string $name, string $type): bool;
    // del
    public function registryDelete(int $userid, string $key, string $name, string $type): bool;
    // get - must return any value as string!
    public function registryRead(int $userid, string $key, string $name, string $type); // mixed
    // set
    public function registryWrite(int $userid, string $key, string $name, string $type, $value): bool; // mixed value

    // all registry keys
    public function registryAll(): array;

    /**
     * System Key Methods.
     */

    // exists
    public function systemExists(string $key, string $name, string $type): bool;
    // del
    public function systemDelete(string $key, string $name, string $type): bool;
    // get - must return any value as string!
    public function systemRead(string $key, string $name, string $type); // mixed
    // set
    public function systemWrite(string $key, string $name, string $type, $value): bool; // mixed value

    // all system keys
    public function systemAll(): array;
}
