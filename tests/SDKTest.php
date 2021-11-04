<?php

namespace Tests;

use Appwrite\SDK\SDK;
use Appwrite\Spec\Swagger2;
use PHPUnit\Framework\TestCase;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
            'supportException' => true,
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
            'supportException' => false,
        ],

        'dart' => [
            'class' => 'Appwrite\SDK\Language\Dart',
            'build' => [
                'mkdir -p tests/sdks/dart/tests',
                'cp tests/languages/dart/tests.dart tests/sdks/dart/tests/tests.dart',
            ],
            'envs' => [
                'dart-2.12' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart dart:2.12 sh -c "dart pub get && dart pub run tests/tests.dart"',
                'dart-stable' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart dart:stable sh -c "dart pub get && dart pub run tests/tests.dart"',
                'dart-beta' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart dart:beta sh -c "dart pub get && dart pub run tests/tests.dart"',
            ],
            'supportException' => true,
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
            'supportException' => true,
            'supportRealtime' => true,
        ],

        'android' => [
            'class' => 'Appwrite\SDK\Language\Android',
            'build' => [
                'mkdir -p tests/sdks/android/library/src/test/java',
                'cp tests/languages/android/ServiceTest.kt tests/sdks/android/library/src/test/java/ServiceTest.kt',
                'chmod +x tests/sdks/android/gradlew',
            ],
            'envs' => [
                'java-8' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/android alvrme/alpine-android:latest-jdk8 sh -c "./gradlew :library:testReleaseUnitTest -q && cat library/result.txt"',
            ],
            'supportException' => true,
            'supportRealtime' => true,
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
            ],
            'supportException' => false,
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
            'supportException' => true,
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
            'supportException' => true,
            'supportRealtime' => true,
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
            'supportException' => true,
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
                'firefox' => 'docker run --rm -v $(pwd):/app -e BROWSER=firefox -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.15.0-focal node tests.js',
                'node' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.15.0-focal node node.js',
            ],
            'supportException' => true,
            'supportRealtime' => true
        ],
        'deno' => [
            'class' => 'Appwrite\SDK\Language\Deno',
            'build' => [
            ],
            'envs' => [
                'deno-1.1.3' => 'docker run --rm -v $(pwd):/app -w /app hayd/alpine-deno:1.1.3 run --allow-net --allow-read tests/languages/deno/tests.ts', // TODO: use official image when its out
            ],
            'supportException' => true,
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
            'supportException' => true,
        ],

        'ruby' => [
            'class' => 'Appwrite\SDK\Language\Ruby',
            'build' => [
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/ruby --env GEM_HOME=/app/vendor ruby:2.7-alpine sh -c "apk add git build-base && bundle install"',
            ],
            'envs' => [
                'ruby-2.7' => 'docker run --rm -v $(pwd):/app -w /app --env GEM_HOME=vendor ruby:2.7-alpine ruby tests/languages/ruby/tests.rb',
            ],
            'supportException' => false,
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
            'supportException' => true,
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

        $whitelist = ['php', 'cli', 'node', 'ruby', 'python', 'deno', 'dotnet', 'dart', 'flutter', 'web', 'android', 'kotlin', 'swift-server', 'swift-client'];

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

                $this->assertGreaterThan(10, count($output));

                $this->assertEquals('GET:/v1/mock/tests/foo:passed', $output[0] ?? '');
                $this->assertEquals('POST:/v1/mock/tests/foo:passed', $output[1] ?? '');
                $this->assertEquals('PUT:/v1/mock/tests/foo:passed', $output[2] ?? '');
                $this->assertEquals('PATCH:/v1/mock/tests/foo:passed', $output[3] ?? '');
                $this->assertEquals('DELETE:/v1/mock/tests/foo:passed', $output[4] ?? '');
                $this->assertEquals('GET:/v1/mock/tests/bar:passed', $output[5] ?? '');
                $this->assertEquals('POST:/v1/mock/tests/bar:passed', $output[6] ?? '');
                $this->assertEquals('PUT:/v1/mock/tests/bar:passed', $output[7] ?? '');
                $this->assertEquals('PATCH:/v1/mock/tests/bar:passed', $output[8] ?? '');
                $this->assertEquals('DELETE:/v1/mock/tests/bar:passed', $output[9] ?? '');
                $this->assertEquals('GET:/v1/mock/tests/general/redirect/done:passed', $output[10] ?? '');
                $this->assertEquals('POST:/v1/mock/tests/general/upload:passed', $output[11] ?? '');

                if ($options['supportException']) {
                    $this->assertEquals('Mock 400 error',$output[12] ?? '');
                    $this->assertEquals('Server Error', $output[13] ?? '');
                    $this->assertEquals('This is a text error', $output[14] ?? '');
                }

                if ($options['supportRealtime'] ?? false) {
                    $this->assertEquals('WS:/v1/realtime:passed', $output[15] ?? '');
                }
            }
        }

    }
}
