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

use Symfony\Component\Yaml\Yaml;
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;

/**
 * The registry logic
 *
 * This class contains the business logic for registry keys !!!
 */
abstract class AbstractRegistry implements RegistryInterface
{
    /**
     *
     * This is not so good design, currently there is no RegistryEngineInterface,
     * there is simply the RegistryInterface re-used
     *
     * @var RegistryInterface
     */
    protected $engine;

    /**
     * boolean, use default key-name/value array
     *
     * @var bool
     */
    protected $use_yaml;

    /**
     * default key-name/value array
     *
     * @var array
     */
    protected $yaml;

    /**
     * field delimiter (used in yaml)
     *
     * @var string
     */
    protected $delimiter;

    /**
     * @param string $type
     * @return string
     */
    private function optimizeType($type)
    {
        switch (trim($type)) {
            case 'i':
            case 'int':
            case 'integer':
                return 'i';
                break;
            case 'b':
            case 'bln':
            case 'boolean':
                return 'b';
                break;
            case 's':
            case 'str':
            case 'string':
                return 's';
                break;
            case 'f':
            case 'flt':
            case 'float':
                return 'f';
                break;
            case 'd':
            case 'dat':
            case 'date':
                return 'd';
                break;
            case 't':
            case 'tim':
            case 'time':
                return 't';
                break;
            default:
                return 's';
        }
    }

    /**
     * Constructor.
     */
    public function __construct(string $default_values_filename = null)
    {
        $this->use_yaml = false;
        $this->yaml = null;

        $this->delimiter = ':';

        $filename = $default_values_filename;

        $this->use_yaml = !is_null($filename) && file_exists($filename);
        if ($this->use_yaml) {
            $this->yaml = Yaml::parse($filename); // load yaml file into array
        }

    }

    /**
     * ----------------
     * Registry Methods
     * ----------------.
     */

    /**
     * Check registry key in database.
     *
     * This does not use User 0 fallback !
     *
     * @param int    $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool
     */
    public function registryExists(int $user_id, string $key, string $name, string $type): bool
    {
        // convert type
        $type = $this->optimizeType($type);

        return $this->engine->registryExists($user_id, $key, $name, $type);
    }

    /**
     * Short method of RegistryExists.
     *
     * @see RegistryExists
     */
    public function re(int $uid, string $k, string $n, string $t): bool
    {
        return $this->registryExists($uid, $k, $n, $t);
    }

    /**
     * Delete registry key from database.
     *
     * This does not use User 0 fallback !
     *
     * @param int    $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool
     */
    public function registryDelete(int $user_id, string $key, string $name, string $type): bool
    {
        // convert type
        $type = $this->optimizeType($type);

        return $this->engine->registryDelete($user_id, $key, $name, $type);
    }

    /**
     * Short method to RegistryDelete.
     *
     * @see RegistryDelete
     */
    public function rd(int $uid, string $k, string $n, string $t): bool
    {
        return $this->registryDelete($uid, $k, $n, $t);
    }

    /**
     * Read registry key from database.
     * If no key is found, the default value will be returned.
     *
     * @param int    $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @param mixed  $default
     * @return mixed
     */
    public function registryReadDefault(int $user_id, string $key, string $name, string $type, $default)
    {
        // RegistryRead returns any found value as string or false if not found!

        // convert type
        $type = $this->optimizeType($type);

        // find user key
        $value = $this->engine->registryRead($user_id, $key, $name, $type);

        if ($value === false) {
            // find default key
            $value = $this->engine->registryRead(0, $key, $name, $type);
        }

        if (is_string($value)) {
            // return value
            switch ($type) {
                case 'i':
                    return (integer) $value;
                    break;
                case 'b':
                    return (boolean) $value;
                    break;
                case 's':
                    return (string) $value;
                    break;
                case 'f':
                    return (float) $value;
                    break;
                case 'd':
                case 't':
                    $value = $value; // this always is a string
                    if (is_numeric($value)) { // don't use is_int here
                        return (integer) $value;
                    } else {
                        return strtotime($value);
                    }
                    break;
                default:
                    return $value;
            }
        } else {
            // return default value
            // special default null handling
            if (is_null($default)) {
                return $default;
            }
            // regular
            switch ($type) {
                case 'i':
                    return (integer) $default;
                    break;
                case 'b':
                    return (boolean) $default;
                    break;
                case 's':
                    return (string) $default;
                    break;
                case 'f':
                    return (float) $default;
                    break;
                case 'd':
                case 't':
                    if ($default instanceof \DateTime) {
                        return $default;
                    } elseif (is_int($default)) {
                        return $default;
                    } elseif (is_string($default)) {
                        return strtotime($default);
                    }
                    break;
                default:
                    return $default;
            }
        }
    }

