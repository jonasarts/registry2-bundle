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
use Doctrine\Persistence\ObjectRepository;
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
    /** @var ObjectRepository<RegKey> */
    private ObjectRepository $registry;

    // doctrine repository for system keys
    /** @var ObjectRepository<SysKey> */
    private ObjectRepository $system;

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
        return $this->registry->findOneBy(array('user_id' => $user_id, 'key' => $key, 'name' => $name, 'type' => $type)) instanceof RegKey;
    }

    // del
    public function registryDelete(int $user_id, string $key, string $name, string $type): bool
    {
        $entity = $this->registry->findOneBy(array('user_id' => $user_id, 'key' => $key, 'name' => $name, 'type' => $type));

        if ($entity instanceof RegKey) {
            $this->em->remove($entity);
            $this->em->flush();
        }

        return $entity instanceof RegKey;
    }

    // get
    public function registryRead(int $user_id, string $key, string $name, string $type): bool|string
    {
        $entity = $this->registry->findOneBy(array('user_id' => $user_id, 'key' => $key, 'name' => $name));

        if ($entity instanceof RegKey) {
            return (string) $entity->getValue();
        } else {
            return false;
        }
    }

    // set
    /**
     * @param mixed $value
     */
    public function registryWrite(int $user_id, string $key, string $name, string $type, $value): bool
    {
        $entity = $this->registry->findOneBy(array('user_id' => $user_id, 'key' => $key, 'name' => $name));

        if (!$entity instanceof RegKey) {
            $entity = new RegKey();
            $entity->setUserId($user_id);
            $entity->setKey($key);
            $entity->setName($name);
        }

        $entity->setType($type);
        // entity value must be of type 'string'
        if (is_array($value)) {
            $entity->setValue(json_encode($value, JSON_THROW_ON_ERROR));
        } else {
            $entity->setValue($this->stringify($value));
        }

        $this->em->persist($entity);
        $this->em->flush();

        return true;
    }

    /**
     * @return array<int, RegKey>
     */
    public function registryAll(): array
    {
        /** @var array<int, RegKey> $entities */
        $entities = $this->em
            ->getRepository(RegKey::class)
            ->findAll();

        return $entities;
    }

    // exists
    public function systemExists(string $key, string $name, string $type): bool
    {
        return $this->system->findOneBy(array('key' => $key, 'name' => $name, 'type' => $type)) instanceof SysKey;
    }

    // del
    public function systemDelete(string $key, string $name, string $type): bool
    {
        $entity = $this->system->findOneBy(array('key' => $key, 'name' => $name, 'type' => $type));

        if ($entity instanceof SysKey) {
            $this->em->remove($entity);
            $this->em->flush();
        }

        return $entity instanceof SysKey;
    }

    // get
    public function systemRead(string $key, string $name, string $type): bool|string
    {
        $entity = $this->system->findOneBy(array('key' => $key, 'name' => $name));

        if ($entity instanceof SysKey) {
            return (string) $entity->getValue();
        } else {
            return false;
        }
    }

    // set
    /**
     * @param mixed $value
     */
    public function systemWrite(string $key, string $name, string $type, $value): bool
    {
        $entity = $this->system->findOneBy(array('key' => $key, 'name' => $name));

        if (!$entity instanceof SysKey) {
            $entity = new SysKey();
            $entity->setKey($key);
            $entity->setName($name);
        }

        $entity->setType($type);
        // entity value must be of type 'string'
        if (is_array($value)) {
            $entity->setValue(json_encode($value, JSON_THROW_ON_ERROR));
        } else {
            $entity->setValue($this->stringify($value));
        }

        $this->em->persist($entity);
        $this->em->flush();

        return true;
    }

    /**
     * @return array<int, SysKey>
     */
    public function systemAll(): array
    {
        /** @var array<int, SysKey> $entities */
        $entities = $this->em
            ->getRepository(SysKey::class)
            ->findAll();

        return $entities;
    }

    /**
     * @param mixed $value
     */
    private function stringify($value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value) || is_bool($value) || $value === null) {
            return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
