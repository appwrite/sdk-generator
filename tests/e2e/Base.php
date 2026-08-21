<?php

declare(strict_types=1);

namespace Tests\E2E;

use Exception;
use Override;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use Throwable;
use Appwrite\SDK\Language;
use Appwrite\SDK\Language\PHP as PHPLanguage;
use Appwrite\SDK\SDK;
use Utopia\OpenAPI\Model\StringSchema;
use Utopia\OpenAPI\Parser;
use Utopia\OpenAPI\Specification;
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

    protected const PATH_PARAM_RESPONSES = [
        'GET:/v1/mock/tests/general/path/grant%2Fspecial%26id:passed',
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

    protected const EXCLUDED_FIXTURE_TOKENS = [
        'zzexcludedservice',
        'zzexcludedpayload',
        'zzexcludedresult',
        'zzexcludedstatus',
        'zzexcludedchild',
        'zzexcludedchildstatus',
        'zzexcludedmethodpayload',
        'zzexcludedmethodresult',
        'zzexcludedmethodstatus',
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
        'Realtime failed!',
        'Realtime unsubscribe:passed',
        'Realtime update:passed',
        'Realtime presence:passed',
        'Realtime disconnect:passed',
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
        '{"method":"containsAny","attribute":"labels","values":["first","second"]}',
        '{"method":"containsAll","attribute":"labels","values":["first","second"]}',
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
        '{"method":"vectorDot","attribute":"embedding","values":[[0.1,0.2,0.3]]}',
        '{"method":"vectorCosine","attribute":"embedding","values":[[0.1,0.2,0.3]]}',
        '{"method":"vectorEuclidean","attribute":"embedding","values":[[0.1,0.2,0.3]]}',
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
        '{"method":"count","attribute":"*","values":["total"]}',
        '{"method":"join","attribute":"orders","values":["$id","=","customerId"]}',
        '{"method":"groupBy","values":["status"]}',
        '{"method":"distinct"}',
        '{"method":"covers","attribute":"location","values":[[1,2]]}',
        '{"method":"countDistinct","attribute":"year","values":["uniqueYears"]}',
        '{"method":"sum","attribute":"price","values":["total"]}',
        '{"method":"avg","attribute":"price","values":["avgPrice"]}',
        '{"method":"min","attribute":"price","values":["lowest"]}',
        '{"method":"max","attribute":"price","values":["highest"]}',
        '{"method":"stddev","attribute":"price","values":["sd"]}',
        '{"method":"stddevPop","attribute":"price","values":["sdp"]}',
        '{"method":"stddevSamp","attribute":"price","values":["sds"]}',
        '{"method":"variance","attribute":"price","values":["var"]}',
        '{"method":"varPop","attribute":"price","values":["vp"]}',
        '{"method":"varSamp","attribute":"price","values":["vs"]}',
        '{"method":"bitAnd","attribute":"flags","values":["band"]}',
        '{"method":"bitOr","attribute":"flags","values":["bor"]}',
        '{"method":"bitXor","attribute":"flags","values":["bxor"]}',
        '{"method":"having","values":[{"method":"greaterThan","attribute":"total","values":[1]}]}',
        '{"method":"leftJoin","attribute":"orders","values":["$id","=","customerId","ord"]}',
        '{"method":"rightJoin","attribute":"orders","values":["$id","=","customerId"]}',
        '{"method":"fullOuterJoin","attribute":"orders","values":["$id","=","customerId"]}',
        '{"method":"crossJoin","attribute":"orders","values":["ord"]}',
        '{"method":"on","values":["$id","=","customerId"]}',
        '{"method":"leftJoin","attribute":"orders","values":["ord",{"method":"on","values":["$id","=","customerId"]},{"method":"equal","attribute":"ord.status","values":["paid"]}]}',
        '{"method":"notCovers","attribute":"location","values":[[1,2]]}',
        '{"method":"spatialEquals","attribute":"location","values":[[1,2]]}',
        '{"method":"notSpatialEquals","attribute":"location","values":[[1,2]]}',
        '{"method":"limit","values":[10]}',
        '{"method":"offset","values":[10]}',
        '{"method":"limit","values":[1]}',
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

    protected const ADDITIONAL_PROPERTIES_RESPONSES = [
        '{"theme":"dark","timezone":"UTC"}',
        '{"$id":"row1","custom":"value","nested":{"enabled":true}}',
        '{"data":{"enabled":true},"status":"ok","extra":"kept"}',
    ];

    protected const CLI_CONSOLE_URL_RESPONSES = [
        'https://cloud.appwrite.io/console/project-sgp-chirag-project-prod/sites/site-chirag-profile-website/deployments/deployment-123',
        'https://cloud.appwrite.io/console/project-sgp-chirag-project-prod/functions/function-sample-function/deployment-123',
        'https://abc.example.com/console/project-default-self-hosted-project/sites/site-docs/deployments/deployment-456',
    ];

    protected const CLI_HEADERS_RESPONSES = [
        'x-sdk-name: cli; x-sdk-platform: server; x-sdk-language: cli; '
            . 'x-sdk-version: 0.0.1; accept: application/json, text/plain',
    ];

    protected const CLI_FUNCTION_RESPONSES = [
        'POST:/v1/functions/{functionId}/executions:passed',
    ];

    protected const CLI_COMPLETION_RESPONSES = [
        'compdef _appwrite appwrite',
        'complete -F _appwrite_completion appwrite',
        'complete -c \'appwrite\' -f -n \'__appwrite_using_command\' -a \'bar client completion foo functions general\'',
        '\'foo:get\') context=\'foo get\' ;;',
    ];

    protected const CLI_TYPEGEN_RESPONSES = [
        'CLI_TYPEGEN:passed',
    ];

    protected const AUTH_LOGIC_RESPONSES = [
        'auth:endpoint-cloud-hostname:passed',
        'auth:endpoint-regional:passed',
        'auth:endpoint-localhost:passed',
        'auth:endpoint-cloud-login:passed',
        'auth:endpoint-dev-override:passed',
        'auth:endpoint-normalize:passed',
        'auth:console-slug-region:passed',
        'auth:decode-id-token:passed',
        'auth:authorization-pending-error:passed',
        'auth:session-account-key:passed',
        'auth:session-local-only:passed',
        'auth:refresh-token-keyring-storage:passed',
        'auth:refresh-token-prefs-fallback:passed',
        'auth:session-legacy:passed',
        'auth:session-has-auth:passed',
        'auth:plan-session-logout:passed',
        'auth:logout-question-choices:passed',
        'auth:logout-skips-empty-prompt:passed',
        'auth:logout-single-account-ignores-current-stub:passed',
        'auth:restore-current-session-fallback:passed',
        'auth:client-endpoint-session-reuse:passed',
        'auth:whoami-signed-in-account-hint:passed',
        'auth:client-reset-confirmation:passed',
        'auth:poll-device-token-success:passed',
        'auth:poll-device-token-retry:passed',
        'auth:poll-device-token-error:passed',
        'auth:poll-device-token-timeout:passed',
        'auth:poll-device-token-slow-down:passed',
        'auth:poll-device-token-empty-error:passed',
        'auth:poll-device-token-default-interval:passed',
        'auth:valid-access-token-cached:passed',
        'auth:valid-access-token-missing-expiry:passed',
        'auth:valid-access-token-session-endpoint:passed',
        'auth:project-session-endpoint-mismatch:passed',
        'auth:organization-header:passed',
        'auth:project-id-override:passed',
        'auth:cloud-login-rejects-credentials:passed',
        'auth:login-switch-selectors:passed',
        'auth:login-switch-headless:passed',
        'auth:login-switch-rejects-credentials:passed',
        'auth:open-browser:passed',
        'auth:context-organization-lookup:passed',
        'auth:context-project-precedence:passed',
    ];

    protected const CLI_REGRESSION_RESPONSES = [
        'CLI_ERROR_HANDLING:passed',
        'CLI_CONSOLE_FALLBACKS:passed',
    ];

    protected const CLI_ATTRIBUTE_SYNC_RESPONSES = [
        'attribute:in-place-updates:passed',
        'attribute:recreates-immutable:passed',
        'attribute:ignores-derived-fields:passed',
        'attribute:omitted-encrypt-not-recreate:passed',
        'attribute:index-columns-change:passed',
        'attribute:attribute-delete-uses-attribute-waiter:passed',
        'attribute:update-guards:passed',
        'attribute:resize-hard-fail:passed',
        'attribute:rename-in-place:passed',
        'attribute:rename-already-applied:passed',
        'attribute:rename-missing-both-creates:passed',
        'attribute:rename-both-exist-deletes-old:passed',
        'attribute:rename-plus-field-change:passed',
        'attribute:rename-preserves-indexes:passed',
        'attribute:rename-hard-fail-before-delete:passed',
        'attribute:rename-schema-validation:passed',
    ];

    protected const CLI_LOCAL_FUNCTION_EMULATION_RESPONSES = [
        'CLI_LOCAL_FUNCTION_RUNNER_CONFIG:passed',
        'CLI_LOCAL_SOURCE_PREFLIGHT:passed',
    ];

    protected const HEADERS_RESPONSE = '__HEADERS_RESPONSE__';

    protected const CLI_RESPONSE_RENDERING_RESPONSES = [
        'CLI_RUNTIME_RENDERING:passed',
        'CLI_DEPLOYMENT_RENDERING:passed',
    ];

    protected const CLI_QUERY_HELPER_RESPONSES = [
        '[' .
        '"{\"method\":\"orderDesc\",\"attribute\":\"rawName\"}",' .
        '"{\"method\":\"equal\",\"attribute\":\"published\",\"values\":[true]}",' .
        '"{\"method\":\"greaterThanEqual\",\"attribute\":\"score\",\"values\":[10]}",' .
        '"{\"method\":\"equal\",\"attribute\":\"legacy\",\"values\":[true]}",' .
        '"{\"method\":\"equal\",\"attribute\":\"status\",\"values\":[\"draft\",\"published\"]}",' .
        '"{\"method\":\"orderAsc\",\"attribute\":\"title\"}",' .
        '"{\"method\":\"orderDesc\",\"attribute\":\"$createdAt\"}",' .
        '"{\"method\":\"limit\",\"values\":[25]}",' .
        '"{\"method\":\"offset\",\"values\":[50]}",' .
        '"{\"method\":\"cursorAfter\",\"values\":[\"row-before\"]}",' .
        '"{\"method\":\"cursorBefore\",\"values\":[\"row-after\"]}",' .
        '"{\"method\":\"select\",\"values\":[\"$id\",\"title\"]}"' .
        ']',
        'CLI_QUERY_HELPERS:passed',
    ];

    protected const CHANNEL_HELPER_RESPONSES = [
        'databases.db1.collections.col1.documents',
        'databases.db1.collections.col1.documents.doc1',
        'databases.db1.collections.col1.documents.doc1.create',
        'databases.db1.collections.col1.documents.doc1.upsert',
        'tablesdb.db1.tables.table1.rows',
        'tablesdb.db1.tables.table1.rows.row1',
        'tablesdb.db1.tables.table1.rows.row1.update',
        'account',
        'buckets.bucket1.files',
        'buckets.bucket1.files.file1',
        'buckets.bucket1.files.file1.delete',
        'functions.func2',
        'functions.func1',
        'executions.exec2',
        'executions.exec1',
        'documents',
        'rows',
        'files',
        'executions',
        'teams',
        'teams.team2',
        'teams.team1',
        'teams.team1.create',
        'memberships',
        'memberships.membership2',
        'memberships.membership1',
        'memberships.membership1.update',
        'presences',
        'presences.presence2',
        'presences.presence1',
        'presences.presence1.upsert',
        'presences.presence1.update',
        'presences.presence1.delete',
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
        self::HEADERS_RESPONSE,
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

    #[Override]
    public function setUp(): void
    {
        \array_unshift($this->expectedOutput, $this->getExpectedSdkHeaders());

        $output = [];
        $status = 0;

        \exec(
            'cd ./mock-server && docker compose build && docker compose up -d --force-recreate 2>&1',
            $output,
            $status
        );

        if ($status !== 0) {
            $this->fail(
                "Failed to start mock-server (exit {$status}):\n" . \implode("\n", $output)
            );
        }
    }

    protected function getExpectedSdkHeaders(): string
    {
        return "x-sdk-name: {$this->sdkName}; x-sdk-platform: {$this->sdkPlatform}; x-sdk-language: {$this->sdkLanguage}; x-sdk-version: {$this->version}";
    }

    #[Override]
    public function tearDown(): void
    {
        // Remove the mock server so local test runs don't leave containers behind
        \exec('
            cd ./mock-server && \
            docker compose down
        ');
    }

    /**
     * @throws SyntaxError
     * @throws Throwable
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function testHTTPSuccess(): void
    {
        $spec = file_get_contents(realpath(__DIR__ . '/../resources/spec-openapi3.json'));

        if (empty($spec)) {
            throw new Exception('Failed to parse spec.');
        }

        $this->assertOpenEnumsAllowAnyString();
        $this->assertClosedOneOfStringEnumsAreEnums();
        $this->assertArrayEnumScalarHydrates();

        $sdk = new SDK($this->getLanguage(), Parser::parse($spec));

        $sdk
            ->setName($this->sdkName)
            ->setVersion($this->version)
            ->setPlatform($this->sdkPlatform)
            ->setDescription('Repo description goes here')
            ->setShortDescription('Repo short description goes here')
            ->setCoverImage('https://github.com/appwrite/appwrite/raw/main/public/images/github.png')
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
            ->setExclude([
                'services' => [
                    ['name' => 'zzexcludedservice'],
                ],
                'methods' => [
                    ['name' => 'createExcludedGeneralFixture'],
                ],
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
        $this->assertOpenEnumSuggestionsGenerated($dir);
        $this->assertOneOfStringEnumsUseWireNames($dir);
        $this->assertExcludedFixtureWasRemoved($dir);
        $this->assertCommentsAreNotHtmlEscaped($dir);

        /**
         * Build SDK
         */
        foreach ($this->build as $command) {
            echo "Build Executing: {$command}\n";

            $buildOutput = [];
            $status = 0;

            exec($command . ' 2>&1', $buildOutput, $status);

            echo \implode("\n", $buildOutput) . "\n";

            // A build step that produces no artifact -- `go test`, a linter --
            // used to fail in silence, because only the exit codes of steps
            // that something later reads were ever noticed.
            $this->assertSame(0, $status, "Build command failed: {$command}");
        }

        $output = [];

        echo "Env Executing: {$this->command}\n";

        exec($this->command, $output);

        $this->assertIsArray($output);

        do {
            $removed = \array_shift($output);
        } while ($removed != 'Test Started' && count($output) !== 0);

        echo \implode("\n", $output);

        foreach ($this->expectedOutput as $index => $expected) {
            if ($expected === self::HEADERS_RESPONSE) {
                $expected = $this->getExpectedSdkHeaders() . '; accept: application/json, text/plain';
            }

            // HACK: Swift does not guarantee the order of the JSON parameters
            if (\str_starts_with((string) $expected, '{')) {
                $this->assertEquals(
                    \json_decode((string) $expected, true),
                    \json_decode($output[$index], true)
                );
            } elseif ($expected == 'unique()') {
                $this->assertNotEmpty($output[$index]);
                $this->assertIsString($output[$index]);
                $this->assertSame(20, strlen($output[$index]));
                $this->assertNotSame('unique()', $output[$index]);
            } else {
                $this->assertEquals($expected, $output[$index]);
            }
        }
    }

    private function assertClosedOneOfStringEnumsAreEnums(): void
    {
        $oneOfKind = [
            'type' => 'string',
            'example' => 'alpha',
            'oneOf' => [
                ['type' => 'string', 'enum' => ['alpha'], 'title' => 'alpha'],
                ['type' => 'string', 'enum' => ['beta'], 'title' => 'beta'],
            ],
        ];
        $specification = Parser::parse([
            'openapi' => '3.0.0',
            'info' => ['title' => 'test', 'version' => '1.0.0'],
            'paths' => ['/items/{kind}' => ['get' => [
                'operationId' => 'getItem',
                'parameters' => [[
                    'name' => 'kind',
                    'in' => 'path',
                    'required' => true,
                    'schema' => $oneOfKind + ['title' => 'MockKind'],
                ]],
                'responses' => ['200' => ['description' => 'ok']],
            ]]],
            'components' => ['schemas' => [
                'widget' => [
                    'type' => 'object',
                    'required' => ['kind'],
                    'properties' => [
                        'kind' => $oneOfKind,
                    ],
                ],
            ]],
        ]);
        $language = $this->getLanguage();
        $parameter = $specification->paths['/items/{kind}']->operations['get']->parameters[0];
        $plainString = Parser::parse([
            'openapi' => '3.0.0',
            'info' => ['title' => 'test', 'version' => '1.0.0'],
            'paths' => ['/test' => ['get' => [
                'operationId' => 'test',
                'parameters' => [
                    ['name' => 'plainScalar', 'in' => 'query', 'schema' => ['type' => 'string']],
                    ['name' => 'plainObject', 'in' => 'query', 'schema' => ['type' => 'object']],
                ],
                'responses' => ['200' => ['description' => 'ok']],
            ]]],
        ])->paths['/test']->operations['get']->parameters;
        [$plainScalar, $plainObject] = $plainString;

        $this->assertNotSame(
            $language->getTypeName($plainObject, $specification),
            $language->getTypeName($parameter, $specification),
            'A oneOf of string enums must not type as a generic object.'
        );
        $this->assertNotSame(
            $language->getTypeName($plainScalar, $specification),
            $language->getTypeName($plainObject, $specification)
        );
        $enumSchema = $language->getEnumSchema($parameter);
        $this->assertSame(['alpha', 'beta'], $enumSchema->enum);

        $untitled = $specification->schemas['widget']->properties['kind'];
        $untitledType = $language->getTypeName($untitled, $specification);
        $this->assertNotSame('', $untitledType);
        $this->assertNotSame(
            $language->getTypeName($plainObject, $specification),
            $untitledType,
            'An untitled oneOf string enum must still produce a concrete type name.'
        );
    }

    private function assertOneOfStringEnumsUseWireNames(string $dir): void
    {
        $expectations = [
            'php' => [
                $dir . '/src/Appwrite/Models/Mock.php',
                "array_key_exists('kind'",
                "array_key_exists('MockKind'",
            ],
            'python' => [
                $dir . '/appwrite/models/mock.py',
                "alias='kind'",
                "alias='MockKind'",
            ],
            'kotlin' => [
                $dir . '/src/main/kotlin/io/appwrite/models/Mock.kt',
                '@SerializedName("kind")',
                '@SerializedName("MockKind")',
            ],
            'android' => [
                $dir . '/library/src/main/java/io/appwrite/models/Mock.kt',
                '@SerializedName("kind")',
                '@SerializedName("MockKind")',
            ],
        ];

        if (!isset($expectations[$this->language])) {
            return;
        }

        [$modelPath, $wireName, $titleName] = $expectations[$this->language];
        $this->assertFileExists($modelPath);
        $contents = file_get_contents($modelPath);
        $this->assertIsString($contents);
        $this->assertStringContainsString($wireName, $contents);
        $this->assertStringNotContainsString($titleName, $contents);
    }

    private function assertArrayEnumScalarHydrates(): void
    {
        if ($this->language !== 'php') {
            return;
        }

        $specification = Parser::parse([
            'openapi' => '3.0.0',
            'info' => ['title' => 'test', 'version' => '1.0.0'],
            'paths' => ['/oauth' => ['post' => [
                'operationId' => 'updateOAuth',
                'tags' => ['project'],
                'responses' => ['200' => [
                    'description' => 'ok',
                    'content' => ['application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/OAuth2Google'],
                    ]],
                ]],
            ]]],
            'components' => ['schemas' => [
                'OAuth2Google' => [
                    'type' => 'object',
                    'required' => ['prompt'],
                    'properties' => [
                        'prompt' => [
                            'type' => 'array',
                            'example' => 'none',
                            'items' => [
                                'type' => 'string',
                                'enum' => ['none', 'consent'],
                                'x-enum-name' => 'OAuth2Prompt',
                            ],
                        ],
                    ],
                ],
            ]],
        ]);

        $php = new class () extends PHPLanguage {
            public function mockPayload(string $definitionName, Specification $spec): string
            {
                return $this->getMockDefinitionPayload($definitionName, $spec);
            }
        };
        $payload = $php->mockPayload('OAuth2Google', $specification);
        $this->assertStringContainsString('"prompt" => array("none")', $payload);
        $this->assertStringNotContainsString('"prompt" => "none"', $payload);

        $dir = \sys_get_temp_dir() . '/sdk-array-enum-' . \bin2hex(\random_bytes(4));
        $sdk = new SDK(new PHPLanguage(), $specification);
        $sdk
            ->setName('php')
            ->setVersion('0.0.1')
            ->setNamespace('appwrite');
        $sdk->generate($dir);

        try {
            $modelPath = $dir . '/src/Appwrite/Models/OAuth2Google.php';
            $this->assertFileExists($modelPath);
            $modelSource = \file_get_contents($modelPath);
            $this->assertIsString($modelSource);
            $this->assertStringContainsString(
                '[static::hydrateTypedValue(OAuth2Prompt::class, $data[\'prompt\'])]',
                $modelSource
            );

            require_once $dir . '/src/Appwrite/Models/ArraySerializable.php';
            foreach (\glob($dir . '/src/Appwrite/Enums/*.php') ?: [] as $file) {
                require_once $file;
            }
            require_once $modelPath;

            $modelClass = 'Appwrite\\Models\\OAuth2Google';
            $model = $modelClass::from(['prompt' => 'none']);
            $this->assertCount(1, $model->prompt);
            $this->assertSame('none', (string) $model->prompt[0]);
        } finally {
            $this->rmdirRecursive($dir);
        }
    }

    private function assertOpenEnumsAllowAnyString(): void
    {
        $enumBranch = [
            'type' => 'string',
            'enum' => ['user.created', 'user.updated'],
            'x-enum-name' => 'WebhookEvent',
            'x-enum-keys' => ['UserCreated', 'UserUpdated'],
        ];
        $specification = Parser::parse([
            'openapi' => '3.0.0',
            'info' => ['title' => 'test', 'version' => '1.0.0'],
            'paths' => ['/test' => ['get' => [
                'operationId' => 'testOpenEnums',
                'parameters' => [
                    ['name' => 'plainScalar', 'in' => 'query', 'schema' => ['type' => 'string']],
                    ['name' => 'openScalar', 'in' => 'query', 'schema' => ['anyOf' => [$enumBranch, ['type' => 'string']]]],
                    ['name' => 'plainArray', 'in' => 'query', 'schema' => ['type' => 'array', 'items' => ['type' => 'string']]],
                    ['name' => 'openArray', 'in' => 'query', 'schema' => ['type' => 'array', 'items' => ['anyOf' => [$enumBranch, ['type' => 'string']]]]],
                ],
                'responses' => ['200' => ['description' => 'ok']],
            ]]],
        ]);
        [$plainScalar, $openScalar, $plainArray, $openArray] = $specification->paths['/test']->operations['get']->parameters;
        $language = $this->getLanguage();
        $sdk = new class ($language, $specification) extends SDK {
            /** @param list<StringSchema> $schemas @return list<StringSchema> */
            public function mergeEnumsForTest(array $schemas): array
            {
                return $this->mergeEnums($schemas);
            }
        };
        $mergedEnums = $sdk->mergeEnumsForTest([
            new StringSchema(title: 'SharedEnum', enum: ['known'], open: true),
            new StringSchema(title: 'SharedEnum', enum: ['known'], open: false),
        ]);
        $this->assertCount(1, $mergedEnums);
        $this->assertFalse($mergedEnums[0]->open, 'A closed use must keep a shared enum type closed.');

        if ($language->keepsOpenEnumType()) {
            $this->assertSame('(WebhookEvent | (string & {}))', $language->getTypeName($openScalar, $specification));
            $this->assertSame('(WebhookEvent | (string & {}))[]', $language->getTypeName($openArray, $specification));

            return;
        }

        $this->assertSame(
            $language->getTypeName($plainScalar, $specification),
            $language->getTypeName($openScalar, $specification),
        );
        $this->assertSame(
            $language->getTypeName($plainArray, $specification),
            $language->getTypeName($openArray, $specification),
        );
    }

    private function assertOpenEnumSuggestionsGenerated(string $dir): void
    {
        $expectations = [
            'web' => ['src/enums/webhook-event.ts', 'UserCreated'],
            'node' => ['src/enums/webhook-event.ts', 'UserCreated'],
            'react-native' => ['src/enums/webhook-event.ts', 'UserCreated'],
            'deno' => ['src/enums/webhook-event.ts', 'UserCreated'],
            'php' => ['src/Appwrite/Enums/WebhookEvent.php', 'public const USERCREATED'],
            'python' => ['appwrite/enums/webhook_event.py', 'USERCREATED = "user.created"'],
            'ruby' => ['lib/appwrite/enums/webhook_event.rb', "USERCREATED = 'user.created'"],
            'dart' => ['lib/src/enums/webhook_event.dart', 'static const String userCreated'],
            'flutter' => ['lib/src/enums/webhook_event.dart', 'static const String userCreated'],
            'kotlin' => ['src/main/kotlin/io/appwrite/enums/WebhookEvent.kt', 'const val USERCREATED'],
            'android' => ['library/src/main/java/io/appwrite/enums/WebhookEvent.kt', 'const val USERCREATED'],
            'swift' => ['Sources/AppwriteEnums/WebhookEvent.swift', 'public static let userCreated'],
            'apple' => ['Sources/AppwriteEnums/WebhookEvent.swift', 'public static let userCreated'],
            'dotnet' => ['Appwrite/Enums/WebhookEvent.cs', 'public const string UserCreated'],
            'unity' => ['Assets/Runtime/Core/Enums/WebhookEvent.cs', 'public const string UserCreated'],
            'rust' => ['src/enums/webhook_event.rs', 'pub const UserCreated'],
        ];

        if (!isset($expectations[$this->language])) {
            return;
        }

        [$relativePath, $knownValueDeclaration] = $expectations[$this->language];
        $path = $dir . '/' . $relativePath;
        $this->assertFileExists($path);
        $contents = file_get_contents($path);
        $this->assertIsString($contents);
        $this->assertStringContainsString($knownValueDeclaration, $contents);
        $this->assertStringContainsString('user.updated', $contents);
    }

    private function rmdirRecursive(string $dir): void
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

    private function assertExcludedFixtureWasRemoved(string $dir): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $path = \strtolower((string) $file->getPathname());

            foreach (self::EXCLUDED_FIXTURE_TOKENS as $token) {
                $this->assertStringNotContainsString($token, $path, "Excluded fixture leaked into generated path: {$path}");
            }

            if (!$file->isFile()) {
                continue;
            }

            $contents = \file_get_contents($file->getPathname());

            if ($contents === false) {
                continue;
            }

            $contents = \strtolower($contents);

            foreach (self::EXCLUDED_FIXTURE_TOKENS as $token) {
                $this->assertStringNotContainsString(
                    $token,
                    $contents,
                    "Excluded fixture leaked into generated file: {$file->getPathname()}"
                );
            }
        }
    }

    /**
     * Twig autoescapes to HTML, so a description that reaches the template through an
     * unsafe filter arrives in the source as `&quot;` rather than `"`. Go doc comments
     * are read as plain text, so the entity is what the reader sees.
     *
     * Scoped to the generated models: the CLI ships hand-written Go that escapes HTML
     * as its job, and those entities are the payload rather than a leak.
     */
    private function assertCommentsAreNotHtmlEscaped(string $dir): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'go') {
                continue;
            }

            if (!\str_contains((string) $file->getPath(), '/models')) {
                continue;
            }

            $contents = \file_get_contents($file->getPathname());

            if ($contents === false) {
                continue;
            }

            foreach (['&quot;', '&#039;', '&amp;', '&lt;', '&gt;'] as $entity) {
                $this->assertStringNotContainsString(
                    $entity,
                    $contents,
                    "HTML entity {$entity} leaked into generated source: {$file->getPathname()}"
                );
            }
        }
    }

    public function getLanguage(): Language
    {
        return new $this->class();
    }
}
