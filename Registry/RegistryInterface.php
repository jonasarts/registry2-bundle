<?php

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
 * Extended interface to the 'basic' registry service
 */
interface RegistryInterface extends AbstractRegistryInterface
{
    // all registry keys
    public function registryAll();
    // all system keys
    public function systemAll();
}