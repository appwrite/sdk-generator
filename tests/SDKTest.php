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
                'php-7.0' => 'docker run --rm -v $(pwd):/app -w /app php:7.0-cli-alpine php tests/languages/php/test.php',
                'php-7.1' => 'docker run --rm -v $(pwd):/app -w /app php:7.1-cli-alpine php tests/languages/php/test.php',
                'php-7.2' => 'docker run --rm -v $(pwd):/app -w /app php:7.2-cli-alpine php tests/languages/php/test.php',
                'php-7.3' => 'docker run --rm -v $(pwd):/app -w /app php:7.3-cli-alpine php tests/languages/php/test.php',
                'php-7.4' => 'docker run --rm -v $(pwd):/app -w /app php:7.4-cli-alpine php tests/languages/php/test.php',
                'php-8.0' => 'docker run --rm -v $(pwd):/app -w /app php:8.0.0rc1-cli-alpine php tests/languages/php/test.php',
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
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart --env PUB_CACHE=vendor google/dart:2.12 pub get',
            ],
            'envs' => [
                'dart-2.12' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart --env PUB_CACHE=vendor google/dart:2.12 dart pub run tests/tests.dart',
                'dart-2.13-dev' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart --env PUB_CACHE=vendor google/dart:2.13-dev dart pub run tests/tests.dart',
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
        ],

        'kotlin' => [
            'class' => 'Appwrite\SDK\Language\Kotlin',
            'build' => [
                'mkdir -p tests/sdks/kotlin/src/test/java/io/appwrite/services',
                'cp tests/languages/kotlin/ServiceTest.kt tests/sdks/kotlin/src/test/java/io/appwrite/services/ServiceTest.kt',
            ],
            'envs' => [
                'java-8' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/java --env PUB_CACHE=vendor maven:3.6-jdk-8-slim mvn clean install test -q',
                'java-11' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/java --env PUB_CACHE=vendor maven:3.6-jdk-11-slim mvn clean install test -q',
            ],
            'supportException' => false,
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
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/web mcr.microsoft.com/playwright:bionic npm install', //  npm list --depth 0 &&
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/web mcr.microsoft.com/playwright:bionic npm run build',
            ],
            'envs' => [
                'chromium' => 'docker run --rm -v $(pwd):/app -e BROWSER=chromium -w /app/tests/sdks/web mcr.microsoft.com/playwright:bionic node tests.js',
                'firefox' => 'docker run --rm -v $(pwd):/app -e BROWSER=firefox -w /app/tests/sdks/web mcr.microsoft.com/playwright:bionic node tests.js',
                'webkit' => 'docker run --rm -v $(pwd):/app -e BROWSER=webkit -w /app/tests/sdks/web mcr.microsoft.com/playwright:bionic node tests.js',
                'node' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/web mcr.microsoft.com/playwright:bionic node node.js',
            ],
            'supportException' => true,
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
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/node node:12.12-alpine npm install',
            ],
            'envs' => [
                'nodejs-8' => 'docker run --rm -v $(pwd):/app -w /app node:8.16-alpine node tests/languages/node/test.js',
                'nodejs-10' => 'docker run --rm -v $(pwd):/app -w /app node:10.16-alpine node tests/languages/node/test.js',
                'nodejs-12' => 'docker run --rm -v $(pwd):/app -w /app node:12.12-alpine node tests/languages/node/test.js',
                'nodejs-14' => 'docker run --rm -v $(pwd):/app -w /app node:14.5-alpine node tests/languages/node/test.js',
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
                'ruby-2.6' => 'docker run --rm -v $(pwd):/app -w /app --env GEM_HOME=vendor ruby:2.6-alpine ruby tests/languages/ruby/tests.rb',
                'ruby-2.5' => 'docker run --rm -v $(pwd):/app -w /app --env GEM_HOME=vendor ruby:2.5-alpine ruby tests/languages/ruby/tests.rb',
                'ruby-2.4' => 'docker run --rm -v $(pwd):/app -w /app --env GEM_HOME=vendor ruby:2.4-alpine ruby tests/languages/ruby/tests.rb',
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
                'python-3.8' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.8-alpine python tests/sdks/python/test.py',
                'python-3.7' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.7-alpine python tests/sdks/python/test.py',
                'python-3.6' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.6-alpine python tests/sdks/python/test.py',
                'python-3.5' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.5-alpine python tests/sdks/python/test.py',
                // 'python-3.4' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.4-alpine python tests/sdks/python/test.py',
                // 'python-3.3' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.3-alpine python tests/sdks/python/test.py',
                // 'python-3.2' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.2 python tests/sdks/python/test.py',
                // 'python-3.1' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.1 python tests/sdks/python/test.py',
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

        echo "Generating SDKs files for all langauges...\n";

        $spec = file_get_contents(realpath(__DIR__ . '/resources/spec.json'));

        if (empty($spec)) {
            throw new \Exception('Failed to fetch spec from Appwrite server');
        }

        $whitelist = ['php', 'cli', 'node', 'ruby', 'python', 'deno', 'dotnet', 'dart', 'flutter', 'web'];

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
            }
        }

    }
}
