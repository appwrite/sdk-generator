<?php

require_once 'vendor/autoload.php';

use Appwrite\Spec\Swagger2;
use Appwrite\SDK\SDK;
use Appwrite\SDK\Language\Rust;

// Read API specification file (Swagger 2) and create spec instance
$spec = new Swagger2(file_get_contents(__DIR__ . '/specs/swagger-appwrite-0.13.0.json'));

// Create language instance
$lang = new Rust();

$lang // Set language or platform specific options
    ->setPackageName('appwrite')
;

// Create the SDK object with the language and spec instances
$sdk  = new SDK($lang, $spec);

$sdk
    ->setLogo('https://appwrite.io/v1/images/console.png')
    ->setLicenseContent('License content here.')
    ->setVersion('1.1.0')
;

$sdk->generate(__DIR__ . '/examples/rust'); // Generate source code