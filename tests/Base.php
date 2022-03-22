<?php

namespace Tests;

use Appwrite\SDK\Language;
use Appwrite\SDK\SDK;
use Appwrite\Spec\Swagger2;
use PHPUnit\Framework\TestCase;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

abstract class Base extends TestCase
{
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

    const COOKIE_RESPONSES = [
        'GET:/v1/mock/tests/general/set-cookie:passed',
        'GET:/v1/mock/tests/general/get-cookie:passed',
    ];

    const LARGE_FILE_RESPONSES = [
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

    protected string $class = '';
    protected string $language = '';
    protected array $build = [];
    protected array $envs = [];
    protected array $expectedOutput = [];

    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
    }

    public function testHTTPSuccess(): void
    {
        $spec = file_get_contents(realpath(__DIR__ . '/resources/spec.json'));

        if (empty($spec)) {
            throw new \Exception('Failed to parse spec.');
        }

        $sdk = new SDK($this->getLanguage(), new Swagger2($spec));

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
            ->setTest("true");

        $sdk->generate(__DIR__ . '/sdks/' . $this->language);

        /**
         * Build SDK
         */
        foreach ($this->build as $key => $command) {
            echo "Building phase #{$key} for {$this->language} package...\n";
            echo "Executing: {$command}\n";

            $buildOutput = [];

            ob_end_clean();
            echo "Build Executing: {$command}\n";
            ob_start();

            exec($command, $buildOutput);

            foreach ($buildOutput as $i => $row) {
                echo "{$i}. {$row}\n";
            }
        }

        /**
         * Run tests on all different envs
         */
        foreach ($this->envs as $key => $command) {
            echo "Running tests for the {$key} environment...\n";

            $output = [];

            ob_end_clean();
            echo "Env Executing: {$command}\n";
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

            $this->assertGreaterThanOrEqual(count($this->expectedOutput), count($output));

            foreach ($this->expectedOutput as $i => $row) {
                $this->assertEquals($output[$i], $row);
            }
        }
    }

    public function getLanguage(): Language 
    {
        return new $this->class();
    }
}
