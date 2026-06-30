<?php

include_once __DIR__ . '/vendor/autoload.php';

use Appwrite\SDK\Language\GraphQL;
use Appwrite\Spec\OpenAPI3;
use Appwrite\Spec\Spec;
use Appwrite\Spec\Swagger2;
use Appwrite\Spec\StaticSpec;
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
use Appwrite\SDK\Language\Unity;
use Appwrite\SDK\Language\Skills;
use Appwrite\SDK\Language\ClaudePlugin;
use Appwrite\SDK\Language\CodexPlugin;
use Appwrite\SDK\Language\CursorPlugin;
use Appwrite\SDK\Language\Rust;

final class Config
{
    public const string VERSION = '1.9.x';
    public const string SPECS_URL = 'https://raw.githubusercontent.com/appwrite/specs/main/specs';
    public const string TITLE = 'Appwrite';
    public const string DESCRIPTION = 'Appwrite backend as a service';
    public const string LICENSE_NAME = 'BSD-3-Clause';
    public const string LICENSE_URL = 'https://raw.githubusercontent.com/appwrite/appwrite/master/LICENSE';
    public const string COVER_IMAGE = 'https://github.com/appwrite/appwrite/raw/main/public/images/github.png';
    public const string TWITTER = 'appwrite';
    public const string DISCORD_CHANNEL = '564160730845151244';
    public const string DISCORD_URL = 'https://appwrite.io/discord';
}

