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

namespace jonasarts\Bundle\RegistryBundle\Registry;

use DateTimeInterface;
use jonasarts\Bundle\RegistryBundle\Engine\RegistryEngineInterface;
use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * The registry logic.
 *
 * This class contains the business logic for registry keys.
 */
abstract class AbstractRegistry implements RegistryInterface
{
    protected RegistryEngineInterface $engine;

    /** boolean, use default key-name/value array */
    protected bool $use_yaml = false;

    /** @var array<string, array<string, scalar>> default key-name/value array */
    protected array $yaml = [];

    /** field delimiter (used in yaml) */
    protected string $delimiter = ':';

    private function optimizeType(string|RegistryKeyType $type): RegistryKeyType
    {
        if ($type instanceof RegistryKeyType) {
            return $type;
        }

        // type is string
        return match (trim(strtolower($type))) {
            'i', 'int', 'integer' => RegistryKeyType::INTEGER,
            'b', 'bln', 'boolean' => RegistryKeyType::BOOLEAN,
            // 's', 'str', 'string' => RegistryKeyType::STRING,
            'f', 'flt', 'float' => RegistryKeyType::FLOAT,
            'd', 'dat', 'date' => RegistryKeyType::DATE,
            't', 'tim', 'time' => RegistryKeyType::TIME,
            'a', 'arr', 'array' => RegistryKeyType::ARRAY,
            default => RegistryKeyType::STRING,
        };
    }

    /**
     * Constructor.
     */
    public function __construct(?string $default_values_filename = null)
    {
        if (is_string($default_values_filename) && file_exists($default_values_filename)) {
            $parsed = Yaml::parseFile($default_values_filename);
            if (is_array($parsed)) {
                /** @var array<string, array<string, scalar>> $yaml */
                $yaml = [];
                foreach ($parsed as $scope => $entries) {
                    if (!is_string($scope) || !is_array($entries)) {
                        continue;
                    }

                    foreach ($entries as $entryKey => $entryValue) {
                        if (!is_string($entryKey) || !is_scalar($entryValue)) {
                            continue;
                        }

                        $yaml[$scope][$entryKey] = $entryValue;
                    }
                }

                $this->yaml = $yaml;
            }

            $this->use_yaml = true;
        }
    }

    public function registryExists(int $user_id, string $key, string $name, string|RegistryKeyType $type): bool
    {
        return $this->engine->registryExists($user_id, $key, $name, $this->optimizeType($type));
    }

    public function re(int $uid, string $k, string $n, string|RegistryKeyType $t): bool
    {
        return $this->registryExists($uid, $k, $n, $t);
    }

    public function registryDelete(int $user_id, string $key, string $name, string|RegistryKeyType $type): bool
    {
        return $this->engine->registryDelete($user_id, $key, $name, $this->optimizeType($type));
    }

    public function rd(int $uid, string $k, string $n, string|RegistryKeyType $t): bool
    {
        return $this->registryDelete($uid, $k, $n, $t);
    }

    public function registryReadDefault(int $user_id, string $key, string $name, string|RegistryKeyType $type, mixed $default): mixed
    {
        $type = $this->optimizeType($type);

        $value = $this->engine->registryRead($user_id, $key, $name, $type);
        if (false === $value) {
            $value = $this->engine->registryRead(0, $key, $name, $type);
        }

        if (is_string($value)) {
            return $this->decodeTypedValue($type, $value);
        }

        return $this->normalizeDefaultValue($type, $default);
    }

    public function rrd(int $uid, string $k, string $n, string|RegistryKeyType $t, mixed $d): mixed
    {
        return $this->registryReadDefault($uid, $k, $n, $t, $d);
    }

    public function registryRead(int $user_id, string $key, string $name, string|RegistryKeyType $type): mixed
    {
        $result = $this->registryReadDefault($user_id, $key, $name, $type, null);

        if (null === $result && $this->use_yaml) {
            $result = $this->readDefaultKeyValue('registry', $key, $name, $type);
        }

        return $result;
    }

    public function rr(int $uid, string $k, string $n, string|RegistryKeyType $t): mixed
    {
        return $this->registryRead($uid, $k, $n, $t);
    }

    public function registryReadOnce(int $user_id, string $key, string $name, string|RegistryKeyType $type): mixed
    {
        $r = $this->registryRead($user_id, $key, $name, $type);
        $this->registryDelete($user_id, $key, $name, $type);

        return $r;
    }

    public function rro(int $uid, string $k, string $n, string|RegistryKeyType $t): mixed
    {
        return $this->registryReadOnce($uid, $k, $n, $t);
    }

    /**
     * @throws RuntimeException
     */
    public function registryWrite(int $user_id, string $key, string $name, string|RegistryKeyType $type, mixed $value): bool
    {
        if (str_contains($name, $this->delimiter)) {
            throw new RuntimeException('delimiter is not allowed in name');
        }

        $internal_type = $this->optimizeType($type);

        if (0 !== $user_id) {
            $result = $this->registryRead(0, $key, $name, $internal_type);
            if (null !== $result && $result === $value) {
                return $this->registryDelete($user_id, $key, $name, $internal_type);
            }
        }

        if ((RegistryKeyType::DATE === $internal_type || RegistryKeyType::TIME === $internal_type) && $value instanceof DateTimeInterface) {
            $value = $value->format('c');
        }

        return $this->engine->registryWrite($user_id, $key, $name, $internal_type, $value);
    }

