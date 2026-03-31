#!/usr/bin/env bash
# Updates committed lock files for all TS-based SDK templates.
# Run this whenever dependencies in a package.json.twig change.
#
# Usage:
#   ./scripts/update-lockfiles.sh           # update all
#   ./scripts/update-lockfiles.sh web       # update one (web | node | react-native | cli)

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TARGET="${1:-all}"
WORKDIR="$(mktemp -d)"
trap 'rm -rf "$WORKDIR"' EXIT

strip_twig() {
    # Replace {{ ... }} expressions with a safe placeholder so npm/bun
    # can parse the file as plain JSON. Only metadata fields use Twig
    # vars; dependency versions are all static.
    sed 's/{{[^}]*}}/PLACEHOLDER/g' "$1"
}

restore_twig_npm() {
    # Replace PLACEHOLDER values in package-lock.json with the correct
    # Twig expressions extracted from the corresponding package.json.twig.
    local lockfile="$1"
    local twig_name="$2"

    # In npm lockfiles, PLACEHOLDER appears in these fields:
    #   root "name", root "version", packages[""] "name", "version", "license"
    # We use distinct markers to replace them with the correct Twig vars.
    sed -i.bak \
        -e "0,/\"name\": \"PLACEHOLDER\"/s//\"name\": \"${twig_name}\"/" \
        -e "0,/\"version\": \"PLACEHOLDER\"/s//\"version\": \"{{ sdk.version }}\"/" \
        -e "0,/\"name\": \"PLACEHOLDER\"/s//\"name\": \"${twig_name}\"/" \
        -e "0,/\"version\": \"PLACEHOLDER\"/s//\"version\": \"{{ sdk.version }}\"/" \
        -e "0,/\"license\": \"PLACEHOLDER\"/s//\"license\": \"{{ sdk.license }}\"/" \
        "$lockfile"
    rm -f "${lockfile}.bak"
}

restore_twig_bun() {
    # Replace PLACEHOLDER in bun.lock with the correct Twig expression.
    local lockfile="$1"
    local twig_name="$2"

    sed -i.bak \
        -e "s/\"name\": \"PLACEHOLDER\"/\"name\": \"${twig_name}\"/" \
        "$lockfile"
    rm -f "${lockfile}.bak"
}

update_npm() {
    local lang="$1"
    local extra_flags="${2:-}"
    local twig_name="$3"
    local template="$ROOT/templates/$lang/package.json.twig"
    local dest="$ROOT/templates/$lang/package-lock.json.twig"
    local dir="$WORKDIR/$lang"

    echo "→ $lang (npm)"
    mkdir -p "$dir"
    strip_twig "$template" > "$dir/package.json"
    # shellcheck disable=SC2086
    (cd "$dir" && npm install --package-lock-only --ignore-scripts --silent $extra_flags 2>/dev/null)
    cp "$dir/package-lock.json" "$dest"
    restore_twig_npm "$dest" "$twig_name"
    echo "  updated templates/$lang/package-lock.json.twig"
}

update_bun() {
    local twig_name="$1"
    local dir="$WORKDIR/cli"
    local template="$ROOT/templates/cli/package.json.twig"
    local dest="$ROOT/templates/cli/bun.lock.twig"

    echo "→ cli (bun)"
    mkdir -p "$dir"
    strip_twig "$template" > "$dir/package.json"
    cd "$dir" && bun install --silent 2>/dev/null
    cp "$dir/bun.lock" "$dest"
    restore_twig_bun "$dest" "$twig_name"
    echo "  updated templates/cli/bun.lock.twig"
}

case "$TARGET" in
    web)           update_npm web "" "{{ language.params.npmPackage }}" ;;
    node)          update_npm node "" "{{ language.params.npmPackage | caseDash }}" ;;
    react-native)  update_npm react-native --legacy-peer-deps "{{ language.params.npmPackage }}" ;;
    cli)           update_bun "{{ language.params.npmPackage|caseDash }}" ;;
    all)
        update_npm web "" "{{ language.params.npmPackage }}"
        update_npm node "" "{{ language.params.npmPackage | caseDash }}"
        update_npm react-native --legacy-peer-deps "{{ language.params.npmPackage }}"
        update_bun "{{ language.params.npmPackage|caseDash }}"
        ;;
    *)
        echo "Unknown target: $TARGET. Use web | node | react-native | cli | all"
        exit 1
        ;;
esac

echo ""
echo "Done. Review the diffs and commit the updated lock files."
