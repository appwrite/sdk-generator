# Go CLI Rewrite — Agent Operating Manual

You are working on rewriting the Appwrite CLI from TypeScript/Bun to Go.

**Read this file completely before your first edit. Read [PLAN.md](PLAN.md) before
starting any phase.** This file tells you *how* to work; PLAN.md tells you *what* to
build.

This document assumes you have also read the repository's `AGENTS.md`. Everything there
still applies — especially the five critical rules about template generation.

---

## 1. The one thing to understand first

**You are not editing a CLI. You are editing a code generator that emits a CLI.**

```
templates/go-cli/**.twig  ──┐
                            ├── php example.php go-cli ──▶ examples/go-cli/
src/SDK/Language/GoCLI.php ─┘                              (gitignored)
```

`examples/go-cli/` is **build output**. It is in `.gitignore`. Editing it is always
wrong — your change vanishes on the next generation and `git status` will not warn you.

If you find yourself typing a path that starts `examples/`, stop and find the template.

---

## 2. Ground rules

### 2.1 Never edit generated output

Covered above. It is the single most common way this work goes wrong, so it gets its
own rule.

### 2.2 Every new template file needs a `getFiles()` entry

The generator does not discover templates. A `.twig` file with no entry in
`GoCLI::getFiles()` is **silently never generated** — generation succeeds, the file just
does not exist. There is no warning.

```php
[
    'scope'       => 'default',   // default|service|method|definition|copy|...
    'destination' => 'internal/cmd/services/{{ service.name | caseLower }}.go',
    'template'    => 'go-cli/internal/cmd/services/service.go.twig',
],
```

`destination` is itself a Twig template — filters work there.

`'scope' => 'copy'` means **no Twig processing at all**. If your file needs a variable,
it cannot be `copy`. Most of the hand-written Go runtime should be `copy`; only files
that vary with the spec need `default` or `service`.

### 2.3 Always regenerate, always verify

After any template or language-class change:

```bash
php example.php go-cli
cd examples/go-cli && go build ./... && go vet ./...
```

`git diff` will show you nothing. You must look at the generated files directly.

To see what your change actually did to the output:

```bash
cp -r examples/go-cli /tmp/before
git stash -u          # -u matters: a new template file is untracked, and a
php example.php go-cli #    plain `git stash` would leave it in the baseline
cp -r examples/go-cli /tmp/after
git stash pop
diff -r /tmp/before /tmp/after
```

Do this whenever you touch a shared template. It is the only reliable review.

### 2.4 Parity is the requirement, not a nice-to-have

