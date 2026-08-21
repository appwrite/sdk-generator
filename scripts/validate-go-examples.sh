#!/usr/bin/env bash

set -euo pipefail

sdk_dir="${1:-.}"
sdk_dir="$(cd "$sdk_dir" && pwd)"
module="$(cd "$sdk_dir" && go list -m)"
go_version="$(awk '$1 == "go" { print $2; exit }' "$sdk_dir/go.mod")"
version="v0.0.0"

if [[ "$module" =~ /v([2-9][0-9]*)$ ]]; then
    version="v${BASH_REMATCH[1]}.0.0"
fi

tmp="$(mktemp -d)"
trap 'rm -rf "$tmp"' EXIT

cat > "$tmp/go.mod" <<EOF
module generated-examples

go $go_version

require $module $version
replace $module => $sdk_dir
EOF

count=0
while IFS= read -r example; do
    package="$tmp/example-$count"
    mkdir "$package"

    awk '
        /^```go$/ { in_block = 1; next }
        /^```$/ && in_block { exit }
        in_block { print }
    ' "$example" > "$package/main.go"

    if [[ ! -s "$package/main.go" ]]; then
        rm -rf "$package"
        continue
    fi

    count=$((count + 1))
done < <(find "$sdk_dir/docs/examples" -type f -name '*.md' | sort)

echo "Compiling $count generated Go examples"
(cd "$tmp" && go build ./...)
