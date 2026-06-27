# Modernization Plan — jonasarts/registry2-bundle

Owner decision 2026-06-27. Target: Symfony 8.1 · PHP 8.4 · Doctrine ORM 3 · PostgreSQL 18. Status: IMPLEMENTED — P0–P6 umgesetzt auf Branch `modernization/p0-baseline` (152 Tests grün; Tag/Release ausstehend). Siehe `EXECUTION_PLAN.md` und `UPGRADE-8.0.md`.

## Decision

**KEEP als Bundle.** Das Bundle wird bereits in 10+ Produkten eingesetzt; "build locally" entfällt. Modernisierung auf SF8.1/PHP8.4/ORM3 und vollständige Testabdeckung sind ab jetzt MUST-HAVE. Die `snc/redis-bundle`-Kopplung wird aufgelöst (Redis-Engine optional, austauschbarer Client). Doctrine-Engine ist Default; Redis-Engine nur, wenn konfiguriert. Die Alias-Kürzel (`rr`/`rw`/…) bleiben erhalten und werden dokumentiert.

## Goals

- Lauffähig und supported unter Symfony 8.1, PHP 8.4, Doctrine ORM 3 / DBAL 4, PostgreSQL 18.
- Keine harte Abhängigkeit mehr auf `snc/redis-bundle`; Redis optional und gegen eine schmale Client-Abstraktion gebaut.
- Doctrine-Engine als Default-Service; Redis-Engine nur bei expliziter Konfiguration registriert.
- Vollständige, DB-/Redis-unabhängige Unit-Tests für die Kernlogik + CI.
- Latente Bugs (nullable `value`) und Toter Code beseitigt; CRUD-UI sicher (POST+CSRF+AuthZ, default-off).
- Klare BC-/Upgrade-Dokumentation; Alias-Schema dokumentiert.

## Work items

### Composer / Constraints
- [ ] `composer.json`: `require` auf `php: ">=8.4"` anheben.
- [ ] `composer.json`: `symfony/config` und `symfony/dependency-injection` auf `^7.0|^8.0` (Ziel SF8.1).
- [ ] `composer.json`: `doctrine/orm: "^3.0"` und `doctrine/dbal: "^4.0"` explizit in `require` (bisher nur via `suggest`/transitiv) — als optional-mit-Engine prüfen, siehe Entkopplung; sonst in `require-dev` für Tests.
- [ ] `composer.json`: `snc/redis-bundle` aus `require` ENTFERNEN → nach `suggest`/`require-dev` verschieben (Entkopplung, s. u.).
- [ ] `composer.json`: `require-dev` PHPUnit auf `^11.0|^12.0` fixieren, `symfony/yaml ^8.0`, Doctrine ORM/DBAL für Engine-Tests ergänzen.
- [ ] `autoload-dev` PSR-4 zeigt auf `tests/` (kleingeschrieben), Verzeichnis heißt aber `Tests/` → vereinheitlichen (Verzeichnis `Tests/` → `tests/` umbenennen ODER Mapping anpassen).

### snc/redis-bundle-Entkopplung
- [ ] `src/Resources/config/services.yaml`: harte Referenz `$redis: "@snc_redis.registry"` entfernen. `RedisRegistry`/`DoctrineRegistry` NICHT mehr unbedingt registrieren.
- [ ] `src/DependencyInjection/RegistryExtension.php`: Engine-Auswahl konfigurierbar machen. Default = Doctrine. Redis-Service nur registrieren, wenn `redis`-Block konfiguriert ist (Service-ID des Clients konfigurierbar, z. B. `redis.client_service` mit Default `snc_redis.registry`, aber ohne Hard-Require).
- [ ] `src/DependencyInjection/Configuration.php`: Knoten `engine` (`doctrine`|`redis`, Default `doctrine`) und `redis.client_service` (scalar, Default `snc_redis.registry`) ergänzen; `redis.prefix` bleibt.
- [ ] `src/Engine/RedisRegistryEngine.php`: Konstruktor akzeptiert bereits `object $redis` und prüft `hExists/hDel/hGet/hSet/hGetAll/keys` per `method_exists` — also bereits client-agnostisch (natives `\Redis` / Predis). Beibehalten; optional dünnes `RedisClientInterface` einführen, um die `method_exists`-Prüfung zu ersetzen (nice-to-have, nicht Pflicht).
- [ ] Doku: Beispiel zeigen, wie ein nativer `\Redis`- oder Predis-Service ODER ein `symfony/cache`-Redis-Adapter als `redis.client_service` injiziert wird, ohne snc-Bundle.

