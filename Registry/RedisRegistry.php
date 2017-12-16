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
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKey as RegKey;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKey as SysKey;
use jonasarts\Bundle\RegistryBundle\Registry\AbstractRegistry;
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;

/**
 * RedisRegistry.
 * 
 * Implementation of AbstractRegistry using redis for persistence.
 */
class RedisRegistry extends AbstractRegistry implements RegistryInterface
{
    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * @return array
     */
    public function registryAll()
    {
        $delimiter = $this->delimiter;
        $alias = $this->container->getParameter('registry.redis.alias');
        $prefix = $this->container->getParameter('registry.redis.prefix');

        $redis = $this->container->get('snc_redis.'.$alias);
        $keys = $redis->keys($prefix.$delimiter.'registry'.$delimiter.'*');

        $entities = array();

        foreach ($keys as $key) {
            $values = $redis->hgetall($key);
            foreach ($values as $name => $value) {
                $k = explode($delimiter, $key, 4);
                $n = explode($delimiter, $name);

                $array = array();
                $array['user_id'] = $k[2];
                $array['key'] = $k[3];
                $array['name'] = $n[0];
                $array['type'] = $n[1];
                $array['value'] = $value;

                $entities[] = RegKey::fromArray($array);
            }
        }

        return $entities;
    }

    /**
     * @return array
     */
    public function systemAll()
    {
        $delimiter = $this->delimiter;
        $alias = $this->container->getParameter('registry.redis.alias');
        $prefix = $this->container->getParameter('registry.redis.prefix');

        $redis = $this->container->get('snc_redis.'.$alias);
        $keys = $redis->keys($prefix.$delimiter.'system'.$delimiter.'*');

        $entities = array();

        foreach ($keys as $key) {
            $values = $redis->hgetall($key);
            foreach ($values as $name => $value) {
                $k = explode($delimiter, $key, 3);
                $n = explode($delimiter, $name);

                $array = array();
                $array['key'] = $k[2];
                $array['name'] = $n[0];
                $array['type'] = $n[1];
                $array['value'] = $value;

                $entities[] = SysKey::fromArray($array);
            }
        }

        return $entities;
    }
}
