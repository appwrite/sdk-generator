<?php

namespace Tests;

use Appwrite\Spec\Swagger2;
use Appwrite\SDK\SDK;
use PHPUnit\Framework\TestCase;

class SDKTest extends TestCase
{
    /**
     * @var array
     */
    protected $languages  = [
        'php' => [
            'class' => 'Appwrite\SDK\Language\PHP',
            'build' => 'docker run --rm -v $(pwd):/app -w /app php:7.3-cli php -v',
            'envs' => [
                'php-5.6' => 'docker run --rm -v $(pwd):/app -w /app php:5.6-cli php tests/php/test.php',
                'php-7.0' => 'docker run --rm -v $(pwd):/app -w /app php:7.0-cli php tests/php/test.php',
                'php-7.1' => 'docker run --rm -v $(pwd):/app -w /app php:7.1-cli php tests/php/test.php',
                'php-7.2' => 'docker run --rm -v $(pwd):/app -w /app php:7.2-cli php tests/php/test.php',
                'php-7.3' => 'docker run --rm -v $(pwd):/app -w /app php:7.3-cli php tests/php/test.php',
            ],
            
        ],
        'node' => [
            'class' => 'Appwrite\SDK\Language\Node',
            'build' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/node node:12.12 npm install',
            'envs' => [
                'nodejs-8' => 'docker run --rm -v $(pwd):/app -w /app node:8.16 node tests/node/test.js',
                'nodejs-10' => 'docker run --rm -v $(pwd):/app -w /app node:10.16 node tests/node/test.js',
                'nodejs-12' => 'docker run --rm -v $(pwd):/app -w /app node:12.12 node tests/node/test.js',
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

        try {
            $spec = file_get_contents(__DIR__ . '/resources/spec.json');
        
            if(empty($spec)) {
                throw new Exception('Failed to fetch spec from Appwrite server');
            }
        
            foreach ($this->languages as $language => $options) {
                $sdk  = new SDK(new $options['class'](), new Swagger2($spec));
        
                $sdk
                    ->setLicenseContent('demo license')
                ;
        
                $sdk->generate(__DIR__ . '/sdks/' . $language);
                
                $output = [];

                echo "Building {$language} package...\n";
    
                exec($options['build'], $output);

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