### Doctrine / PostgreSQL-Bugs
- [ ] `src/Entity/RegistryKeyEntity.php` (Zeile 50/51) und `src/Entity/SystemKeyEntity.php` (Zeile 47/48): `value`-Spalte ist `nullable: true`, Property aber non-nullable `string` → bei NULL aus DB TypeError. Fix: entweder Column `nullable: false` + `options: {default: ''}`, oder Property `?string` + Getter/Setter-Signaturen anpassen. **Empfehlung: `nullable: false`** (Werte werden immer als String persistiert, leerer String statt NULL).
- [ ] Optional: `type`-Column (`length: 1`) auf Doctrine `enumType: RegistryKeyType::class` umstellen statt manuellem String-Mapping in `setType()/getType()`. Erfordert Daten-Konsistenzprüfung (Werte b/i/f/s/d/t/a) — als BC-bewussten Schritt markieren.
- [ ] Doctrine-Migration bereitstellen (oder dokumentieren), die `value NOT NULL DEFAULT ''` setzt; Tabellennamen `registry` und gequotetes `"system"` für PG18 verifizieren.

### Dead Code / Duplikate
- [ ] `src/Entity/AbstractRegistryKey.php`: komplett auskommentierte Leerklasse — entfernen. `RegistryKey`/`SystemKey` `extends AbstractRegistryKey` entsprechend auflösen (Vererbung streichen, Interface bleibt).
- [ ] Doppelte `private function stringify()` in `src/Engine/DoctrineRegistryEngine.php` (Z. 263) und `src/Engine/RedisRegistryEngine.php` (Z. 288): in ein gemeinsames `trait StringifyValue` oder eine kleine Helper-Klasse extrahieren; beide Engines verwenden sie.
- [ ] Auskommentierter Alt-Code in `RedisRegistryEngine::getHashKey()` (Z. 55–61) entfernen.

### CRUD-UI Security (GET-Delete-Fix)
- [ ] **Empfohlene Variante:** `src/Controller/RegistryController.php` und `SystemController.php`: Route `*_delete` von GET auf `methods: ['POST']` umstellen, CSRF-Token via `isCsrfTokenValid()` prüfen; Index-Templates auf `<form method="post">` mit `csrf_token()` umbauen statt Delete-Links.
- [ ] AuthZ ergänzen: `#[IsGranted('ROLE_REGISTRY_ADMIN')]` (konfigurierbare Rolle) auf Controller-Ebene.
- [ ] `editAction`/`deleteAction` deserialisieren eine client-gelieferte JSON-Entity aus `?entity=` (`RegKey::deserialize`): Eingaben strikt validieren (Felder/Typen) bzw. statt Roh-Deserialisierung Identifikatoren (user_id/key/name/type) als getrennte, validierte Parameter führen.
- [ ] UI per Config optional und **default-off**: Knoten `ui.enabled` (Default `false`) in `Configuration.php`; Controller/Routes nur laden/registrieren, wenn aktiviert.
- [ ] Twig-Templates: `{% extends '::base.html.twig' %}` (veraltete SF-Syntax) auf konfigurierbares Base-Template umstellen.

