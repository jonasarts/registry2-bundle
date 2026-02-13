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

use jonasarts\Bundle\RegistryBundle\Engine\RegistryEngineInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * The registry logic
 *
 * This class contains the business logic for registry keys.
 */
abstract class AbstractRegistry implements RegistryInterface
{
    protected RegistryEngineInterface $engine;

    /** boolean, use default key-name/value array */
    protected bool $use_yaml;

    /** @var array<string, array<string, scalar>> default key-name/value array */
    protected array $yaml;

    /** field delimiter (used in yaml) */
    protected string $delimiter;

    private function optimizeType(string $type): string
    {
        return match (trim($type)) {
            'i', 'int', 'integer' => 'i',
            'b', 'bln', 'boolean' => 'b',
            's', 'str', 'string' => 's',
            'f', 'flt', 'float' => 'f',
            'd', 'dat', 'date' => 'd',
            't', 'tim', 'time' => 't',
            default => 's',
        };
    }

    /** Constructor. */
    public function __construct(?string $default_values_filename = null)
    {
        $this->use_yaml = false;
        $this->yaml = [];
        $this->delimiter = ':';

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

    public function registryExists(int $user_id, string $key, string $name, string $type): bool
    {
        return $this->engine->registryExists($user_id, $key, $name, $this->optimizeType($type));
    }

    public function re(int $uid, string $k, string $n, string $t): bool
    {
        return $this->registryExists($uid, $k, $n, $t);
    }

    public function registryDelete(int $user_id, string $key, string $name, string $type): bool
    {
        return $this->engine->registryDelete($user_id, $key, $name, $this->optimizeType($type));
    }

    public function rd(int $uid, string $k, string $n, string $t): bool
    {
        return $this->registryDelete($uid, $k, $n, $t);
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function registryReadDefault(int $user_id, string $key, string $name, string $type, $default)
    {
        $type = $this->optimizeType($type);

        $value = $this->engine->registryRead($user_id, $key, $name, $type);
        if ($value === false) {
            $value = $this->engine->registryRead(0, $key, $name, $type);
        }

        if (is_string($value)) {
            return $this->decodeTypedValue($type, $value);
        }

        return $this->normalizeDefaultValue($type, $default);
    }

    /**
     * @param mixed $d
     * @return mixed
     */
    public function rrd(int $uid, string $k, string $n, string $t, $d)
    {
        return $this->registryReadDefault($uid, $k, $n, $t, $d);
    }

    /** @return mixed */
    public function registryRead(int $user_id, string $key, string $name, string $type)
    {
        $result = $this->registryReadDefault($user_id, $key, $name, $type, null);

        if ($result === null && $this->use_yaml) {
            $result = $this->readDefaultKeyValue('registry', $key, $name, $type);
        }

        return $result;
    }

    /** @return mixed */
    public function rr(int $uid, string $k, string $n, string $t)
    {
        return $this->registryRead($uid, $k, $n, $t);
    }

    /** @return mixed */
    public function registryReadOnce(int $user_id, string $key, string $name, string $type)
    {
        $r = $this->registryRead($user_id, $key, $name, $type);
        $this->registryDelete($user_id, $key, $name, $type);

        return $r;
    }

    /** @return mixed */
    public function rro(int $uid, string $k, string $n, string $t)
    {
        return $this->registryReadOnce($uid, $k, $n, $t);
    }

    /**
     * @param mixed $value
     * @throws \Exception
     */
    public function registryWrite(int $user_id, string $key, string $name, string $type, $value): bool
    {
        if (str_contains($name, $this->delimiter)) {
            throw new \Exception('delimiter is not allowed in name');
        }

        $type = $this->optimizeType($type);

        if ($user_id !== 0) {
            $result = $this->registryRead(0, $key, $name, $type);
            if ($result !== null && $result === $value) {
                return $this->registryDelete($user_id, $key, $name, $type);
            }
        }

        if (($type === 'd' || $type === 't') && $value instanceof \DateTimeInterface) {
            $value = $value->format('c');
        }

        return $this->engine->registryWrite($user_id, $key, $name, $type, $value);
    }

    /**
     * @param mixed $v
     * @throws \Exception
     */
    public function rw(int $uid, string $k, string $n, string $t, $v): bool
    {
        return $this->registryWrite($uid, $k, $n, $t, $v);
    }

    /** @return array<int, mixed> */
    public function registryAll(): array
    {
        return $this->engine->registryAll();
    }

    public function systemExists(string $key, string $name, string $type): bool
    {
        return $this->engine->systemExists($key, $name, $this->optimizeType($type));
    }

    public function se(string $k, string $n, string $t): bool
    {
        return $this->systemExists($k, $n, $t);
    }

    public function systemDelete(string $key, string $name, string $type): bool
    {
        return $this->engine->systemDelete($key, $name, $this->optimizeType($type));
    }

    public function sd(string $k, string $n, string $t): bool
    {
        return $this->systemDelete($k, $n, $t);
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function systemReadDefault(string $key, string $name, string $type, $default)
    {
        $type = $this->optimizeType($type);

        $value = $this->engine->systemRead($key, $name, $type);
        if (is_string($value)) {
            return $this->decodeTypedValue($type, $value);
        }

        return $this->normalizeDefaultValue($type, $default);
    }

    /**
     * @param mixed $d
     * @return mixed
     */
    public function srd(string $k, string $n, string $t, $d)
    {
        return $this->systemReadDefault($k, $n, $t, $d);
    }

    /** @return mixed */
    public function systemRead(string $key, string $name, string $type)
    {
        $result = $this->systemReadDefault($key, $name, $type, null);

        if ($result === null && $this->use_yaml) {
            $result = $this->readDefaultKeyValue('system', $key, $name, $type);
        }

        return $result;
    }

    /** @return mixed */
    public function sr(string $k, string $n, string $t)
    {
        return $this->systemRead($k, $n, $t);
    }

    /** @return mixed */
    public function systemReadOnce(string $key, string $name, string $type)
    {
        $r = $this->systemRead($key, $name, $type);
        $this->systemDelete($key, $name, $type);

        return $r;
    }

    /** @return mixed */
    public function sro(string $k, string $n, string $t)
    {
        return $this->systemReadOnce($k, $n, $t);
    }

    /**
     * @param mixed $value
     * @throws \Exception
     */
    public function systemWrite(string $key, string $name, string $type, $value): bool
    {
        if (str_contains($name, $this->delimiter)) {
            throw new \Exception('delimiter is not allowed in name');
        }

        $type = $this->optimizeType($type);

        if (($type === 'd' || $type === 't') && $value instanceof \DateTimeInterface) {
            $value = $value->format('c');
        }

        return $this->engine->systemWrite($key, $name, $type, $value);
    }

    /**
     * @param mixed $v
     * @throws \Exception
     */
    public function sw(string $k, string $n, string $t, $v): bool
    {
        return $this->systemWrite($k, $n, $t, $v);
    }

    /** @return array<int, mixed> */
    public function systemAll(): array
    {
        return $this->engine->systemAll();
    }

    /**
     * @return mixed
     */
    private function readDefaultKeyValue(string $scope, string $key, string $name, string $type)
    {
        $path = $key . $this->delimiter . $name;

        if (!isset($this->yaml[$scope]) || !array_key_exists($path, $this->yaml[$scope])) {
            return null;
        }

        $value = $this->yaml[$scope][$path];

        if (is_string($value)) {
            return $this->decodeTypedValue($this->optimizeType($type), $value);
        }

        return $this->normalizeDefaultValue($this->optimizeType($type), $value);
    }

    /** @return mixed */
    private function decodeTypedValue(string $type, string $value)
    {
        return match ($type) {
            'i' => (int) $value,
            'b' => (bool) $value,
            's' => $value,
            'f' => (float) $value,
            'd', 't' => is_numeric($value) ? (int) $value : strtotime($value),
            default => $value,
        };
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    private function normalizeDefaultValue(string $type, $default)
    {
        if ($default === null) {
            return null;
        }

        return match ($type) {
            'i' => is_numeric($default) ? (int) $default : 0,
            'b' => (bool) $default,
            's' => is_scalar($default) ? (string) $default : '',
            'f' => is_numeric($default) ? (float) $default : 0.0,
            'd', 't' => $default instanceof \DateTimeInterface
                ? $default
                : (is_int($default)
                    ? $default
                    : (is_string($default) ? strtotime($default) : null)),
            default => $default,
        };
    }
}
