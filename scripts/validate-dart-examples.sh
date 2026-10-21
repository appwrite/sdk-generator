#!/usr/bin/env bash

set -euo pipefail

sdk_dir="${1:-.}"
sdk_dir="$(cd "$sdk_dir" && pwd)"

tmp="$(mktemp -d "$sdk_dir/docs-examples.XXXXXX")"
trap 'rm -rf "$tmp"' EXIT

count=0
while IFS= read -r example; do
    name="${example#"$sdk_dir/docs/examples/"}"
    name="${name%.md}"
    file="$tmp/${name//[^a-zA-Z0-9]/_}.dart"

    awk '
        /^```dart$/ { in_block = 1; next }
        /^```$/ && in_block { exit }
        in_block && /^import / { imports = imports $0 "\n"; next }
        in_block { body = body "  " $0 "\n" }
        END {
            if (body == "") exit
            printf "// ignore_for_file: deprecated_member_use_from_same_package, unused_local_variable\n%s\nFuture<void> main() async {\n%s}\n", imports, body
        }
    ' "$example" > "$file"

    if [[ ! -s "$file" ]]; then
        rm -f "$file"
        continue
    fi

    count=$((count + 1))
done < <(find "$sdk_dir/docs/examples" -type f -name '*.md' | sort)

echo "Analyzing $count generated Dart examples"
(cd "$sdk_dir" && dart analyze --fatal-warnings "$tmp")
