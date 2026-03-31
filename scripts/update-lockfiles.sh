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

update_npm() {
    local lang="$1"
    local extra_flags="${2:-}"
    local template="$ROOT/templates/$lang/package.json.twig"
    local dest="$ROOT/templates/$lang/package-lock.json"
    local dir="$WORKDIR/$lang"

    echo "→ $lang (npm)"
    mkdir -p "$dir"
    strip_twig "$template" > "$dir/package.json"
    # shellcheck disable=SC2086
    (cd "$dir" && npm install --package-lock-only --ignore-scripts --silent $extra_flags 2>/dev/null)
    cp "$dir/package-lock.json" "$dest"
    echo "  updated templates/$lang/package-lock.json"
}

update_bun() {
    local dir="$WORKDIR/cli"
    local template="$ROOT/templates/cli/package.json.twig"
    local dest="$ROOT/templates/cli/bun.lock"

    echo "→ cli (bun)"
    mkdir -p "$dir"
    strip_twig "$template" > "$dir/package.json"
    cd "$dir" && bun install --silent 2>/dev/null
    cp "$dir/bun.lock" "$dest"
    echo "  updated templates/cli/bun.lock"
}

case "$TARGET" in
    web)           update_npm web ;;
    node)          update_npm node ;;
    react-native)  update_npm react-native --legacy-peer-deps ;;
    cli)           update_bun ;;
    all)
        update_npm web
        update_npm node
        update_npm react-native --legacy-peer-deps
        update_bun
        ;;
    *)
        echo "Unknown target: $TARGET. Use web | node | react-native | cli | all"
        exit 1
        ;;
esac

echo ""
echo "Done. Review the diffs and commit the updated lock files."
