# Appwrite SDK Generator

[![Build Status](https://travis-ci.com/appwrite/sdk-generator.svg?branch=master)](https://travis-ci.com/appwrite/sdk-generator)
[![Discord](https://img.shields.io/discord/564160730845151244)](https://discord.gg/GSeTUeA)

**WORK IN PROGRESS - NOT READY FOR GENERAL USAGE**

[Appwrite](https://appwrite.io) SDK generator is a PHP library for auto generating SDK libraries for multiple languages and platforms.

The SDK Generator uses a predefined language settings as [Twig templates](https://twig.symfony.com/) to generate codebases based on different API specs.

Currently the only spec supported is Swagger 2.0, but we intend to add support for more specification in the near future. This generator is still lacking support for any definition/models specs.

## Getting Started

Install using composer:

**CLI**
```bash
composer update --ignore-platform-reqs --optimize-autoloader
```

**Docker (UNIX)**

```bash
docker run --rm --interactive --tty --volume "$(pwd)":/app composer install
```

**Docker (Windows)**

```bash
docker run --rm --interactive --tty --volume "%cd%":/app composer install
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

## Supported Specs

* [OpenAPI 3](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md) (Not Ready)
* [Swagger 2](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md)
* [RAML 1.0](https://raml.org/) (Not Ready)
* [RAML 0.8](https://raml.org/) (Not Ready)
* [Postman 2.0](https://schema.getpostman.com/json/collection/v2.0.0/docs/index.html) (Not Ready)
* [Postman 1.0](https://schema.getpostman.com/json/collection/v1.0.0/docs/index.html) (Not Ready)
* [API Blueprint 1A](https://github.com/apiaryio/api-blueprint/blob/master/API%20Blueprint%20Specification.md) (Not Ready)

## Supported Languages

| Language   | Supported Versions  |  Coding Standards      |  Package Manager   |   Maintainer   |
|------------|---------------------|------------------------|--------------------|----------------|
| Web        | ES5+                | [NPM Coding Style]     | NPM, Yarn,         | [@eldadfux]    |
| TypeScript |                     | [NPM Coding Style]     | NPM, Yarn          | [You?](https://github.com/appwrite/sdk-generator/issues/20)               |
| NodeJS     | 8, 10, 12           | [NPM Coding Style]     | NPM, Yarn          | [@eldadfux]    |
| PHP        | 7.0+                | [PHP FIG]              | Composer           | [@eldadfux]    |
| Ruby       | 2.4+                | [Ruby Style Guide]     | GEM                | [@eldadfux]    |
| Python     | 3.5+                | [PEP8]                 | PIP                | [@eldadfux]    |
| Flutter    |                     | [Effective Dart]       | pub tool           | [@bartektartanus] [@Almoullim]   |
| Dart       |                     | [Effective Dart]       | pub tool           | [You?](https://github.com/appwrite/sdk-generator/issues/20) |
| Go         |                     | [Effective Go]         | go get             | [@panz3r]      |
| .NET       | .NET core 3.1       | [C# Coding Conventions]| NuGet              | [@komemi] [@TorstenDittmann]     |
| D          |                     |                        | ?                  | [You?](https://github.com/appwrite/sdk-generator/issues/20) |
| Kotlin     |                     |                        | ?                  | [You?](https://github.com/appwrite/sdk-generator/issues/20) |
| Swift      |                     |                        | Swift Pkg Manager  | [@armino-dev] |

[@Almoullim]:           https://github.com/Almoullim
[@eldadfux]:            https://github.com/eldadfux
[@panz3r]:              https://github.com/panz3r
[@armino-dev]:          https://github.com/armino-dev
[@bartektartanus]:      https://github.com/bartektartanus
[@komemi]:              https://github.com/komemi
[@TorstenDittmann]:     https://github.com/TorstenDittmann

[PHP FIG]:              https://www.php-fig.org/
[NPM Coding Style]:     https://docs.npmjs.com/misc/coding-style
[NPM Coding Style]:     https://docs.npmjs.com/misc/coding-style
[Ruby Style Guide]:     https://github.com/rubocop-hq/ruby-style-guide
[PEP8]:                 https://www.python.org/dev/peps/pep-0008/
[Effective Dart]:       https://dart.dev/guides/language/effective-dart/style
[Effective Go]:         https://golang.org/doc/effective_go.html
[Swift Style Guide]:    https://google.github.io/swift/
[C# Coding Conventions]:https://docs.microsoft.com/en-us/dotnet/csharp/programming-guide/inside-a-program/coding-conventions

## Contributing

All code contributions - including those of people having commit access - must go through a pull request and approved by a core developer before being merged. This is to ensure proper review of all the code.

We truly ❤️ pull requests! If you wish to help, you can learn more about how you can contribute to this project in the [contribution guide](CONTRIBUTING.md).

## Copyright and license

The MIT License (MIT) http://www.opensource.org/licenses/mit-license.php
