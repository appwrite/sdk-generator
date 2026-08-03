#!/bin/sh
# Ad-hoc sign a macOS build artifact.
#
# install.sh refuses a darwin download that has no embedded code signature --
# it runs `codesign -dv` and aborts with "macOS will kill it on launch". So a
# darwin binary without one does not fail at review time, it fails for every
# macOS user at once.
#
# Only darwin/amd64 actually needs this. The Go linker ad-hoc signs darwin/arm64
# by itself (signing is mandatory on Apple Silicon) but leaves amd64 unsigned,
# which is easy to miss because arm64 is what you build on. Signing both keeps
# the rule "every darwin artifact goes through here" rather than encoding an
# architecture exception that a future toolchain change could invalidate.
#
# This is an AD-HOC signature, the same kind the TypeScript CLI ships today
# (`codesign -dv` on the released binary reports `adhoc,linker-signed`). It
# satisfies the loader and install.sh; it is not Developer ID signing and it is
# not notarisation, neither of which the CLI has ever had.
#
# macOS has codesign. Linux runners do not, which is why the TypeScript
# release workflow builds ldid -- it produces the same ad-hoc signature.
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
