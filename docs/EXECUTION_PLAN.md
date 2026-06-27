# Ausführungsplan — Modernisierung registry2-bundle

Ableitung aus `MODERNIZATION.md` (Owner-Entscheid 2026-06-27). Ziel: Symfony 8.1 · PHP 8.4 · Doctrine ORM 3 / DBAL 4 · PostgreSQL 18. Bundle **bleibt** Bundle; snc-Redis wird entkoppelt; Doctrine-Engine ist Default.

Dieser Plan ordnet die Work-Items zu 7 sequenziellen Phasen mit Abhängigkeiten, je 1 PR pro Phase. Leitprinzip: **erst ein grünes Test-Fundament, dann modernisieren** — jede Phase landet mit eigenen Tests und hält CI grün.

> **Status (Stand 2026-06-28):** P0–P6 umgesetzt auf Branch `modernization/p0-baseline`. 152 Tests grün, Rector/CS-Fixer sauber. Offen: finaler Commit von P6 (Doku) und das Taggen/Veröffentlichen von `8.0.0`.

---

## Phasenübersicht

| # | Phase | Aufwand | Risiko | Hängt ab von |
|---|-------|---------|--------|--------------|
| P0 | Baseline & Test-Harness | S | niedrig | — |
| P1 | Dependency- & Platform-Bump | M | mittel | P0 |
| P2 | snc/redis-Entkopplung (DI/Config) | M | mittel | P1 |
| P3 | Bugfixes & Dead Code | M | mittel (DB-Migration) | P1 |
| P4 | Testabdeckung (MUST-HAVE) | L | niedrig | P2, P3 |
| P5 | CRUD-UI-Härtung | M | mittel (Security) | P2 |
| P6 | Doku & Release | S | niedrig | P0–P5 |

Kritischer Pfad: **P0 → P1 → P2 → P4 → P6**. P3 läuft parallel zu P2 (eigene Dateien), muss aber vor P4 gemerged sein. P5 kann parallel zu P4 laufen, braucht aber den `ui.enabled`-Knoten aus P2.

---

## P0 — Baseline & Test-Harness
**Ziel:** Reproduzierbar testbarer Stand, bevor irgendetwas geändert wird.

- `Tests/` → `tests/` umbenennen **oder** `autoload-dev` PSR-4 auf `Tests/` korrigieren (aktuell zeigt das Mapping auf `tests/`, Verzeichnis heißt `Tests/`). Empfehlung: Verzeichnis auf `tests/` umbenennen (Konvention).
- `phpunit.xml.dist` ins Repo aufnehmen; Testsuites `unit` (kein Kernel/DB) und `integration` (Redis/Kernel) trennen.
- **Tooling-Setup (MUST):** `rector.php` (Sets: PHP 8.4, Symfony, Doctrine, CodeQuality) und `.php-cs-fixer.dist.php` (PSR-12/PER + Symfony-Ruleset) ins Repo; `rector/rector` + `friendsofphp/php-cs-fixer` in `require-dev`. In P0 nur Config + Dry-Run-Baseline (noch nicht erzwingen).
- CI-Skeleton `.github/workflows/ci.yml`: `composer validate`, `composer install`, PHPUnit `unit`, `php-cs-fixer fix --dry-run` + `rector --dry-run` (zunächst non-blocking). (Matrix/Services kommen in P4.)
- Bestehende Tests laufen lassen → Baseline dokumentieren (welche grün/rot, welche Live-Redis brauchen).

**DoD:** `composer validate` grün, `unit`-Suite läuft (auch wenn vorerst leer/teilweise), CI-Pipeline existiert.
**Verifikation:** CI grün auf dem Branch.

---

## P1 — Dependency- & Platform-Bump
**Ziel:** Lauffähig unter SF 8.1 / PHP 8.4 / ORM 3 / DBAL 4.

- `composer.json` `require`: `php: ">=8.4"`; `symfony/config` & `symfony/dependency-injection` auf `^7.0|^8.0`.
- `require-dev`: PHPUnit auf `^11.0|^12.0` fixieren, `symfony/yaml ^8.0`, `doctrine/orm ^3` + `doctrine/dbal ^4` für Engine-Tests ergänzen.
- (snc-Verschiebung selbst erfolgt in P2, damit DI-Änderung und Constraint zusammen reviewt werden.)
- `composer update` auflösen; Build-/Deprecation-Fehler unter PHP 8.4 beheben.
- **Rector-Migrationslauf (MUST):** Rector mit den PHP-8.4-/Symfony-/Doctrine-Sets anwenden (`rector process`); generierte Änderungen reviewen. CS-Fixer einmal voll durchlaufen lassen, damit der gesamte Code dem Ruleset entspricht.
- SF8/PHP8.4-Spezifika gegenchecken (MODERNIZATION §"SF8/PHP8.4 compatibility"): `RegistryExtension extends Extension` bleibt lauffähig (Migration auf `AbstractBundle` = optional, **kein** Blocker dieser Phase); implizit-nullable-Parameter prüfen.

