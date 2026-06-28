# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [8.0.1] - 2026-06-28

### Fixed

- CI: the `composer normalize` gate now uses a project-local
  `ergebnis/composer-normalize` (require-dev + `config.allow-plugins`) instead of
  a global tool, so the command is reliably available.
- The extension test provides the `kernel.environment` / `kernel.build_dir`
  parameters so it passes on the lowest supported Symfony (7.0).

## [8.0.0] - 2026-06-28

### Added

- Full unit-test suite (engine clients mocked, DB/Redis-independent) plus a
  skippable real-redis integration suite; CI with Rector & PHP-CS-Fixer gates.

### Changed

- Requires PHP 8.4, Symfony `^7.0 || ^8.0` (`symfony/config`,
  `symfony/dependency-injection`, `symfony/http-kernel`), Doctrine ORM `^3` /
  DBAL `^4` (when using the Doctrine engine).
- Engine is now chosen by configuration (`registry.engine`); the active
  implementation is registered automatically and bound to `RegistryInterface`.
- Built-in CRUD UI is **off by default** (`registry.ui.enabled`) and hardened:
  delete is POST + CSRF only, access requires a configurable role
  (`registry.ui.role`), edit/delete use validated discrete parameters instead of
  deserializing a client-supplied entity, and templates extend a configurable
  base template (`registry.ui.base_template`).
- Modernized to the current bundle layout: `config/` and `templates/` at the
  package root, single-class `AbstractBundle` (configuration tree and container
  wiring in `RegistryBundle`; the separate Extension/Configuration classes are
  gone). `symfony/http-kernel` is now an explicit dependency.

### Removed

- `snc/redis-bundle` decoupled — removed from `require`. The Doctrine engine is
  the default and works without it. The Redis engine is selected explicitly via
  `registry.engine: redis` and accepts any redis client through
  `registry.redis.client_service` (native `\Redis`, Predis, a symfony/cache
  adapter, or `snc_redis.registry`).
- Removed the empty `AbstractRegistryKey` base class; deduplicated the engine
  `stringify()` helper into a shared trait.

### Fixed

- `value` column is now `NOT NULL DEFAULT ''` (was nullable while the property
  was a non-nullable string → `TypeError` on NULL). See the
  [migration guide](docs/04-migration.md).

See [docs/UPGRADE-8.0.md](docs/UPGRADE-8.0.md) for migration steps.

## [7.0.5]

- Deprecation code cleanup for configuration.

## [7.0.4]

- Bugfixes for bugs introduced by the `RegistryKeyType` enum.

## [7.0.3]

- Added an enum for the registry-key type.
  - WARNING: eventually needs a data migration for the type string of existing
    registry keys in the registry database. Use the short version as type string
    (b, i, f, s, d, t, a). See the `RegistryKeyType` enum.
- Breaking change in `SystemKeyInterface::getType()` and `setType()`.
- Code cleanup.
- Added more tests.

## [7.0.0]

- Requires PHP 8.2.
- Updated for the Symfony 7.0 branch.

## [6.3.0]

- Requires PHP 8.1.
- Updated for the Symfony 6.3 branch.

## [6.0.0]

- Update for PHP 8.* compatibility.
- Update for Symfony 5.* compatibility.
- Test release for Symfony 6.x (not ready for production).

## [4.1.3]

- Updated `TreeBuilder` to support Symfony 5.0.

## [4.1.0]

- Simplified registry behavior.
- Removed the factory.
- Removed engine switching.
- Use explicit constructors for the different engine types.

## [4.0.0]

- Release for Symfony 4.x.
- Introduces some BC; no longer switches engines.

## [2.1.0]

- Release for Symfony 3.x.
- Introduces some BC for registry configuration methods:
  - `registry.switchEngine()` is now `registry.switchEngineType()`.
  - `registry.isMode()` is now `registry.isEngineType()`.

## [2.0.5]

- Stable release after the rewrite of registry-bundle as the new registry2-bundle.

## [1.x]

- Releases for Symfony 2.x.
