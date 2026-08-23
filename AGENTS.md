# Agents Guide — SDK Generator

This file provides guidance for AI agents (Claude Code and subagents) working in this repository.

## Critical Rules for Agents

### Rule 1: Always regenerate after template edits

Templates are not used directly — they're rendered at generation time. The `examples/` folder is the ground truth for what the generator actually produces. Always regenerate after making template changes.

### Rule 2: New files require `getFiles()` registration

The generator does not auto-discover templates. Every output file must have an explicit entry in the `getFiles(): array` method of the corresponding Language class. Forgetting this means the file silently doesn't get generated.

### Rule 3: Check language inheritance before changing parent templates

| Parent | Children affected |
|--------|------------------|
| `Web` | `Node`, `ReactNative` |
| `Dart` | `Flutter` |
| `Swift` | `Apple` |
| `Kotlin` | `Android` |
| `Go` | `CLI` |

Modifying a parent's template or `getFiles()` affects all children. Regenerate and verify child SDKs too.

**One coupling is not visible in the hierarchy.**

`templates/cli/install.sh.twig` and `templates/cli/install.ps1.twig` build every download
URL from `language.params.npmPackage`. The same parameter names release assets produced
by `.goreleaser.yaml` and consumed by the Scoop manifest and npm platform packages.
Change the asset naming in one place and all four must move together.

Regenerate the CLI after changing any of them:

```bash
php example.php cli
```

### Rule 4: `copy` scope = no Twig processing

Files with `'scope' => 'copy'` are copied verbatim — no variable substitution happens. If your new file needs template variables, use `'scope' => 'default'` (or `service`, `method`, etc.).

### Rule 5: Destination paths are also Twig templates

The `destination` string in each `getFiles()` entry supports Twig expressions and filters:
```php
'destination' => 'src/Services/{{ service.name | caseCamel }}.php',
```

### Rule 6: Never modify lock file templates directly

Lock file templates (`package-lock.json.twig`) contain Twig expressions that get corrupted if you copy a raw lock file over them. Always use the update script:

```bash
./scripts/update-lockfiles.sh all
```

The script strips Twig expressions before running `npm install`, then restores them automatically. Never copy a raw lock file over a lock template or edit one by hand.

### Rule 7: Generated source must already be formatter-clean

A clean generation must already match the target formatter. Do not add a post-generation formatting step or run a formatter to fix generated output in place—the fix belongs in the Twig template.

When you change templates for a language below, regenerate and run that language's check. Child SDKs inherit parent templates — regenerate those too.

- **Go / CLI** — `gofmt`

  ```bash
  php example.php go <platform>
  test -z "$(gofmt -l examples/go)"
  ```

  `CLI` inherits from `Go`. After shared Go template changes:

  ```bash
  php example.php cli
  test -z "$(gofmt -l examples/cli)"
  ```

- **Dart / Flutter** — `dart format`

  ```bash
  rm -rf examples/dart examples/flutter
  php example.php dart <platform>
  (cd examples/dart && dart pub get)
  dart format --output=none --set-exit-if-changed examples/dart
  ```

  `Flutter` inherits from `Dart`. After shared Dart template changes:

  ```bash
  php example.php flutter client
  (cd examples/flutter && flutter pub get)
  dart format --output=none --set-exit-if-changed examples/flutter
  ```

- **Python** — Black

  ```bash
  rm -rf examples/python
  php example.php python <platform>
  (cd examples/python && find appwrite test -name '*.py' -print0 | xargs -0 python -m black --check)
  (cd examples/python && python -m black --check setup.py)
  ```

- **PHP** — Pint, PHPStan, and Rector

  ```bash
  rm -rf examples/php
  php example.php php server
  (cd examples/php && composer install)
  (cd examples/php && composer lint && composer analyse && composer refactor)
  ```

- **Rust** — rustfmt 1.83.0

  ```bash
  rm -rf examples/rust
  php example.php rust server
  (cd examples/rust && cargo +1.83.0 fmt --check --all)
  ```

- **Swift / Apple** — swift-format 6.1 (4-space indent, lineLength 200)

  ```bash
  rm -rf examples/swift examples/apple
  php example.php swift server
  (cd examples/swift && swift-format lint --recursive --strict --configuration .swift-format .)
  ```

  `Apple` inherits from `Swift`. After shared Swift template changes:

  ```bash
  php example.php apple client
  (cd examples/apple && swift-format lint --recursive --strict --configuration .swift-format .)
  ```

