# Benchmarks

Two sets of numbers, and it matters which you are reading:

- [**As shipped**](#as-shipped) — the finished CLI, all 608 commands. What to
  quote, and what CI guards.
- [**Phase 0 spike**](#phase-0--spike-results) — a throwaway binary with a handful
  of commands, measured to decide whether to attempt the rewrite at all. Its
  startup number is *better* than the shipped one because it had almost no
  command tree, so do not read it as a regression.

## As shipped

**Binary:** `go build -ldflags="-s -w"`, 13.8 MB
**Method:** median of 15 runs per row, page cache warmed first
**Machines:** Apple M2 Pro, 12 cores, 32 GB, macOS 26.5.2 · and `ubuntu-latest`,
via the `Checks / Go CLI startup` job

Startup is **platform-dependent by more than the target's margin**, so both are
given. Quoting only one would be picking a number.

| Metric | Today (Bun) | Target | Linux (CI) | macOS (M2 Pro) |
|---|---|---|---|---|
| `appwrite --help` | 205.9 ms | < 10 ms | **5.6 ms** — met (37×) | 11.3 ms — miss (18×) |
| `appwrite databases --help` | 205.3 ms | < 10 ms | — | 11.1 ms |
| Tab-completion round trip | full startup per request | < 10 ms | — | 11.9 ms |

| Metric | Today | Target | As shipped | |
|---|---|---|---|---|
| Binary size | 66 MB shipped Bun binary | 20–25 MB | **13.8 MB** | beats it |
| Native modules to codesign | 1 (`@napi-rs/keyring`) | 0 | **0** | met |
| `push` peak RSS | 421 MB, grows with archive | O(chunk size) | **O(chunk size)** | met, by a different route |

### Why macOS is twice Linux, and why it is accepted

Most of the difference is the platform, not the CLI. Measured on the same Mac:

| | median |
|---|---|
| `/usr/bin/true` | 2.65 ms |
| An empty Go binary, `func main() {}`, 1.1 MB | 4.35 ms |
| `appwrite --help`, 13.8 MB, 608 commands | 11.11 ms |

So ~4.4 ms is what it costs this machine to start *any* Go binary — dyld and
signature validation on every exec — before a line of our code runs. The CLI's
own share is ~6.7 ms, and cobra building the 608-command tree is most of it.
Phase 0 already measured lazy registration against exactly this and found the win
did not justify the complexity; that conclusion still holds.

Linux pays a much smaller exec cost and comes in at 5.6 ms, inside the target.

Accepted on macOS rather than chased, for three reasons: over half the gap is
below our code, the remainder is the command surface itself rather than avoidable
work, and the gate that decided the rewrite was the **relative** one — ≥ 5× —
which 18× clears even on the slower platform. Nobody perceives 10 ms against
11 ms; everybody perceived the 195 ms that went away.

`Checks / Go CLI startup` fails the build past 60 ms, which is where a real
regression lives — an `init()` reaching the network, or a keyring read before a
flag is parsed. It runs on Linux, so it reads against the 5.6 ms figure.

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
