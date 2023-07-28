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

use Doctrine\ORM\EntityManagerInterface;
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKeyEntity as RegKey;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKeyEntity as SysKey;

/**
 *
 */
class DoctrineRegistryEngine implements RegistryEngineInterface
{
    // entity manager
    private EntityManagerInterface $em;

    // doctrine repository for registry keys
    private \Doctrine\ORM\EntityRepository $registry;

    // doctrine repository for system keys
    private \Doctrine\ORM\EntityRepository $system;

    /**
     * Constructor.
     */
    public function __construct(EntityManagerInterface $em)
    {
        // get entity manager
        $this->em = $em;

        // get repositories
        $this->registry = $this->em->getRepository(RegKey::class);
        $this->system = $this->em->getRepository(SysKey::class);
    }

    // exists
    public function registryExists(int $user_id, string $key, string $name, string $type): bool
    {
        return !is_null($this->registry->findOneBy(array('user_id' => $user_id, 'key' => $key, 'name' => $name, 'type' => $type)));
    }

    // del
    public function registryDelete(int $user_id, string $key, string $name, string $type): bool
    {
        $entity = $this->registry->findOneBy(array('user_id' => $user_id, 'key' => $key, 'name' => $name, 'type' => $type));

        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
        }

        return !is_null($entity);
    }

    // get
    public function registryRead(int $user_id, string $key, string $name, string $type): bool|string
    {
        $entity = $this->registry->findOneBy(array('user_id' => $user_id, 'key' => $key, 'name' => $name));

        if ($entity) {
            return (string) $entity->getValue();
        } else {
            return false;
        }
    }

    // set
    public function registryWrite(int $user_id, string $key, string $name, string $type, $value): bool
    {
        $entity = $this->registry->findOneBy(array('user_id' => $user_id, 'key' => $key, 'name' => $name));

        if (!$entity) {
            $entity = new RegKey();
            $entity->setUserId($user_id);
            $entity->setKey($key);
            $entity->setName($name);
        }

        $entity->setType($type);
        // entity value must be of type 'string'
        if (is_array($value)) {
            $entity->setValue(json_encode($value));
        } else if (is_object($value)) {
            $entity->setValue((string) $value);
        } else {
            // works for string, int, float, bool
            $entity->setValue(strval($value));
        }

        $this->em->merge($entity);
        $this->em->flush();

        return !is_null($entity);
    }

    /**
     * @return array
     */
    public function registryAll(): array
    {
        $entities = $this->em
            ->getRepository(RegKey::class)
            ->findAll();

        return $entities;
    }

    // exists
    public function systemExists(string $key, string $name, string $type): bool
    {
        return !is_null($this->system->findOneBy(array('key' => $key, 'name' => $name, 'type' => $type)));
    }

    // del
    public function systemDelete(string $key, string $name, string $type): bool
    {
        $entity = $this->system->findOneBy(array('key' => $key, 'name' => $name, 'type' => $type));

        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
        }

        return !is_null($entity);
    }

    // get
    public function systemRead(string $key, string $name, string $type): bool|string
    {
        $entity = $this->system->findOneBy(array('key' => $key, 'name' => $name));

        if ($entity) {
            return (string) $entity->getValue();
        } else {
            return false;
        }
    }

    // set
    public function systemWrite(string $key, string $name, string $type, $value): bool
    {
        $entity = $this->system->findOneBy(array('key' => $key, 'name' => $name));

        if (!$entity) {
            $entity = new SysKey();
            $entity->setKey($key);
            $entity->setName($name);
        }

        $entity->setType($type);
        // entity value must be of type 'string'
        if (is_array($value)) {
            $entity->setValue(json_encode($value));
        } else if (is_object($value)) {
            $entity->setValue((string) $value);
        } else {
            // works for string, int, float, bool
            $entity->setValue(strval($value));
        }

        $this->em->merge($entity);
        $this->em->flush();

        return !is_null($entity);
    }

    /**
     * @return array
     */
    public function systemAll(): array
    {
        $entities = $this->em
            ->getRepository(SysKey::class)
            ->findAll();

        return $entities;
    }
}
