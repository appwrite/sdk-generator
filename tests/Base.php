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

    const EXTENDED_GENERAL_RESPONSES = [
        'GET:/v1/mock/tests/general/download:passed',
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
        'Mock 500 error',
        'This is a text error',
    ];

    const REALTIME_RESPONSES = [
        'WS:/v1/realtime:passed',
    ];

    const QUERY_HELPER_RESPONSES = [
        'equal("released", [true])',
        'equal("title", ["Spiderman","Dr. Strange"])',
        'notEqual("title", ["Spiderman"])',
        'lessThan("releasedYear", [1990])',
        'greaterThan("releasedYear", [1990])',
        'search("name", ["john"])',
        'orderAsc("title")',
        'orderDesc("title")',
        'cursorAfter("my_movie_id")',
        'cursorBefore("my_movie_id")',
        'limit(50)',
        'offset(20)',
    ];

    const PERMISSION_HELPER_RESPONSES = [
        'read("any")',
        'write("user:userid")',
        'create("users")',
        'update("guests")',
        'delete("team:teamId/owner")',
        'delete("team:teamId")',
        'create("member:memberId")',
        'update("users/verified")',
        'update("user:userid/unverified")',
    ];

    const ID_HELPER_RESPONSES = [
        'unique()',
        'custom_id'
    ];

    protected string $class = '';
    protected string $language = '';
    protected array $build = [];
    protected string $command = '';
    protected array $expectedOutput = [];

    public function setUp(): void
    {
        $headers = "x-sdk-name: {$this->sdkName}; x-sdk-platform: {$this->sdkPlatform}; x-sdk-language: {$this->sdkLanguage}; x-sdk-version: {$this->version}";
        array_push($this->expectedOutput, $headers);
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
            ->setName($this->sdkName)
            ->setVersion($this->version)
            ->setPlatform($this->sdkPlatform)
            ->setDescription('Repo description goes here')
            ->setShortDescription('Repo short description goes here')
            ->setLogo('https://appwrite.io/v1/images/console.png')
            ->setWarning('**WORK IN PROGRESS - THIS IS JUST A TEST SDK**')
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

        $dir = __DIR__ . '/sdks/' . $this->language;

        $this->rmdir_recursive($dir);

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

        $output = [];

        ob_end_clean();
        echo "Env Executing: {$this->command}\n";
        ob_start();

        exec($this->command, $output);

        foreach ($output as $i => $row) {
            echo "{$row}\n";
        }

        $this->assertIsArray($output);

        do {
            $removed = array_shift($output);
        } while ($removed != 'Test Started' && sizeof($output) != 0);

        $this->assertGreaterThanOrEqual(count($this->expectedOutput), count($output));

        foreach ($this->expectedOutput as $i => $row) {
            $this->assertEquals($output[$i], $row);
        }
    }

    private function rmdir_recursive($dir) {
        if (!\is_dir($dir)) {
            return;
        }
        foreach(\scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) continue;
            if (\is_dir("$dir/$file")) $this->rmdir_recursive("$dir/$file");
            else \unlink("$dir/$file");
        }
        rmdir($dir);
    }

    public function getLanguage(): Language 
    {
        return new $this->class();
    }
}
