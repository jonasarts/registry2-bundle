#!/usr/bin/env bash
#
# drift-lint — keep the jonasarts Symfony bundles aligned with the registry2
# gold standard. Run it from the container directory that holds all the bundle
# repositories side by side (registry2-bundle is the canonical template):
#
#   bash registry2-bundle/tools/drift-lint.sh
#
# It checks every active bundle for the agreed structure, composer scripts and
# pinned dev-tool versions, and exits non-zero on any drift. It is a maintainer
# tool (cross-repository); it is export-ignored and never shipped in the dist.

set -uo pipefail

# Active bundles to check (deprecated ones are intentionally excluded).
BUNDLES=(
  registry2-bundle
  google-authenticator-bundle
  phpqrcode-bundle
  tcpdf-bundle
)

# Files every active bundle must ship.
REQUIRED_FILES=(
  .editorconfig
  .gitattributes
  .gitignore
  .github/workflows/ci.yml
  .php-cs-fixer.dist.php
  phpstan.dist.neon
  phpunit.dist.xml
  rector.php
  renovate.json
  composer.json
  README.md
  CHANGELOG.md
  CONTRIBUTING.md
  CLAUDE.md
  docs/index.md
  docs/test.md
)

# Process docs that must NOT exist (deleted in Phase 3 / decision D).
FORBIDDEN_FILES=(
  MODERNIZATION.md
  EXECUTION_PLAN.md
  docs/MODERNIZATION.md
  docs/EXECUTION_PLAN.md
  docs/changes.md
)

# composer scripts every bundle must define.
REQUIRED_SCRIPTS=(cs cs-check rector rector-check phpstan test)

# Pinned dev-tool constraints (composer normalizes "|" to " || ").
# "package=constraint" entries (indexed array → portable to bash 3.2 on macOS).
REQUIRED_DEV=(
  "friendsofphp/php-cs-fixer=^3.95"
  "phpstan/phpstan=^2.0"
  "rector/rector=^2.0"
  "phpunit/phpunit=^12.0 || ^13.0"
)

# Resolve the container root = parent of this script's bundle.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

fail=0
note() { printf '  \033[31m✗ %s\033[0m\n' "$1"; fail=1; }

# Read a value from a bundle's composer.json via PHP (always available here).
composer_get() {
  php -r '
    $j = json_decode(file_get_contents($argv[1]), true);
    $path = explode(".", $argv[2]);
    $v = $j;
    foreach ($path as $p) { if (!is_array($v) || !array_key_exists($p, $v)) { exit(1); } $v = $v[$p]; }
    echo is_scalar($v) ? $v : json_encode($v);
  ' "$1" "$2" 2>/dev/null
}

for b in "${BUNDLES[@]}"; do
  dir="$ROOT/$b"
  printf '\n\033[1m== %s ==\033[0m\n' "$b"
  if [ ! -d "$dir" ]; then note "directory not found: $dir"; continue; fi

  for f in "${REQUIRED_FILES[@]}"; do
    [ -f "$dir/$f" ] || note "missing required file: $f"
  done

  for f in "${FORBIDDEN_FILES[@]}"; do
    [ -e "$dir/$f" ] && note "forbidden process doc present: $f"
  done

  # tests/ directory must be tracked lowercase (case matters on Linux CI).
  if [ -d "$dir/.git" ]; then
    cased="$(git -C "$dir" ls-files | grep -iE '^tests?/' | sed -E 's#/.*##' | sort -u)"
    [ "$cased" = "tests" ] || [ -z "$cased" ] || note "tests directory not lowercase in git: '$cased'"
  fi

  cj="$dir/composer.json"
  if [ -f "$cj" ]; then
    # No "version" field (Packagist derives it from the VCS tag).
    composer_get "$cj" "version" >/dev/null && note 'composer.json must not define a "version" field'
    # minimum-stability / prefer-stable / sort-packages.
    [ "$(composer_get "$cj" "minimum-stability")" = "stable" ] || note 'minimum-stability is not "stable"'
    [ "$(composer_get "$cj" "prefer-stable")" = "1" ] || note "prefer-stable is not true"
    [ "$(composer_get "$cj" "config.sort-packages")" = "1" ] || note "config.sort-packages is not true"
    # Required composer scripts.
    for s in "${REQUIRED_SCRIPTS[@]}"; do
      composer_get "$cj" "scripts.$s" >/dev/null || note "missing composer script: $s"
    done
    # Pinned dev-tool versions.
    for entry in "${REQUIRED_DEV[@]}"; do
      pkg="${entry%%=*}"
      want="${entry#*=}"
      have="$(composer_get "$cj" "require-dev.$pkg")"
      [ "$have" = "$want" ] || note "require-dev $pkg = '$have' (expected '$want')"
    done
  fi
done

echo
if [ "$fail" -ne 0 ]; then
  printf '\033[31mdrift-lint: drift detected.\033[0m\n'
  exit 1
fi
printf '\033[32mdrift-lint: all bundles aligned with the gold standard.\033[0m\n'
