#!/usr/bin/env bash
#
# Fails when the Go CLI's startup time regresses.
#
# PLAN.md §1.4 sells this rewrite on startup, and startup is the one number a
# careless change can quietly destroy: an `init()` that reaches the network, a
# config walk at registration time, or a keyring probe before a flag is parsed
# costs tens of milliseconds and nothing else in the suite would notice. The
# guard is deliberately loose -- it is here to catch that class of mistake, not
# to police single-millisecond drift, which on a shared runner is noise.
#
# Measures `--help`, because it exercises the whole command tree without needing
# a server, a config, or credentials.
set -euo pipefail

THRESHOLD_MS="${THRESHOLD_MS:-60}"
RUNS="${RUNS:-15}"
BINARY="${1:?usage: go-cli-startup.sh PATH_TO_BINARY}"

if [ ! -x "$BINARY" ]; then
  echo "not an executable: $BINARY" >&2
  exit 1
fi

# The timing loop runs inside one interpreter. Shelling out for each timestamp
# would measure the interpreter's own startup, which is several times the number
# being measured -- that mistake reported 41 ms for an 11 ms binary.
THRESHOLD_MS="$THRESHOLD_MS" RUNS="$RUNS" BINARY="$BINARY" python3 <<'PY'
import os
import subprocess
import sys
import time

binary = os.environ["BINARY"]
runs = int(os.environ["RUNS"])
threshold = int(os.environ["THRESHOLD_MS"])

# Warm the page cache first: the first execution of a 14 MB binary pays for
# reading it off disk, which is not what this measures.
subprocess.run([binary, "--help"], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)

samples = []
for _ in range(runs):
    started = time.perf_counter()
    result = subprocess.run(
        [binary, "--help"], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL
    )
    samples.append((time.perf_counter() - started) * 1000)
    if result.returncode != 0:
        sys.exit(f"`{binary} --help` exited {result.returncode}")

# The median, not the mean: one descheduled run should not fail the build.
median = sorted(samples)[len(samples) // 2]

print("startup over %d runs: %s ms" % (runs, " ".join("%.1f" % s for s in samples)))
print("median: %.1f ms (threshold %d ms)" % (median, threshold))

if median > threshold:
    sys.exit(
        "\nFAIL: startup regressed past %d ms.\n"
        "Look for work moved into init() or into command registration:\n"
        "  a network call, a filesystem walk, or a keyring read." % threshold
    )
PY
