<?php

namespace Tests;

use Appwrite\SDK\SDK;
use Appwrite\Spec\Swagger2;
use PHPUnit\Framework\TestCase;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

const FOO_RESPONSES = [
    'GET:/v1/mock/tests/foo:passed',
    'POST:/v1/mock/tests/foo:passed',
    'PUT:/v1/mock/tests/foo:passed',
    'PATCH:/v1/mock/tests/foo:passed',
    'DELETE:/v1/mock/tests/foo:passed',
];

const BAR_RESPONSES = [
    'GET:/v1/mock/tests/bar:passed',
    'POST:/v1/mock/tests/bar:passed',
    'PUT:/v1/mock/tests/bar:passed',
    'PATCH:/v1/mock/tests/bar:passed',
    'DELETE:/v1/mock/tests/bar:passed',
];

const GENERAL_RESPONSES = [
    'GET:/v1/mock/tests/general/redirect/done:passed',
    'POST:/v1/mock/tests/general/upload:passed',
];

const EXCEPTION_RESPONSES = [
    'Mock 400 error',
    'Server Error',
    'This is a text error',
];

const REALTIME_RESPONSES = [
    'WS:/v1/realtime:passed',
];

class SDKTest extends TestCase
{
    /**
     * @var array
     */
    protected $languages = [
        'php' => [
            'class' => 'Appwrite\SDK\Language\PHP',
            'build' => [
            ],
            'envs' => [
                'php-7.4' => 'docker run --rm -v $(pwd):/app -w /app php:7.4-cli-alpine php tests/languages/php/test.php',
                'php-8.0' => 'docker run --rm -v $(pwd):/app -w /app php:8.0-cli-alpine php tests/languages/php/test.php',
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
                ...EXCEPTION_RESPONSES,
            ],
        ],

        'cli' => [
            'class' => 'Appwrite\SDK\Language\CLI',
            'build' => [
                'printf "\nCOPY ./files /usr/local/code/files" >> tests/sdks/cli/Dockerfile',
                'cat tests/sdks/cli/Dockerfile',
                'mkdir tests/sdks/cli/files',
                'cp tests/resources/file.png tests/sdks/cli/files/',
                'docker build -t cli:latest tests/sdks/cli',
            ],
            'envs' => [
                'default' => 'php tests/languages/cli/test.php',
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
            ],
        ],

        'dart' => [
            'class' => 'Appwrite\SDK\Language\Dart',
            'build' => [
                'mkdir -p tests/sdks/dart/tests',
                'cp tests/languages/dart/tests.dart tests/sdks/dart/tests/tests.dart',
            ],
            'envs' => [
                'dart-stable' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart dart:stable sh -c "dart pub get && dart pub run tests/tests.dart"',
                'dart-beta' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart dart:beta sh -c "dart pub get && dart pub run tests/tests.dart"',
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
                ...EXCEPTION_RESPONSES,
            ],
        ],

        'flutter' => [
            'class' => 'Appwrite\SDK\Language\Flutter',
            'build' => [
                'mkdir -p tests/sdks/flutter/test',
                'cp tests/languages/flutter/tests.dart tests/sdks/flutter/test/appwrite_test.dart',
            ],
            'envs' => [
                'flutter-stable' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/flutter --env PUB_CACHE=vendor cirrusci/flutter:stable sh -c "flutter pub get && flutter test test/appwrite_test.dart"',
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
                ...EXCEPTION_RESPONSES,
                ...REALTIME_RESPONSES,
            ],
        ],

        'android' => [
            'class' => 'Appwrite\SDK\Language\Android',
            'build' => [
                'mkdir -p tests/sdks/android/library/src/test/java',
                'cp tests/languages/android/ServiceTest.kt tests/sdks/android/library/src/test/java/ServiceTest.kt',
                'chmod +x tests/sdks/android/gradlew',
            ],
            'envs' => [
                'java-11' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/android alvrme/alpine-android:latest-jdk11 sh -c "./gradlew :library:testReleaseUnitTest -q && cat library/result.txt"',
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
                'POST:/v1/mock/tests/general/upload:passed',
                ...EXCEPTION_RESPONSES,
                ...REALTIME_RESPONSES,
            ],
        ],

        'kotlin' => [
            'class' => 'Appwrite\SDK\Language\Kotlin',
            'build' => [
                'mkdir -p tests/sdks/kotlin/src/test/kotlin',
                'cp tests/languages/kotlin/ServiceTest.kt tests/sdks/kotlin/src/test/kotlin/ServiceTest.kt',
                'chmod +x tests/sdks/kotlin/gradlew',
            ],
            'envs' => [
                'java-8' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/kotlin openjdk:8-jdk-alpine sh -c "./gradlew :test -q && cat result.txt"',
                'java-11' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/kotlin adoptopenjdk/openjdk11:alpine sh -c "./gradlew :test -q && cat result.txt"',
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
                'POST:/v1/mock/tests/general/upload:passed', // large file upload
                ...EXCEPTION_RESPONSES,
            ],
        ],

        'swift-server' => [
            'class' => 'Appwrite\SDK\Language\Swift',
            'build' => [
                'mkdir -p tests/sdks/swift-server/Tests/AppwriteTests',
                'cp tests/languages/swift-server/Tests.swift tests/sdks/swift-server/Tests/AppwriteTests/Tests.swift',
            ],
            'envs' => [
                'swift-5.5' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/swift-server swift:5.5 swift test',
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
                ...EXCEPTION_RESPONSES,
            ],
        ],

        'swift-client' => [
            'class' => 'Appwrite\SDK\Language\SwiftClient',
            'build' => [
                'mkdir -p tests/sdks/swift-client/Tests/AppwriteTests',
                'cp tests/languages/swift-client/Tests.swift tests/sdks/swift-client/Tests/AppwriteTests/Tests.swift',
            ],
            'envs' => [
                'swift-5.5' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/swift-client swift:5.5 swift test',
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
                ...EXCEPTION_RESPONSES,
                ...REALTIME_RESPONSES,
            ],
        ],

        'dotnet' => [
            'class' => 'Appwrite\SDK\Language\DotNet',
            'build' => [
                'mkdir -p tests/sdks/dotnet/src/test',
                'cp tests/languages/dotnet/tests.ps1 tests/sdks/dotnet/src/test/tests.ps1',
                'cp -R tests/sdks/dotnet/io/appwrite/src/* tests/sdks/dotnet/src',
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dotnet/src mcr.microsoft.com/dotnet/sdk:5.0-alpine dotnet publish -c Release -o test -f netstandard2.0',
            ],
            'envs' => [
                'dotnet-5.0' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dotnet/src/test/ mcr.microsoft.com/dotnet/sdk:5.0-alpine pwsh tests.ps1',
                'dotnet-3.1' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dotnet/src/test/ mcr.microsoft.com/dotnet/sdk:3.1-alpine pwsh tests.ps1',
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
                ...EXCEPTION_RESPONSES,
            ],
        ],

        'web' => [
            'class' => 'Appwrite\SDK\Language\Web',
            'build' => [
                'cp tests/languages/web/tests.js tests/sdks/web/tests.js',
                'cp tests/languages/web/node.js tests/sdks/web/node.js',
                'cp tests/languages/web/index.html tests/sdks/web/index.html',
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.15.0-focal npm install', //  npm list --depth 0 &&
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.15.0-focal npm run build',
            ],
            'envs' => [
                'chromium' => 'docker run --rm -v $(pwd):/app -e BROWSER=chromium -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.15.0-focal node tests.js',
                // 'firefox' => 'docker run --rm -v $(pwd):/app -e BROWSER=firefox -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.15.0-focal node tests.js',
                'node' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.15.0-focal node node.js',
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
                ...EXCEPTION_RESPONSES,
                ...REALTIME_RESPONSES,
            ],
        ],

        'deno' => [
            'class' => 'Appwrite\SDK\Language\Deno',
            'build' => [
            ],
            'envs' => [
                'deno-1.17.1' => 'docker run --rm -v $(pwd):/app -w /app denoland/deno:alpine-1.17.1 run --allow-net --allow-read tests/languages/deno/tests.ts', // TODO: use official image when its out
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
                ...EXCEPTION_RESPONSES,
            ],
        ],

        'node' => [
            'class' => 'Appwrite\SDK\Language\Node',
            'build' => [
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/node node:16-alpine npm install',
            ],
            'envs' => [
                'nodejs-12' => 'docker run --rm -v $(pwd):/app -w /app node:12-alpine node tests/languages/node/test.js',
                'nodejs-14' => 'docker run --rm -v $(pwd):/app -w /app node:14-alpine node tests/languages/node/test.js',
                'nodejs-16' => 'docker run --rm -v $(pwd):/app -w /app node:16-alpine node tests/languages/node/test.js',
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
                ...EXCEPTION_RESPONSES,
            ],
        ],

        'ruby' => [
            'class' => 'Appwrite\SDK\Language\Ruby',
            'build' => [
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/ruby --env GEM_HOME=/app/vendor ruby:2.7-alpine sh -c "apk add git build-base && bundle install"',
            ],
            'envs' => [
                'ruby-2.7' => 'docker run --rm -v $(pwd):/app -w /app --env GEM_HOME=vendor ruby:2.7-alpine ruby tests/languages/ruby/tests.rb',
                'ruby-3.0' => 'docker run --rm -v $(pwd):/app -w /app --env GEM_HOME=vendor ruby:3.0-alpine ruby tests/languages/ruby/tests.rb',
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
                ...EXCEPTION_RESPONSES,
            ],
        ],

        'python' => [
            'class' => 'Appwrite\SDK\Language\Python',
            'build' => [
                'cp tests/languages/python/tests.py tests/sdks/python/test.py',
                'echo "" > tests/sdks/python/__init__.py',
                'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor python:3.8 pip install -r tests/sdks/python/requirements.txt --upgrade',
            ],
            'envs' => [
                'python-3.9' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.8-alpine python tests/sdks/python/test.py',
                'python-3.8' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.7-alpine python tests/sdks/python/test.py',
                'python-3.7' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.7-alpine python tests/sdks/python/test.py',
                'python-3.6' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.7-alpine python tests/sdks/python/test.py',
            ],
            'expectedOutput' => [
                ...FOO_RESPONSES,
                ...BAR_RESPONSES,
                ...GENERAL_RESPONSES,
                ...EXCEPTION_RESPONSES,
            ],
        ],
    ];

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testHTTPSuccess()
    {
        $output = [];

        /**
         * SDK Generation
         */

        echo "\n";

        echo "Generating SDKs files for all languages...\n";

        $spec = file_get_contents(realpath(__DIR__ . '/resources/spec.json'));

        if (empty($spec)) {
            throw new \Exception('Failed to fetch spec from Appwrite server');
        }

        //$whitelist = ['php', 'cli', 'node', 'ruby', 'python', 'deno', 'dotnet', 'dart', 'flutter', 'web', 'android', 'kotlin', 'swift-server', 'swift-client'];
        $whitelist = ['kotlin','android'];

        foreach ($this->languages as $language => $options) {
            if (!empty($whitelist) && !in_array($language, $whitelist)) {
                continue;
            }

            $sdk = new SDK(new $options['class'](), new Swagger2($spec));

            $sdk
                ->setDescription('Repo description goes here')
                ->setShortDescription('Repo short description goes here')
                ->setLogo('https://appwrite.io/v1/images/console.png')
                ->setWarning('**WORK IN PROGRESS - THIS IS JUST A TEST SDK**')
                ->setVersion('0.0.1')
                ->setExamples('**EXAMPLES** <HTML>')
                ->setNamespace("io appwrite")
                ->setGitUserName('repoowner')
                ->setGitRepoName('reponame')
                ->setLicense('BSD-3-Clause')
                ->setLicenseContent('demo license')
                ->setChangelog('--changelog--')
                ->setDefaultHeaders([
                    'X-Appwrite-Response-Format' => '0.8.0',
                ])
            ;

            $sdk->generate(__DIR__ . '/sdks/' . $language);

            $output = [];

            /**
             * Build SDK
             */
            if (isset($options['build'])) {

                foreach ($options['build'] as $key => $command) {
                    echo "Building phase #{$key} for {$language} package...\n";
                    echo "Executing: {$command}\n";

                    $output = [];

                    ob_end_clean();
                    var_dump('Build Executing: ' . $command);
                    ob_start();

                    exec($command, $output);

                    foreach ($output as $i => $row) {
                        echo "{$i}. {$row}\n";
                    }
                }
            }

            /**
             * Run tests on all different envs
             */
            foreach ($options['envs'] as $key => $command) {
                echo "Running tests for the {$key} environment...\n";

                $output = [];

                ob_end_clean();
                var_dump('Env Executing: ' . $command);
                ob_start();

                exec($command, $output);

                foreach ($output as $i => $row) {
                    echo "{$row}\n";
                }

                $this->assertIsArray($output);
 
                $removed = '';
                do {
                    $removed = array_shift($output);
                } while ($removed != 'Test Started' && sizeof($output) != 0);

                $this->assertGreaterThanOrEqual(count($options['expectedOutput']), count($output));

                foreach ($options['expectedOutput'] as $i => $row) {
                    $this->assertEquals($output[$i], $row);
                }
            }
        }

    }
}
