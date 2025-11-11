<?php

include_once 'vendor/autoload.php';

use Appwrite\SDK\Language\GraphQL;
use Appwrite\SDK\Language\KMP;
use Appwrite\Spec\Swagger2;
use Appwrite\SDK\SDK;
use Appwrite\SDK\Language\Web;
use Appwrite\SDK\Language\Node;
use Appwrite\SDK\Language\CLI;
use Appwrite\SDK\Language\PHP;
use Appwrite\SDK\Language\Python;
use Appwrite\SDK\Language\Ruby;
use Appwrite\SDK\Language\Dart;
use Appwrite\SDK\Language\Go;
use Appwrite\SDK\Language\REST;
use Appwrite\SDK\Language\Swift;
use Appwrite\SDK\Language\Apple;
use Appwrite\SDK\Language\DotNet;
use Appwrite\SDK\Language\Flutter;
use Appwrite\SDK\Language\Android;
use Appwrite\SDK\Language\Kotlin;
use Appwrite\SDK\Language\ReactNative;

try {

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

    function configureSDK($sdk, $overrides = []) {
        $defaults = [
            'name' => 'NAME',
            'version' => '0.0.0',
            'description' => 'Repo description goes here',
            'shortDescription' => 'Repo short description goes here',
            'url' => 'https://example.com',
            'logo' => 'https://appwrite.io/images/logos/logo.svg',
            'licenseContent' => 'test test test',
            'warning' => '**WORK IN PROGRESS - NOT READY FOR USAGE**',
            'changelog' => '**CHANGELOG**',
            'gitUserName' => 'repoowner',
            'gitRepoName' => 'reponame',
            'twitter' => 'appwrite',
            'discord' => ['564160730845151244', 'https://appwrite.io/discord'],
            'defaultHeaders' => ['X-Appwrite-Response-Format' => '1.6.0'],
            'readme' => '**README**',
        ];

        $config = array_merge($defaults, $overrides);

        $sdk->setName($config['name'])
            ->setVersion($config['version'])
            ->setDescription($config['description'])
            ->setShortDescription($config['shortDescription'])
            ->setURL($config['url'])
            ->setLogo($config['logo'])
            ->setLicenseContent($config['licenseContent'])
            ->setWarning($config['warning'])
            ->setChangelog($config['changelog'])
            ->setGitUserName($config['gitUserName'])
            ->setGitRepoName($config['gitRepoName'])
            ->setTwitter($config['twitter'])
            ->setDiscord($config['discord'][0], $config['discord'][1])
            ->setDefaultHeaders($config['defaultHeaders'])
            ->setReadme($config['readme']);

        if (isset($config['namespace'])) {
            $sdk->setNamespace($config['namespace']);
        }
        if (isset($config['exclude'])) {
            $sdk->setExclude($config['exclude']);
        }

        return $sdk;
    }

    $requestedSdk = isset($argv[1]) ? $argv[1] : null;
    $requestedPlatform = isset($argv[2]) ? $argv[2] : null;

    if ($requestedPlatform) {
        $platform = $requestedPlatform;
    } else {
        $platform = 'console';
        // $platform = 'client';
        // $platform = 'server';
    }

    $version = '1.8.x';
    $spec = getSSLPage("https://raw.githubusercontent.com/appwrite/appwrite/{$version}/app/config/specs/swagger2-{$version}-{$platform}.json");

    if(empty($spec)) {
        throw new Exception('Failed to fetch spec from Appwrite server');
    }

    if ($requestedSdk) {
        echo "Generating SDK: $requestedSdk (platform: $platform)\n";
    }

    // PHP
    if (!$requestedSdk || $requestedSdk === 'php') {
        $php = new PHP();
        $php
            ->setComposerVendor('appwrite')
            ->setComposerPackage('appwrite');
        $sdk  = new SDK($php, new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/php');
    }

    // Web
    if (!$requestedSdk || $requestedSdk === 'web') {
        $sdk  = new SDK(new Web(), new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/web');
    }

    // Node
    if (!$requestedSdk || $requestedSdk === 'node') {
        $sdk  = new SDK(new Node(), new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/node');
    }

    // CLI
    if (!$requestedSdk || $requestedSdk === 'cli') {
        $language  = new CLI();
        $language->setNPMPackage('appwrite-cli');
        $language->setExecutableName('appwrite');
        $language->setLogo(json_encode("
    _                            _ _           ___   __   _____
   /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
  //_\\\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
 /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_
 \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/
       |_|   |_|

"));
        $language->setLogoUnescaped("
     _                            _ _           ___   __   _____
    /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
   //_\\\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
  /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_
  \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/
        |_|   |_|                                                ");

        $sdk  = new SDK($language, new Swagger2($spec));
        configureSDK($sdk, [
            'exclude' => [
                'services' => [
                    ['name' => 'assistant'],
                    ['name' => 'avatars'],
                ],
            ],
        ]);

        $sdk->generate(__DIR__ . '/examples/cli');
    }

    // Ruby
    if (!$requestedSdk || $requestedSdk === 'ruby') {
        $sdk  = new SDK(new Ruby(), new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/ruby');
    }

    // Python
    if (!$requestedSdk || $requestedSdk === 'python') {
        $sdk  = new SDK(new Python(), new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/python');
    }

    // Dart
    if (!$requestedSdk || $requestedSdk === 'dart') {
        $dart = new Dart();
        $dart->setPackageName('dart_appwrite');
        $sdk  = new SDK($dart, new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/dart');
    }

    // Flutter
    if (!$requestedSdk || $requestedSdk === 'flutter') {
        $flutter = new Flutter();
        $flutter->setPackageName('appwrite');
        $sdk  = new SDK($flutter, new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/flutter');
    }

    // React Native
    if (!$requestedSdk || $requestedSdk === 'react-native') {
        $reactNative = new ReactNative();
        $reactNative->setNPMPackage('react-native-appwrite');
        $sdk  = new SDK($reactNative, new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/react-native');
    }

    // GO
    if (!$requestedSdk || $requestedSdk === 'go') {
        $sdk  = new SDK(new Go(), new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/go');
    }

    // Swift
    if (!$requestedSdk || $requestedSdk === 'swift') {
        $sdk  = new SDK(new Swift(), new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/swift');
    }

    // Apple
    if (!$requestedSdk || $requestedSdk === 'apple') {
        $sdk  = new SDK(new Apple(), new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/apple');
    }

    // DotNet
    if (!$requestedSdk || $requestedSdk === 'dotnet') {
        $sdk  = new SDK(new DotNet(), new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/dotnet');
    }

    // REST
    if (!$requestedSdk || $requestedSdk === 'rest') {
        $sdk  = new SDK(new REST(), new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/REST');
    }

    // Android
    if (!$requestedSdk || $requestedSdk === 'android') {
        $sdk = new SDK(new Android(), new Swagger2($spec));
        configureSDK($sdk, [
            'namespace' => 'io.appwrite',
        ]);
        $sdk->generate(__DIR__ . '/examples/android');
    }

    // KMP
    if (!$requestedSdk || $requestedSdk === 'kmp') {
        $sdk = new SDK(new KMP(), new Swagger2($spec));
        configureSDK($sdk, [
            'namespace' => 'io.appwrite',
        ]);
        $sdk->generate(__DIR__ . '/examples/kmp');
    }

    // Kotlin
    if (!$requestedSdk || $requestedSdk === 'kotlin') {
        $sdk = new SDK(new Kotlin(), new Swagger2($spec));
        configureSDK($sdk, [
            'namespace' => 'io.appwrite',
        ]);
        $sdk->generate(__DIR__ . '/examples/kotlin');
    }

    // GraphQL
    if (!$requestedSdk || $requestedSdk === 'graphql') {
        $sdk = new SDK(new GraphQL(), new Swagger2($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/graphql');
    }
}
catch (Exception $exception) {
    echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
}
catch (Throwable $exception) {
    echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
}

echo "Example SDKs generated successfully\n";