- **Web / Node / React Native** — Prettier

  ```bash
  rm -rf examples/web
  php example.php web <platform>
  (cd examples/web && npm ci && npm run format:check)
  ```

  Prettier's layout depends on the print width, so a template cannot always know
  whether a construct fits on one line. `src/SDK/Language/Web.php` and
  `src/SDK/Language/JS.php` render the width-sensitive constructs (method
  signatures, union and conditional types, guards, assignments) through helpers
  exposed as Twig filters. Extend those helpers rather than hand-wrapping output
  in a template.

  `Node` and `ReactNative` read several `templates/web/src/**` files directly.
  After shared Web template changes:

  ```bash
  php example.php node server
  php example.php react-native client
  ```

## Repository at a Glance

- **Purpose:** Generate Appwrite SDKs and tooling targets for 20+ languages/platforms from Swagger/OpenAPI specs using Twig templates
- **Language:** PHP (generator engine) + Twig (templates)
- **Entry point:** `example.php` — runs generation for all or a specific SDK
- **Output:** `examples/<lang>/` — generated SDK output for local verification. **Not checked in** — `.gitignore` excludes `examples/*`, so it is a scratch area you regenerate, never a diff baseline

```
src/SDK/Language/<Lang>.php   ← Language class: defines files, types, keywords
templates/<lang>/             ← Twig templates for that language
examples/<lang>/              ← Generated SDK output (gitignored; regenerate to verify)
example.php                   ← Entry point: regenerates all SDKs from specs
```

**Supported SDKs:** PHP, Web, Node, CLI, Ruby, Python, Dart, Flutter, React Native, Go, Swift, Apple, DotNet, Android, Kotlin, Unity, REST, GraphQL, Rust, Skills, CursorPlugin, ClaudePlugin, CodexPlugin

## Primary Workflows

### Modifying an Existing SDK Template

1. Edit template(s) in `templates/<lang>/`
2. Regenerate:
   ```bash
   php example.php <lang>
   ```
3. Inspect `examples/<lang>/` to verify the output is correct. `git diff` will **not** show it — `examples/*` is gitignored. To compare before/after, copy `examples/` aside, `git stash -u` your template changes, regenerate, and `diff -r` the two trees. **`-u` matters:** a newly added template is untracked, and a plain `git stash` leaves it in place, so the "before" tree is generated with your change still applied and the comparison shows nothing
4. Run linters and refactor check:
   ```bash
   composer refactor:check
   composer lint-twig
   # or directly
   uvx djLint templates/ --lint
   ```

### Adding a New Template File to an Existing SDK

1. Create the `.twig` file in `templates/<lang>/`
2. **Register it in `src/SDK/Language/<Lang>.php` → `getFiles()` array** — this is mandatory:

```php
public function getFiles(): array
{
    return [
        // ...existing entries...
        [
            'scope'       => 'default',   // default|service|method|definition|requestModel|enum|copy|download
            'destination' => 'path/to/output.ext',
            'template'    => 'lang/path/to/template.twig',
        ],
    ];
}
```

**Scopes:**
- `default` — generated once per SDK (config files, README, main entry point)
- `service` — generated once per API service
- `method` — generated once per service×method combination
- `definition` — generated once per model/definition
- `requestModel` — generated once per request model
- `enum` — generated once per enum
- `copy` — static files copied as-is, no Twig processing
- `download` — generated once per SDK by downloading the URL in `template` to `destination`

3. Regenerate and verify

### Adding a New Language SDK

1. Create `src/SDK/Language/NewLang.php` (extend `Language` or a related language)
2. Implement: `getName()`, `getKeywords()`, `getIdentifierOverrides()`, `getFiles()`, `getTypeName()`, `getParamDefault()`, `getParamExample()`
3. Create `templates/newlang/` and add all Twig files
4. Register all template files in `getFiles()`
5. Add generation block to `example.php`
6. Generate: `php example.php newlang`
7. Inspect `examples/newlang/`

## File Reference Map

| What you want to change | Where to look |
|------------------------|---------------|
| Template for a language | `templates/<lang>/` |
| Which files get generated | `src/SDK/Language/<Lang>.php` → `getFiles()` |
| Type mappings for a language | `src/SDK/Language/<Lang>.php` → `getTypeName()` |
| Available Twig filters | `src/SDK/SDK.php` (around line 62) |
| Canonical spec parser and DTOs | `utopia-php/openapi` (VCS dependency) |
| SDK grouping and filtering | `src/SDK/SDK.php` |
| Generation orchestration | `src/SDK/SDK.php` → `generate()` |
| Example generation script | `example.php` |
| Generated output for review (gitignored) | `examples/<lang>/` |

## Available SDK Names for `example.php`

Pass as first argument to generate only that SDK:

| Argument | Language class | Output dir |
|----------|---------------|------------|
| `php` | PHP | `examples/php/` |
| `unity` | Unity | `examples/unity/` |
| `web` | Web | `examples/web/` |
| `node` | Node | `examples/node/` |
| `cli` | CLI | `examples/cli/` |
| `ruby` | Ruby | `examples/ruby/` |
| `python` | Python | `examples/python/` |
| `dart` | Dart | `examples/dart/` |
| `flutter` | Flutter | `examples/flutter/` |
| `react-native` | ReactNative | `examples/react-native/` |
| `go` | Go | `examples/go/` |
| `swift` | Swift | `examples/swift/` |
| `apple` | Apple | `examples/apple/` |
| `dotnet` | DotNet | `examples/dotnet/` |
| `rest` | REST | `examples/REST/` |
| `android` | Android | `examples/android/` |
| `kotlin` | Kotlin | `examples/kotlin/` |
| `graphql` | GraphQL | `examples/graphql/` |
| `rust` | Rust | `examples/rust/` |
| `skills` | Skills | `examples/skills/` |
| `cursor-plugin` | CursorPlugin | `examples/cursor-plugin/` |
| `claude-plugin` | ClaudePlugin | `examples/claude-plugin/` |
| `codex-plugin` | CodexPlugin | `examples/codex-plugin/` |
| `zed-extension` | ZedExtension | `examples/zed-extension/` |

## Twig Template Variables by Scope

| Scope | Extra variables available |
|-------|--------------------------|
| All scopes | `spec`, `language`, `sdk` |
| `service` | `+ service` |
| `method` | `+ service`, `method` |
| `definition` | `+ definition` |
| `requestModel` | `+ requestModel` |
| `enum` | `+ enum` |

## Common Pitfalls

- **Silent no-op:** A new `.twig` file with no `getFiles()` entry — generation runs successfully but the file is never created
- **Wrong scope:** Using `default` scope when you need `service` scope means your template can't access `{{ service.name }}`
- **Copy scope surprises:** A `copy`-scoped file with Twig syntax — the syntax is output literally, not rendered
- **Spec fetch failure:** `example.php` requires internet access to fetch the live spec from GitHub; generation fails with an exception if the fetch returns empty. Spec URL pattern (prefix is `open-api3` or `swagger2` depending on the format):
  ```
  https://raw.githubusercontent.com/appwrite/specs/main/specs/{version}/open-api3-{version}-{platform}.json
  ```
- **Spec formats:** `example.php` parses every document through `Utopia\OpenAPI\Parser`. OpenAPI 3 is fetched by default; Swagger 2 is also supported. Pass the fetched format as the third argument:
  ```bash
  php example.php <sdk> <platform> swagger2             # use Swagger 2 spec
  SDK_GEN_SPEC_FILE=/path/to/spec.json php example.php  # use a local spec file
  ```
- **Platform mismatch:** Pass the right platform (`console`, `client`, `server`) as second arg — different platforms expose different API services
- **Child language gaps:** Adding a file to a parent's `getFiles()` but the child language needs a different template — child classes can override `getFiles()` to replace or remove entries

## Installing Dependencies

```bash
composer update --ignore-platform-reqs --optimize-autoloader --no-plugins --no-scripts --prefer-dist
```

## Running Tests

The tests in `tests/e2e/` generate an SDK from `tests/resources/spec-openapi3.json` into `tests/e2e/sdks/` and run it in Docker against a mock API. Parser behavior is tested by `utopia-php/openapi` rather than this repository. The mock server (`./mock-server`) is started in `setUp()` and removed in `tearDown()` (`docker compose down`); after interrupted runs, clean up with `cd mock-server && docker compose down`.

```bash
vendor/bin/phpunit tests/e2e/PHP83Test.php # one language e2e (needs Docker)
```

If local PHP is missing, is not the required version, or has extension issues, use the matching PHP Docker image as a fallback for that command.

## Pre-Submit Checklist

Before submitting changes that touch templates or language classes:

- [ ] Regenerated the affected SDK(s) with `example.php`
- [ ] Inspected `examples/<lang>/` output looks correct (gitignored — inspect the files directly, `git status` will not list them)
- [ ] Any new template files are listed in `getFiles()` of the language class
- [ ] Any new language class is added to `example.php`
- [ ] Rector check passes (`composer refactor:check`)
- [ ] Twig linter passes (`composer lint-twig`)
- [ ] If a parent language was modified, child SDKs were also checked
- [ ] If `Concern/CliCommandSurface.php` was touched, the CLI was regenerated and its e2e suite run
- [ ] CLI changes compile and pass their tests:
      `cd examples/cli && go build ./... && go vet ./... && go test ./...`
