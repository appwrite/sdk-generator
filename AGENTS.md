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
| `Node` | `CLI`, `ReactNative` |
| `Dart` | `Flutter` |
| `Swift` | `Apple` |
| `Kotlin` | `Android` |

Modifying a parent's template or `getFiles()` affects all children. Regenerate and verify child SDKs too.

### Rule 4: `copy` scope = no Twig processing

Files with `'scope' => 'copy'` are copied verbatim — no variable substitution happens. If your new file needs template variables, use `'scope' => 'default'` (or `service`, `method`, etc.).

### Rule 5: Destination paths are also Twig templates

The `destination` string in each `getFiles()` entry supports Twig expressions and filters:
```php
'destination' => 'src/Services/{{ service.name | caseCamel }}.php',
```

## Repository at a Glance

- **Purpose:** Generate Appwrite SDKs for ~16 languages from Swagger/OpenAPI specs using Twig templates
- **Language:** PHP (generator engine) + Twig (templates)
- **Entry point:** `example.php` — runs generation for all or a specific SDK
- **Output:** `examples/<lang>/` — checked-in generated SDK output for verification

```
src/SDK/Language/<Lang>.php   ← Language class: defines files, types, keywords
templates/<lang>/             ← Twig templates for that language
examples/<lang>/              ← Generated SDK output (checked in for verification)
example.php                   ← Entry point: regenerates all SDKs from specs
```

**Supported SDKs:** PHP, Web, Node, CLI, Ruby, Python, Dart, Flutter, React Native, Go, Swift, Apple, DotNet, Android, Kotlin, GraphQL, Markdown, AgentSkills

## Primary Workflows

### Modifying an Existing SDK Template

1. Edit template(s) in `templates/<lang>/`
2. Regenerate:
   ```bash
   docker run --rm -v $(pwd):/app -w /app php:8.3-cli php example.php <lang>
   ```
3. Diff `examples/<lang>/` to verify the output is correct
4. Run linter:
   ```bash
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
            'scope'       => 'default',   // default|service|method|definition|requestModel|enum|copy
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

3. Regenerate and verify

### Adding a New Language SDK

1. Create `src/SDK/Language/NewLang.php` (extend `Language` or a related language)
2. Implement: `getName()`, `getKeywords()`, `getIdentifierOverrides()`, `getFiles()`, `getTypeName()`, `getParamDefault()`, `getParamExample()`
3. Create `templates/newlang/` and add all Twig files
4. Register all template files in `getFiles()`
5. Add generation block to `example.php`
6. Generate: `docker run --rm -v $(pwd):/app -w /app php:8.3-cli php example.php newlang`
7. Inspect `examples/newlang/`

## File Reference Map

| What you want to change | Where to look |
|------------------------|---------------|
| Template for a language | `templates/<lang>/` |
| Which files get generated | `src/SDK/Language/<Lang>.php` → `getFiles()` |
| Type mappings for a language | `src/SDK/Language/<Lang>.php` → `getTypeName()` |
| Available Twig filters | `src/SDK/SDK.php` (around line 62) |
| How specs are parsed | `src/Spec/Swagger2.php` |
| Generation orchestration | `src/SDK/SDK.php` → `generate()` |
| Example generation script | `example.php` |
| Generated output for review | `examples/<lang>/` |

## Available SDK Names for `example.php`

Pass as first argument to generate only that SDK:

| Argument | Language class | Output dir |
|----------|---------------|------------|
| `php` | PHP | `examples/php/` |
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
| `android` | Android | `examples/android/` |
| `kotlin` | Kotlin | `examples/kotlin/` |
| `graphql` | GraphQL | `examples/graphql/` |
| `markdown` | Markdown | `examples/markdown/` |

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
- **Spec fetch failure:** `example.php` requires internet access to fetch the live spec from GitHub; generation fails with an exception if the fetch returns empty. Spec URL pattern:
  ```
  https://raw.githubusercontent.com/appwrite/specs/main/specs/{version}/swagger2-{version}-{platform}.json
  ```
- **Platform mismatch:** Pass the right platform (`console`, `client`, `server`) as second arg — different platforms expose different API services
- **Child language gaps:** Adding a file to a parent's `getFiles()` but the child language needs a different template — child classes can override `getFiles()` to replace or remove entries

## Installing Dependencies

```bash
# With Composer installed locally
composer update --ignore-platform-reqs --optimize-autoloader --no-plugins --no-scripts --prefer-dist

# With Docker
docker run --rm -it -v "$(pwd)":/app composer update --ignore-platform-reqs --optimize-autoloader --no-plugins --no-scripts --prefer-dist
```

## Running Tests

```bash
docker run --rm -v $(pwd):$(pwd):rw -w $(pwd) php:8.3-cli-alpine vendor/bin/phpunit
```

## Pre-Submit Checklist

Before submitting changes that touch templates or language classes:

- [ ] Regenerated the affected SDK(s) with `example.php`
- [ ] Inspected `examples/<lang>/` output looks correct
- [ ] Any new template files are listed in `getFiles()` of the language class
- [ ] Any new language class is added to `example.php`
- [ ] Twig linter passes (`composer lint-twig`)
- [ ] If a parent language was modified, child SDKs were also checked
