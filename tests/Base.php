<?php

namespace Tests;

use Appwrite\SDK\Language;
use Appwrite\SDK\SDK;
use Appwrite\Spec\Swagger2;
use PHPUnit\Framework\TestCase;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

abstract class Base extends TestCase
{
    protected const FOO_RESPONSES = [
        'GET:/v1/mock/tests/foo:passed',
        'POST:/v1/mock/tests/foo:passed',
        'PUT:/v1/mock/tests/foo:passed',
        'PATCH:/v1/mock/tests/foo:passed',
        'DELETE:/v1/mock/tests/foo:passed',
    ];

    protected const BAR_RESPONSES = [
        'GET:/v1/mock/tests/bar:passed',
        'POST:/v1/mock/tests/bar:passed',
        'PUT:/v1/mock/tests/bar:passed',
        'PATCH:/v1/mock/tests/bar:passed',
        'DELETE:/v1/mock/tests/bar:passed',
    ];

    protected const GENERAL_RESPONSES = [
        'GET:/v1/mock/tests/general/redirect/done:passed',
    ];

    protected const OAUTH_RESPONSES = [
        'https://localhost?code=abcdef&state=123456',
    ];

    protected const DOWNLOAD_RESPONSES = [
        'GET:/v1/mock/tests/general/download:passed',
    ];

    protected const COOKIE_RESPONSES = [
        'GET:/v1/mock/tests/general/set-cookie:passed',
        'GET:/v1/mock/tests/general/get-cookie:passed',
    ];

    protected const ENUM_RESPONSES = [
        'POST:/v1/mock/tests/general/enum:passed',
    ];

    protected const UPLOAD_RESPONSE = [
        'POST:/v1/mock/tests/general/upload:passed',
    ];

    protected const UPLOAD_RESPONSES = [
        'POST:/v1/mock/tests/general/upload:passed',
        'POST:/v1/mock/tests/general/upload:passed',
        'POST:/v1/mock/tests/general/upload:passed',
        'POST:/v1/mock/tests/general/upload:passed',
    ];

    protected const EXCEPTION_RESPONSES = [
        'Mock 400 error',
        'Mock 500 error',
        'This is a text error',
    ];

    protected const REALTIME_RESPONSES = [
        'WS:/v1/realtime:passed',
    ];

    protected const MULTIPART_RESPONSES = [
        'abc',
        'd80e7e6999a3eb2ae0d631a96fe135a4',
        'Hello, World!',
        'myStringValue',
        
    ];

    protected const MULTIPART_RESPONSE_FILE = [
        'd80e7e6999a3eb2ae0d631a96fe135a4'
    ];

    protected const QUERY_HELPER_RESPONSES = [
        '{"method":"equal","attribute":"released","values":[true]}',
        '{"method":"equal","attribute":"title","values":["Spiderman","Dr. Strange"]}',
        '{"method":"notEqual","attribute":"title","values":["Spiderman"]}',
        '{"method":"lessThan","attribute":"releasedYear","values":[1990]}',
        '{"method":"greaterThan","attribute":"releasedYear","values":[1990]}',
        '{"method":"search","attribute":"name","values":["john"]}',
        '{"method":"isNull","attribute":"name"}',
        '{"method":"isNotNull","attribute":"name"}',
        '{"method":"between","attribute":"age","values":[50,100]}',
        '{"method":"between","attribute":"age","values":[50.5,100.5]}',
        '{"method":"between","attribute":"name","values":["Anna","Brad"]}',
        '{"method":"startsWith","attribute":"name","values":["Ann"]}',
        '{"method":"endsWith","attribute":"name","values":["nne"]}',
        '{"method":"select","values":["name","age"]}',
        '{"method":"orderAsc","attribute":"title"}',
        '{"method":"orderDesc","attribute":"title"}',
        '{"method":"cursorAfter","values":["my_movie_id"]}',
        '{"method":"cursorBefore","values":["my_movie_id"]}',
        '{"method":"limit","values":[50]}',
        '{"method":"offset","values":[20]}',
        '{"method":"contains","attribute":"title","values":["Spider"]}',
        '{"method":"contains","attribute":"labels","values":["first"]}',
        '{"method":"or","values":[{"method":"equal","attribute":"released","values":[true]},{"method":"lessThan","attribute":"releasedYear","values":[1990]}]}',
        '{"method":"and","values":[{"method":"equal","attribute":"released","values":[false]},{"method":"greaterThan","attribute":"releasedYear","values":[2015]}]}'
    ];