**DoD:** `composer install` unter PHP 8.4 grün, bestehende Suite läuft unverändert.
**Risiko:** ORM-3/DBAL-4-API-Drift — laut Analyse kein harter Bruch erwartet, in P4 mit Tests verifizieren.

---

## P2 — snc/redis-Entkopplung (DI & Config)
**Ziel:** Doctrine-Engine out-of-the-box; Redis nur bei expliziter Konfiguration; keine Hard-Dependency auf `snc/redis-bundle`.

- `Configuration.php`: neue Knoten
  - `engine` (`doctrine`|`redis`, Default `doctrine`)
  - `redis.client_service` (scalar, Default `snc_redis.registry`)
  - `ui.enabled` (bool, Default `false`) — vorbereitend für P5
- `RegistryExtension.php`: Engine-abhängige Service-Registrierung. Default Doctrine. Redis-Service nur registrieren, wenn `engine: redis` + `redis.client_service` gesetzt. UI-Controller/Routes nur bei `ui.enabled`.
- `services.yaml`: harte `$redis: "@snc_redis.registry"`-Referenz entfernen; `RedisRegistry`/`DoctrineRegistry` nicht mehr unbedingt registrieren.
- `composer.json`: `snc/redis-bundle` aus `require` **entfernen** → `suggest`/`require-dev`.
- `RedisRegistryEngine`: bereits client-agnostisch (`method_exists`-Prüfung auf `hGet/hSet/...`). Beibehalten; dünnes `RedisClientInterface` = nice-to-have, **nicht** Pflicht.

**DoD:** Installation & Nutzung der Doctrine-Engine **ohne** snc-Bundle möglich; Redis-Engine aktivierbar mit nativem `\Redis`/Predis/Cache-Adapter.
**Risiko:** BC — Bestandsapps mit Redis müssen künftig `engine: redis` setzen → in P6 Upgrade-Notes.

---

## P3 — Bugfixes & Dead Code
**Ziel:** Latente Bugs weg, Code dedupliziert. (Parallel zu P2 möglich — disjunkte Dateien.)

- **Nullable-`value`-Bug:** `RegistryKeyEntity` (Z. 50/51) & `SystemKeyEntity` (Z. 47/48): Column `nullable: true`, Property non-nullable `string` → TypeError bei NULL. **Empfehlung: `nullable: false` + `options: {default: ''}`.**
- Doctrine-Migration bereitstellen/dokumentieren: `value NOT NULL DEFAULT ''`; Tabellennamen `registry` und gequotetes `"system"`, Unique-Constraints `uix_userid_key_name`/`uix_key_name` für PG18 verifizieren.
- `AbstractRegistryKey` (auskommentierte Leerklasse) entfernen; `RegistryKey`/`SystemKey extends` auflösen (Interface bleibt).
- Doppelte `stringify()` (`DoctrineRegistryEngine` Z.263 / `RedisRegistryEngine` Z.288) in gemeinsames `trait StringifyValue` extrahieren.
- Auskommentierten Alt-Code in `RedisRegistryEngine::getHashKey()` (Z.55–61) entfernen.
- *(Optional, BC-bewusst:)* `type`-Column auf Doctrine `enumType: RegistryKeyType::class` — nur mit Daten-Konsistenzprüfung (b/i/f/s/d/t/a). Kann nach P6 verschoben werden.

**DoD:** Entity-Bugfix + Migration vorhanden; jede Änderung mit Regressionstest abgesichert (Übergabe an P4).
**Risiko:** Migration auf Produktivdaten — als separates, dokumentiertes Skript.

---

## P4 — Testabdeckung (MUST-HAVE)
**Ziel:** DB-/Redis-unabhängige Kernlogik-Tests + grüne Integration in CI.

- **`AbstractRegistry`-Kernlogik** gegen In-Memory-Fake von `RegistryEngineInterface`:
  - Fallback-Kette user → user 0 → YAML-Default → Code-Default.
  - Auto-Delete bei Gleichheit mit user-0-Wert.
  - `decodeTypedValue`/`normalizeDefaultValue` für alle `RegistryKeyType` (int/bool/float/string/date/time/array).
  - YAML-Default-Laden inkl. Delimiter-Pfad `key:name`.
  - Delimiter-Verbot im Namen → `RuntimeException`.
- **DoctrineRegistryEngine:** bestehende Mock-Tests beibehalten/erweitern, auf ORM-3-API verifizieren.
- **RedisRegistryEngine:** Umbau auf Fake-Redis (hash-basiert, in-memory); Reihenfolgeabhängigkeit (`@depends`) entfernen. Optionale, klar markierte Integration-Suite gegen echtes Redis.
- `tests/EntityTest.php` & `tests/AbstractRegistryTest.php` an entfernten `AbstractRegistryKey` + nullable-Fix anpassen.
- **CI ausbauen:** Matrix PHP 8.4; `unit` immer; `integration` mit Redis- + PostgreSQL-18-Service-Container; optional PHPStan (Level beibehalten). **Rector- & CS-Fixer-Checks jetzt blocking** (`--dry-run` muss sauber sein).

