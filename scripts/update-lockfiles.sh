#!/usr/bin/env bash
# Updates committed lock files for all TS-based SDK templates.
# Run this whenever dependencies in a package.json.twig change.
#
# Usage:
#   ./scripts/update-lockfiles.sh           # update all
#   ./scripts/update-lockfiles.sh web       # update one (web | node | react-native | cli)
#   ./scripts/update-lockfiles.sh renovate  # update lock files and regenerate examples

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TARGET="${1:-all}"
WORKDIR="$(mktemp -d)"
trap 'rm -rf "$WORKDIR"' EXIT

ensure_tool() {
    local command_name="$1" tool_name="$2" version="${3:-}"

    if command -v "$command_name" >/dev/null 2>&1; then
        return
    fi

    if ! command -v install-tool >/dev/null 2>&1; then
        echo "ERROR: required command '$command_name' is missing" >&2
        return 1
    fi

    if [ -n "$version" ]; then
        install-tool "$tool_name" "$version"
    else
        install-tool "$tool_name"
    fi
}

ensure_npm() {
    ensure_tool npm node "${SDK_GEN_NODE_VERSION:-24.18.0}"
}

ensure_bun() {
    ensure_tool bun bun "${SDK_GEN_BUN_VERSION:-1.3.4}"
}

ensure_php() {
    ensure_tool php php "${SDK_GEN_PHP_VERSION:-8.5.0}"
}

ensure_composer() {
    ensure_tool composer composer "${SDK_GEN_COMPOSER_VERSION:-2.10.2}"
}

strip_twig() {
    # Replace {{ ... }} expressions with a safe placeholder so npm/bun
    # can parse the file as plain JSON. Only metadata fields use Twig
    # vars; dependency versions are all static.
    sed 's/{{[^}]*}}/PLACEHOLDER/g' "$1"
}

replace_first() {
    # POSIX-portable first-match replacement (no GNU sed required).
    # Exits with code 2 if the pattern is not found.
    # Usage: replace_first <file> <literal_old> <literal_new>
    local file="$1" old="$2" new="$3"
    awk -v o="$old" -v n="$new" -v done=0 \
        '{ if (!done && index($0, o)) { sub(o, n); done=1 } print } END { exit(done ? 0 : 2) }' \
        "$file" > "${file}.tmp" && mv "${file}.tmp" "$file"
}

validate_no_placeholders() {
    local lockfile="$1"
    if grep -q '"PLACEHOLDER"' "$lockfile"; then
        echo "ERROR: unresolved PLACEHOLDER values remain in $lockfile" >&2
        return 1
    fi
}

sync_npm_overrides() {
    local lockfile="$1"
    local packagefile="$2"

    node - "$lockfile" "$packagefile" <<'NODE'
const fs = require('fs');

const [lockfile, packagefile] = process.argv.slice(2);
const lock = JSON.parse(fs.readFileSync(lockfile, 'utf8'));
const pkg = JSON.parse(fs.readFileSync(packagefile, 'utf8'));

if (pkg.overrides) {
  lock.packages ??= {};
  lock.packages[''] ??= {};
  lock.packages[''].overrides = pkg.overrides;
}

fs.writeFileSync(lockfile, `${JSON.stringify(lock, null, 2)}\n`);
NODE
}

restore_twig_npm() {
    # Replace PLACEHOLDER values in package-lock.json with the correct
    # Twig expressions extracted from the corresponding package.json.twig.
    # PLACEHOLDER appears in: root "name", root "version",
    # packages[""] "name", "version", "license".
    local lockfile="$1"
    local twig_name="$2"

    replace_first "$lockfile" '"name": "PLACEHOLDER"' "\"name\": \"${twig_name}\""
    replace_first "$lockfile" '"version": "PLACEHOLDER"' '"version": "{{ sdk.version }}"'
    replace_first "$lockfile" '"name": "PLACEHOLDER"' "\"name\": \"${twig_name}\""
    replace_first "$lockfile" '"version": "PLACEHOLDER"' '"version": "{{ sdk.version }}"'
    replace_first "$lockfile" '"license": "PLACEHOLDER"' '"license": "{{ sdk.license }}"'
}