try {

    function getSSLPage(string $url): bool|string {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return curl_exec($ch);
    }

    function configureSDK(SDK $sdk, array $overrides = []): SDK {
        $defaults = [
            'name' => 'NAME',
            'version' => '0.0.0',
            'description' => 'Repo description goes here',
            'shortDescription' => 'Repo short description goes here',
            'url' => 'https://example.com',
            'coverImage' => Config::COVER_IMAGE,
            'licenseContent' => 'test test test',
            'warning' => '**WORK IN PROGRESS - NOT READY FOR USAGE**',
            'changelog' => '**CHANGELOG**',
            'gitUserName' => 'repoowner',
            'gitRepoName' => 'reponame',
            'twitter' => Config::TWITTER,
            'discord' => [Config::DISCORD_CHANNEL, Config::DISCORD_URL],
            'readme' => '**README**',
            'exclude' => [
                'services' => [
                    ['name' => 'documentsDB'],
                    ['name' => 'vectorsDB'],
                ],
            ],
        ];

        // Deep-merge exclude services so overrides add to defaults rather than replacing
        if (isset($overrides['exclude']['services']) && isset($defaults['exclude']['services'])) {
            $overrides['exclude']['services'] = [
                ...$defaults['exclude']['services'],
                ...$overrides['exclude']['services'],
            ];
        }

        $config = [...$defaults, ...$overrides];

        $sdk->setName($config['name'])
            ->setVersion($config['version'])
            ->setDescription($config['description'])
            ->setShortDescription($config['shortDescription'])
            ->setURL($config['url'])
            ->setCoverImage($config['coverImage'])
            ->setLicenseContent($config['licenseContent'])
            ->setWarning($config['warning'])
            ->setChangelog($config['changelog'])
            ->setGitUserName($config['gitUserName'])
            ->setGitRepoName($config['gitRepoName'])
            ->setTwitter($config['twitter'])
            ->setDiscord($config['discord'][0], $config['discord'][1])
            ->setReadme($config['readme']);

        if (isset($config['namespace'])) {
            $sdk->setNamespace($config['namespace']);
        }
        if (isset($config['exclude'])) {
            $sdk->setExclude($config['exclude']);
        }
        if (isset($config['platform'])) {
            $sdk->setPlatform($config['platform']);
        }

        return $sdk;
    }

    function buildSpec(string $format, string $content): Spec {
        return $format === 'swagger2' ? new Swagger2($content) : new OpenAPI3($content);
    }

    function buildStaticSpec(): StaticSpec {
        return new StaticSpec(
            title: Config::TITLE,
            description: Config::DESCRIPTION,
            version: Config::VERSION,
            licenseName: Config::LICENSE_NAME,
            licenseURL: Config::LICENSE_URL,
        );
    }

    $requestedSdk = $argv[1] ?? null;
    $requestedPlatform = $argv[2] ?? null;
    $requestedFormat = $argv[3] ?? null;

    $platform = $requestedPlatform ?: 'console';
    // $platform = 'client';
    // $platform = 'server';

    // Spec format: 'openapi3' (default) or 'swagger2', e.g. php example.php node console swagger2
    $specFormat = strtolower($requestedFormat ?: 'openapi3');
    if (!in_array($specFormat, ['openapi3', 'swagger2'])) {
        throw new Exception("Unsupported spec format: $specFormat (expected 'openapi3' or 'swagger2')");
    }

    $version = Config::VERSION;
    $sdkTargets = [
        'php',
        'unity',
        'web',
        'node',
        'cli',
        'ruby',
        'python',
        'dart',
        'flutter',
        'react-native',
        'go',
        'swift',
        'apple',
        'dotnet',
        'rest',
        'android',
        'kotlin',
        'graphql',
        'skills',
        'cursor-plugin',
        'claude-plugin',
        'codex-plugin',
        'rust',
    ];
    if ($requestedSdk && !in_array($requestedSdk, $sdkTargets)) {
        throw new Exception("Unsupported SDK target: $requestedSdk");
    }

    $speclessSDKs = ['skills', 'cursor-plugin', 'claude-plugin', 'codex-plugin'];
    $needsSpec = !$requestedSdk || !in_array($requestedSdk, $speclessSDKs);
    $spec = '';

    if ($needsSpec) {
        // Optional local spec file override, e.g. SDK_GEN_SPEC_FILE=/path/to/spec.json
        $specFile = getenv('SDK_GEN_SPEC_FILE');

        if ($specFile) {
            $spec = file_get_contents($specFile);
        } else {
            $specPrefix = $specFormat === 'swagger2' ? 'swagger2' : 'open-api3';
            $spec = getSSLPage(Config::SPECS_URL . "/{$version}/{$specPrefix}-{$version}-{$platform}.json");
        }

        if(empty($spec)) {
            throw new Exception('Failed to fetch spec from Appwrite server');
        }
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
        $sdk  = new SDK($php, buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/php');
    }

    // Unity
    if (!$requestedSdk || $requestedSdk === 'unity') {
        $unity = new Unity();
        $unity->setPackageName('io.appwrite.unity');
        $sdk  = new SDK($unity, buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/unity');
    }

    // Web
    if (!$requestedSdk || $requestedSdk === 'web') {
        $sdk  = new SDK(new Web(), buildSpec($specFormat, $spec));
        configureSDK($sdk, ['platform' => $platform]);
        $sdk->generate(__DIR__ . '/examples/web');
    }

    // Node
    if (!$requestedSdk || $requestedSdk === 'node') {
        $sdk  = new SDK(new Node(), buildSpec($specFormat, $spec));
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

        $sdk  = new SDK($language, buildSpec($specFormat, $spec));
        $sdk->setTest(false);
        configureSDK($sdk, [
            'exclude' => [
                'services' => [
                    ['name' => 'assistant'],
                    ['name' => 'avatars'],
                    ['name' => 'advisor'],
                    ['name' => 'compute'],
                    ['name' => 'apps'],
                    ['name' => 'oauth'],
                ],
            ],
        ]);

        $sdk->generate(__DIR__ . '/examples/cli');
    }

    // Ruby
    if (!$requestedSdk || $requestedSdk === 'ruby') {
        $sdk  = new SDK(new Ruby(), buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/ruby');
    }

    // Python
    if (!$requestedSdk || $requestedSdk === 'python') {
        $sdk  = new SDK(new Python(), buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/python');
    }

    // Dart
    if (!$requestedSdk || $requestedSdk === 'dart') {
        $dart = new Dart();
        $dart->setPackageName('dart_appwrite');
        $sdk  = new SDK($dart, buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/dart');
    }

    // Flutter
    if (!$requestedSdk || $requestedSdk === 'flutter') {
        $flutter = new Flutter();
        $flutter->setPackageName('appwrite');
        $sdk  = new SDK($flutter, buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/flutter');
    }

    // React Native
    if (!$requestedSdk || $requestedSdk === 'react-native') {
        $reactNative = new ReactNative();
        $reactNative->setNPMPackage('react-native-appwrite');
        $sdk  = new SDK($reactNative, buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/react-native');
    }

    // GO
    if (!$requestedSdk || $requestedSdk === 'go') {
        $sdk  = new SDK(new Go(), buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/go');
    }

    // Swift
    if (!$requestedSdk || $requestedSdk === 'swift') {
        $sdk  = new SDK(new Swift(), buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/swift');
    }

    // Apple
    if (!$requestedSdk || $requestedSdk === 'apple') {
        $sdk  = new SDK(new Apple(), buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/apple');
    }

    // DotNet
    if (!$requestedSdk || $requestedSdk === 'dotnet') {
        $sdk  = new SDK(new DotNet(), buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/dotnet');
    }

    // REST
    if (!$requestedSdk || $requestedSdk === 'rest') {
        $sdk  = new SDK(new REST(), buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/REST');
    }

    // Android
    if (!$requestedSdk || $requestedSdk === 'android') {
        $sdk = new SDK(new Android(), buildSpec($specFormat, $spec));
        configureSDK($sdk, [
            'namespace' => 'io.appwrite',
        ]);
        $sdk->generate(__DIR__ . '/examples/android');
    }

    // Kotlin
    if (!$requestedSdk || $requestedSdk === 'kotlin') {
        $sdk = new SDK(new Kotlin(), buildSpec($specFormat, $spec));
        configureSDK($sdk, [
            'namespace' => 'io.appwrite',
        ]);
        $sdk->generate(__DIR__ . '/examples/kotlin');
    }

    // GraphQL
    if (!$requestedSdk || $requestedSdk === 'graphql') {
        $sdk = new SDK(new GraphQL(), buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/graphql');
    }

    // Skills
    if (!$requestedSdk || $requestedSdk === 'skills') {
        $sdk = new SDK(new Skills(), buildStaticSpec());
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/skills');
    }

    // Cursor Plugin
    if (!$requestedSdk || $requestedSdk === 'cursor-plugin') {
        $sdk = new SDK(new CursorPlugin(), buildStaticSpec());
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/cursor-plugin');
    }

    // Claude Plugin
    if (!$requestedSdk || $requestedSdk === 'claude-plugin') {
        $sdk = new SDK(new ClaudePlugin(), buildStaticSpec());
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/claude-plugin');
    }

    // Codex Plugin
    if (!$requestedSdk || $requestedSdk === 'codex-plugin') {
        $sdk = new SDK(new CodexPlugin(), buildStaticSpec());
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/codex-plugin');
    }

    // Rust
    if (!$requestedSdk || $requestedSdk === 'rust') {
        $sdk = new SDK(new Rust(), buildSpec($specFormat, $spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/rust');
    }
}
catch (Throwable $exception) {
    echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
    exit(1);
}

echo "Example SDKs generated successfully\n";
