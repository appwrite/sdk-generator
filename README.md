# Appwrite SDK Generator

**WORK IN PROGRESS - NOT READY FOR USAGE**

[Appwrite](https://appwrite.io) SDK generator is a PHP library for auto generating SDK library for multiple languages or platforms.

The SDK uses predefined language settings and [Twig templates](https://twig.symfony.com/) to generate code base on different API specs.

Currently the only spec supported is Swagger 2.0, but we intend to add support for more specification in the near future. 

## Examples

You can view examples of generated code libraries in the [examples](examples) directory.

## Getting Started

Install using composer:
```bash
composer require appwrite/sdk-generator
```

Create language and SDK instances and generate code to target directory.

```php
<?php

require_once 'vendor/autoload.php';

use Appwrite\Spec\Swagger2;
use Appwrite\SDK\SDK;
use Appwrite\SDK\Language\PHP;

// Read API specification file (Swagger 2) anc create spec instance
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

| Language   |  Coding Standards   |  Package Manager   |   Maintainer   |
|------------|---------------------|--------------------|----------------|
| PHP        | [PHP FIG]           | Composer           | [@eldadfux]    |
| Javascript | [NPM Coding Style]  | NPM, Yarn, Bower   | [@eldadfux]    |
| NodeJS     | [NPM Coding Style]  | NPM, Yarn          | [@eldadfux]    |
| Ruby       | [Ruby Style Guide]  | GEM                | [@eldadfux]    |
| Python     | [PEP8]              | PIP                | [@eldadfux]    |
| Dart       | [Effective Dart]    | pub tool           | [@Almoullim]   |
| Kotlin     |                     | ?                  |                |
| Swift      |                     | ?                  |                |

[@Almoullim]:       https://github.com/Almoullim
[@eldadfux]:        https://github.com/eldadfux

[PHP FIG]:          https://www.php-fig.org/
[NPM Coding Style]: https://docs.npmjs.com/misc/coding-style
[NPM Coding Style]: https://docs.npmjs.com/misc/coding-style
[Ruby Style Guide]: https://github.com/rubocop-hq/ruby-style-guide
[PEP8]:             https://www.python.org/dev/peps/pep-0008/
[Effective Dart]:   https://dart.dev/guides/language/effective-dart/style


## Development

```bash
composer update --ignore-platform-reqs --optimize-autoloader
```

## TODO List

* Better spec modeling
* Spec Validators
* XML Content Types
    
## SDK Checklist

It is very important for us to create consistent structure, architecture and native like feel for the SDKs we generate.
In order to accomplish that we have made a checklist of points to support while adding a new language to the SDK generator.

The checklist aims to balance consistency among languages, and follow each platform's best practices and coding standards.

* Proper Coding Standards and Conventions
* Proper Skeleton Structure
* Readme Doc
* HTTP Client class or object
    * Client Setters
        * Set Auth Keys Method
        * Set Basic Auth Method
        * Set OAuth Dialog Method
        * Set Endpoint Method
        * Set Self Signed Certificates
    * Default Headers
        * 'appwrite-sdk-version' header
        * Add 'User-Agent' header with server name and language version (ubuntu-18.02:php-7.0.1)
    * Methods
        * addHeader(key, value)
        * call(method, path = '', headers = [], params = [])
            * Concat GET params to path
            * Parse request params by content type header
            * Parse response params by content type header
            * Throw error on bad response
* Service Abstraction (optional)
    * Constructor receiving an instance of the client class 
* Service Class
    * Headers Support (Content Type)
    * Parameters Support
        * Default Values Support
        * Required Values Support
        * String Support
        * Integer Support
        * Boolean Support
        * Files Support (+array file and multiple header support and params flatten)
        * Arrays / Dictionaries / Lists Support (+concatenation type)
* Usage Example Docs
* Definitions / Models Classes - with setters and getters

## Contributing

All code contributions - including those of people having commit access - must go through a pull request and approved by a core developer before being merged. This is to ensure proper review of all the code.

Fork the project, create a feature branch, and send us a pull request.

## Copyright and license

The MIT License (MIT) http://www.opensource.org/licenses/mit-license.php
