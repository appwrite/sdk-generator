# Differential conformance harness

The full-surface differential harness: it compares a built Go CLI against a built
TypeScript CLI without needing a live Appwrite server.

Its findings are written up in `../AUDIT.md`, which lands with the finished CLI
rather than here -- there is no point auditing a port that is four phases from
complete.

**Scope, and when to delete this directory.** These scripts sweep all 606
generated service commands against the real spec, which is what caught the 24
genuine divergences recorded in `../AUDIT.md`. They are deliberately not in
CI: they need two built CLIs and a recording server, and the sweep takes minutes.

CI covers the same ground at a smaller scale — `CLIDifferential` diffs `--json`
between both binaries over a scripted command list against the mock API, and
`GoCLI126` runs the Go CLI through the same shared harness as the TypeScript one.
That is the standing guard. This is the tool you re-run by hand before flipping
the default, when "the scripted list agrees" is not enough and you want the whole
command surface checked.

It therefore retires with the TypeScript CLI: Phase 8 step 5 removes
`templates/cli/`, and a TypeScript-vs-Go differential has nothing to compare
after that. Delete this directory in the same change.

## Setup

```sh
php example.php go-cli && (cd examples/go-cli && go mod tidy && go build -o /tmp/conform/appwrite-go .)
php example.php cli    && (cd examples/cli && bun install && bun run build)
printf '#!/bin/sh\nexec node %s/examples/cli/dist/cli.cjs "$@"\n' "$PWD" > /tmp/conform/appwrite-ts
chmod +x /tmp/conform/appwrite-ts
```

Both CLIs need a configured endpoint pointing at the recorder, in separate
`HOME`s so neither can see the other's session. The Go side additionally needs
`APPWRITE_PROJECT_ID` in the environment, because `client --project-id` does not
work there — that is finding 4, and working around it is the only reason the
environment variable appears.

## The scripts

| Script | What it does |
|---|---|
| `recorder.py PORT` | Logs every request to `$RECORD_TO` as JSONL and answers with a stub body. |
| `tree.py go BIN` / `tree.py ts COMPLETION.bash` | Extracts a command tree. Help text cannot be parsed for this -- see "Two traps" below. |
| `treediff.py GO.json TS.json` | Diffs two trees: missing commands, extra commands, flag gaps. |
| `drive.py [prefix]` | Runs every service command on both CLIs and diffs the requests. The main harness. |
| `drivejson.py [prefix]` | Same command set, diffs `--json` stdout instead. Written, not yet run. |
| `requireddiff.py` | Compares cobra's required flags against the TypeScript's `.requiredOption` calls. |

## Two traps

Both cost a rerun during the audit, and both look like findings until you check:

- **`__complete` hides optional flags.** When a command has required flags still
  unset, cobra returns only those. Read flags off `--help`; use `__complete`
  only for subcommands and for the required set.
- **`__complete` with `-` lists *every* flag when nothing is required.** Treat a
  result containing `help` as "no required flags", not as a requirement set.
  Without this, `drive.py` invokes commands with `--help stub` and the
  TypeScript's failures look like 167 divergences.

Also: the stub answers with fixed field types, and the Go SDK unmarshals into
typed models while the TypeScript does not type-check responses at all. 54
commands fail on the Go side for that reason alone. Those are artifacts of the
stub, not findings.
