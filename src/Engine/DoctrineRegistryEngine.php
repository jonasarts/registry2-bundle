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
use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use JsonException;

/**
 * DoctrineRegistryEngine.
 */
class DoctrineRegistryEngine implements RegistryEngineInterface
{
    /**
     * @var ObjectRepository<RegKey> doctrine repository for registry keys
     */
    private readonly ObjectRepository $registry;

    /**
     * @var ObjectRepository<SysKey> doctrine repository for system keys
     */
    private readonly ObjectRepository $system;

    /**
     * Constructor.
     */
    public function __construct(/**
     * @var EntityManagerInterface entity manager
     */
        private readonly EntityManagerInterface $em)
    {
        // get repositories
        $this->registry = $this->em->getRepository(RegKey::class);
        $this->system = $this->em->getRepository(SysKey::class);
    }

    /**
     * exists.
     */
    public function registryExists(int $user_id, string $key, string $name, RegistryKeyType $type): bool
    {
        return $this->registry->findOneBy(['user_id' => $user_id, 'key' => $key, 'name' => $name, 'type' => $type->value]) instanceof RegKey;
    }

    /**
     * del.
     */
    public function registryDelete(int $user_id, string $key, string $name, RegistryKeyType $type): bool
    {
        $entity = $this->registry->findOneBy(['user_id' => $user_id, 'key' => $key, 'name' => $name, 'type' => $type->value]);

        if ($entity instanceof RegKey) {
            $this->em->remove($entity);
            $this->em->flush();
        }

        return $entity instanceof RegKey;
    }

    /**
     * get.
     */
    public function registryRead(int $user_id, string $key, string $name, RegistryKeyType $type): bool|string
    {
        $entity = $this->registry->findOneBy(['user_id' => $user_id, 'key' => $key, 'name' => $name, 'type' => $type->value]);

        if ($entity instanceof RegKey) {
            return $entity->getValue();
        }

        return false;
    }

    /**
     * set.
     *
     * @throws JsonException
     */
    public function registryWrite(int $user_id, string $key, string $name, RegistryKeyType $type, mixed $value): bool
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
            $entity->setValue(json_encode($value, \JSON_THROW_ON_ERROR));
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
     * exists.
     */
    public function systemExists(string $key, string $name, RegistryKeyType $type): bool
    {
        return $this->system->findOneBy(['key' => $key, 'name' => $name, 'type' => $type->value]) instanceof SysKey;
    }

    /**
     * del.
     */
    public function systemDelete(string $key, string $name, RegistryKeyType $type): bool
    {
        $entity = $this->system->findOneBy(['key' => $key, 'name' => $name, 'type' => $type->value]);

        if ($entity instanceof SysKey) {
            $this->em->remove($entity);
            $this->em->flush();
        }

        return $entity instanceof SysKey;
    }

    /**
     * get.
     */
    public function systemRead(string $key, string $name, RegistryKeyType $type): bool|string
    {
        $entity = $this->system->findOneBy(['key' => $key, 'name' => $name, 'type' => $type->value]);

        if ($entity instanceof SysKey) {
            return $entity->getValue();
        }

        return false;
    }

    /**
     * set.
     *
     * @throws JsonException
     */
    public function systemWrite(string $key, string $name, RegistryKeyType $type, mixed $value): bool
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
            $entity->setValue(json_encode($value, \JSON_THROW_ON_ERROR));
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
     * @throws JsonException
     */
    private function stringify(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value) || is_bool($value) || null === $value) {
            return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return json_encode($value, \JSON_THROW_ON_ERROR);
    }
}
