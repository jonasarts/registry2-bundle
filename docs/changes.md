CHANGE LOG
==========

V 8.0.0
-------

- Requires PHP 8.4, Symfony ^8 (config/dependency-injection ^7|^8), Doctrine
  ORM ^3 / DBAL ^4
- **snc/redis-bundle decoupled**: removed from `require`. The Doctrine engine is
  the default and works without it. The Redis engine is selected explicitly via
  `registry.engine: redis` and accepts any redis client through
  `registry.redis.client_service` (native `\Redis`, Predis, a symfony/cache
  adapter, or `snc_redis.registry`).
- Engine is now chosen by configuration (`registry.engine`); the active
  implementation is registered automatically and bound to `RegistryInterface`.
- **Bugfix**: `value` column is now `NOT NULL DEFAULT ''` (was nullable while the
  property was a non-nullable string → `TypeError` on NULL). See
  [migration guide](04-migration.md).
- Built-in CRUD UI is **off by default** (`registry.ui.enabled`), hardened:
  delete is POST + CSRF only, access requires a configurable role
  (`registry.ui.role`), edit/delete use validated discrete parameters instead of
  deserializing a client-supplied entity, and templates extend a configurable
  base template (`registry.ui.base_template`).
- Removed the empty `AbstractRegistryKey` base class; deduplicated the engine
  `stringify()` helper into a shared trait.
- Full unit-test suite (engine clients mocked, DB/Redis-independent) plus a
  skippable real-redis integration suite; CI with Rector & PHP-CS-Fixer gates.
- See [UPGRADE-8.0.md](../UPGRADE-8.0.md) for migration steps.

V 7.0.5
-------

- Deprecation code cleanup for configuration

V 7.0.4
-------

- Bugfixes for bugs introduced by RegistryKeyType enum

V 7.0.3
-------

- Adding enum for registry-key type
  - WARNING: eventually needs a data migration for the type string of existing registry keys in the registry database!  
    Use the short version as type string (b, i, f, s, d, t, a). See RegistryKeyType enum.
- Breaking change in SystemKeyInterface getType() and setType()
- Code cleanup
- Adding more tests

V 7.0.0
-------

- Requires PHP 8.2
- Updated for Symfony 7.0 Branch
 
V 6.3.0
-------

- Requires PHP 8.1
- Updated for Symfony 6.3 Branch

V 6.0.0
-------

- Update for PHP 8.* compatibility
- Update for Symfony 5.* compatibility
- Test-Release for Symfony 6.x
- Not ready for production

V 4.1.3
-------

- Updated TreeBuilder to support Symfony 5.0

V 4.1.0
-------

- Simplified registry behavior
- Removed factory
- Removed engine switching
- Use explicit constructors for different engine types

V 4.0.0
-------

- Release for Symfony 4.x
- Introduces some BC, no longer switching of engines

V 2.1.0
-------

- Release for Symfony 3.x
- Introduces some BC for registry configuration methods
  - registry.switchEngine() is now registry.switchEngineType()
  - registry.isMode() is now registry.isEngineType()


V 2.0.5
-------

- Stable release after rewrite of registry-bundle as new registry2-bundle

V 1.x
-----

- Releases for Symfony 2.x
