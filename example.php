<?php

include_once __DIR__ . '/vendor/autoload.php';

use Appwrite\SDK\Language\GraphQL;
use Appwrite\SDK\SDK;
use Utopia\OpenAPI\Parser;
use Utopia\OpenAPI\Specification;
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
use Appwrite\SDK\Language\ZedExtension;

final class Config
{
    public const string VERSION = '2.0.x';
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
            'license' => 'BSD-3-Clause',
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
            ->setLicense($config['license'])
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

    function buildSpecification(string $content): Specification {
        return Parser::parse($content);
    }

    function buildStaticSpecification(): Specification {
        return Parser::parse([
            'openapi' => '3.0.0',
            'info' => [
                'title' => Config::TITLE,
                'description' => Config::DESCRIPTION,
                'version' => Config::VERSION,
                'license' => [
                    'name' => Config::LICENSE_NAME,
                    'url' => Config::LICENSE_URL,
                ],
            ],
            'paths' => [],
        ]);
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
        'zed-extension',
        'rust',
    ];
    if ($requestedSdk && !in_array($requestedSdk, $sdkTargets)) {
        throw new Exception("Unsupported SDK target: $requestedSdk");
    }

    $speclessSDKs = ['skills', 'cursor-plugin', 'claude-plugin', 'codex-plugin', 'zed-extension'];
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
        $sdk  = new SDK($php, buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/php');
    }