### Alias-Schema-Doku (BLEIBT)
- [ ] In README/`docs/` das Kürzel-Schema dokumentieren, nicht entfernen. Schema: `r`=registry, `s`=system; zweiter Buchstabe = Operation: `r`=read, `w`=write, `d`=delete, `e`=exists; Sondervarianten `rd`/`sd` (readDefault) bzw. Endung `o` (readOnce). Mapping-Tabelle:
  - `re`→`registryExists`, `rd`→`registryDelete`, `rr`→`registryRead`, `rrd`→`registryReadDefault`, `rro`→`registryReadOnce`, `rw`→`registryWrite`.
  - `se`→`systemExists`, `sd`→`systemDelete`, `sr`→`systemRead`, `srd`→`systemReadDefault`, `sro`→`systemReadOnce`, `sw`→`systemWrite`.
- [ ] Hinweis dokumentieren: Aliase sind Delegates der Full-Name-Methoden in `src/Registry/AbstractRegistry.php` / `RegistryInterface.php` — Signaturen müssen synchron bleiben.

### Tooling: Rector & CS-Fixer (MUST-HAVE)
- [ ] `rector/rector` und `friendsofphp/php-cs-fixer` in `require-dev`; Configs `rector.php` (Sets: PHP 8.4, Symfony, Doctrine, CodeQuality) und `.php-cs-fixer.dist.php` (PSR-12/PER + Symfony-Ruleset) ins Repo.
- [ ] Rector-Migrationslauf als Teil des SF8.1/PHP8.4-Bumps anwenden (`rector process`); Ergebnisse reviewen. CS-Fixer einmal voll über den Code laufen lassen.
- [ ] CI: `rector --dry-run` und `php-cs-fixer fix --dry-run` als **blocking** Checks aufnehmen (zunächst non-blocking während der Einführung, dann scharf).

## Testing (MUST-HAVE)

- [ ] `phpunit.xml(.dist)` ins Repo aufnehmen (fehlt aktuell): Testsuites `unit` (kein Kernel/DB) und `integration` (Redis/Kernel) trennen.
- [ ] **AbstractRegistry-Kernlogik DB-unabhängig testen** (`src/Registry/AbstractRegistry.php`) gegen ein In-Memory-Fake von `RegistryEngineInterface`:
  - Fallback-Kette user → user 0 → YAML-Default → Code-Default (`registryRead`/`registryReadDefault`, Z. 144–189).
  - Auto-Delete bei Gleichheit mit user-0-Wert (`registryWrite`, Z. 247–252).
  - Typ-Dekodierung `decodeTypedValue` und `normalizeDefaultValue` für alle `RegistryKeyType` (int/bool/float/string/date/time/array).
  - YAML-Default-Laden (`readDefaultKeyValue`) inkl. Delimiter-Pfad `key:name`.
  - Delimiter-Verbot im Namen (`registryWrite`/`systemWrite` werfen `RuntimeException`).
- [ ] **DoctrineRegistryEngine**: bestehende Mock-Tests (`Tests/DoctrineRegistryEngineTest.php`, 24 Tests) beibehalten/erweitern; auf ORM-3-API verifizieren.
- [ ] **RedisRegistryEngine**: aktuell `WebTestCase`-/Live-Redis-abhängig (`Tests/RedisRegistryEngineTest.php`, `Tests/RegistryTest.php`, ordnungsabhängig). Umbauen auf:
  - Unit-Tests gegen ein Fake-Redis-Objekt (hash-basiert, in-memory) → ohne Live-Redis lauffähig; Reihenfolgeabhängigkeit (`@depends`/"must run in order") entfernen.
  - Optional eine eigene, klar markierte Integration-Suite gegen echtes Redis (in CI als Service-Container).
