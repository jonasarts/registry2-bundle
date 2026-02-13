<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Engine;

use jonasarts\Bundle\RegistryBundle\Engine\RegistryEngineInterface;

/**
 * In-memory implementation of RegistryEngineInterface for testing.
 *
 * Mimics the storage behavior of the real engines (Doctrine/Redis)
 * without requiring external services.
 */
class InMemoryRegistryEngine implements RegistryEngineInterface
{
    /**
     * Registry storage: keyed by "userid|key|name|type"
     */
    private array $registryStore = [];

    /**
     * System storage: keyed by "key|name|type"
     */
    private array $systemStore = [];

    private function registryKey(int $userid, string $key, string $name, string $type): string
    {
        return "$userid|$key|$name|$type";
    }

    private function systemKey(string $key, string $name, string $type): string
    {
        return "$key|$name|$type";
    }

    public function registryExists(int $userid, string $key, string $name, string $type): bool
    {
        return array_key_exists($this->registryKey($userid, $key, $name, $type), $this->registryStore);
    }

    public function registryDelete(int $userid, string $key, string $name, string $type): bool
    {
        $k = $this->registryKey($userid, $key, $name, $type);

        if (array_key_exists($k, $this->registryStore)) {
            unset($this->registryStore[$k]);
            return true;
        }

        return false;
    }

    public function registryRead(int $userid, string $key, string $name, string $type): bool|string
    {
        $k = $this->registryKey($userid, $key, $name, $type);

        if (array_key_exists($k, $this->registryStore)) {
            return (string) $this->registryStore[$k];
        }

        return false;
    }

    public function registryWrite(int $userid, string $key, string $name, string $type, $value): bool
    {
        $k = $this->registryKey($userid, $key, $name, $type);
        $this->registryStore[$k] = strval($value);

        return true;
    }

    public function registryAll(): array
    {
        return array_values($this->registryStore);
    }

    public function systemExists(string $key, string $name, string $type): bool
    {
        return array_key_exists($this->systemKey($key, $name, $type), $this->systemStore);
    }

    public function systemDelete(string $key, string $name, string $type): bool
    {
        $k = $this->systemKey($key, $name, $type);

        if (array_key_exists($k, $this->systemStore)) {
            unset($this->systemStore[$k]);
            return true;
        }

        return false;
    }

    public function systemRead(string $key, string $name, string $type): bool|string
    {
        $k = $this->systemKey($key, $name, $type);

        if (array_key_exists($k, $this->systemStore)) {
            return (string) $this->systemStore[$k];
        }

        return false;
    }

    public function systemWrite(string $key, string $name, string $type, $value): bool
    {
        $k = $this->systemKey($key, $name, $type);
        $this->systemStore[$k] = strval($value);

        return true;
    }

    public function systemAll(): array
    {
        return array_values($this->systemStore);
    }

    public function clear(): void
    {
        $this->registryStore = [];
        $this->systemStore = [];
    }
}
