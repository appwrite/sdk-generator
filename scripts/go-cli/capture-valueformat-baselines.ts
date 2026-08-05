// Capture the TypeScript CLI's scalar formatting as pinned baselines.
//
// Companion to capture-typegen-baselines.ts, same discipline: the Go port is
// checked against output this script produced from the real implementation,
// never against expectations someone wrote down.
//
// Copied into the generated CLI to run, the same way the other capture
// scripts are -- the `./lib/...` import has to resolve against the CLI's own
// tree, not against scripts/:
//
//     php example.php cli
//     cp scripts/go-cli/capture-valueformat-baselines.ts examples/cli/
//     cd examples/cli && FORCE_COLOR=0 bun run capture-valueformat-baselines.ts \
//         ../../templates/go-cli/internal/output/testdata/valueformat.json
//     rm examples/cli/capture-valueformat-baselines.ts
//
// FORCE_COLOR=0 is mandatory. chalk emits ANSI when it thinks it has a
// terminal, and a baseline captured with escape codes in it would pin the
// colour decisions of whatever shell happened to run this rather than the
// text. The CLI itself makes the same call at runtime.
//
// RELATIVE TIME IS FROZEN. formatTimestamp renders "2h ago" against
// Date.now(), so an un-pinned capture produces a file that fails the moment it
// is replayed. Date.now is stubbed to a fixed instant here, and the Go tests
// inject the same one.

import fs from "node:fs";
import path from "node:path";

import { humanizeSeconds, formatTimestamp } from "./lib/response-config.js";

// 2026-08-03T12:00:00Z. Chosen to sit a round distance from the fixtures below
// so a tier boundary error is visible rather than absorbed.
const FROZEN_NOW = Date.parse("2026-08-03T12:00:00.000Z");

// Stubbed after the import rather than before: relativeTime() reads Date.now()
// when it is called, not when the module loads, so this is early enough.
Date.now = () => FROZEN_NOW;

const DURATIONS = [
  0, -1, 0.4, 1, 59, 60, 61, 90, 3599, 3600, 3661, 86399, 86400, 90061,
  1234567, Number.NaN, Number.POSITIVE_INFINITY,
];

// Spread across every relative tier, both directions, plus the shapes that
// must fall through: no offset, not a date, empty.
const TIMESTAMPS = [
  "2026-08-03T11:59:30.000Z",
  "2026-08-03T11:58:00.000Z",
  "2026-08-03T10:00:00.000Z",
  "2026-08-02T12:00:00.000Z",
  "2026-07-04T12:00:00.000Z",
  "2025-08-03T12:00:00.000Z",
  "2026-08-03T13:00:00.000Z",
  "2026-08-04T12:00:00.000Z",
  "2026-07-31T02:49:41.895+00:00",
  "2026-07-31T02:49:41.895+05:30",
  "2026-07-31T02:49:41-08:00",
  "2026-07-31 02:49:41Z",
  "2026-07-31T02:49:41",
  "not a timestamp",
  "",
  "2026-13-45T99:99:99Z",
];

const baseline = {
  frozenNow: new Date(FROZEN_NOW).toISOString(),
  humanizeSeconds: Object.fromEntries(
    DURATIONS.map((seconds) => [String(seconds), humanizeSeconds(seconds)]),
  ),
  formatTimestamp: Object.fromEntries(
    TIMESTAMPS.map((value) => [value, formatTimestamp(value)]),
  ),
};

const outFile = process.argv[2];
if (!outFile) {
  console.error("usage: capture-valueformat-baselines.ts <outFile>");
  process.exit(1);
}

fs.mkdirSync(path.dirname(outFile), { recursive: true });
fs.writeFileSync(outFile, JSON.stringify(baseline, null, 2) + "\n");
console.log(`wrote ${outFile}`);