    /**
     * Short method to RegistryReadDefault.
     *
     * @see RegistryReadDefault
     */
    public function rrd(int $uid, string $k, string $n, string $t, $d)
    {
        return $this->registryReadDefault($uid, $k, $n, $t, $d);
    }

    /**
     * Read registry key from database.
     *
     * @param int    $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @return mixed
     */
    public function registryRead(int $user_id, string $key, string $name, string $type)
    {
        $result = $this->registryReadDefault($user_id, $key, $name, $type, null);

        if (($result === null) && ($this->use_yaml)) {
            // default key-name/value
            $result = $this->ReadDefaultKeyValue('registry', $key, $name, $type);
        }

        return $result;
    }

    /**
     * Short method to RegistryRead.
     *
     * @see RegistryRead
     */
    public function rr(int $uid, string $k, string $n, string $t)
    {
        return $this->registryRead($uid, $k, $n, $t);
    }

    /**
     * Read registry key from database and delete it immediately.
     *
     * @param int    $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @return mixed
     */
    public function registryReadOnce(int $user_id, string $key, string $name, string $type)
    {
        $r = $this->registryRead($user_id, $key, $name, $type);

        $this->registryDelete($user_id, $key, $name, $type);

        return $r;
    }

    /**
     * Short method to RegistryReadOnce.
     */
    public function rro(int $uid, string $k, string $n, string $t)
    {
        return $this->registryReadOnce($uid, $k, $n, $t);
    }

    /**
     * Write registry key to database.
     *
     * @param int    $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     * @param mixed  $value
     * @return bool
     */
    public function registryWrite(int $user_id, string $key, string $name, string $type, $value): bool
    {
        // validate registry key - delimiter is not allowed
        if (strpos($key, $this->delimiter) !== false) {
            // why not ?
        }

        // validate name - delimiter is not allowed in name
        if (strpos($name, $this->delimiter) !== false) {
            throw new \Exception('delimiter is not allowed in name');
        }

        // convert type
        $type = $this->optimizeType($type);

        // value = default key value?
        if ($user_id != 0) {
            $result = $this->registryRead(0, $key, $name, $type);
            if ($result) {
                if ($result === $value) {
                    // equals default value, delete user key
                    return $this->registryDelete($user_id, $key, $name, $type);
                }
            }
        }

        // is not default key value...

        // convert value to string for writing
        switch ($type) {
            case 'd':
            case 't':
                if ($value instanceof \DateTime) {
                    // convert DateTime to string
                    $value = $value->format('c');
                } elseif (is_int($value)) {
                    // nothing to do
                } elseif (is_string($value)) {
                    // nothing to do
                }
                break;
        }

        // insert / update
        return $this->engine->registryWrite($user_id, $key, $name, $type, $value);
    }

    /**
     * Short method to RegistryWrite.
     *
     * @see RegistryWrite
     */
    public function rw(int $uid, string $k, string $n, string $t, $v): bool
    {
        return $this->registryWrite($uid, $k, $n, $t, $v);
    }

    /**
     *
     */
    public function registryAll(): array
    {
        return $this->engine->registryAll();
    }

    /**
     * --------------
     * System Methods
     * --------------.
     */

    /**
     * Check system key in database.
     *
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool
     */
    public function systemExists(string $key, string $name, string $type): bool
    {
        // convert type
        $type = $this->optimizeType($type);

        return $this->engine->systemExists($key, $name, $type);
    }

    /**
     * Short method of SystemExists.
     *
     * @see SystemExits
     */
    public function se(string $k, string $n, string $t): bool
    {
        return $this->systemExists($k, $n, $t);
    }

    /**
     * Delete system key from database.
     *
     * @param string $key
     * @param string $name
     * @param string $type
     * @return bool
     */
    public function systemDelete(string $key, string $name, string $type): bool
    {
        // convert type
        $type = $this->optimizeType($type);

        return $this->engine->systemDelete($key, $name, $type);
    }

    /**
     * Short method to SystemDelete.
     *
     * @see SystemDelete
     */
    public function sd(string $k, string $n, string $t): bool
    {
        return $this->systemDelete($k, $n, $t);
    }

