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
 * DoctrineRegistryEngine
 */
class DoctrineRegistryEngine implements RegistryEngineInterface
{
    /**
     * @var EntityManagerInterface entity manager
     */
    private EntityManagerInterface $em;

    /**
     * @var ObjectRepository<RegKey> doctrine repository for registry keys
     */
    private ObjectRepository $registry;

    /**
     * @var ObjectRepository<SysKey> doctrine repository for system keys
     */
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

    /**
     * exists
     *
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool
     */
    public function registryExists(int $user_id, string $key, string $name, string $type): bool
    {
        return $this->registry->findOneBy(['user_id' => $user_id, 'key' => $key, 'name' => $name, 'type' => $type]) instanceof RegKey;
    }

    /**
     * del
     *
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool
     */
    public function registryDelete(int $user_id, string $key, string $name, string $type): bool
    {
        $entity = $this->registry->findOneBy(['user_id' => $user_id, 'key' => $key, 'name' => $name, 'type' => $type]);

        if ($entity instanceof RegKey) {
            $this->em->remove($entity);
            $this->em->flush();
        }

        return $entity instanceof RegKey;
    }

    /**
     * get
     *
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool|string
     */
    public function registryRead(int $user_id, string $key, string $name, string $type): bool|string
    {
        $entity = $this->registry->findOneBy(['user_id' => $user_id, 'key' => $key, 'name' => $name]);

        if ($entity instanceof RegKey) {
            return (string) $entity->getValue();
        } else {
            return false;
        }
    }

    /**
     * set
     *
     * @param int $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @param mixed $value
     * @return bool
     * @throws \JsonException
     */
    public function registryWrite(int $user_id, string $key, string $name, string $type, mixed $value): bool
    {
        $entity = $this->registry->findOneBy(['user_id' => $user_id, 'key' => $key, 'name' => $name]);

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

    /**
     * exists
     *
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool
     */
    public function systemExists(string $key, string $name, string $type): bool
    {
        return $this->system->findOneBy(['key' => $key, 'name' => $name, 'type' => $type]) instanceof SysKey;
    }

    /**
     * del
     *
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool
     */
    public function systemDelete(string $key, string $name, string $type): bool
    {
        $entity = $this->system->findOneBy(['key' => $key, 'name' => $name, 'type' => $type]);

        if ($entity instanceof SysKey) {
            $this->em->remove($entity);
            $this->em->flush();
        }

        return $entity instanceof SysKey;
    }

    /**
     * get
     *
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool|string
     */
    public function systemRead(string $key, string $name, string $type): bool|string
    {
        $entity = $this->system->findOneBy(['key' => $key, 'name' => $name]);

        if ($entity instanceof SysKey) {
            return (string) $entity->getValue();
        } else {
            return false;
        }
    }

    /**
     * set
     *
     * @param string $key
     * @param string $name
     * @param string $type
     * @param mixed $value
     * @return bool
     * @throws \JsonException
     */
    public function systemWrite(string $key, string $name, string $type, mixed $value): bool
    {
        $entity = $this->system->findOneBy(['key' => $key, 'name' => $name]);

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
     * @return string
     * @throws \JsonException
     */
    private function stringify(mixed $value): string
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
