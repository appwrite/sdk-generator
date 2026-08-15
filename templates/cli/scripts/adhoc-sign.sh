#!/bin/sh
# Ad-hoc sign a macOS build artifact.
#
# install.sh runs `codesign -dv` and refuses a darwin download with no embedded
# signature, so an unsigned binary fails for every macOS user at once.
#
# Only darwin/amd64 needs it -- the Go linker ad-hoc signs arm64 itself, which is
# easy to miss because arm64 is what you build on. Signing both keeps the rule
# rather than an architecture exception a toolchain change could invalidate.
#
# Ad-hoc, the same kind the established CLI ships: it satisfies the loader and
# install.sh, and is neither Developer ID signing nor notarisation. Linux runners
# have no codesign, hence the ldid fallback.
set -eu

binary="$1"
target="${2-}"

case "$target" in
    darwin*) ;;
    *) exit 0 ;;
esac

if command -v codesign >/dev/null 2>&1; then
    codesign --sign - --force "$binary"
elif command -v ldid >/dev/null 2>&1; then
    ldid -S "$binary"
else
    echo "adhoc-sign: need codesign or ldid to sign $binary" >&2
    exit 1
fi
