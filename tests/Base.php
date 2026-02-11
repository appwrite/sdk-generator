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
    protected const PING_RESPONSE = [
        'GET:/v1/ping:passed',
    ];

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

    protected const MODEL_RESPONSES = [
        'POST:/v1/mock/tests/general/models:passed',
        'POST:/v1/mock/tests/general/models/array:passed',
    ];

    protected const UNION_RESPONSES = [
        'GET:/v1/mock/tests/union:passed',
        'test-data',
        'stub',
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

    protected const LARGE_FILE_RESPONSES = [
        'POST:/v1/mock/tests/general/upload:passed',
    ];

    /**
     * 'Mock 400 error'                              -> message
     * '{"message":"Mock 400 error","code":400}'     -> response
     */
    protected const EXCEPTION_RESPONSES = [
        'Mock 400 error',
        '{"message":"Mock 400 error","code":400}',
        'Mock 500 error',
        '{"message":"Mock 500 error","code":500}',
        'This is a text error',
        'This is a text error',
        'Invalid endpoint URL: htp://cloud.appwrite.io/v1',
    ];

    protected const REALTIME_RESPONSES = [
        'WS:/v1/realtime:passed',
        'WS:/v1/realtime:passed',
        'Realtime failed!'
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
        '{"method":"orderRandom"}',
        '{"method":"cursorAfter","values":["my_movie_id"]}',
        '{"method":"cursorBefore","values":["my_movie_id"]}',
        '{"method":"limit","values":[50]}',
        '{"method":"offset","values":[20]}',
        '{"method":"contains","attribute":"title","values":["Spider"]}',
        '{"method":"contains","attribute":"labels","values":["first"]}',
        '{"method":"notContains","attribute":"title","values":["Spider"]}',
        '{"method":"notSearch","attribute":"name","values":["john"]}',
        '{"method":"notBetween","attribute":"age","values":[50,100]}',
        '{"method":"notStartsWith","attribute":"name","values":["Ann"]}',
        '{"method":"notEndsWith","attribute":"name","values":["nne"]}',
        '{"method":"lessThan","attribute":"$createdAt","values":["2023-01-01"]}',
        '{"method":"greaterThan","attribute":"$createdAt","values":["2023-01-01"]}',
        '{"method":"between","attribute":"$createdAt","values":["2023-01-01","2023-12-31"]}',
        '{"method":"lessThan","attribute":"$updatedAt","values":["2023-01-01"]}',
        '{"method":"greaterThan","attribute":"$updatedAt","values":["2023-01-01"]}',
        '{"method":"between","attribute":"$updatedAt","values":["2023-01-01","2023-12-31"]}',
        '{"method":"distanceEqual","attribute":"location","values":[[[[40.7128,-74],[40.7128,-74]],1000,true]]}',
        '{"method":"distanceEqual","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"distanceNotEqual","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"distanceNotEqual","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"distanceGreaterThan","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"distanceGreaterThan","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"distanceLessThan","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"distanceLessThan","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"intersects","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"notIntersects","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"crosses","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"notCrosses","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"overlaps","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"notOverlaps","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"touches","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"notTouches","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"contains","attribute":"location","values":[[40.7128,-74],[40.7128,-74]]}',
        '{"method":"notContains","attribute":"location","values":[[40.7128,-74],[40.7128,-74]]}',
        '{"method":"equal","attribute":"location","values":[[40.7128,-74],[40.7128,-74]]}',
        '{"method":"notEqual","attribute":"location","values":[[40.7128,-74],[40.7128,-74]]}',
        '{"method":"or","values":[{"method":"equal","attribute":"released","values":[true]},{"method":"lessThan","attribute":"releasedYear","values":[1990]}]}',
        '{"method":"and","values":[{"method":"equal","attribute":"released","values":[false]},{"method":"greaterThan","attribute":"releasedYear","values":[2015]}]}',
        '{"method":"regex","attribute":"name","values":["pattern.*"]}',
        '{"method":"exists","values":["attr1","attr2"]}',
        '{"method":"notExists","values":["attr1","attr2"]}',
        '{"method":"elemMatch","attribute":"friends","values":[{"method":"equal","attribute":"name","values":["Alice"]},{"method":"greaterThan","attribute":"age","values":[18]}]}',
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

    protected const CHANNEL_HELPER_RESPONSES = [
        'databases.*.collections.*.documents.*',
        'databases.db1.collections.col1.documents.doc1',
        'databases.db1.collections.col1.documents.doc1.create',
        'tablesdb.*.tables.*.rows.*',
        'tablesdb.db1.tables.table1.rows.row1',
        'tablesdb.db1.tables.table1.rows.row1.update',
        'account',
        'account.user123',
        'buckets.*.files.*',
        'buckets.bucket1.files.file1',
        'buckets.bucket1.files.file1.delete',
        'functions.*.executions.*',
        'functions.func1.executions.exec1',
        'functions.func1.executions.exec1.create',
        'documents',
        'rows',
        'files',
        'executions',
        'teams',
        'teams.*',
        'teams.team1',
        'teams.team1.create',
        'memberships.*',
        'memberships.membership1',
        'memberships.membership1.update',
    ];

    protected const OPERATOR_HELPER_RESPONSES = [
        '{"method":"increment","values":[1]}',
        '{"method":"increment","values":[5,100]}',
        '{"method":"decrement","values":[1]}',
        '{"method":"decrement","values":[3,0]}',
        '{"method":"multiply","values":[2]}',
        '{"method":"multiply","values":[3,1000]}',
        '{"method":"divide","values":[2]}',
        '{"method":"divide","values":[4,1]}',
        '{"method":"modulo","values":[5]}',
        '{"method":"power","values":[2]}',
        '{"method":"power","values":[3,100]}',
        '{"method":"arrayAppend","values":["item1","item2"]}',
        '{"method":"arrayPrepend","values":["first","second"]}',
        '{"method":"arrayInsert","values":[0,"newItem"]}',
        '{"method":"arrayRemove","values":["oldItem"]}',
        '{"method":"arrayUnique","values":[]}',
        '{"method":"arrayIntersect","values":["a","b","c"]}',
        '{"method":"arrayDiff","values":["x","y"]}',
        '{"method":"arrayFilter","values":["equal","test"]}',
        '{"method":"stringConcat","values":["suffix"]}',
        '{"method":"stringReplace","values":["old","new"]}',
        '{"method":"toggle","values":[]}',
        '{"method":"dateAddDays","values":[7]}',
        '{"method":"dateSubDays","values":[3]}',
        '{"method":"dateSetNow","values":[]}',
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

        \exec('
            cd ./mock-server && \
            docker compose build && \
            docker compose up -d --force-recreate
        ');
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
            ->setGitUserName('repoowner')
            ->setGitRepoName('reponame')
            ->setLicense('BSD-3-Clause')
            ->setLicenseContent('demo license')
            ->setChangelog('--changelog--')
            ->setDefaultHeaders([
                'X-Appwrite-Response-Format' => '0.8.0',
            ])
            ->setTest("true");

        if ($this->language === 'android' || $this->language === 'kotlin') {
            $sdk->setNamespace("io.appwrite");
        } else {
            $sdk->setNamespace("appwrite");
        }

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
                $this->assertEquals(
                    \json_decode($expected, true),
                    \json_decode($output[$index], true)
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
