# Upgrade from 7.x to 8.0

Version 8.0 targets **PHP 8.4 · Symfony ^8 · Doctrine ORM ^3 / DBAL ^4** and
decouples the bundle from `snc/redis-bundle`. This guide lists the
backwards-incompatible changes and the steps to migrate.

## Platform requirements

- PHP `>= 8.4`
- Symfony `^7 | ^8` for `symfony/config` and `symfony/dependency-injection`
  (target Symfony 8.1)
- Doctrine ORM `^3`, DBAL `^4` (when using the Doctrine engine)

## snc/redis-bundle is no longer required

`snc/redis-bundle` was removed from `require`. If you used the Redis engine, it
no longer comes in transitively.

**Before** — the Redis registry was wired to `@snc_redis.registry` implicitly.

**After** — select the engine and the redis client service explicitly:

```yaml
# config/packages/registry.yaml
registry:
    engine: redis
    redis:
        client_service: snc_redis.registry   # or your own \Redis / Predis / cache adapter service
```

If you keep using `snc/redis-bundle`, add it to your application's
`composer.json` (it is still listed under `suggest`). You may instead provide a
native `\Redis`, a `Predis\Client`, or a `symfony/cache` redis adapter as the
`client_service`.

The **Doctrine engine is the default** and needs no configuration.

## Service / interface binding

`RegistryInterface` is now bound automatically to the active engine. Inject it
directly instead of fetching `@registry` from the container:

```php
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;

public function __construct(private readonly RegistryInterface $registry) {}
```

The removed runtime engine-switching helpers (`switchEngineType()`,
`getEngineType()`, `isEngineType()`) have no replacement — choose the engine via
`registry.engine`.

## Database migration (`value` column)

The `value` column of the `registry` and `system` tables changed from nullable
to `NOT NULL DEFAULT ''`. Apply the change to existing databases — see
[04-migration.md](04-migration.md) for ready-to-run SQL (PostgreSQL / MariaDB)
and a sample Doctrine migration.

## CRUD UI is off by default

The built-in `RegistryController` / `SystemController` are no longer active
unless enabled:

```yaml
registry:
    ui:
        enabled: true
        base_template: base.html.twig
        role: ROLE_REGISTRY_ADMIN
```

Security-relevant changes if you used the UI:

- Delete is now **POST + CSRF** only (the list view renders a delete form per
  row instead of a link). Update any custom templates/links accordingly.
- Access requires the configured role (`ui.role`).
- `edit` / `delete` take discrete `user_id` / `key` / `name` / `type`
  parameters instead of a serialized `?entity=` blob.
- Templates extend `ui.base_template` (default `base.html.twig`) instead of the
  removed `::base.html.twig` syntax.

Import the controller routes only when the UI is enabled (see
[05-crud-ui.md](05-crud-ui.md)).

## Removed internals

- `AbstractRegistryKey` (empty base class) was removed. `RegistryKey` /
  `SystemKey` no longer extend it; they still implement
  `RegistryKeyInterface` / `SystemKeyInterface`.

## Running both engines at once (optional)

By default only the configured engine is wired and bound to
`RegistryInterface`. To use the Doctrine **and** Redis registries side by side,
register the second concrete service in your application and inject it by class:

```yaml
# config/services.yaml
services:
    jonasarts\Bundle\RegistryBundle\Registry\DoctrineRegistry:
        arguments:
            $em: '@doctrine.orm.entity_manager'
            $default_values_filename: '%registry.globals.default_values%'

    jonasarts\Bundle\RegistryBundle\Registry\RedisRegistry:
        arguments:
            $redis: '@snc_redis.registry'   # any \Redis / Predis / cache-adapter service
            $registry_prefix: '%registry.redis.prefix%'
            $registry_delimiter: '%registry.globals.delimiter%'
            $default_values_filename: '%registry.globals.default_values%'
```

Then type-hint the concrete classes (`DoctrineRegistry`, `RedisRegistry`)
instead of `RegistryInterface`.