    // Unity
    if (!$requestedSdk || $requestedSdk === 'unity') {
        $unity = new Unity();
        $unity->setPackageName('io.appwrite.unity');
        $sdk  = new SDK($unity, buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/unity');
    }

    // Web
    if (!$requestedSdk || $requestedSdk === 'web') {
        $sdk  = new SDK(new Web(), buildSpecification($spec));
        configureSDK($sdk, ['platform' => $platform]);
        $sdk->generate(__DIR__ . '/examples/web');
    }

    // Node
    if (!$requestedSdk || $requestedSdk === 'node') {
        $sdk  = new SDK(new Node(), buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/node');
    }

    $cliLogo = "
    _                            _ _           ___   __   _____
   /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
  //_\\\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
 /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_
 \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/
       |_|   |_|

";

    $cliLogoUnescaped = "
     _                            _ _           ___   __   _____
    /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
   //_\\\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
  /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_
  \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/
        |_|   |_|                                                ";

    $cliExcludes = [
        'services' => [
            ['name' => 'assistant'],
            ['name' => 'avatars'],
            ['name' => 'advisor'],
            ['name' => 'compute'],
            ['name' => 'apps'],
            ['name' => 'oauth'],
            ['name' => 'organizations'],
            ['name' => 'console'],
            ['name' => 'projects'],
            ['name' => 'waf'],
            ['name' => 'domains'],
            ['name' => 'manager'],
            ['name' => 'mysql'],
            ['name' => 'postgresql'],
            ['name' => 'mongo'],
            ['name' => 'usage'],
        ],
        'methods' => [
            ['name' => 'createBillingAddress'],
            ['name' => 'createPaymentMethod'],
            ['name' => 'deleteBillingAddress'],
            ['name' => 'deletePaymentMethod'],
            ['name' => 'getBillingAddress'],
            ['name' => 'getCoupon'],
            ['name' => 'getPaymentMethod'],
            ['name' => 'listBillingAddresses'],
            ['name' => 'listInvoices'],
            ['name' => 'listPaymentMethods'],
            ['name' => 'updateBillingAddress'],
            ['name' => 'updateConsoleAccess'],
            ['name' => 'updatePaymentMethod'],
            ['name' => 'updatePaymentMethodMandateOptions'],
            ['name' => 'updatePaymentMethodProvider'],
            ['name' => 'createPlanEstimation'],
            // Not yet available in the released @appwrite.io/console package
            ['name' => 'listStages'],
            ['name' => 'updateStage'],
            ['name' => 'approve'],
        ],
    ];

    // Absent from the published Go SDK, which is generated from the server spec:
    // three console-only services, plus `migrations`, which has never shipped.
    // Generating their commands would import packages that do not exist.
    $cliExcludes['services'] = [
        ...$cliExcludes['services'],
        ['name' => 'affiliates'],
        ['name' => 'migrations'],
        ['name' => 'notifications'],
        ['name' => 'vcs'],
    ];
    // Individual endpoints the same SDK has no function for. Read off the
    // compiler, not guessed -- an invented name silently matches nothing.
    $cliExcludes['methods'] = [
        ...$cliExcludes['methods'],
        // Account API keys and push targets, and account deletion.
        ['service' => 'account', 'name' => 'createKey'],
        ['service' => 'account', 'name' => 'listKeys'],
        ['service' => 'account', 'name' => 'getKey'],
        ['service' => 'account', 'name' => 'updateKey'],
        ['service' => 'account', 'name' => 'deleteKey'],
        ['service' => 'account', 'name' => 'createPushTarget'],
        ['service' => 'account', 'name' => 'updatePushTarget'],
        ['service' => 'account', 'name' => 'deletePushTarget'],
        ['service' => 'account', 'name' => 'createOAuth2Session'],
        ['service' => 'account', 'name' => 'createJWT'],
        ['service' => 'account', 'name' => 'delete'],
        // OIDC logout.
        ['service' => 'oauth2', 'name' => 'logout'],
        ['service' => 'oauth2', 'name' => 'logoutPost'],
        // Function and site templates.
        ['service' => 'functions', 'name' => 'listTemplates'],
        ['service' => 'functions', 'name' => 'getTemplate'],
        ['service' => 'sites', 'name' => 'listTemplates'],
        ['service' => 'sites', 'name' => 'getTemplate'],
        // Table migrations -- tablesDB carries four of its own.
        ['service' => 'tablesDB', 'name' => 'createMigration'],
        ['service' => 'tablesDB', 'name' => 'listMigrations'],
        ['service' => 'tablesDB', 'name' => 'getMigration'],
        ['service' => 'tablesDB', 'name' => 'deleteMigration'],
        // A signature mismatch rather than a missing function: the released SDK
        // takes a further path parameter.
        ['service' => 'presences', 'name' => 'upsert'],
        ['service' => 'presences', 'name' => 'update'],
        // Usage and log reporting.
        ['service' => 'presences', 'name' => 'getUsage'],
        ['service' => 'project', 'name' => 'createKey'],
        ['service' => 'project', 'name' => 'getUsage'],
        ['service' => 'users', 'name' => 'getUsage'],
        ['service' => 'teams', 'name' => 'listLogs'],
    ];

    // CLI
    if (!$requestedSdk || $requestedSdk === 'cli') {
        $language = new CLI();
        $language->setExecutableName('appwrite');
        $language->setLogo($cliLogo);
        $language->setLogoUnescaped($cliLogoUnescaped);
        $language->setNPMPackage('appwrite-cli');

        $sdk = new SDK($language, buildSpecification($spec));
        $sdk->setTest(false);
        configureSDK($sdk, [
            'exclude' => $cliExcludes,
        ]);

        $sdk->generate(__DIR__ . '/examples/cli');
    }

    // Ruby
    if (!$requestedSdk || $requestedSdk === 'ruby') {
        $sdk  = new SDK(new Ruby(), buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/ruby');
    }

    // Python
    if (!$requestedSdk || $requestedSdk === 'python') {
        $python = new Python();
        $python->setPipPackage('appwrite');
        $sdk  = new SDK($python, buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/python');
    }

    // Dart
    if (!$requestedSdk || $requestedSdk === 'dart') {
        $dart = new Dart();
        $dart->setPackageName('dart_appwrite');
        $sdk  = new SDK($dart, buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/dart');
    }

    // Flutter
    if (!$requestedSdk || $requestedSdk === 'flutter') {
        $flutter = new Flutter();
        $flutter->setPackageName('appwrite');
        $sdk  = new SDK($flutter, buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/flutter');
    }

    // React Native
    if (!$requestedSdk || $requestedSdk === 'react-native') {
        $reactNative = new ReactNative();
        $reactNative->setNPMPackage('react-native-appwrite');
        $sdk  = new SDK($reactNative, buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/react-native');
    }

    // GO
    if (!$requestedSdk || $requestedSdk === 'go') {
        $sdk  = new SDK(new Go(), buildSpecification($spec));
        // The version decides the major-version suffix Go requires from v2 on,
        // so examples/go declares `sdk-for-go/v6` like the published module.
        configureSDK($sdk, [
            'gitUserName' => 'appwrite',
            'gitRepoName' => 'sdk-for-go',
            'version' => '6.2.0',
        ]);
        $sdk->generate(__DIR__ . '/examples/go');
    }

    // Swift
    if (!$requestedSdk || $requestedSdk === 'swift') {
        $sdk  = new SDK(new Swift(), buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/swift');
    }

    // Apple
    if (!$requestedSdk || $requestedSdk === 'apple') {
        $sdk  = new SDK(new Apple(), buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/apple');
    }

    // DotNet
    if (!$requestedSdk || $requestedSdk === 'dotnet') {
        $sdk  = new SDK(new DotNet(), buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/dotnet');
    }

    // REST
    if (!$requestedSdk || $requestedSdk === 'rest') {
        $sdk  = new SDK(new REST(), buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/REST');
    }

    // Android
    if (!$requestedSdk || $requestedSdk === 'android') {
        $sdk = new SDK(new Android(), buildSpecification($spec));
        configureSDK($sdk, [
            'namespace' => 'io.appwrite',
        ]);
        $sdk->generate(__DIR__ . '/examples/android');
    }

    // Kotlin
    if (!$requestedSdk || $requestedSdk === 'kotlin') {
        $sdk = new SDK(new Kotlin(), buildSpecification($spec));
        configureSDK($sdk, [
            'namespace' => 'io.appwrite',
        ]);
        $sdk->generate(__DIR__ . '/examples/kotlin');
    }

    // GraphQL
    if (!$requestedSdk || $requestedSdk === 'graphql') {
        $sdk = new SDK(new GraphQL(), buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/graphql');
    }

    // Skills
    if (!$requestedSdk || $requestedSdk === 'skills') {
        $sdk = new SDK(new Skills(), buildStaticSpecification());
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/skills');
    }

    // Cursor Plugin
    if (!$requestedSdk || $requestedSdk === 'cursor-plugin') {
        $sdk = new SDK(new CursorPlugin(), buildStaticSpecification());
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/cursor-plugin');
    }

    // Claude Plugin
    if (!$requestedSdk || $requestedSdk === 'claude-plugin') {
        $sdk = new SDK(new ClaudePlugin(), buildStaticSpecification());
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/claude-plugin');
    }

    // Codex Plugin
    if (!$requestedSdk || $requestedSdk === 'codex-plugin') {
        $sdk = new SDK(new CodexPlugin(), buildStaticSpecification());
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/codex-plugin');
    }

    // Zed Extension
    if (!$requestedSdk || $requestedSdk === 'zed-extension') {
        $sdk = new SDK(new ZedExtension(), buildStaticSpecification());
        configureSDK($sdk, [
            'licenseContent' => rtrim(file_get_contents(__DIR__ . '/LICENSE.md')),
        ]);
        $sdk->generate(__DIR__ . '/examples/zed-extension');
    }

    // Rust
    if (!$requestedSdk || $requestedSdk === 'rust') {
        $sdk = new SDK(new Rust(), buildSpecification($spec));
        configureSDK($sdk);
        $sdk->generate(__DIR__ . '/examples/rust');
    }
}
catch (Throwable $exception) {
    echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
    exit(1);
}

echo "Example SDKs generated successfully\n";
