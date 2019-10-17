<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class SDKTest extends TestCase
{
    /**
     * @var Client
     */
    protected $containers = [
        'php-5.6' => 'docker run --rm -v $(pwd):/app -w /app php:5.6-cli php tests/languages/tests-for-php.php',
        'php-7.0' => 'docker run --rm -v $(pwd):/app -w /app php:7.0-cli php tests/languages/tests-for-php.php',
        'php-7.1' => 'docker run --rm -v $(pwd):/app -w /app php:7.1-cli php tests/languages/tests-for-php.php',
        'php-7.2' => 'docker run --rm -v $(pwd):/app -w /app php:7.2-cli php tests/languages/tests-for-php.php',
        'php-7.3' => 'docker run --rm -v $(pwd):/app -w /app php:7.3-cli php tests/languages/tests-for-php.php',
        //'nodejs-12.12' => 'docker run --rm -v $(pwd):/app -w /app node:12.12 node tests/languages/tests-for-node.js',
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
        exec('docker run --rm -v $(pwd):/app -w /app php:7.3-cli php tests/resources/generate.php', $output);

        $this->assertEquals($output[0], 'Example SDKs generated successfully');

        foreach ($this->containers as $key => $command) {
            $output = [];

            exec($command, $output);

            var_dump($key, $command, $output);

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
