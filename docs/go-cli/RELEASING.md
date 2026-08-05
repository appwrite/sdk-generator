# Releasing the Go CLI

What the automated pipeline does, and the three things it cannot do for you.

`templates/go-cli/.github/workflows/publish.yml.twig` generates the release
workflow into the shipped CLI repository. It fires on `release: published` and
handles goreleaser, signing, GitHub assets, npm and Homebrew. Everything below
is either a prerequisite it assumes, or a decision it reads off the release.

## Which repository

**`appwrite/sdk-for-cli`** — the same repository the TypeScript CLI ships from.

The Go module path derives from `gitUserName`/`gitRepoName`, so that decision
alone gives `github.com/appwrite/sdk-for-cli` in `go.mod`, in every import, and
in the goreleaser ldflags. Nothing is configured twice.

Two things come for free from reusing that repository:

**Windows signing.** The TypeScript CLI's workflow falls back to the project
slug `sdk-for-cli`, and the Go CLI's falls back to `gitRepoName`, which is the
same string. The policy and artifact-configuration slugs default identically
(`release-signing`, `initial`), the SignPath secrets are already present, and
the uploaded artifact has the same shape — `upload-artifact` strips the common
ancestor directory, so `staging/` and `build/` both yield two `.exe` files at
the artifact root. Signing switches itself on because it keys off the token
being present.

**npm trust for the parent package.** Trusted publishing is scoped to a
repository plus a workflow file path, and both CLIs generate
`.github/workflows/publish.yml` into `appwrite/sdk-for-cli`. Whatever trust
`appwrite-cli` already has carries over. The six platform packages do not
exist, so they still need the bootstrap below.

### This is a cutover, not coexistence

Both CLIs generate to identical destinations — `publish.yml`, `package.json`,
`install.sh`, `install.ps1`, the scoop manifest. Generating the Go CLI into
`sdk-for-cli` replaces the TypeScript CLI there; they cannot both live in that
repository.

So the repository's default branch becomes the Go CLI at the moment the sdks
task is repointed, while `appwrite-cli@latest` on npm is still TypeScript until
a stable release moves it. That gap is fine, but it is a sequencing decision
rather than something to discover during a release. An rc can be cut from a
branch to keep the default branch on TypeScript until the cutover is intended.

## Prerequisites, once per repository

### 1. Create the six npm platform packages by hand

npm's trusted publishing is configured **per package that already exists**. The
six platform packages do not exist yet, so the first publish cannot use OIDC —
the workflow's `npm publish` would fail with `ENEEDAUTH` no matter how the
runner is configured.

Publish each one once, from a machine with an npm token:

```
appwrite-cli-darwin-arm64   appwrite-cli-linux-arm64   appwrite-cli-win-arm64
appwrite-cli-darwin-x64     appwrite-cli-linux-x64     appwrite-cli-win-x64
```

A stub version is enough; the release overwrites it. Then enable trusted
publishing for the publish workflow on each of the seven packages — the six
above plus the parent `appwrite-cli`.

Skipping this does not degrade gracefully. It fails the release after the
GitHub assets have already been uploaded.

### 2. Wire the credentials

| Secret / variable | Used for | Required |
|---|---|---|
| `vars.APPWRITE_BOT_APP_ID` | GitHub App that writes to the Homebrew tap | for a stable release |
| `secrets.APPWRITE_BOT_PRIVATE_KEY` | its private key | for a stable release |
| `secrets.WINDOWS_SIGNING_API_TOKEN` | SignPath | no |
| `vars.WINDOWS_SIGNING_ORGANIZATION_ID` | SignPath organisation | no |

**Windows signing is optional.** The workflow keys off
`WINDOWS_SIGNING_API_TOKEN`: set it and the `.exe` assets are sent to SignPath
and their checksums recomputed afterwards; leave it unset and the three signing
steps are skipped, the binaries ship unsigned, and the run summary carries a
warning. Unsigned means Windows SmartScreen warns on first run — acceptable for
a release candidate, not for a stable release.

Nothing else changes when it is off. The Windows assets are still uploaded, and
the npm `win32` packages and the scoop manifest still resolve.

Three further slug variables — `WINDOWS_SIGNING_PROJECT_SLUG`,
`WINDOWS_SIGNING_POLICY_SLUG`, `WINDOWS_SIGNING_ARTIFACT_CONFIGURATION_SLUG` —
only matter once signing is on. Each falls back to a convention
(`gitRepoName`, `release-signing`, `initial`), so a SignPath project following
it needs none of them set. The fallback is a guess at a naming convention, not
a way to skip the lookup: if the project is named something else, signing fails.

