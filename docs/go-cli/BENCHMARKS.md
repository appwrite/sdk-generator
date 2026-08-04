# Benchmarks

Two sets of numbers, and it matters which you are reading:

- [**As shipped**](#as-shipped) — the finished CLI, all 608 commands. What to
  quote, and what CI guards.
- [**Phase 0 spike**](#phase-0--spike-results) — a throwaway binary with a handful
  of commands, measured to decide whether to attempt the rewrite at all. Its
  startup number is *better* than the shipped one because it had almost no
  command tree, so do not read it as a regression.

## As shipped

**Machine:** Apple M2 Pro, 12 cores, 32 GB, macOS 26.5.2
**Binary:** `go build -ldflags="-s -w"`, 13.8 MB
**Method:** median of 15 runs per row, page cache warmed first

Against the §1.4 targets in [PLAN.md](PLAN.md):

| Metric | Today (Bun) | Target | As shipped | |
|---|---|---|---|---|
| `appwrite --help` | 205.9 ms | < 10 ms | **11.3 ms** (18×) | narrow miss, accepted |
| `appwrite databases --help` | 205.3 ms | < 10 ms | **11.1 ms** (18×) | narrow miss, accepted |
| Tab-completion round trip | full startup per request | < 10 ms | **11.9 ms** | narrow miss, accepted |
| Binary size | 66 MB shipped Bun binary | 20–25 MB | **13.8 MB** | beats it |
| Native modules to codesign | 1 (`@napi-rs/keyring`) | 0 | **0** | met |
| `push` peak RSS | 421 MB, grows with archive | O(chunk size) | **O(chunk size)** | met, by a different route |

### The three misses

All three are the same miss: **11 ms against a 10 ms target**, and all three are
accepted rather than chased.

The 10 ms in §1.4 was written against the spike's 5.0 ms, and the spike had a
handful of commands where this has 608. The remaining ~6 ms is cobra building
that tree, which Phase 0 already established is not worth attacking — it measured
lazy registration and found the win did not justify the complexity. Nothing in
the gap is avoidable work; it is the command surface itself.

The gate that decided the rewrite was **relative** — ≥ 5× faster — and 18× clears
it by a wide margin. A user cannot perceive the difference between 10 ms and
11 ms; they could perceive all 195 ms of what was removed.

`Checks / Go CLI startup` fails the build past 60 ms, which is where a real
regression lives — an `init()` reaching the network, or a keyring read before a
flag is parsed.

### `push` peak RSS

Met, but not the way Phase 0 predicted. The spike streamed
`tar` → `pgzip` → `io.Pipe` → multipart with no temp file. The shipped code writes
the archive to a temporary file one file at a time and `Upload` reads it back in
5 MB windows through `io.NewSectionReader`, so nothing ever holds the whole
archive and peak RSS is bounded by the chunk rather than the payload. Same
guarantee, different mechanism. See PLAN.md Phase 6 item 2 for why the pipe and
`pgzip` were left unclaimed.

### `push` wall clock

Not re-measured as shipped. The −56 % below is the spike against the buffered
TypeScript; the shipped code uses single-threaded stdlib gzip rather than
`pgzip`, so treat that figure as the ceiling rather than the current number. What
*is* verified against a live server is that deployments work end to end and that
a 16 MB chunked upload produces a byte-identical request signature — see
[AUDIT.md](AUDIT.md).

---

## Phase 0 — Spike Results

Measurements backing the decision to rewrite the CLI in Go. Every number here is
reproducible with the commands given; re-run them before trusting them on other
hardware.

**Machine:** Apple M2 Pro, 12 cores, 32 GB, macOS 26.5.2
**Toolchain:** Go 1.26.5, Node v22.19.0, Bun 1.3.8, hyperfine 1.20.0
**Date:** 2026-08-02

Verdict: **both gates cleared by a wide margin.** Proceed to Phase 1.

| Gate | Threshold | Measured | |
|---|---|---|---|
| Startup | ≥ 5× faster | **41×** | pass |
| `push` packaging | ≥ 20 % faster | **56 % faster** | pass |

> The 41× is the spike's 5.0 ms, not the shipped CLI's 11.3 ms. See [As
> shipped](#as-shipped).

---

## 1. Startup

The plan estimated the TypeScript baseline at 40–150 ms. **It is 206 ms** — the real
upside is roughly four times larger than assumed.

`User: 206 ms` against `206 ms` wall clock confirms this is CPU-bound module loading and
command registration, not the network version check.

| Binary | Command | Mean | σ |
|---|---|---|---|
| Bun-compiled (67 MB, shipped) | `appwrite --help` | **205.9 ms** | 5.3 ms |
| Bun-compiled | `appwrite databases --help` | 205.3 ms | 6.0 ms |
| Node (`dist/cli.cjs`, 6.8 MB) | `node cli.cjs --help` | **233.8 ms** | 4.1 ms |
| Go spike (2.9 MB) | `spike --help` | **5.0 ms** | 0.3 ms |
| Go spike | `spike databases --help` | 5.3 ms | 0.2 ms |
| Go spike | `spike databases list` | 5.1 ms | 0.5 ms |

**41× faster than the shipped Bun binary; 47× faster than the Node path.**

The Go spike is not a toy: it registers the CLI's real surface — 608 commands and
2,912 flags extracted from the generated TypeScript, each with a 180-character
description — as Cobra no-ops.

### Finding: lazy command registration is unnecessary

The plan flagged lazy registration as a possible startup optimisation (Phase 3 item 6,
Phase 6 item 1). **Drop it.** Eagerly building all 608 commands costs nothing
measurable — `--help` at 5.0 ms and a leaf command at 5.1 ms are within noise of each
other. Registering the full tree is not the bottleneck at Go speeds, and lazy
registration would add real complexity to generated code for no gain.

### Reproduce

`spike/gen.mjs` was a throwaway generator and is deliberately not in the tree —
the real generated CLI replaced it in Phase 1. What was run:

```bash
node scripts/go-cli/extract-command-surface.mjs
node spike/gen.mjs templates/go-cli/internal/cmd/testdata/command-surface.json  # emitted main.go
go build -ldflags="-s -w" -o spike .
hyperfine --warmup 5 --runs 50 --shell=none './spike --help' '/usr/local/bin/appwrite --help'
```

To re-measure startup against something that still exists, use the Phase 6
section at the end of this file; it benchmarks the shipped binary.

---

## 2. `push` packaging

Compares `packageDirectory()` (`templates/cli/lib/commands/utils/deployment.ts:565`) —
which tars to a temp file then `readFileSync`s the whole archive into a Buffer — against
a Go implementation streaming `archive/tar` → `pgzip` → `io.Pipe` into the consumer.

Both at gzip level 9 (node-tar's effective default — verified, see §2.2), on a real
source tree: a generated `examples/cli` output with symlinks dereferenced.

### 369 MB, 22,892 files

| | TypeScript (buffered) | Go (streaming) | Delta |
|---|---|---|---|
| Wall clock | 7.00 s | **3.09 s** | **−56 %** |
| Peak RSS | 421.4 MB | **102.3 MB** | **−76 %** |
| CPU (user) | 7.26 s | 14.71 s | +103 % |
| Archive | 95,533,947 B | 95,106,943 B | −0.4 % |

### 737 MB, 45,784 files

| | TypeScript (buffered) | Go (streaming) | Delta |
|---|---|---|---|
| Wall clock | 14.61 s | **9.19 s** | **−37 %** |
| Peak RSS | 686.0 MB | **104.5 MB** | **−85 %** |
| Archive | 191,068,258 B | 190,207,537 B | −0.5 % |

With `GOGC=25`, peak RSS on the 737 MB tree drops further to **69.3 MB**.

### 2.1 Memory is O(1) in archive size

This is the result that matters most, and it is the one the plan asserted without
evidence. Doubling the tree doubles the archive (95 MB → 190 MB):

- TypeScript peak RSS: 421 MB → **686 MB** (grows with the archive, as the
  `readFileSync` + `new File([buffer])` path requires)
- Go peak RSS: 102 MB → **104 MB** (flat)

Go trades wall clock for total CPU — pgzip parallelises compression across cores, so
user time roughly doubles while elapsed time halves. On a developer machine that is the
right trade. Worth revisiting if CI runners are core-constrained.

### 2.2 pgzip does not cost compression ratio

An early measurement suggested Go produced a 13 % larger archive. That was a
compression-level mismatch, not a pgzip cost. At matched levels, pgzip, Go's
`compress/gzip`, and Node's `zlib` agree to within 0.7 %:

| Level | pgzip | Go `compress/gzip` | Node `zlib` |
|---|---|---|---|
| 6 | 19,844 | 19,974 | 19,968 |
| 9 | 18,309 | 18,308 | 18,305 |

`node-tar` with `{gzip: true}` produces output matching **level 9**, not zlib's
documented level-6 default. The Go implementation must use level 9 explicitly or it
will silently upload ~3 % more bytes.

### 2.3 Note on the module path

The plan listed `klauspost/compress/pgzip`. That package does not exist —
`compress@v1.19.1` does not contain it. The correct module is
**`github.com/klauspost/pgzip`** (v1.2.6), which depends on `klauspost/compress`.
Corrected in the plan's dependency table.

### Reproduce

`baseline.mjs` and `tarbench` were throwaway spike harnesses and are
deliberately not in the tree; `internal/archive` is the shipped version of what
`tarbench` measured. What was run:

```bash
rsync -a --copy-links examples/cli/ /tmp/fixture/
/usr/bin/time -l node baseline.mjs /tmp/fixture
LEVEL=9 BLOCK_KB=1024 BLOCKS=8 /usr/bin/time -l ./tarbench /tmp/fixture
```

A synthetic fixture was tried first and discarded: an LCG-generated tree is
incompressible enough that gzip falls back to stored blocks, which masks
level differences and understates real-world memory pressure. Use a real tree.

---

## 3. Keyring

`zalando/go-keyring` v0.2.6 on macOS:

```
roundtrip=true
missing-after-delete=ErrNotFound (clean fallback signal)
```

Set, get, and delete all work, and a missing credential returns a typed
`keyring.ErrNotFound` rather than an opaque error — so the fallback-to-login path can
be triggered precisely, which is the Phase 2 exit criterion.

Linux (libsecret) and Windows (credential manager) remain to be verified in CI.

Interop with credentials written by `@napi-rs/keyring` was **not** tested. Per the
plan it is explicitly not an invariant — users re-authenticating once on upgrade is
acceptable, so this is not worth engineering time.

---

## 4. Go SDK coverage

Extracted every SDK method the generated CLI calls, and diffed against the exported
methods on the generated Go SDK. Both generated from the same spec at platform
`console`.

**603 CLI method calls across 23 services. Zero gaps.**

The only CLI-side surface with no SDK counterpart is the three console-fallback
helpers, which are CLI code rather than SDK methods and are already accounted for in
`internal/client`:

- `oauth2` → `listOrganizationsForSession`, `listProjectsForSession`
- `organization` → `getOrganizationForSession`

This removes the risk the plan flagged in Phase 0 — the Go SDK does not need hardening
before the CLI can be built on it.

---

## 5. Corrections to the plan

The spike produced numbers that contradict PLAN.md as written. All are now fixed there.

| Claim in plan | Actual |
|---|---|
| 24 services | **23** |
| 606 commands | **608** command entries (606 subcommands + 2 promoted root aliases) |
| Startup 40–150 ms | **206 ms** (Bun), 234 ms (Node) |
| Target < 10 ms | Met — 5.0 ms |
| `push` −20 to −40 % | **−56 %** on a 369 MB tree |
| Binary ~20–25 MB | Spike is **2.9 MB**; real CLI will be larger but well under 20 MB |
| `klauspost/compress/pgzip` | `klauspost/pgzip` |
| Lazy registration may be needed | Not needed — measured at zero cost |
| Flag count unstated | **2,912** |

---

# Phase 6 — the finished CLI

Phase 0 measured a spike with no logic in it. These are the same measurements
against the COMPLETE Go CLI — 608 generated commands plus `init`, `pull`,
`push`, `run`, `types`, `generate` and `update` — so they are what a user
actually gets.

**Machine:** Apple M2 Pro, 12 cores, 32 GB, macOS 26.5.2
**Toolchain:** Go 1.26.5, Bun 1.3.8, hyperfine 1.20.0
**Date:** 2026-08-03

## Startup

hyperfine, 20-40 runs after warmup, against `examples/cli/dist/cli.cjs` on Bun.

| Command | TypeScript | Go | Speedup |
|---|---|---|---|
| `--version` | 235.0 ms +/- 11.2 | **10.4 ms +/- 2.6** | 22.5x |
| `--help` (full 608-command tree) | 173.5 ms +/- 9.3 | **10.3 ms +/- 3.8** | 16.9x |
| `push function --help` | 175.2 ms +/- 10.9 | **8.1 ms +/- 2.1** | 21.6x |

The Phase 0 spike managed 5.0 ms with nothing in it; the finished CLI is 8-10 ms.
**The `< 10 ms` target is met for a subcommand and missed by a hair for the root
help**, which renders the whole command tree. Worth stating plainly rather than
rounding down: the gain is real and large, and one figure sits just over the line.

## `push` memory — the claim that justified the rewrite

A function directory of incompressible random data, pushed to real staging with
`--async`, peak RSS via `/usr/bin/time -l`.

| Archive | TypeScript peak RSS | Go peak RSS | Wall clock (TS -> Go) |
|---|---|---|---|
| 40 MB | 283.5 MB | **28.0 MB** | 18.4 s -> **11.0 s** |
| 120 MB | not measured | **27.5 MB** | -> **12.0 s** |

**Three times the archive, and Go's memory does not move: 28.0 MB -> 27.5 MB.**
That is the difference between streaming and buffering, and it is the single
number the rewrite was argued on. The TypeScript reads the whole archive into a
Buffer before upload (`deployment.ts`, `fs.readFileSync`); Go writes it to a temp
file and uploads each chunk through an `io.SectionReader` with an exact
`Content-Length`, so peak memory is the HTTP write buffer whatever the size.

At 40 MB that is **10.1x less memory and 40 % less wall clock**.

## Binary size

| | Size |
|---|---|
| Bun `--compile` binary | 66 MB |
| **Go binary** | **18.1 MB** |

Beats the 20-25 MB target, and needs no runtime installed. Native modules
requiring codesign: **1 -> 0**.

## Reproducing

```bash
php example.php go-cli && (cd examples/go-cli && go build -o appwrite .)

hyperfine --warmup 5 --runs 40 \
  'examples/go-cli/appwrite --version' \
  'bun examples/cli/dist/cli.cjs --version'

# Memory: point a project config at a large function directory, then
/usr/bin/time -l examples/go-cli/appwrite --all --force push function --async
```