    /**
     * @throws RuntimeException
     */
    public function rw(int $uid, string $k, string $n, string|RegistryKeyType $t, mixed $v): bool
    {
        return $this->registryWrite($uid, $k, $n, $t, $v);
    }

    /**
     * @return array<int, mixed>
     */
    public function registryAll(): array
    {
        return $this->engine->registryAll();
    }

    public function systemExists(string $key, string $name, string|RegistryKeyType $type): bool
    {
        return $this->engine->systemExists($key, $name, $this->optimizeType($type));
    }

    public function se(string $k, string $n, string|RegistryKeyType $t): bool
    {
        return $this->systemExists($k, $n, $t);
    }

    public function systemDelete(string $key, string $name, string|RegistryKeyType $type): bool
    {
        return $this->engine->systemDelete($key, $name, $this->optimizeType($type));
    }

    public function sd(string $k, string $n, string|RegistryKeyType $t): bool
    {
        return $this->systemDelete($k, $n, $t);
    }

    public function systemReadDefault(string $key, string $name, string|RegistryKeyType $type, mixed $default): mixed
    {
        $type = $this->optimizeType($type);

        $value = $this->engine->systemRead($key, $name, $type);
        if (is_string($value)) {
            return $this->decodeTypedValue($type, $value);
        }

        return $this->normalizeDefaultValue($type, $default);
    }

    public function srd(string $k, string $n, string|RegistryKeyType $t, mixed $d): mixed
    {
        return $this->systemReadDefault($k, $n, $t, $d);
    }

    public function systemRead(string $key, string $name, string|RegistryKeyType $type): mixed
    {
        $result = $this->systemReadDefault($key, $name, $type, null);

        if (null === $result && $this->use_yaml) {
            $result = $this->readDefaultKeyValue('system', $key, $name, $type);
        }

        return $result;
    }

    public function sr(string $k, string $n, string|RegistryKeyType $t): mixed
    {
        return $this->systemRead($k, $n, $t);
    }

    public function systemReadOnce(string $key, string $name, string|RegistryKeyType $type): mixed
    {
        $r = $this->systemRead($key, $name, $type);
        $this->systemDelete($key, $name, $type);

        return $r;
    }

    public function sro(string $k, string $n, string|RegistryKeyType $t): mixed
    {
        return $this->systemReadOnce($k, $n, $t);
    }

    /**
     * @throws RuntimeException
     */
    public function systemWrite(string $key, string $name, string|RegistryKeyType $type, mixed $value): bool
    {
        if (str_contains($name, $this->delimiter)) {
            throw new RuntimeException('delimiter is not allowed in name');
        }

        $internal_type = $this->optimizeType($type);

        if ((RegistryKeyType::DATE === $internal_type || RegistryKeyType::TIME === $internal_type) && $value instanceof DateTimeInterface) {
            $value = $value->format('c');
        }

        return $this->engine->systemWrite($key, $name, $internal_type, $value);
    }

    /**
     * @throws RuntimeException
     */
    public function sw(string $k, string $n, string|RegistryKeyType $t, mixed $v): bool
    {
        return $this->systemWrite($k, $n, $t, $v);
    }

    /**
     * @return array<int, mixed>
     */
    public function systemAll(): array
    {
        return $this->engine->systemAll();
    }

    private function readDefaultKeyValue(string $scope, string $key, string $name, string|RegistryKeyType $type): mixed
    {
        $path = $key.$this->delimiter.$name;

        if (!isset($this->yaml[$scope]) || !array_key_exists($path, $this->yaml[$scope])) {
            return null;
        }

        $value = $this->yaml[$scope][$path];

        if (is_string($value)) {
            return $this->decodeTypedValue($this->optimizeType($type), $value);
        }

        return $this->normalizeDefaultValue($this->optimizeType($type), $value);
    }

    private function decodeTypedValue(RegistryKeyType $type, string $value): mixed
    {
        return match ($type) {
            RegistryKeyType::INTEGER => (int) $value,
            RegistryKeyType::BOOLEAN => (bool) $value,
            RegistryKeyType::STRING => $value,
            RegistryKeyType::FLOAT => (float) $value,
            RegistryKeyType::DATE, RegistryKeyType::TIME => is_numeric($value) ? (int) $value : strtotime($value),
            RegistryKeyType::ARRAY => json_decode($value, true),
            default => $value,
        };
    }

    private function normalizeDefaultValue(RegistryKeyType $type, mixed $default): mixed
    {
        if (null === $default) {
            return null;
        }

        return match ($type) {
            RegistryKeyType::INTEGER => is_numeric($default) ? (int) $default : 0,
            RegistryKeyType::BOOLEAN => (bool) $default,
            RegistryKeyType::STRING => is_scalar($default) ? (string) $default : '',
            RegistryKeyType::FLOAT => is_numeric($default) ? (float) $default : 0.0,
            RegistryKeyType::DATE, RegistryKeyType::TIME => $default instanceof DateTimeInterface
                ? $default
                : (is_int($default)
                    ? $default
                    : (is_string($default) ? strtotime($default) : null)),
            RegistryKeyType::ARRAY => is_array($default) ? $default : [],
            default => $default,
        };
    }
}
