<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class SDKTest extends TestCase
{
    /**
     * @var array
     */
    protected $containers = [
        'php-5.6' => 'docker run --rm -v $(pwd):/app -w /app php:5.6-cli php tests/php/test.php',
        'php-7.0' => 'docker run --rm -v $(pwd):/app -w /app php:7.0-cli php tests/php/test.php',
        'php-7.1' => 'docker run --rm -v $(pwd):/app -w /app php:7.1-cli php tests/php/test.php',
        'php-7.2' => 'docker run --rm -v $(pwd):/app -w /app php:7.2-cli php tests/php/test.php',
        'php-7.3' => 'docker run --rm -v $(pwd):/app -w /app php:7.3-cli php tests/php/test.php',
        //'nodejs-12.12' => 'docker run --rm -v $(pwd):/app -w /app node:12.12 node tests/languages/tests-for-node.js',
    ];

    /**
     * @var array
     */
    protected $packaging = [
        'node' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/node node:12.12 npm install',
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

        exec('docker run --rm -v $(pwd):/app -w /app php:7.3-cli php tests/resources/generate.php', $output);

        $this->assertEquals($output[0], 'Example SDKs generated successfully');

        foreach ($this->packaging as $key => $command) {
            $output = [];

            echo "Installing {$key} package dependencies...\n";

            exec($command, $output);

            //var_dump($output);
        }

        foreach ($this->containers as $key => $command) {
            echo "Running tests for the {$key} environment...\n";
            
            $output = [];

            exec($command, $output);

            //var_dump($key, $command, $output);

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

        exec('rm -r ./tests/sdks');
    }
}
