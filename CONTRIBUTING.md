# Contributing

We would ❤️ for you to contribute to Appwrite and help make it better! As a contributor, here are the guidelines we would like you to follow:

## Code of Conduct

Help us keep Appwrite open and inclusive. Please read and follow our [Code of Conduct](/CODE_OF_CONDUCT.md).

## Creating Language Class

First, create a new class for the new language in this directory: https://github.com/appwrite/sdk-generator/tree/master/src/SDK/Language

You can use the interface to know which methods are required to be implemented:
https://github.com/appwrite/sdk-generator/blob/master/src/SDK/Language.php

**getName**
SDK Language name (JS, PHP…)

**getKeywords**
An array with language keywords to avoid using as param or function names, template engine will solve conflicts

**getFiles**
An array with a list of language template files in [twig format](https://twig.symfony.com/). 
Each file scope determines what template parameters will be available.

* default scope - Basic SDK and language-specific params (package name, language name, etc…)
* service scope - Generate x templates where x is the number of API services, adds service-specific params to the template (service name, methods, etc…)
* method scope - Generate x*y templates where x is the number of API services and y is the number of methods, adds service and method-specific params to the template (service name, method name, method params, etc…), good for generating MD files with examples for using each method

**getTypeName**
This method receives the API param type and should return the equivalent param in the implemented language.

**getParamDefault**
This method receives the API param and should return the equivalent default value of param in the implemented language, for example, a default array param in PHP is represented as [].

**getParamExample**
This method receives the API param and should return the equivalent example value of param in the implemented language, for example, if an example value is **some text** in PHP return value should be **'some text'** (with quotes).

Notice: The easiest way to get started is to copy an existing language class close to the new language about to be implemented and just edit it.

## Adding Templates

Add your new templates as listed in your language class **getFiles** method. Make sure to follow the [checklist](https://github.com/appwrite/sdk-generator#sdk-checklist) when building the language templates.

Make sure to follow the objects structure and service separation architecture. We aim to keep developer experience as consistent as possible across different SDKs to make the learning curve as small as possible.

> Appwrite SDK generator adds some filters to the TWIG templates to allow common code fotmatting options like converting text to camelCase, dash-case and other. Full list is available in the [SDK class](https://github.com/appwrite/sdk-generator/blob/master/src/SDK/SDK.php#L62)

When in need to test the API templates output, add your new language instance to the example.php file like this:

sdk-generator/blob/master/example.php:

```php
    use Appwrite\Spec\Swagger2;
    use Appwrite\SDK\SDK;
    use Appwrite\SDK\Language\NewLang;
    
    function getSSLPage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    $spec = getSSLPage('https://appwrite.io/v1/open-api-2.json?extensions=1');

    // NewLang
    $sdk  = new SDK(new NewLang(), new Swagger2($spec));
    $sdk
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
    ;
    $sdk->generate(__DIR__ . '/examples/new-lang');
```

Run the following command:

```bash
php example.php
```

>Note: Make sure to have PHP CLI installed on your host. You can just add the new language next to the other languages in example.php file, no need to rewrite the file completely.

Check your output files at: /examples/new-lang and make sure the SDK works. When possible add some unit tests.