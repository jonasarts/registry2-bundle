<?php

/*
 * This file is part of the jonasarts Registry bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\RegistryBundle\Engines;

use Symfony\Component\DependencyInjection\ContainerInterface;
use jonasarts\Bundle\RegistryBundle\Registry\AbstractRegistryInterface;
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKeyEntity as RegKey;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKeyEntity as SysKey;

/**
 * 
 */
class DoctrineRegistryEngine implements AbstractRegistryInterface
{
    // entity manager
    private $em;

    // doctrine repository for registry keys
    private $registry;

    // doctrine repository for system keys
    private $system;

    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        // get entity manager
        $this->em = $container->get('doctrine.orm.entity_manager');

        // get repositories
        $this->registry = $this->em->getRepository(RegKey::class);
        $this->system = $this->em->getRepository(SysKey::class);
    }

    // exists
    public function registryExists($user_id, $key, $name, $type)
    {
        return !is_null($this->registry->findOneBy(array('user_id' => $user_id, 'key' => $key, 'name' => $name, 'type' => $type)));
    }

    // del
    public function registryDelete($user_id, $key, $name, $type)
    {
        $entity = $this->registry->findOneBy(array('user_id' => $user_id, 'key' => $key, 'name' => $name, 'type' => $type));

        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
        }

        return !is_null($entity);
    }

    // get
    public function registryRead($user_id, $key, $name, $type)
    {
        $entity = $this->registry->findOneBy(array('user_id' => $user_id, 'key' => $key, 'name' => $name));

        if ($entity) {
            return (string) $entity->getValue();
        } else {
            return false;
        }
    }

    // set
    public function registryWrite($user_id, $key, $name, $type, $value)
    {
        $entity = $this->registry->findOneBy(array('user_id' => $user_id, 'key' => $key, 'name' => $name));

        if (!$entity) {
            $entity = new RegKey();
            $entity->setUserId($user_id);
            $entity->setKey($key);
            $entity->setName($name);
        }

        $entity->setType($type);
        $entity->setValue($value);

        $this->em->merge($entity);
        $this->em->flush();

        return !is_null($entity);
    }

    // exists
    public function systemExists($key, $name, $type)
    {
        return !is_null($this->system->findOneBy(array('key' => $key, 'name' => $name, 'type' => $type)));
    }

    // del
    public function systemDelete($key, $name, $type)
    {
        $entity = $this->system->findOneBy(array('key' => $key, 'name' => $name, 'type' => $type));

        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
        }

        return !is_null($entity);
    }

    // get
    public function systemRead($key, $name, $type)
    {
        $entity = $this->system->findOneBy(array('key' => $key, 'name' => $name));

        if ($entity) {
            return (string) $entity->getValue();
        } else {
            return false;
        }
    }

    // set
    public function systemWrite($key, $name, $type, $value)
    {
        $entity = $this->system->findOneBy(array('key' => $key, 'name' => $name));

        if (!$entity) {
            $entity = new SysKey();
            $entity->setKey($key);
            $entity->setName($name);
        }

        $entity->setType($type);
        $entity->setValue($value);

        $this->em->merge($entity);
        $this->em->flush();

        return !is_null($entity);
    }
}
