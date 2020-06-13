<?php

namespace Tests;

use Appwrite\Spec\Swagger2;
use Appwrite\SDK\SDK;
use PHPUnit\Framework\TestCase;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class SDKTest extends TestCase
{
    /**
     * @var array
     */
    protected $languages  = [
        'php' => [
            'class' => 'Appwrite\SDK\Language\PHP',
            'build' => [
            ],
            'envs' => [
                'php-7.0' => 'docker run --rm -v $(pwd):/app -w /app php:7.0-cli php tests/languages/php/test.php',
                'php-7.1' => 'docker run --rm -v $(pwd):/app -w /app php:7.1-cli php tests/languages/php/test.php',
                'php-7.2' => 'docker run --rm -v $(pwd):/app -w /app php:7.2-cli php tests/languages/php/test.php',
                'php-7.3' => 'docker run --rm -v $(pwd):/app -w /app php:7.3-cli php tests/languages/php/test.php',
                'php-7.4' => 'docker run --rm -v $(pwd):/app -w /app php:7.4-cli php tests/languages/php/test.php',
            ],
        ],
        'dart' => [
            'class' => 'Appwrite\SDK\Language\Dart',
            'build' => [
                'mkdir -p tests/sdks/dart/tests',
                'cp tests/languages/dart/tests.dart tests/sdks/dart/tests/tests.dart',
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart --env PUB_CACHE=vendor appwrite/flutter:0.3.0 flutter pub get',
            ],
            'envs' => [
                'flutter' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart --env PUB_CACHE=vendor appwrite/flutter:0.3.0 flutter pub run tests/tests.dart',
                //'dart-2.6' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart --env PUB_CACHE=tests/sdks/dart/vendor google/dart:2.6 pub run test/tests.dart',
                //'dart-2.7' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart --env PUB_CACHE=tests/sdks/dart/vendor google/dart:2.7 pub run test/tests.dart',
                //'dart-2.8-dev' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dart --env PUB_CACHE=tests/sdks/dart/vendor google/dart:2.8-dev pub run test/tests.dart',
            ],
        ],

        'java' => [
            'class' => 'Appwrite\SDK\Language\CSharp',
            'build' => [
                'mkdir -p tests/sdks/java/src/test/java/io/appwrite/services',
                'cp tests/languages/java/ServiceTest.java tests/sdks/java/src/test/java/io/appwrite/services/ServiceTest.java',
            ],
            'envs' => [
                'java-11' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/java --env PUB_CACHE=vendor maven:3.6-jdk-11-slim mvn clean install test -q',
//                'java-14' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/java --env PUB_CACHE=vendor maven:3.6-jdk-14-slim mvn clean install test -q',
            ],
        ],

        'csharp' => [
            'class' => 'Appwrite\SDK\Language\CSharp',
            'build' => [
                'mkdir -p tests/sdks/csharp/src/test',
                'cp tests/languages/csharp/ServiceTest.cs tests/sdks/csharp/src/test/ServiceTest.cs',
            ],
            'envs' => [
                // 'java-11' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/java --env PUB_CACHE=vendor maven:3.6-jdk-11-slim mvn clean install test -q'
            ],
        ],

        'typescript' => [
            'class' => 'Appwrite\SDK\Language\TypeScript',
            'build' => [
                'cp tests/languages/typescript/tests.ts tests/sdks/typescript/tests.ts',
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/typescript node:12.12 npm install', //  npm list --depth 0 && 
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/typescript node:12.12 ls node_modules/.bin',
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/typescript node:12.12 node_modules/.bin/tsc --lib ES6,DOM tests'
            ],
            'envs' => [
                'nodejs-12' => 'docker run --rm -v $(pwd):/app -w /app node:12.12 node tests/sdks/typescript/tests.js',
            ],
        ],

        
        'deno' => [
            'class' => 'Appwrite\SDK\Language\Deno',
            'build' => [
            ],
            'envs' => [
                'deno-1.0.0' => 'docker run --rm -v $(pwd):/app -w /app hayd/alpine-deno:1.0.0 run --allow-net tests/languages/deno/tests.ts', // TODO: use official image when its out
            ],
        ],

        'node' => [
            'class' => 'Appwrite\SDK\Language\Node',
            'build' => [
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/node node:12.12 npm install'
            ],
            'envs' => [
                'nodejs-8' => 'docker run --rm -v $(pwd):/app -w /app node:8.16 node tests/languages/node/test.js',
                'nodejs-10' => 'docker run --rm -v $(pwd):/app -w /app node:10.16 node tests/languages/node/test.js',
                'nodejs-12' => 'docker run --rm -v $(pwd):/app -w /app node:12.12 node tests/languages/node/test.js',
            ],
        ],

        'ruby' => [
            'class' => 'Appwrite\SDK\Language\Ruby',
            'build' => [
            ],
            'envs' => [
                'ruby-2.7' => 'docker run --rm -v $(pwd):/app -w /app ruby:2.7-rc ruby tests/languages/ruby/tests.rb',
                'ruby-2.6' => 'docker run --rm -v $(pwd):/app -w /app ruby:2.6.5 ruby tests/languages/ruby/tests.rb',
                'ruby-2.5' => 'docker run --rm -v $(pwd):/app -w /app ruby:2.5.7 ruby tests/languages/ruby/tests.rb',
                'ruby-2.4' => 'docker run --rm -v $(pwd):/app -w /app ruby:2.4.9 ruby tests/languages/ruby/tests.rb',
            ],
        ],

        'python' => [
            'class' => 'Appwrite\SDK\Language\Python',
            'build' => [
                'cp tests/languages/python/tests.py tests/sdks/python/test.py',
                'echo "" > tests/sdks/python/__init__.py',
                'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor python:3.8 pip install -r tests/sdks/python/requirements.txt --upgrade'
            ],
            'envs' => [
                'python-3.8' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.8 python tests/sdks/python/test.py',
                'python-3.7' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.7 python tests/sdks/python/test.py',
                'python-3.6' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.6 python tests/sdks/python/test.py',
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

        echo "Generating SDKs files for all langauges...\n";

        $spec = file_get_contents(realpath(__DIR__ . '/resources/spec.json'));

        if(empty($spec)) {
            throw new \Exception('Failed to fetch spec from Appwrite server');
        }

        $whitelist = ['php', 'java', 'node', 'ruby', 'python', 'typescript', 'deno'];

        foreach ($this->languages as $language => $options) {
            if(!empty($whitelist) && !in_array($language, $whitelist)) {
                continue;
            }

            $sdk  = new SDK(new $options['class'](), new Swagger2($spec));

            $sdk
                ->setDescription('Repo description goes here')
                ->setShortDescription('Repo short description goes here')
                ->setLogo('https://appwrite.io/v1/images/console.png')
                ->setWarning('**WORK IN PROGRESS - THIS IS JUST A TEST SDK**')
                ->setVersion('0.0.1')
                ->setNamespace("io appwrite")
                ->setGitUserName('repoowner')
                ->setGitRepoName('reponame')
                ->setLicense('BSD-3-Clause')
                ->setLicenseContent('demo license')
                ->setChangelog('--changelog--')
            ;

            $sdk->generate(__DIR__ . '/sdks/' . $language);

            $output = [];

            /**
             * Build SDK
             */
            if(isset($options['build'])) {
                
                foreach ($options['build'] as $key => $command) {
                    echo "Building phase #{$key} for {$language} package...\n";
                    exec($command, $output);

                    foreach($output as $i => $row) {
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

                exec($command, $output);

                foreach($output as $i => $row) {
                    echo "{$row}\n";
                }

                $this->assertIsArray($output);
                $this->assertGreaterThan(10, count($output));

                $this->assertEquals('GET:/v1/mock/tests/foo:passed', (isset($output[0])) ? $output[0] : '');
                $this->assertEquals('POST:/v1/mock/tests/foo:passed', (isset($output[1])) ? $output[1] : '');
                $this->assertEquals('PUT:/v1/mock/tests/foo:passed', (isset($output[2])) ? $output[2] : '');
                $this->assertEquals('PATCH:/v1/mock/tests/foo:passed', (isset($output[3])) ? $output[3] : '');
                $this->assertEquals('DELETE:/v1/mock/tests/foo:passed', (isset($output[4])) ? $output[4] : '');
                $this->assertEquals('GET:/v1/mock/tests/bar:passed', (isset($output[5])) ? $output[5] : '');
                $this->assertEquals('POST:/v1/mock/tests/bar:passed', (isset($output[6])) ? $output[6] : '');
                $this->assertEquals('PUT:/v1/mock/tests/bar:passed', (isset($output[7])) ? $output[7] : '');
                $this->assertEquals('PATCH:/v1/mock/tests/bar:passed', (isset($output[8])) ? $output[8] : '');
                $this->assertEquals('DELETE:/v1/mock/tests/bar:passed', (isset($output[9])) ? $output[9] : '');
                
                $this->assertEquals('GET:/v1/mock/tests/general/redirected:passed', $output[10]);
                //$this->assertEquals($output[11], 'POST:/v1/mock/tests/general/upload:passed');
            }
        }
    
    }
}
