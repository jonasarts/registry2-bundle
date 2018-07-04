Configure the bundle
====================

Since verison 1.1 the registry service can use redis as database engine.

## Configuration options

```yaml
#app/config/config.yml
registry:
    globals:
        default_values: %kernel.root_dir%/config/registry.yml # path and filename for the
                                                              # default key/name-values
        delimiter:      '/'
    redis:
        prefix:         'registry' # prefix redis keys to make them 'unique'
                                   # if multiple projects are using the same redis instance
```

## Required for Redis Mode

To use redis as database engine, you must install and configure the [SncRedisBundle](https://github.com/snc/SncRedisBundle).

Configure the snc_redis client alias to 'registry' for the client to use for the 
registy operations.

```yaml
#app/config/config.yml
snc_redis:
    clients:
        [...]
        registry:
            type: phpredis
            alias: registry
            dsn: "%env(REDIS_URL)%"
``

## That's all

[Return to the index.](index.md)
