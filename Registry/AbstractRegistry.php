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
use Symfony\Component\Yaml\Yaml;
use jonasarts\Bundle\RegistryBundle\Factory\RegistryEngineFactory;
use jonasarts\Bundle\RegistryBundle\Interfaces\AbstractRegistryInterface;

abstract class AbstractRegistry implements AbstractRegistryInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RegistryEngineInterface
     */
    protected $engine;

    // boolean, use default key-name/value array
    protected $use_yaml;

    // default key-name/value array
    protected $yaml;

    // field delimiter
    protected $delimiter;

    /**
     * @param string $type
     *
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
     * @param string $realm
     * @param string $key
     * @param string $name
     * @param string $type
     *
     * @return mixed
     */
    private function readDefaultKeyValue($realm, $key, $name, $type)
    {
        // convert type
        $type = $this->optimizeType($type);

        // default key-name/value
        if (is_array($this->yaml) && is_array($this->yaml[$realm]) && array_key_exists($key.$this->key_name_delimiter.$name, $this->yaml[$realm])) {
            $result = $this->yaml[$realm][$key.$this->key_name_delimiter.$name];
        } else {
            $result = null;
        }

        switch ($type) {
            case 'i':
                if (!is_int($result)) {
                    $result = 0;
                }
                break;
            case 'b':
                if (!is_bool($result)) {
                    $result = false;
                }
                break;
            case 's':
                if (!is_string($result)) {
                    $result = '';
                }
                break;
            case 'f':
                if (!is_double($result)) {
                    $result = 0.00;
                }
                break;
            case 'd':
            case 't':
                if ($result instanceof \DateTime) {
                    // nothing to do
                } elseif (is_int($result)) {
                    // nothing to do
                } elseif (is_string($result)) {
                    $result = strtotime($result);
                }
                break;
            default:
                // nothing
                break;
        }

        return $result;
    }

    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->use_yaml = false;
        $this->yaml = null;

        $filename = $container->getParameter('registry.globals.defaultvalues');

        $this->use_yaml = file_exists($filename);
        if ($this->use_yaml) {
            $this->yaml = Yaml::parse($filename); // load yaml file into array
        }

        $this->delimiter = $container->getParameter('registry.globals.delimiter');

        // create the engine
        $engine_type = $container->getParameter('registry');

        $this->engine = RegistryEngineFactory::build($engine_type, $container);
    }

    /**
     * @param string             $engine
     * @param ContainerInterface $container
     *
     * @return Registry
     */
    public function switchEngine($engine, ContainerInterface $container)
    {
        $this->engine = RegistryEngineFactory::build($engine, $container);

        return $this;
    }

    /**
     * ----------------
     * Registry Methods
     * ----------------.
     */

    /**
     * Check registry key in database.
     *
     * @param int    $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     *
     * @return bool
     */
    public function registryExists($user_id, $key, $name, $type)
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
    public function re($uid, $k, $n, $t)
    {
        return $this->registryExists($uid, $k, $n, $t);
    }

    /**
     * Delete registry key from database.
     * 
     * @param int    $user_id
     * @param string $key
     * @param string $name
     * @param string $type
     *
     * @return bool
     */
    public function registryDelete($user_id, $key, $name, $type)
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
    public function rd($uid, $k, $n, $t)
    {
        return $this->registryDelete($uid, $k, $n, $t);
    }

    /** 
     * Read registry key from database.
     * If no key is found, the default value will be returned.
     * 
     * @param int    $user_id
     * @param string $key
     * @param string $string
     * @param string $type
     * @param mixed  $default
     *
     * @return mixed
     */
    public function registryReadDefault($user_id, $key, $name, $type, $default)
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
    public function rrd($uid, $k, $n, $t, $d)
    {
        return $this->registryReadDefault($uid, $k, $n, $t, $d);
    }

    /**
     * Read registry key from database.
     * 
     * @param int    $user_id
     * @param string $key
     * @param string $string
     * @param string $type
     *
     * @return mixed
     */
    public function registryRead($user_id, $key, $name, $type)
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
    public function rr($uid, $k, $n, $t)
    {
        return $this->registryRead($uid, $k, $n, $t);
    }

    /**
     * Read registry key from database and delete it immediately.
     * 
     * @param int    $user_id
     * @param string $key
     * @param string $string
     * @param string $type
     *
     * @return mixed
     */
    public function registryReadOnce($user_id, $key, $name, $type)
    {
        $r = $this->registryRead($user_id, $key, $name, $type);

        $this->registryDelete($user_id, $key, $name, $type);

        return $r;
    }

    /**
     * Short method to RegistryReadOnce.
     */
    public function rro($uid, $k, $n, $t)
    {
        return $this->registryReadOnce($uid, $k, $n, $t);
    }

    /**
     * Write registry key to database.
     * 
     * @param int    $user_id
     * @param string $key
     * @param string $string
     * @param string $type
     * @param mixed  $value
     *
     * @return bool
     */
    public function registryWrite($user_id, $key, $name, $type, $value)
    {
        // validate registry key - delimiter is not allowed
        if (strpos($key, $this->delimiter) !== false) {
            return false;
        }

        // validate name - delimiter is not allowed
        if (strpos($name, $this->delimiter) !== false) {
            return false;
        }

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
        return $this->engine->registryWrite($user_id, $key, $name, $type, $value);
    }

    /**
     * Short method to RegistryWrite.
     * 
     * @see RegistryWrite
     */
    public function rw($uid, $k, $n, $t, $v)
    {
        return $this->registryWrite($uid, $k, $n, $t, $v);
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
     *
     * @return bool
     */
    public function systemExists($key, $name, $type)
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
    public function se($k, $n, $t)
    {
        return $this->systemExists($k, $n, $t);
    }

    /**
     * Delete system key from database.
     * 
     * @param string $key
     * @param string $name
     * @param string $type
     *
     * @return bool
     */
    public function systemDelete($key, $name, $type)
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
    public function sd($k, $n, $t)
    {
        return $this->systemDelete($k, $n, $t);
    }

    /**
     * Read system key from database.
     * If no key is found, the default value will be returned.
     * 
     * @param string $key
     * @param string $string
     * @param string type
     * @param mixed $default
     *
     * @return mixed
     */
    public function systemReadDefault($key, $name, $type, $default)
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
    public function srd($k, $n, $t, $d)
    {
        return $this->systemReadDefault($k, $n, $t, $d);
    }

    /**
     * Read system key from database.
     * 
     * @param string $key
     * @param string $string
     * @param string type
     *
     * @return mixed
     */
    public function systemRead($key, $name, $type)
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
    public function sr($k, $n, $t)
    {
        return $this->systemRead($k, $n, $t);
    }

    /**
     * Read system key from database and delete it immediately.
     * 
     * @param string $key
     * @param string $string
     * @param string $type
     *
     * @return mixed
     */
    public function systemReadOnce($key, $name, $type)
    {
        $r = $this->systemRead($key, $name, $type);

        $this->systemDelete($key, $name, $type);

        return $r;
    }

    /**
     * Short method to SystemReadOnce.
     */
    public function sro($k, $n, $t)
    {
        return $this->systemReadOnce($k, $n, $t);
    }

    /**
     * Write system key to database.
     * 
     * @param string $key
     * @param string $string
     * @param string type
     * @param mixed value
     *
     * @return bool
     */
    public function systemWrite($key, $name, $type, $value)
    {
        // validate registry key - delimiter is not allowed
        if (strpos($key, $this->delimiter) !== false) {
            return false;
        }

        // validate name - delimiter is not allowed
        if (strpos($name, $this->delimiter) !== false) {
            return false;
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
    public function sw($k, $n, $t, $v)
    {
        return $this->systemWrite($k, $n, $t, $v);
    }
}