The invariants in [PLAN.md §3](PLAN.md#3-invariants) are hard constraints. Flag names,
config schema, exit codes, `--json` output, redaction behaviour, install paths.

You will repeatedly find TypeScript code that is awkward, redundant, or that you could
write better in Go. **Port it as-is.** Improvement PRs come after Phase 4 proves parity,
and they come separately, so a regression can be bisected to one change.

Two exceptions, both explicit in §3: human-readable table output, and keyring credential
interop with the TypeScript binary. Users re-authenticating once on upgrade is fine.

### 2.5 Do not start a phase whose entry criteria are unmet

Each phase in PLAN.md lists entry criteria. They exist because the phases build on each
other — Phase 5 without the Phase 4 harness means porting 4,380 lines of `push.ts` with
no way to know if you broke it.

If entry criteria are unmet, say so and stop. Do not improvise around them.

### 2.6 Reference the TypeScript by file and line

When porting, cite the source: `// ports lib/commands/push.ts:2864 — poll debounce`.
Reviewers need to diff against the original, and so will you in three weeks.

---

## 3. Setup

```bash
# PHP dependencies (generator side)
composer update --ignore-platform-reqs --optimize-autoloader \
  --no-plugins --no-scripts --prefer-dist

# Confirm the existing CLI generates before you change anything
php example.php cli
```

Requirements: PHP 8.3+, Go 1.24+, Docker (e2e tests), `hyperfine` (benchmarks).

If local PHP is missing or the wrong version, run the command in the matching PHP
Docker image.

`example.php` fetches the live spec from GitHub and needs network access. To work
offline or against a fixed spec:

```bash
SDK_GEN_SPEC_FILE=tests/resources/spec-openapi3.json php example.php go-cli
```

---

## 4. The loop

Every change follows the same cycle. Do not skip steps.

```bash
# 1. Edit templates/go-cli/** or src/SDK/Language/GoCLI.php

# 2. Regenerate
php example.php go-cli

# 3. Compile and vet
cd examples/go-cli && go build ./... && go vet ./... && cd -

# 4. Unit tests (Go runtime)
cd examples/go-cli && go test ./internal/... && cd -

# 5. Generator lints
composer refactor:check
composer lint-twig

# 6. Conformance (from Phase 4 onward)
vendor/bin/phpunit tests/e2e/GoCLI124Test.php
```

Steps 2–5 run on every change. Step 6 runs before every PR.

If step 6 leaves containers behind after an interrupt:

```bash
cd mock-server && docker compose down
```

---

## 5. Where things are

### Generator side

| What | Where |
|---|---|
| New language class | `src/SDK/Language/GoCLI.php` |
| New templates | `templates/go-cli/` |
| Generated output (gitignored) | `examples/go-cli/` |
| Generation entry point | `example.php` — the `go-cli` block |
| Available Twig filters | `src/SDK/SDK.php` (~line 62) |
| Generation orchestration | `src/SDK/SDK.php` → `generate()` |

### The TypeScript you are porting from

| What | Where |
|---|---|
| Current language class | `src/SDK/Language/CLI.php` (1,169 lines) |
| Current templates | `templates/cli/` |
| Current generated output | `examples/cli/` — 23 services, 608 commands, 2,912 flags |
| Service command template | `templates/cli/lib/commands/services/services.ts.twig` |
| Entry point template | `templates/cli/cli.ts.twig` |

### Conformance

| What | Where |
|---|---|
| Shared expectations | `tests/e2e/Base.php` — the `CLI_*` constants |
| Existing CLI e2e | `tests/e2e/CLIBun13Test.php` |
| Test harness | `tests/e2e/languages/cli/test.js` (2,754 lines) |
| Mock API | `mock-server/` |
| Spec fixture | `tests/resources/spec-openapi3.json` |
| Command surface contract | `docs/go-cli/command-surface.json` (built in Phase 1) |

---

## 6. Codegen contract

Nine Twig helpers in `CLI.php` decide what the generated commands look like. Every one
needs a Go equivalent in `GoCLI.php`. Each is a place where behaviour drifts silently.

| Helper | `CLI.php` | Decides |
|---|---|---|
| `cliQueryConfig` | `:420` | Which query flags a method gets, and `--queries` help text |
| `cliServiceScope` | `:276` | Header-scoped services and their ID flag |
| `cliTopLevelAliases` | `:307` | Root-level promoted commands |
| `cliCommandTargets` | `:348` | Subcommand + hidden/standalone variants |
| `cliFallbackHelpers` | `:383` | Console-fallback helper imports |
| `hasCliQueryParam` | — | Whether a service needs query imports |
| `getCliOption` | — | Flag syntax, description, parser |
| `getCliVarName` | — | Action-handler variable name |
| `getCliArgExpression` | — | Argument passed to the SDK call |

Also carried over: `consoleIgnoreFunctions` / `consoleIgnoreServices` (`CLI.php:20–70`),
`TOP_LEVEL_COMMANDS` (`:244`), `CONSOLE_FALLBACK_METHODS` (`:256`), `SCOPE_FACTORIES`
(`:229`), and the exclusion list in `example.php:267–308`.

Port these **as behaviour, not as text**. `cliQueryConfig` in particular branches on
method name, parameter-name collisions, and a description substring
(`hasOnlyLimitOffsetQueries` reads the parameter's prose). Read it carefully. A
transcription that looks right and gets one branch wrong will pass compilation and fail
in the field.

### Twig scopes

| Scope | Extra variables |
|---|---|
| all | `spec`, `language`, `sdk` |
| `service` | `+ service` |
| `method` | `+ service`, `method` |
| `definition` | `+ definition` |
| `enum` | `+ enum` |
| `copy` | none — **no Twig processing** |

---

## 7. Go conventions for this project

The repository's global style rules apply. Go-specific points:

- **Full types everywhere.** No `interface{}` / `any` in config or response structs.
  Typed structs, matching the JSON exactly. `json:"..."` tags on every field.
- **No abbreviations.** `certificate` not `cert`, `connection` not `conn`,
  `request` not `req`. Well-known acronyms (HTTP, TLS, TCP, ID, URL, JSON) are fine and
  follow Go convention: `userID`, `parseURL`, `HTTPClient`.
- **Single-word names when context makes it obvious.** `connections`, not
  `backendConnections`, when there is only one set.
- **No section-header comments.** No `// ---`, no `// ===`. Remove them where you find
  them.
- **Constants, not magic strings.** Typed string constants or an enum-like type.
- **One package per concern**, under `internal/`. Nothing exported that does not need to
  be.
- **Errors wrap with context**: `fmt.Errorf("push deployment %s: %w", id, err)`. Never
  discard an error to satisfy the compiler.
- **`errgroup` for concurrency.** Always `SetLimit`. An unbounded fan-out over user
  resources is a bug.
- **No global mutable state.** Pass a context struct. The TypeScript uses module-level
  singletons (`cliConfig` in `parser.ts:37`, the per-service client caches) — that
  pattern does not survive the port.
- **`gofmt` and `go vet` clean.** Non-negotiable, both in CI.

---

## 8. Commits and PRs

Conventional commits, scoped:

```
feat(go-cli): generate query flags from cliQueryConfig
fix(go-cli): match TS redaction for nested apiKey fields
test(go-cli): differential --json harness against the TS binary
```

Every PR:

- Names its phase and work item from PLAN.md.
- States what it ports, with `file:line` references to the TypeScript.
- Shows evidence: `go test` output, the relevant e2e run, or benchmark numbers.
- Confirms `composer refactor:check` and `composer lint-twig` pass.
- Calls out any invariant it touches — flags, config, exit codes, output, redaction.

PRs target `main`. Version branches are for backports only.

**Never merge without green CI.** No `--admin`, no `--auto`. This applies here with no
exceptions: the whole safety argument for this rewrite is the conformance suite.

Keep PRs to one work item. A PR that ports `pull` and refactors `output` cannot be
bisected when something regresses.

---

## 9. Failure modes

Ranked by how often they will bite you.

**Your change did nothing.** No `getFiles()` entry. Check `GoCLI::getFiles()`.

**You removed a `getFiles()` entry but the file is still there.** Generation writes
files; it never deletes them. `examples/` keeps the old output, and the build fails with
a redeclaration error that points at code you thought was gone. Delete the stale file
from `examples/go-cli/` by hand, or regenerate into a clean directory.

**Your Twig syntax appears literally in the output.** The file is `'scope' => 'copy'`.
Change the scope or remove the variable.

**Generation fails with an empty-spec exception.** No network. Use
`SDK_GEN_SPEC_FILE=tests/resources/spec-openapi3.json`.

**A service is missing entirely.** It is in the exclusion list in `example.php`, or in
`consoleIgnoreServices` in the language class. Both lists must match the TypeScript
CLI's exactly.

**A flag name differs by one character.** `getCliOptionName` (`CLI.php:412`) does
non-obvious kebab-casing with a lookahead regex, and prefixes reserved keywords with
`x`. Do not reimplement from intuition — port the regex.

**e2e passes locally, fails in CI.** Stale containers.
`cd mock-server && docker compose down`, then re-run.

**Keyring works on your machine, not on Linux CI.** libsecret needs a session bus.
This is an environment problem, not a code problem. Note that reading credentials
written by the TypeScript binary is *not* required — if that is what is failing, leave
it and make sure the fallback login prompt is clean.

**A benchmark improved suspiciously much.** You are probably measuring a binary that
does not do the work yet. Verify against the conformance suite before believing a
number.

---

## 10. Escalate rather than guess

Most decisions are yours. These are not — stop and ask:

- Any change to an invariant in [PLAN.md §3](PLAN.md#3-invariants).
- Adding a dependency not in [PLAN.md §2.3](PLAN.md#23-dependencies).
- Starting Phase 8 step 5 (deleting the TypeScript CLI).
- Phase 0 benchmarks missing their thresholds — that is a project-level decision, not a
  "try harder" situation.
- A conformance expectation in `Base.php` that seems wrong. It is far more likely your
  port is wrong. If you are convinced otherwise, make the case; do not edit the
  expectation to make your build pass.

Everything else: make the call, document it in the PR, keep going.
