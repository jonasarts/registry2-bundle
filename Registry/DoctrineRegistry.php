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

use Symfony\Component\DependencyInjection\ContainerInterface;
use jonasarts\Bundle\RegistryBundle\Registry\AbstractRegistry;
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;

use jonasarts\Bundle\RegistryBundle\Entity\RegistryKeyEntity;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKeyEntity;

/**
 * DoctrineRegistry.
 * 
 * Implementation of AbstractRegistry using doctrine for persistence.
 */
class DoctrineRegistry extends AbstractRegistry implements RegistryInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        
        // get entity manager
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * @return ArrayCollection
     */
    public function registryAll()
    {
        $entities = $this->em
            ->getRepository(RegistryKeyEntity::class)
            ->findAll();

        return $entities;
    }

    /**
     * @return ArrayCollection
     */
    public function systemAll()
    {
        $entities = $this->em
            ->getRepository(SystemKeyEntity::class)
            ->findAll();

        return $entities;
    }
}
