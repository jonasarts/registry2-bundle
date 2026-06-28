# CLAUDE.md — rules for AI changes to this bundle

This is a jonasarts Symfony bundle. **registry2 is the canonical template**; keep
all bundles aligned. This file and `CONTRIBUTING.md` are `export-ignore`d.

## Always

- After editing `composer.json`, run `composer normalize` and
  `composer validate --strict`. `composer normalize` owns field order/format —
  never hand-sort.
- Keep `require` minimal and correct: `php` + explicit `ext-*` + the
  `symfony/*` components actually used at runtime, all at `^7.0 || ^8.0`.
  Optional integrations go in `suggest` (+ `require-dev` for tests), never in
  `require`.
- If `src/` loads `config/services.yaml` via `$container->import()` /
  `YamlFileLoader`, then `symfony/yaml` is a **runtime** dependency → it belongs
  in `require` (composer-require-checker will not catch this; composer-unused
  will falsely call it unused — ignore that false positive).
- Before claiming done, all gates must pass: `composer validate --strict`,
  `composer normalize --dry-run`, `composer cs-check`, `composer rector-check`,
  `composer phpstan`, `composer test`, `composer test-integration` (with redis),
  `composer audit`, plus `composer-require-checker check
  --config-file=composer-require-checker.json` and `composer-unused`.
- The integration suite needs redis: `docker compose up -d` first.

## Never

- Never add a `version` field to `composer.json` (Packagist uses the VCS tag).
- Never use `symfony/symfony`; depend on individual components.
- Never commit `composer.lock` (it is git-ignored; libraries don't ship a lock).
- Never rename the test dir away from lowercase `tests/`, or the config away from
  `phpunit.dist.xml`.
- Never delete user docs. `UPGRADE-*.md` / migration guides are durable user
  docs, **not** process docs. Process docs (`MODERNIZATION.md`,
  `EXECUTION_PLAN.md`, `HANDOFF-*.md`, `handoffs/`, `docs/changes.md`) must not
  exist here.
- Never weaken a CI gate to make it pass; fix the cause.

## Conventions

- Test suites: `unit` and `integration`. `composer test` = `phpunit --testsuite
  unit`; `composer test-integration` = `phpunit --testsuite integration`.
- Docs: `docs/index.md`, numbered topics (contiguous, no gaps),
  `02-configuration.md` only if the bundle has configuration, plus `docs/test.md`.
  Changelog is the root `CHANGELOG.md` (Keep a Changelog + SemVer); the top
  section is `Unreleased` until tagged.
- New dev/test/doc artifacts must be added to `.gitattributes` as `export-ignore`.
- `composer-require-checker.json` whitelists the optional CRUD-UI / Doctrine
  symbols (provided by `suggest` + `require-dev`). Extend it only for genuinely
  optional integrations.

## Drift

When changing a shared convention, update this template (registry2) and then the
other bundles, and confirm with `tools/drift-lint.sh` (run from the directory
holding all bundle repos).
