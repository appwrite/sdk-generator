# ⚙️ Appwrite SDK Generator

[![Discord](https://img.shields.io/discord/564160730845151244?label=discord&style=flat-square)](https://appwrite.io/discord)
[![Twig Linting](https://github.com/appwrite/sdk-generator/actions/workflows/djlint.yml/badge.svg)](https://github.com/appwrite/sdk-generator/actions/workflows/djlint.yml)
[![X Account](https://img.shields.io/badge/follow-@appwrite-000000?style=flat-square&logo=x&logoColor=white)](https://x.com/appwrite)
[![appwrite.io](https://img.shields.io/badge/appwrite-.io-f02e65?style=flat-square)](https://appwrite.io)

[Appwrite](https://appwrite.io) SDK generator is a PHP library for auto-generating SDK libraries for multiple languages and platforms.

The SDK Generator uses predefined language settings as [Twig templates](https://twig.symfony.com/) to generate codebases based on different API specs.

Currently, the only spec supported is Swagger 2.0, but we intend to add support for more specifications in the near future. This generator is still lacking support for any definition/model specs.

## Getting Started

Install using composer:

**CLI**
```bash
composer update --ignore-platform-reqs --optimize-autoloader
```

**Docker (UNIX)**

```bash
docker run --rm --interactive --tty --volume "$(pwd)":/app composer install --ignore-platform-reqs
```

**Docker (Windows)**

```bash
docker run --rm --interactive --tty --volume "%cd%":/app composer install --ignore-platform-reqs
```

Create language and SDK instances and generate code to target directory.

```php
<?php

require_once 'vendor/autoload.php';

use Appwrite\Spec\Swagger2;
use Appwrite\SDK\SDK;
use Appwrite\SDK\Language\PHP;

// Read API specification file (Swagger 2) and create spec instance
$version = '1.9.x';
$platform = 'server';
$spec = new Swagger2(file_get_contents("https://raw.githubusercontent.com/appwrite/specs/main/specs/{$version}/swagger2-{$version}-{$platform}.json"));

// Create language instance
$lang = new PHP();

$lang // Set language or platform specific options
    ->setComposerPackage('my-api')
    ->setComposerVendor('my-company')
;

// Create the SDK object with the language and spec instances
$sdk  = new SDK($lang, $spec);

$sdk
    ->setLogo('https://appwrite.io/v1/images/console.png')
    ->setLicenseContent('License content here.')
    ->setVersion('v1.1.0')
;

$sdk->generate(__DIR__ . '/examples/php'); // Generate source code

```

For generated artifacts that do not need an API specification, use `StaticSpec`:

```php
<?php

require_once 'vendor/autoload.php';

use Appwrite\Spec\StaticSpec;
use Appwrite\SDK\SDK;
use Appwrite\SDK\Language\AgentSkills;

$spec = new StaticSpec(
    title: 'Appwrite',
    description: 'Appwrite backend as a service',
    version: '1.9.x',
    licenseName: 'BSD-3-Clause',
    licenseURL: 'https://raw.githubusercontent.com/appwrite/appwrite/master/LICENSE',
);

$sdk = new SDK(new AgentSkills(), $spec);

$sdk
    ->setName('Appwrite')
    ->setVersion('1.9.x')
;

$sdk->generate(__DIR__ . '/examples/agent-skills');
```

## Linting Twig Templates

This project uses [djLint](https://djlint.com/) to lint Twig template files for syntax and common issues.

**Note:** Formatting is disabled as it breaks code generation syntax. Only linting is used.

**Available command:**
```bash
composer lint-twig  # Check for linting errors
```

Requires [uv](https://github.com/astral-sh/uv) to be installed. Configuration is in `pyproject.toml`. The linter runs automatically on pull requests via GitHub Actions.

## Supported Specs

* [Swagger 2](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md)
* [OpenAPI 3](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md) (Not Ready)
* [RAML 1.0](https://raml.org/) (Not Ready)
* [RAML 0.8](https://raml.org/) (Not Ready)
* [Postman 2.0](https://schema.getpostman.com/json/collection/v2.0.0/docs/index.html) (Not Ready)
* [Postman 1.0](https://schema.getpostman.com/json/collection/v1.0.0/docs/index.html) (Not Ready)
* [API Blueprint 1A](https://github.com/apiaryio/api-blueprint/blob/master/API%20Blueprint%20Specification.md) (Not Ready)

## Generated SDKs and Artifacts

The primary generation targets are defined in `example.php`. Run it without arguments to generate every target with the default `console` platform spec, or pass a target and optional platform to generate one SDK:

```bash
php example.php
php example.php <target>
php example.php <target> <platform>
```

`<platform>` can be `console`, `client`, or `server`. If omitted, it defaults to `console`.

Examples:

```bash
php example.php web client
php example.php node server
php example.php cli console
php example.php agent-skills
```

### Client SDKs

| Target | Argument | Supported Versions | Coding Standards | Package Manager | Output |
|--------|----------|--------------------|------------------|-----------------|--------|
| Web | `web` | ES5+; Node.js >=18 for builds | [NPM Coding Style] | NPM | `examples/web/` |
| Flutter | `flutter` | Dart >=2.17 <4; Flutter stable | [Effective Dart] | pub | `examples/flutter/` |
| Apple | `apple` | iOS 15+, macOS 11+, watchOS 7+, tvOS 13+ | [Swift Style Guide] | Swift Package Manager | `examples/apple/` |
| Android | `android` | Android 5.0+; Java 17 in CI | [Android style guide] | Gradle, Maven | `examples/android/` |
| React Native | `react-native` | React Native >=0.76.7 <1.0.0; Node.js >=18 | [NPM Coding Style] | NPM | `examples/react-native/` |

### Server SDKs

| Target | Argument | Supported Versions | Coding Standards | Package Manager | Output |
|--------|----------|--------------------|------------------|-----------------|--------|
| Node.js | `node` | Node.js 20 in CI | [NPM Coding Style] | NPM | `examples/node/` |
| PHP | `php` | PHP >=8.2 | [PHP FIG] | Composer | `examples/php/` |
| Python | `python` | Python >=3.9 | [PEP8] | pip | `examples/python/` |
| Ruby | `ruby` | Ruby 3.1 in CI | [Ruby Style Guide] | RubyGems, Bundler | `examples/ruby/` |
| Dart | `dart` | Dart >=2.17 <4 | [Effective Dart] | pub | `examples/dart/` |
| Go | `go` | Go 1.22.5 | [Effective Go] | Go modules | `examples/go/` |
| Swift | `swift` | Swift 5.1+; Swift 5.9.2 in CI | [Swift Style Guide] | Swift Package Manager | `examples/swift/` |
| .NET | `dotnet` | .NET Standard 2.0; .NET Framework 4.6.2 | [C# Coding Conventions] | NuGet | `examples/dotnet/` |
| Kotlin | `kotlin` | JVM 1.8 target; Java 17 in CI | [Kotlin style guide] | Gradle, Maven | `examples/kotlin/` |
| Rust | `rust` | Rust >=1.83 | [Rust API Guidelines] | Cargo | `examples/rust/` |

### Tooling and Documentation

| Target | Argument | Supported Versions | Coding Standards | Package Manager | Output |
|--------|----------|--------------------|------------------|-----------------|--------|
| CLI | `cli` | Node.js 20 and Bun 1.3.11 in CI | [NPM Coding Style] | NPM, Bun, native binaries | `examples/cli/` |
| REST examples | `rest` | N/A | Markdown | N/A | `examples/REST/` |
| GraphQL | `graphql` | N/A | GraphQL | N/A | `examples/graphql/` |
| Markdown docs | `markdown` | N/A | Markdown | N/A | `examples/markdown/` |
| Agent Skills | `agent-skills` | N/A | Markdown | N/A | `examples/agent-skills/` |
| Cursor Plugin | `cursor-plugin` | N/A | Markdown | N/A | `examples/cursor-plugin/` |
| Claude Plugin | `claude-plugin` | N/A | Markdown | N/A | `examples/claude-plugin/` |

[PHP FIG]: https://www.php-fig.org/
[NPM Coding Style]: https://docs.npmjs.com/misc/coding-style
[Ruby Style Guide]: https://github.com/rubocop/ruby-style-guide
[PEP8]: https://peps.python.org/pep-0008/
[Effective Dart]: https://dart.dev/effective-dart/style
[Effective Go]: https://go.dev/doc/effective_go
[Swift Style Guide]: https://google.github.io/swift/
[C# Coding Conventions]: https://learn.microsoft.com/en-us/dotnet/csharp/fundamentals/coding-style/coding-conventions
[Kotlin style guide]: https://kotlinlang.org/docs/coding-conventions.html
[Android style guide]: https://developer.android.com/kotlin/style-guide
[Rust API Guidelines]: https://rust-lang.github.io/api-guidelines/

## Contributing

All code contributions, including those by people with commit access, must go through a pull request and be approved by a core developer before being merged. This is to ensure proper review of all the code.

We truly ❤️ pull requests! If you wish to help, you can learn more about how you can contribute to this project in the [contribution guide](CONTRIBUTING.md).

## Copyright and license

The MIT License (MIT) http://www.opensource.org/licenses/mit-license.php
