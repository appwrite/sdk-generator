# ⚙️ Appwrite SDK Generator

[![Discord](https://img.shields.io/discord/564160730845151244?label=discord&style=flat-square)](https://appwrite.io/discord)
[![CI](https://github.com/appwrite/sdk-generator/actions/workflows/tests.yml/badge.svg)](https://github.com/appwrite/sdk-generator/actions/workflows/tests.yml)
[![Twig Linting](https://github.com/appwrite/sdk-generator/actions/workflows/djlint.yml/badge.svg)](https://github.com/appwrite/sdk-generator/actions/workflows/djlint.yml)
[![X Account](https://img.shields.io/badge/follow-@appwrite-000000?style=flat-square&logo=x&logoColor=white)](https://x.com/appwrite)
[![appwrite.io](https://img.shields.io/badge/appwrite-.io-f02e65?style=flat-square)](https://appwrite.io)

**WORK IN PROGRESS - NOT READY FOR GENERAL USAGE**

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
$spec = new Swagger2(file_get_contents('https://appwrite.io/v1/open-api-2.json?extension=1'));

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

The primary generation targets are defined in `example.php`. Pass the argument below to generate a single target:

```bash
php example.php <argument>
```

### Client SDKs

| Target | Argument | Output |
|--------|----------|--------|
| Web | `web` | `examples/web/` |
| Flutter | `flutter` | `examples/flutter/` |
| Apple | `apple` | `examples/apple/` |
| Android | `android` | `examples/android/` |
| React Native | `react-native` | `examples/react-native/` |

### Server SDKs

| Target | Argument | Output |
|--------|----------|--------|
| Node.js | `node` | `examples/node/` |
| PHP | `php` | `examples/php/` |
| Python | `python` | `examples/python/` |
| Ruby | `ruby` | `examples/ruby/` |
| Dart | `dart` | `examples/dart/` |
| Go | `go` | `examples/go/` |
| Swift | `swift` | `examples/swift/` |
| .NET | `dotnet` | `examples/dotnet/` |
| Kotlin | `kotlin` | `examples/kotlin/` |
| Rust | `rust` | `examples/rust/` |

### Tooling and Documentation

| Target | Argument | Output |
|--------|----------|--------|
| CLI | `cli` | `examples/cli/` |
| REST examples | `rest` | `examples/REST/` |
| GraphQL | `graphql` | `examples/graphql/` |
| Markdown docs | `markdown` | `examples/markdown/` |
| Agent Skills | `agent-skills` | `examples/agent-skills/` |
| Cursor Plugin | `cursor-plugin` | `examples/cursor-plugin/` |
| Claude Plugin | `claude-plugin` | `examples/claude-plugin/` |

## Contributing

All code contributions, including those by people with commit access, must go through a pull request and be approved by a core developer before being merged. This is to ensure proper review of all the code.

We truly ❤️ pull requests! If you wish to help, you can learn more about how you can contribute to this project in the [contribution guide](CONTRIBUTING.md).

## Copyright and license

The MIT License (MIT) http://www.opensource.org/licenses/mit-license.php
