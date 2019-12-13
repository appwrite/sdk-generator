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
                'php-7.0' => 'docker run --rm -v $(pwd):/app -w /app php:7.0-cli php tests/php/test.php',
                'php-7.1' => 'docker run --rm -v $(pwd):/app -w /app php:7.1-cli php tests/php/test.php',
                'php-7.2' => 'docker run --rm -v $(pwd):/app -w /app php:7.2-cli php tests/php/test.php',
                'php-7.3' => 'docker run --rm -v $(pwd):/app -w /app php:7.3-cli php tests/php/test.php',
            ],

        ],
        'node' => [
            'class' => 'Appwrite\SDK\Language\Node',
            'build' => [
                'docker run --rm -v $(pwd):/app -w /app/tests/sdks/node node:12.12 npm install'
            ],
            'envs' => [
                'nodejs-8' => 'docker run --rm -v $(pwd):/app -w /app node:8.16 node tests/node/test.js',
                'nodejs-10' => 'docker run --rm -v $(pwd):/app -w /app node:10.16 node tests/node/test.js',
                'nodejs-12' => 'docker run --rm -v $(pwd):/app -w /app node:12.12 node tests/node/test.js',
            ],
        ],
        'ruby' => [
            'class' => 'Appwrite\SDK\Language\Ruby',
            'build' => [
            ],
            'envs' => [
                'ruby-2.7' => 'docker run --rm -v $(pwd):/app -w /app ruby:2.7-rc ruby tests/ruby/tests.rb',
                'ruby-2.6' => 'docker run --rm -v $(pwd):/app -w /app ruby:2.6.5 ruby tests/ruby/tests.rb',
                'ruby-2.5' => 'docker run --rm -v $(pwd):/app -w /app ruby:2.5.7 ruby tests/ruby/tests.rb',
                'ruby-2.4' => 'docker run --rm -v $(pwd):/app -w /app ruby:2.4.9 ruby tests/ruby/tests.rb',
            ],
        ],
        // 'python' => [
        //     'class' => 'Appwrite\SDK\Language\Python',
        //     'build' => [
        //         'cp tests/python/tests.py tests/sdks/python/test.py',
        //     ],
        //     'envs' => [
        //         'python-3.8' => 'docker run --rm -v $(pwd):/app -w /app python:3.8 pip install -r tests/sdks/python/requirements.txt > test1.txt && python tests/sdks/python/test.py',
        //         'python-3.7' => 'docker run --rm -v $(pwd):/app -w /app python:3.7 pip install -r tests/sdks/python/requirements.txt > test2.txt && python tests/sdks/python/test.py',
        //         'python-3.6' => 'docker run --rm -v $(pwd):/app -w /app python:3.6 pip install -r tests/sdks/python/requirements.txt > test3.txt && python tests/sdks/python/test.py',
        //     ],
        // ],
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

        try {
            $spec = file_get_contents(realpath(__DIR__ . '/resources/spec.json'));

            if(empty($spec)) {
                throw new Exception('Failed to fetch spec from Appwrite server');
            }

            foreach ($this->languages as $language => $options) {
                $sdk  = new SDK(new $options['class'](), new Swagger2($spec));

                $sdk
                    ->setLogo('https://appwrite.io/v1/images/console.png')
                    ->setWarning('**WORK IN PROGRESS - THIS IS JUST A TEST SDK**')
                    ->setVersion('0.0.1')
                    ->setGitUserName('repoowner')
                    ->setGitRepoName('reponame')
                    ->setLicenseContent('demo license')
                ;

                $sdk->generate(__DIR__ . '/sdks/' . $language);

                $output = [];

                /**
                 * Build SDK
                 */
                if(isset($options['build'])) {
                    echo "Building {$language} package...\n";

                    foreach ($options['build'] as $key => $command) {
                        exec($command, $output);
                    }
                }

                /**
                 * Run tests on all different envs
                 */
                foreach ($options['envs'] as $key => $command) {
                    echo "Running tests for the {$key} environment...\n";

                    $output = [];

                    exec($command, $output);

                    $this->assertEquals($output[0], 'GET:/v1/mock/tests/foo:passed');
                    $this->assertEquals($output[1], 'POST:/v1/mock/tests/foo:passed');
                    $this->assertEquals($output[2], 'PUT:/v1/mock/tests/foo:passed');
                    $this->assertEquals($output[3], 'PATCH:/v1/mock/tests/foo:passed');
                    $this->assertEquals($output[4], 'DELETE:/v1/mock/tests/foo:passed');
                    $this->assertEquals($output[5], 'GET:/v1/mock/tests/bar:passed');
                    $this->assertEquals($output[6], 'POST:/v1/mock/tests/bar:passed');
                    $this->assertEquals($output[7], 'PUT:/v1/mock/tests/bar:passed');
                    $this->assertEquals($output[8], 'PATCH:/v1/mock/tests/bar:passed');
                    $this->assertEquals($output[9], 'DELETE:/v1/mock/tests/bar:passed');
                }
            }
        }
        catch (Exception $exception) {
            echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
        }
        catch (Throwable $exception) {
            echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
        }
    }
}
