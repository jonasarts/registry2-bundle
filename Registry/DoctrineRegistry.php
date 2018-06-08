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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use jonasarts\Bundle\RegistryBundle\Engines\DoctrineRegistryEngine;
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKeyEntity;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKeyEntity;
use jonasarts\Bundle\RegistryBundle\Registry\AbstractRegistry;
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;


/**
 * DoctrineRegistry.
 * 
 * Implementation of AbstractRegistry using doctrine for persistence.
 */
class DoctrineRegistry extends AbstractRegistry implements RegistryInterface
{
    /**
     * 
     */
    public function __constructor(ContainerInterface $container, EntityManagerInterface $em)
    {
        parent::__construct($container);

        // create the engine
        $this->engine = new DoctrineRegistryEngine($container, $em);
    }
}