restore_twig_bun() {
    # Replace PLACEHOLDER in bun.lock with the correct Twig expression.
    local lockfile="$1"
    local twig_name="$2"

    replace_first "$lockfile" '"name": "PLACEHOLDER"' "\"name\": \"${twig_name}\""
    validate_no_placeholders "$lockfile"
}

update_npm() {
    local lang="$1"
    local twig_name="$2"
    local template="$ROOT/templates/$lang/package.json.twig"
    local dest="$ROOT/templates/$lang/package-lock.json.twig"
    local dir="$WORKDIR/$lang"

    ensure_npm

    echo "→ $lang (npm)"
    mkdir -p "$dir"
    strip_twig "$template" > "$dir/package.json"
    (cd "$dir" && npm_config_legacy_peer_deps=false npm install --package-lock-only --ignore-scripts --silent 2>/dev/null)
    cp "$dir/package-lock.json" "$dest"
    sync_npm_overrides "$dest" "$template"
    restore_twig_npm "$dest" "$twig_name"
    echo "  updated templates/$lang/package-lock.json.twig"
}

update_bun() {
    local twig_name="$1"
    local dir="$WORKDIR/cli"
    local template="$ROOT/templates/cli/package.json.twig"
    local dest="$ROOT/templates/cli/bun.lock.twig"

    ensure_bun

    echo "→ cli (bun)"
    mkdir -p "$dir"
    strip_twig "$template" > "$dir/package.json"
    cd "$dir" && bun install --silent 2>/dev/null
    cp "$dir/bun.lock" "$dest"
    restore_twig_bun "$dest" "$twig_name"
    echo "  updated templates/cli/bun.lock.twig"
}

restore_cli_bin() {
    replace_first "$ROOT/templates/cli/package-lock.json.twig" \
        '"PLACEHOLDER": "dist/cli.cjs"' \
        '"{{ language.params.executableName|caseLower }}": "dist/cli.cjs"'
    validate_no_placeholders "$ROOT/templates/cli/package-lock.json.twig"
}

update_all() {
    update_npm web "{{ language.params.npmPackage }}"
    validate_no_placeholders "$ROOT/templates/web/package-lock.json.twig"
    update_npm node "{{ language.params.npmPackage | caseDash }}"
    validate_no_placeholders "$ROOT/templates/node/package-lock.json.twig"
    update_npm react-native "{{ language.params.npmPackage }}"
    validate_no_placeholders "$ROOT/templates/react-native/package-lock.json.twig"
    update_npm cli "{{ language.params.npmPackage|caseDash }}"
    restore_cli_bin
    update_bun "{{ language.params.npmPackage|caseDash }}"
}

regenerate_examples() {
    cd "$ROOT"

    ensure_php
    ensure_composer

    if [ ! -f vendor/autoload.php ]; then
        composer install --ignore-platform-reqs --no-interaction --prefer-dist
    fi

    php example.php

    if grep -R '"PLACEHOLDER"' "$ROOT"/templates/*/*lock*.twig >/dev/null 2>&1; then
        echo "ERROR: unresolved PLACEHOLDER values remain in generated lockfile templates" >&2
        exit 1
    fi
}

case "$TARGET" in
    web)
        update_npm web "{{ language.params.npmPackage }}"
        validate_no_placeholders "$ROOT/templates/web/package-lock.json.twig"
        ;;
    node)
        update_npm node "{{ language.params.npmPackage | caseDash }}"
        validate_no_placeholders "$ROOT/templates/node/package-lock.json.twig"
        ;;
    react-native)
        update_npm react-native "{{ language.params.npmPackage }}"
        validate_no_placeholders "$ROOT/templates/react-native/package-lock.json.twig"
        ;;
    cli)
        update_npm cli "{{ language.params.npmPackage|caseDash }}"
        restore_cli_bin
        update_bun "{{ language.params.npmPackage|caseDash }}"
        ;;
    all)
        update_all
        ;;
    renovate)
        update_all
        regenerate_examples
        ;;
    *)
        echo "Unknown target: $TARGET. Use web | node | react-native | cli | all | renovate"
        exit 1
        ;;
esac

echo ""
echo "Done. Review the diffs and commit the updated lock files."