    /**
     * Read system key from database.
     * If no key is found, the default value will be returned.
     *
     * @param string $key
     * @param string $name
     * @param string type
     * @param mixed $default
     * @return mixed
     */
    public function systemReadDefault(string $key, string $name, string $type, $default)
    {
        // SystemRead returns any found value as string or false if not found!

        // convert type
        $type = $this->optimizeType($type);

        $value = $this->engine->systemRead($key, $name, $type);

        if (is_string($value)) {
            // return value
            switch ($type) {
                case 'i':
                    return (integer) $value;
                    break;
                case 'b':
                    return (boolean) $value;
                    break;
                case 's':
                    return (string) $value;
                    break;
                case 'f':
                    return (float) $value;
                    break;
                case 'd':
                case 't':
                    $value = $value; // this always is a string
                    if (is_numeric($value)) { // don't use is_int here
                        return (integer) $value;
                    } else {
                        return strtotime($value);
                    }
                    break;
                default:
                    return $value;
            }
        } else {
            // return default value
            // special default null handling
            if (is_null($default)) {
                return $default;
            }
            // regular
            switch ($type) {
                case 'i':
                    return (integer) $default;
                    break;
                case 'b':
                    return (boolean) $default;
                    break;
                case 's':
                    return (string) $default;
                    break;
                case 'f':
                    return (float) $default;
                    break;
                case 'd':
                case 't':
                    if ($default instanceof \DateTime) {
                        return $default;
                    } elseif (is_int($default)) {
                        return $default;
                    } elseif (is_string($default)) {
                        return strtotime($default);
                    }
                    break;
                default:
                    return $default;
            }
        }
    }

    /**
     * Short method to SystemReadDefault.
     *
     * @see SystemReadDefault
     */
    public function srd(string $k, string $n, string $t, $d)
    {
        return $this->systemReadDefault($k, $n, $t, $d);
    }

    /**
     * Read system key from database.
     *
     * @param string $key
     * @param string $name
     * @param string type
     * @return mixed
     */
    public function systemRead(string $key, string $name, string $type)
    {
        $result = $this->systemReadDefault($key, $name, $type, null);

        if (($result === null) && ($this->use_yaml)) {
            // default key-name/value
            $result = $this->ReadDefaultKeyValue('system', $key, $name, $type);
        }

        return $result;
    }

    /**
     * Short method to SystemRead.
     *
     * @see SystemRead
     */
    public function sr(string $k, string $n, string $t)
    {
        return $this->systemRead($k, $n, $t);
    }

    /**
     * Read system key from database and delete it immediately.
     *
     * @param string $key
     * @param string $name
     * @param string $type
     * @return mixed
     */
    public function systemReadOnce(string $key, string $name, string $type)
    {
        $r = $this->systemRead($key, $name, $type);

        $this->systemDelete($key, $name, $type);

        return $r;
    }

    /**
     * Short method to SystemReadOnce.
     */
    public function sro(string $k, string $n, string $t)
    {
        return $this->systemReadOnce($k, $n, $t);
    }

    /**
     * Write system key to database.
     *
     * @param string $key
     * @param string $name
     * @param string type
     * @param mixed value
     * @return bool
     */
    public function systemWrite(string $key, string $name, string $type, $value): bool
    {
        // validate registry key - delimiter is not allowed
        if (strpos($key, $this->delimiter) !== false) {
            // why not ?
        }

        // validate name - delimiter is not allowed in name
        if (strpos($name, $this->delimiter) !== false) {
            throw new \Exception('delimiter is not allowed in name');
        }

        // convert type
        $type = $this->optimizeType($type);

        // convert value to string for writing
        switch ($type) {
            case 'd':
            case 't':
                if ($value instanceof \DateTime) {
                    // convert DateTime to string
                    $value = $value->format('c');
                } elseif (is_int($value)) {
                    // nothing to do
                } elseif (is_string($value)) {
                    // nothing to do
                }
                break;
        }

        // insert / update
        return $this->engine->systemWrite($key, $name, $type, $value);
    }

    /**
     * Short method to SystemWrite.
     *
     * @see SystemWrite
     */
    public function sw(string $k, string $n, string $t, $v): bool
    {
        return $this->systemWrite($k, $n, $t, $v);
    }

    /**
     *
     */
    public function systemAll(): array
    {
        return $this->engine->systemAll();
    }
}
