# Go CLI — conformance audit, 2026-08-03

What was tested, what diverges from the TypeScript CLI, and what is still
untested.

## Status — closed

All 23 findings are resolved: **19 fixed, 3 accepted in writing, and 1 that turned
out to be a defect in the TypeScript CLI rather than in this one.** Nothing here is
outstanding, and nothing here blocks the rollout.

| | Findings | |
|---|---|---|
| **Fixed** | 1, 2, 3, 3b, 4, 5, 6, 7, 8, 9, 11, 12, 13, 14, 15, 16, 17, 18, 20 | Each carries a regression test that was run against the reverted fix. `logout` had the same server-side hole as `client --reset` and was fixed alongside it. Finding 9 is fixed except for `--id`, which is declined rather than missed — see [What is left](#what-is-left). |
| **Accepted** | 10, 19, 22 | Deliberate, and none of it observable to a server. See [What is left](#what-is-left). |
| **Not a Go defect** | 21 | Go is correct; the released TypeScript CLI returns 401 on the same command. |

Findings 17 and 19 are resolved by diverging from the TypeScript on purpose
rather than by matching it. Each says so at the finding.

The sweep that produced this audit ended at **532 of 606 commands byte-identical,
up from 237** when it opened. The 74 that differ are [itemised
below](#remaining-sweep-differences-itemised): 50 are an artifact of the stub
server, and the rest are the accepted findings.

Every finding was checked against a live Appwrite instance, not only against the
recording stub. Doing so changed four verdicts, each marked inline: finding 2 was
worse than first described, findings 1 and 3 were milder in one respect and worse
in another, and one item filed as "needs live verification" turned out to be a
hard failure.

Compared: the Go CLI built from `templates/go-cli` at `dc545278a` against the
TypeScript CLI built from `templates/cli` at the same commit.

The released `appwrite-cli@25.1.0` was walked too, but it is generated from a
newer spec than `example.php` fetches — it carries a whole `apps` service (23
commands) the local spec has no trace of, and its `teams
update-membership-status` and `storage get-file-download` are likewise spec
drift, not port gaps. Comparing preview-Go against released-TS conflates two
unrelated things, so **every finding below comes from the same-spec
comparison**.

That gap is release timing, not a defect and nothing for this stack to fix: the
published package was generated from whatever spec was current when it shipped,
while `example.php` fetches `main`. It matters here only as a reason not to
compare a released binary against a locally generated one -- still true today,
since the spec the generator fetches has no `apps` service.

## How it was tested

Neither CLI reports an unknown subcommand when `--help` follows it, and cobra
pads its longest entry with a single space, so scraping help text both invents
and misses commands. Both trees come from the completion machinery instead,
which is generated from the command definitions:

- Go: cobra's hidden `__complete`, one call per node.
- TypeScript: the context table inside `appwrite completion bash`.

Flags are read off `--help`. They cannot come from `__complete`: when a command
has required flags still unset, cobra returns **only those**, hiding every
optional flag. An earlier pass of this audit did use `__complete` and produced
a 200-entry list of "flags missing from Go" that were all present.

Request behaviour is compared by pointing both CLIs at a recording HTTP server,
running every generated service command with its required flags filled by type,
and diffing method, path, query, body and headers.

Required-flag sets are compared separately: cobra's on one side, the
`.requiredOption` calls in the generated TypeScript on the other.

## Results when the audit opened

This is the opening snapshot, kept so the findings can be read against what
prompted them. Every row marked "Diverges" is closed — see [Status](#status--closed)
for where each one landed, and [Remaining sweep
differences](#remaining-sweep-differences-itemised) for the request diff as it
stands now.

| Check | Result |
|---|---|
| Command tree, 671 commands | **Parity.** 0 missing, 1 extra (`sessions`). |
| `--json` / `--raw` rendering | **Diverges.** Findings 13 and 14. |
| Required flags, 604 commands | **Parity.** 0 differences (479 have required flags). |
| Optional flags, 671 commands | 9 commands differ — findings 8 and 9. |
| Requests, 606 service commands | 237 byte-identical, **369 differ**. |
| Live staging run | 18 findings re-checked against a real instance. |
| File upload, single and 16 MB chunked | **Parity.** Identical signatures; Go 2.3× faster. |
| `pull settings` content | **Parity** — once finding 16 is worked around. |
| `pull all` on a real project | **Parity.** Byte-identical config, identical tree. |
| `push settings` | **Works**; write verified by reading back. |
| `push function` deploy | **Works** end to end — build logs, polling, activation. |
| `push table` reconciliation | **Works** — add and delete, verified by reading back. |
| `push all`: teams, topics, webhooks, buckets | **Works** — all four renames read back. |
| `types`, 8 languages + `--strict` | **Parity.** Byte-identical. |
| `generate` | 7 of 8 files identical; `constants.ts` is finding 18. |
| Go unit tests | 19 packages, pass. |
| Generator PHP unit tests | 33 tests, 125 assertions, pass. |

Of the 369 differing commands, 216 are explained entirely by finding 1, 82 by
finding 2, 11 by finding 10, and 54 are harness artifacts (the stub server
answers with field types the Go SDK's typed models reject — e.g.
`Variable.secret` is a bool in the model and a string in the stub; the
TypeScript never type-checks the response so it does not notice). Those 54 are
artifacts of the stub's field types, not divergences.

## Findings

### 1. The Go SDK sends every path parameter a second time

> **FIXED.** Path parameters are no longer added to the params map.

`appwrite users get --user-id abc`

| | request |
|---|---|
| TypeScript | `GET /users/abc` |
| Go | `GET /users/abc?userId=abc` |

On a write it lands in the body instead: `tokens update --token-id stub` sends
`{"tokenId":"stub"}` where the TypeScript sends `{}`.

Cause is `templates/go/base/params.twig`, which builds `params` from
`method.parameters.all` — a set that includes path parameters. Python
(`templates/python/base/params.twig`) and Ruby build theirs from
`parameters.query` merged with `parameters.body`, so Go is the outlier.

**Not a CLI bug.** It is in the published `sdk-for-go` and affects every Go SDK
user; the CLI only makes it visible. 216 of 606 commands.

**Live:** the API tolerates it. `users update-name` succeeded with `userId` in
the body, and reads succeed with the duplicate in the query string. So this is
a wire divergence with no observed breakage today — worth fixing for SDK
correctness and because a stricter validator would turn it into an outage, but
it is not urgent.

### 2. Boolean flags invert when given a value

> **FIXED.** The value is joined to the flag before pflag parses it.

**The most serious finding.** `--flag value` silently sends `true`, whatever
the value was:

```
$ appwrite users update-email-verification --user-id u1 --email-verification false
  Go sends {"emailVerification": true}      ← the opposite of what was asked
  TS sends {"emailVerification": false}
```

`--email-verification=false` (equals form) is correct. Only the space-separated
form breaks.

Cause: the generator sets `NoOptDefVal = "true"` on every generated boolean
flag, so pflag does not consume the following word — it becomes a stray
positional argument, which cobra ignores. The TypeScript models the same
parameters as value-taking options with a `parseBool` validator, so it rejects
`--required stub` with a type error where Go accepts it and sends `true`.

263 generated boolean flags carry `NoOptDefVal`. The commands where this is
most dangerous all take one:

- `users update-status --status false` → **enables** the user
- `users update-email-verification --email-verification false` → **verifies** them
- `users update-phone-verification`, `users update-mfa`,
  `users update-impersonator` — same shape
- every `databases create-*-attribute --required false`

A user who writes `--enabled false` in a script gets the opposite of what they
wrote, with no error. This is the one finding that can corrupt real data
rather than just diverge.

**Live proof**, against a real user on staging:

```
$ appwrite users get --user-id conformbool1          # before
  "emailVerification": false
$ appwrite users update-email-verification --user-id conformbool1 \
      --email-verification false
$ appwrite users get --user-id conformbool1          # after
  "emailVerification": true
```

Asking for `false` marked the account's email **verified**. No warning, exit 0.

### 3. The Go SDK does not escape path parameters

> **FIXED.** Path parameters go through url.PathEscape.

`appwrite users get --user-id 'a b/../c'`

| | path sent |
|---|---|
| TypeScript | `/users/a%20b%2F..%2Fc` |
| Go | `/users/a%20b/../c` |

A server or proxy normalises the second to `/users/c` — the id escapes its
segment. Same template as finding 1:
`strings.NewReplacer("{userId}", UserId)` does no encoding, where the Node SDK
(`templates/node/src/services/template.ts.twig:112`) wraps each path parameter
in `encodeURIComponent`.

Python and Ruby do not encode either, so Go matches most SDKs and Node is the
strict one. That is not a reason to leave it.

**Live:** worse than the stub could show. `users get --user-id
'conformbool1/../conformbool1'` in Go reaches a **different route entirely** —
the server answers `general_route_not_found` — where the TypeScript's encoded
path reaches the users endpoint and returns the API's own validation message:
`Invalid userId param: UID must contain at most 36 chars...`. The id does
escape its segment against a real server.

### 3b. A non-JSON error response is dumped raw to the terminal

> **FIXED.** Non-JSON bodies are summarised; the body moves behind --verbose.

Fallout from the previous finding, but independent of it: when the server
answers with an HTML error page instead of JSON, Go prints **all 8,946 bytes of
it** to stdout. The TypeScript prints one line. Exit codes agree (1 and 1), so
only the human suffers.

Any proxy 502, WAF block or maintenance page produces this.

### 4. `client --project-id` is inert

> **FIXED.** Written to and read from appwrite.config.json.

`appwrite client --project-id X` then `appwrite users list` fails with
`project is not set` in Go, succeeds in TypeScript. Two independent halves:

- **Write.** TypeScript calls `localConfig.setProject(projectId, "")`, writing
  `appwrite.config.json` in the working directory. Go writes
  `PreferenceProject` into the global `prefs.json`.
- **Read.** TypeScript's `resolveProjectId` (`lib/context.ts:22`) falls back
  through override → `APPWRITE_PROJECT_ID` → `appwrite.config.json` →
  **`globalConfig.getProject()`**. Go's `ForProject` chain (`sdk/sdk.go.twig`)
  stops at the project config, so even the value Go just wrote is never read.

This breaks the documented non-interactive setup path — the one CI uses, and
the one the conformance harness itself needs to point the CLI at a mock API.

**Live, in an empty directory with a valid session:**

```
$ appwrite client --project-id batch-test-165715   # Go: silent, exit 0
$ appwrite users list
project is not set. Run `appwrite init project` or pass --project-id
```

The TypeScript prints `✓ Success: Client configuration updated`, writes
`appwrite.config.json`, and the next command works. Go also prints **no
confirmation at all**, which is a second small divergence.

Worth flagging for anyone re-testing this: run it in a directory with no
`appwrite.config.json` and no ancestor carrying one, and do not let the
TypeScript run first. A TypeScript run leaves behind a config file that Go then
reads happily, which makes the bug look fixed. That contaminated this audit's
first live attempt.

### 5. `client --endpoint` accepts an endpoint without checking it

> **FIXED.** verifyEndpoint runs before the endpoint is stored.

TypeScript calls `verifyEndpoint` (`lib/auth/session.ts:42`), requiring `GET
/health/version` to answer with a version, and saves **nothing** if it does
not. Go saves endpoint, project and key with no request at all:

```
$ appwrite client --endpoint http://127.0.0.1:1/v1 --project-id p --key k
$ echo $?
0
```

A typo'd endpoint is stored silently and every later command fails somewhere
less obvious.

### 6. `client --endpoint` does not pick the matching session

> **FIXED.** Selects the session for the endpoint, preferring a signed-in one,
> and warns when it detaches an account.

Beyond verification, the TypeScript's endpoint branch (`generic.ts:288-329`)
selects the stored session matching the new endpoint, reports `Using signed-in
account <email>`, and warns when it detaches an authenticated session. Go sets
the endpoint on whatever session is current, creating one named `default` if
none exists.

### 7. `client --reset` does not sign out

> **FIXED**, and `logout` had the same hole. Both revoke server-side first.

TypeScript enumerates signed-in accounts, refuses non-interactively without
`--force`, prompts interactively, then calls `logoutSessions` — server side. Go
deletes the local entries and prints `Configuration reset.` The server-side
sessions stay alive.

### 8. `client --debug` reports the wrong project and omits fields

> **FIXED.** Reads the project config, and renders through the renderer.

Run in a directory whose `appwrite.config.json` names `testproj`:

```
Go                                TypeScript
endpoint     : http://...         endpoint     http://...
key          : SECRETKE...TAIL    key          SECRETKE...TAIL
accessToken  : TOKENSTA...TEND    accessToken  [hidden]NEND
selfSigned   :                    selfSigned   false
projectId    :                    projectId    testproj
version      : (devel)            projectName  Test
                                  ♥ Hint: Sensitive values were redacted.
```

Go reports an empty `projectId` because it reads global prefs (finding 4). It
also omits `projectName` and `organizationId`, adds `version`, and prints no
redaction hint.

Secret masking is *not* a divergence — both show the key's first and last eight
characters. (The TypeScript's `[hidden]NEND` for the access token looks like a
redaction bug on its own side.)

### 9. Missing flags

> **FIXED** except `--id`, which is dead in the TypeScript too.

| Flag | Where | Effect of absence |
|---|---|---|
| `--report` | global | No prefilled bug-report link. Both CLIs' error output says to "pass the --verbose or --report flag"; in Go the second half of that sentence is a lie. |
| `-V` | global | `--verbose` has no short form. |
| `-a` | `push`, `pull` | `--all` has no short form. Go's `--all` is a root persistent flag; the TypeScript's is per-command. |
| `--manual` | `update` | No way to print manual update instructions instead of self-updating. |
| `--id [id...]` | `push`, `pull` | See below. |
| `-ss` | `client` | pflag has no multi-character short flags. Accepted divergence. |

`--id` is a special case: in the TypeScript it is **written and never read**.
`cliConfig.ids` is assigned in `push.ts:4278` and `pull.ts:1127` and consulted
in exactly one place — the guard rejecting `--all` and `--id` together
(`parser.ts:957`). No resource selection reads it, so `appwrite push --id foo`
silently pushes what it would have pushed anyway. Go's omission is the more
honest behaviour, but the surfaces still differ: Go says `unknown flag: --id`.

### 10. `oauth2` commands send `X-Appwrite-Project`, TypeScript does not

> **RESOLVED — cosmetic, accepted.** The API treats both identically.

On the 11 `oauth2` commands that carry the project in the path
(`POST /oauth2/{projectId}/grants` and friends), Go sets the header as well.
Checked against staging: `oauth2 get-grant` and
`oauth2 create-device-authorization` return byte-identical responses from both
CLIs, so the extra header changes nothing. Go sending it is consistent with
every other request it makes. Accepted as a divergence rather than fixed.

### 11. Negatable flags are hidden rather than documented

> **FIXED.** Both spellings are documented.

`--no-code`, `--no-logs` and `--no-reload` all work in Go, but `negatableBool`
registers them hidden, so they appear in the TypeScript's help and not in Go's.
Functional parity, discoverability gap.

### 12. `x-appwrite-locale` is not sent

> **FIXED.** X-Appwrite-Locale is sent.

Every TypeScript request carries `x-appwrite-locale: en-US`; no Go request
does. It affects server-localised strings in responses.

### 13. `--json` turns every number into a string

> **FIXED.** Only integers past 2^53 are stringified.

Invariant 4 says `--json` is byte-for-byte. Against a response of
`{"total":42,"big":9007199254740993,"ratio":1.5,"flag":true,"users":[]}`:

| | `total` | `big` | `ratio` | `flag` |
|---|---|---|---|---|
| TypeScript | `42` | `"9007199254740993"` | `1.5` | `true` |
| Go | `"42"` | *dropped* | *dropped* | *dropped* |

`appwrite users list --json \| jq '.total'` returns `"0"` in Go and `0` in the
TypeScript. Any script doing arithmetic or a numeric comparison on `--json`
output breaks.

The port is deliberate and its reasoning is written down in
`internal/output/filter.go`:

> Numbers become strings, which looks odd but is what the TypeScript does:
> json-bigint yields BigNumber instances and `String(value)` is how they are
> rendered.

The premise is wrong. `filterData` in `parser.ts` stringifies only values whose
`constructor.name === "BigNumber"` — integers json-bigint promoted because they
exceed JavaScript's safe range. Ordinary numbers fall through to `result[key] =
value` and stay numbers. The Go port mapped "BigNumber" onto `json.Number`, but
with `UseNumber()` that is *every* number, not just oversized ones.

`--raw` is unaffected: it skips the filter and prints `42`.

**`TestFilterData` in `filter_test.go` enshrines the bug** — it asserts
`"count": 42` renders as `"count": "42"`. The test was written from the same
misreading, so it passes and the divergence ships. The correct predicate is
"integer outside the JS safe-integer range", and the test needs a case for each
side of that line.

### 14. `--raw` is not raw

> **FIXED.** The response body is captured at the transport.

Both `--raw` and `--json` render the SDK's typed model, not the response. Any
field the API returns that the generated model does not declare is silently
dropped — `big`, `ratio` and `flag` above vanish from **both** modes, where the
TypeScript passes all of them through.

`--raw` is documented as "Output the full raw JSON response". In Go it is the
parsed model re-encoded. A field the API adds is invisible until `sdk-for-go`
is regenerated and the CLI re-pinned, and a script capturing `--raw` to keep a
complete record silently keeps an incomplete one.

### 15. `appwrite.config.json` is not looked for in parent directories

> **FIXED.** The config is found by walking up, legacy name included.

Running any project command from a subdirectory fails in Go:

```
project/            appwrite.config.json lives here
project/sub/        $ appwrite users list
                    Go: project is not set. Run `appwrite init project`...
                    TS: total 2
```

From the project root both work. The TypeScript walks up the tree to find the
config; Go looks only in the working directory.

This is an everyday-usability regression, not an edge case — `src/`,
`functions/<name>/` and `sites/<name>/` are exactly where people run commands
from, and `push`/`pull` are exactly the commands they run.

### 16. `pull settings` fails unless `organizationId` is already in the config

> **FIXED.** The organization is resolved from the project.

```
$ appwrite pull settings          # config has projectId only
ℹ Info: Pulling project settings ...
Team with the requested ID could not be found.        ← Go
```

The TypeScript resolves the organization from the project and says so:

```
ℹ Warning: Resolved the organization for this command from project
  batch-test-165715. Run `appwrite init project` to persist organizationId.
✓ Success: Successfully pulled all project settings.
```

Add `organizationId` to `appwrite.config.json` and Go succeeds, writing a
settings block **byte-identical** to the TypeScript's. So the pull itself is a
correct port; only the organization fallback is missing, and its absence
surfaces as an error message that names a team the user never mentioned.

This was filed as "needs live verification" in the first pass. It is a hard
failure.

### 17. `--json` is ignored by the hand-written commands

> **FIXED, by diverging.** `whoami` and `sessions` now render through the same
> path as every other command, so `--json` and `--raw` work. Go emits valid
> JSON where the TypeScript emits `util.inspect` output that `jq` cannot read,
> so this is deliberately *not* byte-parity: matching it would mean copying a
> bug. `sessions` also carries the active session as an `Active` field rather
> than a leading asterisk, because a marker in a table cannot survive JSON.

`whoami --json` and `sessions --json` print the human-readable rendering in Go.
The TypeScript emits a structure for `whoami`.

Invariant 4 covers these too. Note the TypeScript's own `whoami --json` is not
valid JSON either — it is `util.inspect` output, with single quotes and
unquoted keys, so `appwrite whoami --json | jq` fails on both CLIs for
different reasons.

### 18. `generate` emits an empty endpoint

> **FIXED.** Falls back to the session endpoint, then the default.

The generated SDK's `constants.ts` is the only file of the eight that differs,
and it differs in the one line that matters:

```diff
-export const ENDPOINT = '';                                   // Go
+export const ENDPOINT = 'https://cloud.staging.appwrite.io/v1';  // TypeScript
```

A client built from Go's output does not know where to connect.

Go reads the endpoint only from `appwrite.config.json`; the TypeScript falls
back to the session's endpoint when the config has none. `pull` does not write
an `endpoint` key, so the default flow — pull, then generate — produces the
broken file. Adding `endpoint` to the config by hand makes Go's output
byte-identical.

Same shape as finding 4: a resolution chain that is one fallback short.

### 19. `sessions` exists only in Go

> **ACCEPTED — kept deliberately.** An addition, not a gap.

`appwrite sessions` lists the sessions in the CLI preferences; the TypeScript
has no such command.

Kept because the preferences file holds more than one session and, without this,
nothing shows you what is in it: `login --switch` prompts with the list but
cannot be scripted, and reading `prefs.json` by hand is not an interface. It is
additive, so no TypeScript user can be broken by it, and the surface-parity
check reports it as the single extra command rather than hiding it.

## Shared bugs — present in both CLIs

These are not port gaps. The Go CLI reproduces the TypeScript's behaviour
exactly, which is the point; they are recorded because they are real bugs
someone should fix in both.

### `client --project-id` does not follow the project to its region

> **FIXED in Go by diverging.** Still present in the TypeScript.

```
$ appwrite client --project-id <a project in syd>
$ appwrite databases list
✗ Error: Project is not accessible in this region
```

On Cloud a project lives in one region and answers only on that region's host.
Both CLIs' `client --project-id` wrote the id and stopped there — the TypeScript
at `generic.ts:341`, which is one `localConfig.setProject(projectId, "")` — so
the endpoint kept pointing at whichever host was configured and the next command
was refused. Nothing in the config explained why, even though the CLI had been
handed the project id and could have asked.

Both CLIs already do the right thing through the other door: `init project`
resolves the region and pins the regional endpoint (`init.ts:433`,
`initproject.go`). Go now does the same from `client --project-id`, reporting the
change because it is one the user did not type. Every failure — offline, no
session, a project id that does not exist yet, a self-hosted endpoint with no
regions at all — falls back to setting just the project, which is what the
TypeScript does in every case.

### `pull settings` writes a config that `push settings` cannot push back

```
$ appwrite pull settings          # writes "limit": null, "sessionsLimit": null
$ appwrite push settings
✗ Error: Invalid `total` param: Value must be a valid range between 1 and 100
```

Byte-for-byte the same failure from both CLIs on the same config. The round
trip that PLAN.md invariant 2 is about — pull, then push — does not survive a
project whose session limits are unset.

Replace the nulls with a number in 1-100 and both succeed.

### `pull function` writes a specification `push function` rejects

```
$ appwrite pull all               # writes "buildSpecification": "s-0.5vcpu-512mb"
$ appwrite push function --all
✗ Error: Invalid `buildSpecification` param: Specification must be one of:
         s-2vcpu-2gb, s-2vcpu-4gb, s-4vcpu-4gb or null
```

Identical failure from both CLIs. `pull` faithfully records the function's
current specification and the API will not accept that same value back — the
set it validates against does not contain the value it reports. `deploymentRetention`
and `runtimeSpecification` come back from `pull` the same way.

Delete the two specification keys and the push succeeds, so nothing else about
the function is at fault.

This is the field the original `push site` change-table report was about. It is
a server-side or spec-side inconsistency, not a CLI bug, and neither CLI can
work around it without dropping data the user asked to keep.

### A failed `push settings` leaves the project half-applied

Both CLIs apply the project name, the 12 service statuses and the 3 protocol
statuses before reaching the auth block that fails. There is no rollback, so a
rejected settings push leaves the remote in a state matching neither the old
config nor the new one.

## Resolved by the live run

### File upload — not a bug

The two CLIs take visibly different routes:

| | Go | TypeScript |
|---|---|---|
| preflight | `GET /storage/buckets/b/files/f` | none |
| `fileId` sent as | header `x-appwrite-id` | multipart field `fileId` |

Both work. A 16 MB chunked upload of `tests/resources/large_file.mp4` produced
the **same multipart signature** from both CLIs —
`b50bc89df9bfa57aa46323d03acd8c20-4`, same `sizeOriginal` — as did a small
single-part upload. The divergent shape is two valid ways to drive the same
endpoint.

Go took 4.55 s to the TypeScript's 10.40 s on that upload, 2.3× faster.

### `pull all` — full parity

Run against a real project holding a function, a database with one table, and a
bucket, the two CLIs produced a **byte-identical `appwrite.config.json`** and an
identical file tree, function source included. `pull.ts` is 1,206 lines and this
is the strongest single parity result in the audit.

One divergence in the flow, and Go has the better of it: the TypeScript still
prompts `Do you want to pull source code of the latest deployment?` under
`--force`, so `pull all --force` hangs in a script. Go does not prompt and pulls
the code.

### `push settings` — works, and the write was verified

Toggling `sessionAlerts`, `limit` and `sessionsLimit` through Go's
`push settings` and reading back with the TypeScript's `pull settings` showed
exactly the requested values. Renaming the project through the config works too.

Go omits the `ℹ Info: Applying project name ...` line the TypeScript prints —
but only the line. The rename itself is applied; verified by pushing a new name
with Go and reading it back.

### `push function` — the deployment state machine works

With the unpushable specification keys removed, Go deployed a real function to
staging end to end in 10.1 s: packaging, upload, live build-log streaming,
status polling through `waiting` → `building` → finished, activation, and both
the preview and console links.

`push.ts` is 4,380 lines and its deploy loop is the single riskiest thing in the
port. It works.

### `push table` — schema reconciliation works in both directions

Adding a column to `appwrite.config.json` and pushing produced the right change
table and created it:

```
  Key                                             Action  Reason
  conformProbe in Mythical Creatures (creatures)  adding  Field isn't present on the remote server
```

Reading back with the TypeScript's `pull table` showed
`{"key":"conformProbe","type":"integer","required":false,"array":false,"default":7,"min":0,"max":100}`
— every property preserved.

Removing it from the config and pushing again detected the deletion
(`Field isn't present on the appwrite.config.json file`) and applied it; the
table is back to its original four columns.

`attributes.ts` is 1,138 lines of reconciliation logic and both directions work.

### `push all` — every remaining resource type works

A team, a topic and a webhook were created on the project so the empty paths
would stop being empty. With those present, `pull all` stayed **byte-identical**
between the two CLIs.

Renaming all four of team, topic, webhook and bucket in the config and running
Go's `push all --force` produced a change table for each and applied all four;
a fresh TypeScript `pull all` read back every new name. Combined with the
function deploy and the table reconciliation above, every resource type
`push all` handles is now exercised except sites.

### `types` — byte-identical across every language

`ts, js, php, kotlin, swift, java, dart, cs` all produced byte-identical output
from the two CLIs, as did `--strict` for the three languages where it changes
naming.

### `generate` — identical but for one line

Seven of the eight generated files match byte for byte. Only `constants.ts`
differs, and that is finding 18.

### `--total` — a TypeScript bug, not a Go one

`users list --total false`, `--total=false` and bare `--total` all produce an
empty query string from the TypeScript; the flag never reaches the wire. Go
sends `total=false` and `total=true` respectively. Both CLIs document the flag.
Go is correct here.

## Not tested — and why

1. **The Docker e2e suite.** `tests/e2e/GoCLI126Test.php` is the authoritative
   conformance test and it did not run here: no Docker daemon on this machine. It
   runs in CI on every PR, which is where that gap is closed.
2. **Interactive flows.** Login device flow, `init` prompts, the push/pull
   resource pickers, every confirmation prompt.
3. **`push site`.** Functions and tables are verified; the project used for the
   audit has no site, so that path is still untested.
4. **`--json` stdout parity beyond the structural bugs.** The comparison ran far
   enough to establish findings 13, 14 and 17, which affect every command; a
   per-command byte diff is only worth collecting once those are fixed, since
   until then it reports the same causes 606 times.
5. **The five structured collection renderers.** Still ported by reading and
   verified by eye; no captured baselines, as PLAN.md already notes.
6. **The 54 stub-artifact commands**, re-run against the live API. Spot checks
   passed; the set was not swept again.

## What is left

Nothing. Every finding has been checked against a live instance and closed. What
follows is the reasoning behind the four that were closed by decision rather than
by a code change, so a reviewer does not have to reconstruct it.

1. **`--id` on `push` and `pull`** — omitting it from Go is correct, because the
   TypeScript's does nothing. It is documented as "Limit the push to these
   resource ids", assigned at `push.ts:4278` and `pull.ts:1127`, and its only
   read in the entire tree is the guard at `parser.ts:955` that rejects it
   alongside `--all`. No code path filters on it, so `push function --id abc`
   pushes every function. Go declining to offer the flag is better than
   reproducing one that lies; real resource-id filtering is a product feature,
   not a port gap, and belongs to whichever CLI survives Phase 8.
2. **Finding 10** — accepted. Go sends `X-Appwrite-Project` on the `oauth2`
   commands; the API returns identical responses either way.
3. **Finding 19** — accepted. `sessions` is additive, so it breaks nobody, and
   without it nothing shows what is in the preferences file.
4. **Finding 22 and query-parameter ordering** — accepted. Neither is observable
   to a server, and the ordering fix would mean reworking `params` across every
   generated method in the published SDK.
5. **Finding 21 is a bug in the TypeScript CLI**, not in Go: `storage
   get-file-download`, `get-file-view` and `get-file-preview` all return 401 in
   the released `appwrite-cli@25.1.0`, because it puts the credentials in the
   query string. Go authenticates through headers and works. Closed here as "Go
   is correct"; the TypeScript defect is a separate product bug and out of scope
   for the rewrite, so it needs raising against that CLI rather than tracked in
   this document.

Untested rather than unresolved: the interactive flows, which need a terminal.
The Docker e2e suite runs in CI as `GoCLI126`.

## Verified live after the fixes

Re-checked against staging once the fixes were in:

| Check | Result |
|---|---|
| `--email-verification false` | Sends `false`. The data-corruption bug is closed. |
| `--email-verification true` | Sends `true`. |
| `pull settings` with no `organizationId` | Succeeds, with the same warning the TypeScript prints. |
| A command from a subdirectory | Works. |
| `whoami` | Renders, and `--json` is valid JSON. |
| Downloads, views, previews | Work in Go; the TypeScript 401s. |

## Remaining sweep differences, itemised

532 of 606 commands were byte-identical when the sweep last ran, and finding 20
has been fixed since, which accounts for 4 more. Of the 74 that differed, none
is an open defect:

| Count | Cause | |
|---|---|---|
| 50 | The stub server answers with field types the Go SDK's typed models reject, so Go exits 1 where the TypeScript, which never type-checks a response, exits 0. | Harness, not product. |
| 11 | Finding 10, the `oauth2` project header. | Accepted. |
| 5 | Finding 21, the download and preview commands. | Go is correct; the TypeScript 401s. |
| 4 | Finding 20, array query encoding. | Fixed. |
| 2 | Finding 22, multipart field order on `create-deployment`. | Accepted. |
| 1 | Query-parameter ordering on `project get-usage`. | Accepted. |
| 1 | `storage create-file`, whose upload shape was verified identical against a live server. | Harness. |

Three earlier rounds of this sweep reported far worse numbers, every time
because of harness drift rather than the product: the binary was rebuilt
mid-run; only the Go side had `APPWRITE_PROJECT_ID` set, so the two CLIs used
different projects; an earlier probe had changed the TypeScript's stored API
key; the driver read each flag's *description* as its type and so fed `stub` to
every boolean; and required booleans need an explicit value on the TypeScript
side. All five were fixed in the sweep harness before the numbers above were
taken. **Treat a sudden jump in this number as harness drift until proven
otherwise.**

## Found by the final sweep

These three surfaced only once the harness stopped generating noise. None is
fixed.

### 20. Array query parameters are encoded differently

> **FIXED.** Go now sends the indexed form, matching every other SDK.

`migrations get-appwrite-report --resources stub`

| | query |
|---|---|
| TypeScript | `resources%5B0%5D=stub` — `resources[0]=stub` |
| Go | `resources%5B%5D=stub` — `resources[]=stub` |

Settled without a live server by asking what the other SDKs do: the Node
(`client.ts.twig:597`), Python (`client.py.twig:273`) and PHP
(`Client.php.twig:386`) clients all flatten a list to `key[0]`, `key[1]`. Go
was the only one sending `key[]`, so it was the outlier rather than the
convention, and it now sends the indexed form too. Both CLIs produce
`queries%5B0%5D=a&queries%5B1%5D=b` for the same command.

The API accepts either, so this was about the SDKs agreeing rather than about
a broken request.

### 21. Download and preview commands build a different request

> **RESOLVED against a live server — the TypeScript is the broken one.** Go is
> correct; nothing to change here.

`storage get-file-download --bucket-id b --file-id f --destination out`

| | request |
|---|---|
| TypeScript | `GET .../download?project=<id>&impersonateuserid=`, **no auth headers** |
| Go | `GET .../download`, full auth headers, no query |

These are the spec's `location` methods, which return a URL rather than a
document. The TypeScript puts the credentials in the query string so the
resulting link can be handed to a browser; Go authenticates the request
normally.

Checked against staging, and the API rejects the TypeScript's form:

```
$ appwrite storage get-file-download --bucket-id b --file-id f --destination out
  Go: writes the file
  TS: ✗ Error: Failed to download file: 401 Unauthorized
```

Same for `get-file-view` and `get-file-preview`, and the same in the
**released `appwrite-cli@25.1.0`** — so `storage get-file-download` and its two
siblings are broken in the shipped TypeScript CLI. Go authenticates through
headers and works, so there is nothing to fix on this side -- recorded because a
sweep looking for Go defects found a TypeScript one, and somebody should know.

### 22. `create-deployment` orders its multipart fields differently

> **ACCEPTED.** Field order is not significant, and deployments were verified
> working end to end against a live server.

The bodies of `functions create-deployment` and `sites create-deployment`
differ past the first part, with the boundary normalised away. Field order in a
multipart body is not significant to any conforming server, so this is
cosmetic, but it is the last unexplained difference in the sweep and is
recorded rather than dismissed.