**DoD / Zielabdeckung:** Unit-Tests laufen **ohne** Live-Redis/DB grün; ≥ 85 % Lines für `src/Registry/` & `src/Engine/`; `AbstractRegistry`-Fallback-/Typ-Pfade 100 % Branch.

---

## P5 — CRUD-UI-Härtung
**Ziel:** Sichere, optionale UI (default-off). (Braucht `ui.enabled` aus P2.)

- `RegistryController` & `SystemController`: Route `*_delete` von GET auf `methods: ['POST']`; CSRF via `isCsrfTokenValid()`. Index-Templates: Delete-Links → `<form method="post">` mit `csrf_token()`.
- AuthZ: `#[IsGranted('ROLE_REGISTRY_ADMIN')]` (konfigurierbare Rolle) auf Controller-Ebene.
- `editAction`/`deleteAction`: keine Roh-Deserialisierung einer client-gelieferten JSON-Entity aus `?entity=` mehr — stattdessen getrennte, validierte Identifikatoren (user_id/key/name/type) **oder** strikte Feld-/Typvalidierung.
- UI default-off: Controller/Routes nur laden, wenn `ui.enabled: true`.
- Twig: veraltetes `{% extends '::base.html.twig' %}` → konfigurierbares Base-Template.

**DoD:** Delete nur via POST+CSRF, AuthZ aktiv, UI per Config aus; Funktionstests (Playwright/Functional) für POST+CSRF-Pfad.
**Risiko:** Security-sensibel — vor Merge `code-review`/`security-engineer`-Lens.

---

## P6 — Doku & Release
**Ziel:** Konsistente Doku, BC-Hinweise, getaggte Version.

- Alias-Schema dokumentieren (BLEIBT): `r`=registry/`s`=system; 2. Buchstabe = Operation (`r/w/d/e`), Sondervarianten `rd`/`sd` (readDefault), Endung `o` (readOnce). Mapping-Tabelle `re/rd/rr/rrd/rro/rw` + `se/sd/sr/srd/sro/sw`. Hinweis: Aliase sind Delegates der Full-Name-Methoden in `AbstractRegistry`/`RegistryInterface` → Signaturen synchron halten.
- `docs/01-install.md` korrigieren: veraltetes `type: annotation`/`routing.yml` und nicht existente Methoden (`setDefaultKeysEnabled()`, `ReadRegistryDefault()`) → reale Attribut-Routing-/API-Doku.
- CHANGELOG/Upgrade-Notes: Engine-Konfiguration, snc-Entkopplung, UI default-off, nullable-Migration.
- Neuer Tag mit `symfony/^8`-Constraint; Packagist.

**DoD:** Doku stimmt mit Code überein; Upgrade-Pfad beschrieben; Release getaggt.

---

## Abschluss-Verifikation (Definition of Done gesamt)
1. `composer require` der neuen Version unter SF8.1/PHP8.4 grün — **ohne** `snc/redis-bundle`; Doctrine-Engine out-of-the-box.
2. Redis-Engine nur bei `engine: redis` + `redis.client_service` aktiv; funktioniert mit `\Redis`/Predis/Cache-Adapter.
3. Alle Unit-Tests ohne Live-Redis/DB grün; Integration-Tests in CI grün; Coverage-Ziel erreicht.
4. nullable-Bug behoben + Migration; `AbstractRegistryKey` weg; `stringify()` dedupliziert.
5. CRUD-UI: Delete nur POST+CSRF, AuthZ aktiv, UI default-off.
6. Alias-Schema dokumentiert; `docs/` korrigiert; CHANGELOG/Upgrade-Notes gepflegt; Tag veröffentlicht.
7. **Rector & CS-Fixer als blocking CI-Gate grün** (`rector --dry-run` + `php-cs-fixer fix --dry-run` ohne Findings); Configs im Repo.

**Empfohlene Verifikations-Lenses je PR:** `code-review` (alle), zusätzlich `security-engineer` (P5), `database-specialist` (P3-Migration), `doctrine` (P1/P3).

## Out of scope (unverändert)
Voll-Rewrite auf `symfony/cache` als alleinige Engine · Entfernen/Umbenennen der Alias-Kürzel · EasyAdmin-Ersatz · Multi-Mandanten/ACL.

> **Hinweis:** Rector & CS-Fixer waren in `MODERNIZATION.md` als Out-of-scope markiert, sind per Owner-Korrektur (2026-06-27) nun **MUST** und in P0/P1/P4 + DoD verankert. `MODERNIZATION.md` sollte entsprechend angeglichen werden.