Asset upload uses the job's own `GITHUB_TOKEN`, covered by `contents: write`.
npm uses trusted publishing and needs no token. There is deliberately no Apple
credential: `install.sh` requires an embedded signature rather than a trusted
one, and goreleaser ad-hoc signs the darwin builds in the build hook — which is
all the TypeScript CLI has ever shipped, and needs no external service.

## Known blocker: the published Go SDK is behind the CLI

`go.mod` requires `github.com/appwrite/sdk-for-go/v6` at the version
`GoCLI::setSDKVersion` pins. Four packages the CLI imports do not exist in
v6.2.0:

    affiliates    migrations    notifications    vcs

`affiliates` is new in the spec and no SDK release carries it yet. The other
three have never been in the published module. So a build from the shipped
repository — one without the local `replace` that `example.php` adds for the
examples tree — cannot resolve its own dependency.

The `go-cli (console)` validation job builds with the replace dropped precisely
to keep this visible, and it fails today. That failure is accurate, not a
misconfigured check: **a release cannot be cut until those four packages are
published**, or until the CLI vendors the SDK the way the preview fork does.

The preview fork at `ChiragAgg5k/appwrite-cli-go` is unaffected because it
vendors a console-platform SDK into `internal/appwritesdk` rather than resolving
the published module. That vendoring is structural, not a shortcut.

## Version numbers

**Continue the TypeScript CLI's version line.** The CLI reports an available
update by polling `https://registry.npmjs.org/appwrite-cli/latest`, which is the
same package both CLIs publish to. A Go build numbered below the TypeScript
CLI's current version would tell every user an "upgrade" is available and then
replace the Go binary with the TypeScript one.

So an rc is `26.0.0-rc.1`, not `1.0.0-rc.1` — the next major of the same
product. Check the registry's current `latest` before picking the number.

## Cutting a release candidate

1. Tag and publish a GitHub release with **Pre-release ticked.** The workflow
   reads `github.event.release.prerelease`, not the tag text, so this checkbox
   is what routes the release.
2. The workflow then:
   - builds all six targets and ad-hoc signs the darwin ones,
   - sends the Windows binaries to SignPath **if that is configured**, and
     recomputes checksums afterwards either way — signing changes the bytes,
   - uploads every asset plus `checksums.txt` to the release,
   - publishes npm under the **`next`** tag, platform packages first,
   - **skips the Homebrew tap entirely.**

A prerelease therefore reaches users through `npm i -g appwrite-cli@next`, a
direct asset download, or `install.sh` pointed at the tag. `brew install
appwrite` keeps serving the last stable build, which is what you want.

## Cutting a stable release

Identical, with Pre-release unticked. npm publishes to `latest` and the
Homebrew formula's version and four checksums are rewritten and pushed to the
tap. The formula itself is hand-maintained in the tap — the release only
rewrites those five fields.

## install.sh and appwrite.io

`GoCLI::getFiles()` generates `install.sh` and `install.ps1` into the CLI
repository, shared verbatim with the TypeScript CLI. Both build their download
URL from `language.params.npmPackage`, which is also what names every release
asset, so the installer and the release cannot disagree about a filename.

Note what this does **not** do: `curl -sL https://appwrite.io/cli/install.sh`
serves whatever script is hosted at that path, which is generated from
whichever CLI is published there. Until that hosting is repointed, the curl
one-liner installs the TypeScript CLI. Repointing it is a deliberate cutover,
not a side effect of this release.

The script bakes its version at generation time rather than resolving
`/releases/latest`, which is why it can serve a prerelease at all — GitHub
resolves `latest` to the newest *non*-prerelease.

## After the release

- `appwrite update` on a standalone install downloads from a versioned tag, so
  it will not walk an rc backwards onto the last stable build. It then verifies
  the binary against `checksums.txt` from the same release before replacing
  itself, and refuses to install if that file is absent or disagrees — so the
  manifest the workflow uploads is load-bearing, not just a courtesy. Releases
  published before the Go CLI carry no `checksums.txt`, and cannot be installed
  this way.
- Scoop is generated with its URLs baked in at generation time and no release
  step updates it, exactly as on the TypeScript CLI. Treat the scoop manifest
  as stale until someone regenerates it.
