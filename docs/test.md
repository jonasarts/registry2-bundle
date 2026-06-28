Testing the bundle
==================

This bundle ships a real test suite plus the usual static-analysis and
code-style tooling. All commands are exposed as Composer scripts, mirroring the
other jonasarts bundles.

## Requirements

- PHP 8.4+
- Composer
- For the **integration** suite only: the `redis` PHP extension (phpredis) and a
  reachable redis server. A ready-to-use `compose.yaml` is provided (see below).
  The suite skips automatically when no redis is available.

## Install the dev dependencies

From the bundle root:

```bash
composer install
```

This pulls in PHPUnit, PHPStan, Rector and PHP-CS-Fixer (see `require-dev`).

## Composer scripts

| Command | What it runs |
|---------|--------------|
| `composer test` | PHPUnit – **unit** suite (default suite) |
| `composer test-integration` | PHPUnit – **integration** suite (live redis) |
| `composer phpstan` | Static analysis (`phpstan.dist.neon`) |
| `composer cs-check` | PHP-CS-Fixer dry-run (report only) |
| `composer cs` | PHP-CS-Fixer – apply fixes |
| `composer rector-check` | Rector dry-run (report only) |
| `composer rector` | Rector – apply changes |

A full local check before tagging:

```bash
composer cs-check
composer rector-check
composer phpstan
composer test
composer test-integration
```

## Test suites

The suites are defined in `phpunit.dist.xml`.

### unit — `tests/`

Pure unit tests; no external service is required. The registry engines are
exercised with mocked clients, so the suite is DB- and Redis-independent:

- `AbstractRegistryTest` — shared key/value behaviour across engines.
- `DoctrineRegistryEngineTest` / `RedisRegistryEngineTest` — the two engine
  implementations against mocked backends.
- `EntityTest` / `EditableValueTest` — entity and value-object behaviour.
- `RegistryExtensionTest` — the DI layer (`configure()` + `loadExtension()`):
  default and overridden configuration, engine selection and the
  `RegistryInterface` binding.

Run only the unit suite:

```bash
composer test
# or
vendor/bin/phpunit --testsuite unit
```

### integration — `tests/RegistryTest.php`

End-to-end test of `RedisRegistry` against a **real** redis server. It skips
automatically when the phpredis extension is missing or no redis is reachable.
Connection is configured via the `REDIS_HOST` / `REDIS_PORT` environment
variables (defaults `127.0.0.1:6379`).

The bundle ships a `compose.yaml` to start redis locally:

```bash
docker compose up -d          # start redis on 127.0.0.1:6379
composer test-integration     # run the integration suite
docker compose down           # stop
```

## Running a single test

```bash
vendor/bin/phpunit --testsuite unit --filter testSetAndGet
```

## Coverage

Coverage needs Xdebug or PCOV. With one of them enabled:

```bash
XDEBUG_MODE=coverage vendor/bin/phpunit --testsuite unit --coverage-text
```

The covered sources are restricted to `src/` (see the `<source>` block in
`phpunit.dist.xml`).

## Continuous integration

`.github/workflows/ci.yml` runs the whole chain on a PHP 8.4 matrix: the unit
and integration suites (the latter with a live redis service), PHPStan,
`composer rector-check` and `composer cs-check`. A green pipeline is the release
gate.

[Return to the index.](index.md)