- [ ] `Tests/EntityTest.php` und `Tests/AbstractRegistryTest.php` an entfernten `AbstractRegistryKey` und nullable-Fix anpassen.
- [ ] **CI**: GitHub-Actions-Workflow (`.github/workflows/ci.yml`): Matrix PHP 8.4, `composer validate`, PHPUnit (unit immer; integration mit Redis+PostgreSQL-Service), optional PHPStan (Level beibehalten, da Code bereits annotiert).
- [ ] **Zielabdeckung**: ≥ 85 % Lines für `src/Registry/` und `src/Engine/`; Kernlogik in `AbstractRegistry` 100 % Branch für Fallback-/Typ-Pfade.

## SF8/PHP8.4 compatibility specifics

- `RegistryExtension extends Symfony\Component\DependencyInjection\Extension\Extension`: läuft unter SF8 weiter; sauberer wäre Migration auf `AbstractBundle` mit `loadExtension()`/`configure()` und Inline-Service-Definition. Optional, kein Blocker.
- `getAlias(): 'registry'` und der Config-Tree (`Configuration.php`) sind SF8-kompatibel; neue Knoten (`engine`, `redis.client_service`, `ui.enabled`) ergänzen.
- Routing: `docs/01-install.md` nennt `type: annotation`/`routing.yml` (veraltet) und nicht existente Methoden (`setDefaultKeysEnabled()`, `ReadRegistryDefault()`). Doku auf PHP-Attribut-Routing (`#[Route]`, bereits in den Controllern) und reale API korrigieren.
- Doctrine ORM 3 / DBAL 4: verwendete API (`EntityManagerInterface`, `findOneBy/findAll`, `persist/remove/flush`, Attribut-Mapping, `Doctrine\Persistence\ObjectRepository`) ist ORM-3-kompatibel — kein harter Bruch erwartet; Repository-Generics-PHPDoc beibehalten.
- PHP 8.4: keine entfernten Features genutzt; `enum`, `match`, named args bereits im Einsatz. Implizit-nullable-Parameter prüfen (PHP 8.4 Deprecation): `?string $default_values_filename = null` ist bereits explizit nullable → ok.
- PostgreSQL 18: gequoteter Tabellenname `"system"` korrekt; `value NOT NULL DEFAULT ''` nach Bugfix; Unique-Constraints `uix_userid_key_name` / `uix_key_name` verifizieren.

## Definition of Done

- [ ] `composer require` der neuen Version unter SF8.1/PHP8.4 erfolgreich, OHNE `snc/redis-bundle` installieren zu müssen; Doctrine-Engine out-of-the-box nutzbar.
- [ ] Redis-Engine nur bei konfiguriertem `engine: redis` + `redis.client_service` aktiv; funktioniert mit nativem `\Redis`/Predis/Cache-Adapter.
- [ ] Alle Unit-Tests laufen ohne Live-Redis/DB grün; Integration-Tests in CI grün; Zielabdeckung erreicht.
- [ ] `value`-nullable-Bug behoben + Migration/Doku; `AbstractRegistryKey` entfernt; `stringify()` dedupliziert.
- [ ] CRUD-UI: Delete nur POST+CSRF, AuthZ aktiv, UI default-off per Config.
- [ ] Alias-Schema dokumentiert; `docs/` korrigiert (Routing, API).
- [ ] CHANGELOG/Upgrade-Notes mit BC-Hinweisen (Engine-Konfiguration, snc-Entkopplung, UI default-off) gepflegt; neuer Tag mit `symfony/^8`-Constraint veröffentlicht.
- [ ] Rector & CS-Fixer als blocking CI-Gate grün (`rector --dry-run` + `php-cs-fixer fix --dry-run` ohne Findings); Configs im Repo.

## Out of scope

- Vollständiger Rewrite auf `symfony/cache` als alleinige Engine (Redis-Engine bleibt eigenständig; Cache-Adapter nur als möglicher Client).
- Entfernen oder Umbenennen der Alias-Kürzel (`rr`/`rw`/…) — bleiben per Owner-Entscheid.
- Ersatz des CRUD-UI durch EasyAdmin.
- Mehr-Mandanten-/ACL-Modell über die simple `user_id`-Skalierung hinaus.
