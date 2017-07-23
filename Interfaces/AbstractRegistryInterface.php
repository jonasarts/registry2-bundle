<?php

/*
 * This file is part of the jonasarts Registry bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\RegistryBundle\Interfaces;

/**
 * AbstractRegistryInterface.
 * 
 * Interface to the abstract registry service
 */
interface AbstractRegistryInterface
{
    /**
     * Registry Key Methods.
     */

    // exists
    public function registryExists($userid, $key, $name, $type);
    // del
    public function registryDelete($userid, $key, $name, $type);
    // get - must return any value as string!
    public function registryRead($userid, $key, $name, $type);
    // set
    public function registryWrite($userid, $key, $name, $type, $value);
    // expire
    public function registrySetTimeout($user_id, $key, $name, $seconds);

    /**
     * System Key Methods.
     */

    // exists
    public function systemExists($key, $name, $type);
    // del
    public function systemDelete($key, $name, $type);
    // get - must return any value as string!
    public function systemRead($key, $name, $type);
    // set
    public function systemWrite($key, $name, $type, $value);
    // expire
    public function systemSetTimeout($key, $name, $seconds);
}