    protected const PERMISSION_HELPER_RESPONSES = [
        'read("any")',
        'write("user:userid")',
        'create("users")',
        'update("guests")',
        'delete("team:teamId/owner")',
        'delete("team:teamId")',
        'create("member:memberId")',
        'update("users/verified")',
        'update("user:userid/unverified")',
        'create("label:admin")',
    ];

    protected const ID_HELPER_RESPONSES = [
        'unique()',
        'custom_id'
    ];

    protected string $class = '';
    protected string $language = '';
    protected array $build = [];
    protected string $command = '';
    protected array $expectedOutput = [];
    protected string $sdkName;
    protected string $sdkPlatform;
    protected string $sdkLanguage;
    protected string $version;

    public function setUp(): void
    {
        $headers = "x-sdk-name: {$this->sdkName}; x-sdk-platform: {$this->sdkPlatform}; x-sdk-language: {$this->sdkLanguage}; x-sdk-version: {$this->version}";

        $this->expectedOutput[] = $headers;

        // Figure out if mock-server is running
        $isMockAPIRunning = \strlen(\exec('docker ps | grep mock-server')) > 0;

        if (!$isMockAPIRunning) {
            echo "Starting Mock API Server";

            \exec('
                cd ./mock-server && \
                docker compose build && \
                docker compose up -d --force-recreate
            ');
        }
    }

    public function tearDown(): void
    {
    }

    /**
     * @throws SyntaxError
     * @throws \Throwable
     * @throws RuntimeError
     * @throws LoaderError
     */
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

        $this->rmdirRecursive($dir);

        $sdk->generate(__DIR__ . '/sdks/' . $this->language);

        /**
         * Build SDK
         */
        foreach ($this->build as $command) {
            echo "Build Executing: {$command}\n";

            exec($command);
        }

        $output = [];

        echo "Env Executing: {$this->command}\n";

        exec($this->command, $output);

        $this->assertIsArray($output);

        do {
            $removed = \array_shift($output);
        } while ($removed != 'Test Started' && sizeof($output) != 0);

        echo \implode("\n", $output);

        foreach ($this->expectedOutput as $index => $expected) {
            // HACK: Swift does not guarantee the order of the JSON parameters
            if (\str_starts_with($expected, '{')) {
                $expectedJson = \json_decode($expected, true);
                $this->assertNotNull($expectedJson, 'Failed to decode expected JSON output: ' . $expected);

                $actualJson = \json_decode($output[$index], true);
                $this->assertNotNull($actualJson, 'Expected JSON object: ' . $expected . ', does not match received JSON object: ' . $output[$index]);

                $this->assertEquals(
                    $expectedJson,
                    $actualJson,
                );
            } elseif ($expected == 'unique()') {
                $this->assertNotEmpty($output[$index]);
                $this->assertIsString($output[$index]);
                $this->assertEquals(20, strlen($output[$index]));
                $this->assertNotEquals($output[$index], 'unique()');
            } else {
                $this->assertEquals($expected, $output[$index]);
            }
        }
    }

    private function rmdirRecursive($dir): void
    {
        if (!\is_dir($dir)) {
            return;
        }
        foreach (\scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }
            if (\is_dir("$dir/$file")) {
                $this->rmdirRecursive("$dir/$file");
            } else {
                \unlink("$dir/$file");
            }
        }
        \rmdir($dir);
    }

    public function getLanguage(): Language
    {
        return new $this->class();
    }
}
