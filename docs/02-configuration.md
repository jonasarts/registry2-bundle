Configure the bundle
====================

The bundle works out of the box with the **Doctrine** engine and no
configuration. All options below are optional.

## Full configuration reference

```yaml
# config/packages/registry.yaml
registry:
    # persistence engine: 'doctrine' (default) or 'redis'
    engine: doctrine

    globals:
        # path to the default key/name-values file (null = disabled)
        default_values: '%kernel.project_dir%/config/registry_defaults.yaml'
        # separator between <key> and <name> in storage and in the defaults file
        delimiter: ':'

    redis:
        # prefix for redis keys (avoids collisions when several projects share
        # one redis instance)
        prefix: 'registry'
        # service id of the redis client to inject when engine = redis
        client_service: 'snc_redis.registry'

    ui:
        # enable the built-in CRUD controllers (default: false)
        enabled: false
        # layout the bundle templates extend
        base_template: 'base.html.twig'
        # role required to access the CRUD controllers
        role: 'ROLE_REGISTRY_ADMIN'
```

## Using the Redis engine

The Redis engine is optional and has **no** hard dependency on
`snc/redis-bundle`. Select it and point `client_service` at any redis client
service — a native `\Redis`, a `Predis\Client`, a `symfony/cache` redis adapter,
or the `snc_redis.registry` service:

```yaml
registry:
    engine: redis
    redis:
        client_service: snc_redis.registry
```

If you use [SncRedisBundle](https://github.com/snc/SncRedisBundle), configure a
client and reference its service:

```yaml
# config/packages/snc_redis.yaml
snc_redis:
    clients:
        registry:
            type: phpredis
            alias: registry
            dsn: '%env(REDIS_URL)%'
```

The client only needs the hash methods used by the engine
(`hExists`, `hDel`, `hGet`, `hSet`, `hGetAll`, `keys`).

## Using both engines at once

`registry.engine` selects a single engine, which is bound to
`RegistryInterface`. If you need the Doctrine **and** Redis registries side by
side, register the second concrete service yourself and inject it by class — see
[UPGRADE-8.0.md](UPGRADE-8.0.md#running-both-engines-at-once-optional).

## That's all

[Return to the index.](index.md)
