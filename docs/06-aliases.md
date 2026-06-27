Method aliases (shortcuts)
==========================

Every registry/system method has a short alias. The aliases are kept for
backwards compatibility and convenience; they are thin delegates of the
full-name methods on `AbstractRegistry` / `RegistryInterface`.

## Naming scheme

The alias is built from two parts:

- **First letter — scope:** `r` = registry (user-scoped), `s` = system
  (global).
- **Second letter — operation:** `e` = exists, `r` = read, `w` = write,
  `d` = delete.

Two special read variants extend the read operation:

- **`…d` = readDefault** — `rd` would collide with delete, so the readDefault
  aliases are `rrd` / `srd` (read + default).
- **`…o` = readOnce** — read then delete: `rro` / `sro`.

> Note on `rd` / `sd`: these are **delete** (registryDelete / systemDelete).
> The *readDefault* aliases are the three-letter `rrd` / `srd`.

## Mapping

### Registry (user-scoped)

| Alias | Full method            | Operation     |
|-------|------------------------|---------------|
| `re`  | `registryExists`       | exists        |
| `rr`  | `registryRead`         | read          |
| `rrd` | `registryReadDefault`  | read, default |
| `rro` | `registryReadOnce`     | read once     |
| `rw`  | `registryWrite`        | write         |
| `rd`  | `registryDelete`       | delete        |

### System (global)

| Alias | Full method          | Operation     |
|-------|----------------------|---------------|
| `se`  | `systemExists`       | exists        |
| `sr`  | `systemRead`         | read          |
| `srd` | `systemReadDefault`  | read, default |
| `sro` | `systemReadOnce`     | read once     |
| `sw`  | `systemWrite`        | write         |
| `sd`  | `systemDelete`       | delete        |

## Maintenance note

The aliases simply forward to their full-name counterparts. When a signature of
a full-name method changes, the corresponding alias signature **must** be kept
in sync (both live in `AbstractRegistry` and are declared in
`RegistryInterface`).

[Return to the index.](index.md)
