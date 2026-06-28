# Contributing

This bundle follows the shared jonasarts Symfony-bundle conventions. **registry2
is the canonical template**; the other bundles (`google-authenticator`,
`phpqrcode`, `tcpdf`) are kept aligned with it (see [Drift lint](#drift-lint)).

This file and `CLAUDE.md` are `export-ignore`d — they live in the repository, not
in the Composer dist.

## Requirements

- PHP 8.4+ (the CI matrix runs 8.4 and 8.5)
- Composer 2
- For the integration suite: a redis server (a `compose.yaml` is provided)

## Local workflow

```bash
composer install
docker compose up -d            # redis for the integration suite
composer cs-check               # PHP-CS-Fixer (report)
composer rector-check           # Rector (report)
composer phpstan                # PHPStan
composer test                   # unit suite
composer test-integration       # integration suite (needs redis)
docker compose down
```

Apply autofixes with `composer cs` and `composer rector`. Always run
`composer normalize` after editing `composer.json`.

## Gold standard (all active bundles)

- **composer.json**
  - No `version` field (Packagist derives it from the VCS tag).
  - `type: "symfony-bundle"`, `minimum-stability: "stable"`, `prefer-stable: true`,
    `config.sort-packages: true`.
  - `require`: `php: ">=8.4"`, all `ext-*` explicit, individual `symfony/*`
    components at `^7.0 || ^8.0` (never `symfony/symfony`).
  - `require-dev`: PHPUnit `^12.0 || ^13.0`, php-cs-fixer `^3.95`,
    phpstan `^2.0`, rector `^2.0`.
  - Optional integrations belong in `suggest` (+ `require-dev` for tests), not in
    `require`.
  - Field order and formatting are owned by `composer normalize`; every bundle
    must pass `composer validate --strict` and `composer normalize --dry-run`.
- **Layout / naming**
  - Test directory is `tests/` (lowercase); PHPUnit config is `phpunit.dist.xml`.
  - PSR-4: `…\<Bundle>\` → `src/`, `…\<Bundle>\Tests\` → `tests/`.
  - Test suites are `unit` and `integration` (a bundle without integration tests
    omits the latter).
- **Scripts**: `cs`, `cs-check`, `rector`, `rector-check`, `phpstan`,
  `test` (= `phpunit --testsuite unit`), and `test-integration` where applicable.
- **Docs**: `docs/index.md`, `01-install.md`, `02-configuration.md` (only where the
  bundle has configuration) → otherwise `02-basic-usage.md`, further `NN-*.md`
  topics as needed, and `docs/test.md`. The changelog is the root `CHANGELOG.md`
  ([Keep a Changelog](https://keepachangelog.com/) + [SemVer](https://semver.org/)).
  Numbered doc sequences are contiguous (no gaps).
- **Dist hygiene**: `tests/`, `docs/`, CI configs, tooling configs,
  `CONTRIBUTING.md`, `CLAUDE.md` and the dev tooling are kept out of the Composer
  dist via `.gitattributes export-ignore`. `composer.lock` is git-ignored
  (libraries don't commit it).

## CI gates

`.github/workflows/ci.yml` enforces, on every push/PR:

- `composer validate --strict` and `composer normalize --dry-run`
- `cs-check`, `rector-check`, `phpstan`
- `composer-require-checker` and `composer-unused`
- `composer audit` (fails on known CVEs)
- unit tests on a PHP 8.4/8.5 × highest/lowest dependency matrix
- the integration suite against a live redis service

`composer-require-checker` is configured via `composer-require-checker.json`,
which whitelists the symbols of the **optional** CRUD-UI / Doctrine integrations
(declared in `suggest` + `require-dev`).

## Drift lint

`tools/drift-lint.sh` diffs every active bundle against this template (required
files, forbidden process docs, composer scripts, pinned dev-tool versions,
lowercase `tests/`). Run it from the directory that holds all the bundle repos:

```bash
bash registry2-bundle/tools/drift-lint.sh
```

## Releasing

1. Update `CHANGELOG.md` (move the `Unreleased` section to the new version).
2. Ensure CI is green (all gates above).
3. Before tagging a new major, check backward compatibility
   (e.g. `roave/backward-compatibility-check`) and bump SemVer accordingly.
4. Tag `vX.Y.Z`, push the tag, create the GitHub release; Packagist syncs via webhook.
5. Spot-check the dist: `composer archive` must not contain `tests/`, `docs/`,
   CI/tooling configs, `CONTRIBUTING.md` or `CLAUDE.md`.
