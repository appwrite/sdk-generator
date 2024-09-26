<?php

namespace Utopia\MockServer;

if (\file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use Swoole\Constant;
use Utopia\App;
use Utopia\Database\Helpers\ID;
use Utopia\MockServer\Utopia\Exception;
use Utopia\MockServer\Utopia\File;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Utopia\CLI\Console;
use Utopia\MockServer\Utopia\Response;
use Utopia\Swoole\Request;
use Utopia\Validator\Text;
use Utopia\Validator\Integer;
use Utopia\Validator\ArrayList;
use Utopia\Validator\Host;
use Utopia\Validator\Nullable;
use Utopia\Validator\WhiteList;
use Swoole\Process;
use Swoole\Http\Server;

// Appwrite Init Consts
const APP_AUTH_TYPE_SESSION = 'Session';
const APP_AUTH_TYPE_JWT = 'JWT';
const APP_AUTH_TYPE_KEY = 'Key';
const APP_AUTH_TYPE_ADMIN = 'Admin';
const APP_LIMIT_ARRAY_PARAMS_SIZE = 100; // Default maximum of how many elements can there be in API parameter that expects array value
const APP_PLATFORM_SERVER = 'server';
const APP_PLATFORM_CLIENT = 'client';
const APP_PLATFORM_CONSOLE = 'console';
const APP_STORAGE_CACHE = '/storage/cache';

$http = new Server(
    host: '0.0.0.0',
    port: App::getEnv('PORT', 80),
    mode: SWOOLE_PROCESS
);
$payloadSize = 6 * (1024 * 1024); // 6MB
$workerNumber = swoole_cpu_num() * intval(App::getEnv('_APP_WORKER_PER_CORE', 6));

$http->set([
    'worker_num' => $workerNumber,
    'open_http2_protocol' => true,
    'http_compression' => true,
    'http_compression_level' => 6,
    'package_max_length' => $payloadSize,
    'buffer_output_size' => $payloadSize,
]);

// Version Route for CLI
App::get('/v1/health/version')
    ->desc('Get version')
    ->groups(['api', 'health'])
    ->label('scope', 'public')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->inject('response')
    ->action(function (Response $response) {
        $response->json([ 'version' => '1.0.0' ]);
    });

// Mock Routes
App::get('/v1/mock/tests/foo')
    ->desc('Get Foo')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'foo')
    ->label('sdk.method', 'get')
    ->label('sdk.description', 'Mock a get request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('x', '', new Text(100), 'Sample string param')
    ->param('y', '', new Integer(true), 'Sample numeric param')
    ->param('z', null, new ArrayList(new Text(256), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Sample array param')
    ->action(function ($x, $y, $z) {
    });

App::post('/v1/mock/tests/foo')
    ->desc('Post Foo')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'foo')
    ->label('sdk.method', 'post')
    ->label('sdk.description', 'Mock a post request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('x', '', new Text(100), 'Sample string param')
    ->param('y', '', new Integer(true), 'Sample numeric param')
    ->param('z', null, new ArrayList(new Text(256), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Sample array param')
    ->action(function ($x, $y, $z) {
    });

App::patch('/v1/mock/tests/foo')
    ->desc('Patch Foo')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'foo')
    ->label('sdk.method', 'patch')
    ->label('sdk.description', 'Mock a patch request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('x', '', new Text(100), 'Sample string param')
    ->param('y', '', new Integer(true), 'Sample numeric param')
    ->param('z', null, new ArrayList(new Text(256), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Sample array param')
    ->action(function ($x, $y, $z) {
    });

App::put('/v1/mock/tests/foo')
    ->desc('Put Foo')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'foo')
    ->label('sdk.method', 'put')
    ->label('sdk.description', 'Mock a put request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('x', '', new Text(100), 'Sample string param')
    ->param('y', '', new Integer(true), 'Sample numeric param')
    ->param('z', null, new ArrayList(new Text(256), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Sample array param')
    ->action(function ($x, $y, $z) {
    });

App::delete('/v1/mock/tests/foo')
    ->desc('Delete Foo')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'foo')
    ->label('sdk.method', 'delete')
    ->label('sdk.description', 'Mock a delete request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('x', '', new Text(100), 'Sample string param')
    ->param('y', '', new Integer(true), 'Sample numeric param')
    ->param('z', null, new ArrayList(new Text(256), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Sample array param')
    ->action(function ($x, $y, $z) {
    });

App::get('/v1/mock/tests/bar')
    ->desc('Get Bar')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'bar')
    ->label('sdk.method', 'get')
    ->label('sdk.description', 'Mock a get request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('required', '', new Text(100), 'Sample string param')
    ->param('default', '', new Integer(true), 'Sample numeric param')
    ->param('z', null, new ArrayList(new Text(256), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Sample array param')
    ->action(function ($required, $default, $z) {
    });

App::post('/v1/mock/tests/bar')
    ->desc('Post Bar')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'bar')
    ->label('sdk.method', 'post')
    ->label('sdk.description', 'Mock a post request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.offline.model', '/mock/tests/bar')
    ->label('sdk.offline.key', '{required}')
    ->label('sdk.mock', true)
    ->param('required', '', new Text(100), 'Sample string param')
    ->param('default', '', new Integer(true), 'Sample numeric param')
    ->param('z', null, new ArrayList(new Text(256), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Sample array param')
    ->action(function ($required, $default, $z) {
    });

App::patch('/v1/mock/tests/bar')
    ->desc('Patch Bar')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'bar')
    ->label('sdk.method', 'patch')
    ->label('sdk.description', 'Mock a patch request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('required', '', new Text(100), 'Sample string param')
    ->param('default', '', new Integer(true), 'Sample numeric param')
    ->param('z', null, new ArrayList(new Text(256), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Sample array param')
    ->action(function ($required, $default, $z) {
    });

App::put('/v1/mock/tests/bar')
    ->desc('Put Bar')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'bar')
    ->label('sdk.method', 'put')
    ->label('sdk.description', 'Mock a put request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('required', '', new Text(100), 'Sample string param')
    ->param('default', '', new Integer(true), 'Sample numeric param')
    ->param('z', null, new ArrayList(new Text(256), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Sample array param')
    ->action(function ($required, $default, $z) {
    });

App::delete('/v1/mock/tests/bar')
    ->desc('Delete Bar')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'bar')
    ->label('sdk.method', 'delete')
    ->label('sdk.description', 'Mock a delete request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('required', '', new Text(100), 'Sample string param')
    ->param('default', '', new Integer(true), 'Sample numeric param')
    ->param('z', null, new ArrayList(new Text(256), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Sample array param')
    ->action(function ($required, $default, $z) {
    });

App::get('/v1/mock/tests/general/headers')
    ->desc('Get headers')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'headers')
    ->label('sdk.description', 'Return headers from the request')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->inject('request')
    ->inject('response')
    ->action(function (Request $request, Response $response) {
        $res = [
            'x-sdk-name' => $request->getHeader('x-sdk-name'),
            'x-sdk-platform' => $request->getHeader('x-sdk-platform'),
            'x-sdk-language' => $request->getHeader('x-sdk-language'),
            'x-sdk-version' => $request->getHeader('x-sdk-version'),
        ];
        $res = array_map(function ($key, $value) {
            return $key . ': ' . $value;
        }, array_keys($res), $res);
        $res = implode("; ", $res);

        $response->json(['result' => $res]);
    });

App::get('/v1/mock/tests/general/download')
    ->desc('Download File')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'download')
    ->label('sdk.methodType', 'location')
    ->label('sdk.description', 'Mock a file download request.')
    ->label('sdk.response.type', '*/*')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.mock', true)
    ->inject('response')
    ->action(function (Response $response) {

        $response
            ->setContentType('text/plain')
            ->addHeader('Content-Disposition', 'attachment; filename="test.txt"')
            ->addHeader('Expires', \date('D, d M Y H:i:s', \time() + (60 * 60 * 24 * 45)) . ' GMT') // 45 days cache
            ->addHeader('X-Peak', \memory_get_peak_usage())
            ->send("GET:/v1/mock/tests/general/download:passed");
    });

App::post('/v1/mock/tests/general/upload')
    ->desc('Upload File')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'upload')
    ->label('sdk.description', 'Mock a file upload request.')
    ->label('sdk.request.type', 'multipart/form-data')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('x', '', new Text(100), 'Sample string param')
    ->param('y', '', new Integer(true), 'Sample numeric param')
    ->param('z', null, new ArrayList(new Text(256), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Sample array param')
    ->param('file', [], new File(), 'Sample file param', skipValidation: true)
    ->inject('request')
    ->inject('response')
    ->action(function (string $x, int $y, array $z, mixed $file, Request $request, Response $response) {
        $file = $request->getFiles('file');

        $contentRange = $request->getHeader('content-range');

        $chunkSize = 5 * 1024 * 1024; // 5MB

        if (!empty($contentRange)) {
            $start = $request->getContentRangeStart();
            $end = $request->getContentRangeEnd();
            $size = $request->getContentRangeSize();
            $id = $request->getHeader('x-appwrite-id', '');
            $file['size'] = (\is_array($file['size'])) ? $file['size'][0] : $file['size'];

            if (is_null($start) || is_null($end) || is_null($size) || $end >= $size) {
                throw new Exception(Exception::GENERAL_MOCK, 'Invalid content-range header');
            }

            if ($start > $end || $end > $size) {
                throw new Exception(Exception::GENERAL_MOCK, 'Invalid content-range header');
            }

            if ($start === 0 && !empty($id)) {
                throw new Exception(Exception::GENERAL_MOCK, 'First chunked request cannot have id header');
            }

            if ($start !== 0 && $id !== 'newfileid') {
                throw new Exception(Exception::GENERAL_MOCK, 'All chunked request must have id header (except first)');
            }

            if ($end !== $size - 1 && $end - $start + 1 !== $chunkSize) {
                throw new Exception(Exception::GENERAL_MOCK, 'Chunk size must be 5MB (except last chunk)');
            }

            if ($end !== $size - 1 && $file['size'] !== $chunkSize) {
                throw new Exception(Exception::GENERAL_MOCK, 'Wrong chunk size');
            }

            if ($file['size'] > $chunkSize) {
                throw new Exception(Exception::GENERAL_MOCK, 'Chunk size must be 5MB or less');
            }

            if ($end !== $size - 1) {
                $response->json([
                    '$id' => ID::custom('newfileid'),
                    'chunksTotal' => (int) ceil($size / ($end + 1 - $start)),
                    'chunksUploaded' => ceil($start / $chunkSize) + 1
                ]);
            }
        } else {
            $file['tmp_name'] = (\is_array($file['tmp_name'])) ? $file['tmp_name'][0] : $file['tmp_name'];
            $file['name'] = (\is_array($file['name'])) ? $file['name'][0] : $file['name'];
            $file['size'] = (\is_array($file['size'])) ? $file['size'][0] : $file['size'];

            if ($file['name'] !== 'file.png') {
                throw new Exception(Exception::GENERAL_MOCK, 'Wrong file name');
            }

            if ($file['size'] !== 38756) {
                throw new Exception(Exception::GENERAL_MOCK, 'Wrong file size');
            }

            if (\md5(\file_get_contents($file['tmp_name'])) !== 'd80e7e6999a3eb2ae0d631a96fe135a4') {
                throw new Exception(Exception::GENERAL_MOCK, 'Wrong file uploaded');
            }
        }
    });

App::get('/v1/mock/tests/general/multipart')
    ->alias('/v1/mock/tests/general/multipart-compiled')
    ->desc('Multipart')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'multipart')
    ->label('sdk.description', 'Mock a multipart request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_MULTIPART)
    ->label('sdk.response.model', Response::MODEL_MULTIPART)
    ->label('sdk.mock', true)
    ->inject('response')
    ->action(function (Response $response) {
        $file = \file_get_contents(\getcwd() . '/resources/file.png');

        $response->multipart([
            'x' => 'abc',
            'y' => 123,
            'responseBody' => $file,
        ]);
    });

App::get('/v1/mock/tests/general/multipart-echo')
    ->desc('Multipart echo')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'multipartEcho')
    ->label('sdk.description', 'Echo a multipart request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_MULTIPART)
    ->label('sdk.response.model', Response::MODEL_MULTIPART)
    ->label('sdk.mock', true)
    ->param('body', '', new Text(100), 'Sample string param', false, [], true)
    ->inject('response')
    ->action(function (string $body, Response $response) {

        $response->multipart([
            'responseBody' => $body
        ]);
    });

App::get('/v1/mock/tests/general/redirect')
    ->desc('Redirect')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'redirect')
    ->label('sdk.description', 'Mock a redirect request.')
    ->label('sdk.response.code', Response::STATUS_CODE_MOVED_PERMANENTLY)
    ->label('sdk.response.type', Response::CONTENT_TYPE_HTML)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->inject('response')
    ->action(function (Response $response) {
        $response->redirect('/v1/mock/tests/general/redirect/done');
    });

App::get('/v1/mock/tests/general/redirect/done')
    ->desc('Redirected')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'redirected')
    ->label('sdk.description', 'Mock a redirected request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->action(function () {
    });

App::get('/v1/mock/tests/general/set-cookie')
    ->desc('Set Cookie')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'setCookie')
    ->label('sdk.description', 'Mock a set cookie request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->inject('response')
    ->inject('request')
    ->action(function (Response $response, Request $request) {
        $response->addCookie('cookieName', 'cookieValue', \time() + 31536000, '/', $request->getHostname(), true, true);
    });

App::get('/v1/mock/tests/general/get-cookie')
    ->desc('Get Cookie')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'getCookie')
    ->label('sdk.description', 'Mock a cookie response.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->inject('request')
    ->action(function (Request $request) {
        if ($request->getCookie('cookieName', '') !== 'cookieValue') {
            throw new Exception(Exception::GENERAL_MOCK, 'Missing cookie value');
        }
    });

App::get('/v1/mock/tests/general/empty')
    ->desc('Empty Response')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'empty')
    ->label('sdk.description', 'Mock an empty response.')
    ->label('sdk.response.code', Response::STATUS_CODE_NOCONTENT)
    ->label('sdk.response.model', Response::MODEL_NONE)
    ->label('sdk.mock', true)
    ->inject('response')
    ->action(function (Response $response) {
        $response->noContent();
    });

App::post('/v1/mock/tests/general/nullable')
    ->desc('Nullable Test')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'nullable')
    ->label('sdk.description', 'Mock a nullable parameter.')
    ->label('sdk.mock', true)
    ->param('required', '', new Text(100), 'Sample string param')
    ->param('nullable', '', new Nullable(new Text(100)), 'Sample string param')
    ->param('optional', '', new Text(100), 'Sample string param', true)
    ->action(function (string $required, string $nullable, ?string $optional) {
    });

App::post('/v1/mock/tests/general/enum')
    ->desc('Enum Test')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'enum')
    ->label('sdk.description', 'Mock an enum parameter.')
    ->label('sdk.mock', true)
    ->param('mockType', '', new WhiteList(['first', 'second', 'third']), 'Sample enum param')
    ->action(function (string $mockType) {
    });

App::get('/v1/mock/tests/general/400-error')
    ->desc('400 Error')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'error400')
    ->label('sdk.description', 'Mock a 400 failed request.')
    ->label('sdk.response.code', Response::STATUS_CODE_BAD_REQUEST)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_ERROR)
    ->label('sdk.mock', true)
    ->action(function () {
        throw new Exception(Exception::GENERAL_MOCK, 'Mock 400 error');
    });

App::get('/v1/mock/tests/general/500-error')
    ->desc('500 Error')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'error500')
    ->label('sdk.description', 'Mock a 500 failed request.')
    ->label('sdk.response.code', Response::STATUS_CODE_INTERNAL_SERVER_ERROR)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_ERROR)
    ->label('sdk.mock', true)
    ->action(function () {
        throw new Exception(Exception::GENERAL_MOCK, 'Mock 500 error', 500);
    });

App::get('/v1/mock/tests/general/502-error')
    ->desc('502 Error')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.platform', [APP_PLATFORM_CLIENT, APP_PLATFORM_SERVER])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'error502')
    ->label('sdk.description', 'Mock a 502 bad gateway.')
    ->label('sdk.response.code', Response::STATUS_CODE_BAD_GATEWAY)
    ->label('sdk.response.type', Response::CONTENT_TYPE_TEXT)
    ->label('sdk.response.model', Response::MODEL_ANY)
    ->label('sdk.mock', true)
    ->inject('response')
    ->action(function (Response $response) {

        $response
            ->setStatusCode(502)
            ->text('This is a text error');
    });

App::get('/v1/mock/tests/general/oauth2')
    ->desc('OAuth Login')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('docs', false)
    ->label('sdk.mock', true)
    ->label('sdk.methodType', 'webAuth')
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'oauth2')
    ->param('clientId', '', new Text(100), 'OAuth2 Client ID.')
    ->param('scopes', [], new ArrayList(new Text(100)), 'OAuth2 scope list.')
    ->param('state', '', new Text(1024), 'OAuth2 state.')
    ->param('success', '', new Text(1024), 'OAuth2 success redirect URI.')
    ->param('failure', '', new Text(1024), 'OAuth2 failure redirect URI.')
    ->inject('response')
    ->action(function (string $clientId, array $scopes, string $state, string $success, string $failure, Response $response) {
        $response->redirect($success . '?' . \http_build_query(['code' => 'abcdef', 'state' => $state]));
    });

App::get('/v1/mock/tests/general/oauth2/token')
    ->desc('OAuth2 Token')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('docs', false)
    ->label('sdk.mock', true)
    ->label('sdk.methodType', 'webAuth')
    ->param('client_id', '', new Text(100), 'OAuth2 Client ID.')
    ->param('client_secret', '', new Text(100), 'OAuth2 scope list.')
    ->param('grant_type', 'authorization_code', new WhiteList(['refresh_token', 'authorization_code']), 'OAuth2 Grant Type.', true)
    ->param('redirect_uri', '', new Host(['localhost']), 'OAuth2 Redirect URI.', true)
    ->param('code', '', new Text(100), 'OAuth2 state.', true)
    ->param('refresh_token', '', new Text(100), 'OAuth2 refresh token.', true)
    ->inject('response')
    ->action(function (string $client_id, string $client_secret, string $grantType, string $redirectURI, string $code, string $refreshToken, Response $response) {
        if ($client_id != '1') {
            throw new Exception(Exception::GENERAL_MOCK, 'Invalid client ID');
        }

        if ($client_secret != '123456') {
            throw new Exception(Exception::GENERAL_MOCK, 'Invalid client secret');
        }

        $responseJson = [
            'access_token' => '123456',
            'refresh_token' => 'tuvwxyz',
            'expires_in' => 14400
        ];

        if ($grantType === 'authorization_code') {
            if ($code !== 'abcdef') {
                throw new Exception(Exception::GENERAL_MOCK, 'Invalid token');
            }

            $response->json($responseJson);
        } elseif ($grantType === 'refresh_token') {
            if ($refreshToken !== 'tuvwxyz') {
                throw new Exception(Exception::GENERAL_MOCK, 'Invalid refresh token');
            }

            $response->json($responseJson);
        } else {
            throw new Exception(Exception::GENERAL_MOCK, 'Invalid grant type');
        }
    });

App::get('/v1/mock/tests/general/oauth2/user')
    ->desc('OAuth2 User')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('docs', false)
    ->param('token', '', new Text(100), 'OAuth2 Access Token.')
    ->inject('response')
    ->action(function (string $token, Response $response) {
        if ($token != '123456') {
            throw new Exception(Exception::GENERAL_MOCK, 'Invalid token');
        }

        $response->json([
            'id' => 1,
            'name' => 'User Name',
            'email' => 'useroauth@localhost.test',
        ]);
    });

App::get('/v1/mock/tests/general/oauth2/success')
    ->desc('OAuth2 Success')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('docs', false)
    ->inject('response')
    ->action(function (Response $response) {

        $response->json([
            'result' => 'success',
        ]);
    });

App::get('/v1/mock/tests/general/oauth2/failure')
    ->desc('OAuth2 Failure')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('docs', false)
    ->inject('response')
    ->action(function (Response $response) {

        $response
            ->setStatusCode(Response::STATUS_CODE_BAD_REQUEST)
            ->json([
                'result' => 'failure',
            ]);
    });

App::shutdown()
    ->groups(['mock'])
    ->inject('utopia')
    ->inject('response')
    ->inject('request')
    ->action(function (App $utopia, Response $response, Request $request) {

        $result = [];
        $route  = $utopia->getRoute();
        $path   = APP_STORAGE_CACHE . '/tests.json';
        $tests  = (\file_exists($path)) ? \json_decode(\file_get_contents($path), true) : [];

        if (!\is_array($tests)) {
            throw new Exception(Exception::GENERAL_MOCK, 'Failed to read results', 500);
        }

        $result[$route->getMethod() . ':' . $route->getPath()] = true;

        $tests = \array_merge($tests, $result);

        if (!\file_put_contents($path, \json_encode($tests), LOCK_EX)) {
            throw new Exception(Exception::GENERAL_MOCK, 'Failed to save results', 500);
        }

        if ($route->getPath() !== '/v1/mock/tests/general/multipart') {
            $response->json(['result' => $route->getMethod() . ':' . $route->getPath() . ':passed']);
        }
    });

App::error()
    ->inject('utopia')
    ->inject('error')
    ->inject('request')
    ->inject('response')
    ->action(
        function ($utopia, $error, $request, $response) {
            $route = $utopia->match($request);

            $code = $error->getCode();
            $message = $error->getMessage();
            $file = $error->getFile();
            $line = $error->getLine();
            $trace = $error->getTrace();

            if (php_sapi_name() === 'cli') {
                Console::error('[Error] Timestamp: ' . date('c', time()));

                if ($route) {
                    Console::error('[Error] Method: ' . $route->getMethod());
                    Console::error('[Error] URL: ' . $route->getPath());
                }

                Console::error('[Error] Type: ' . get_class($error));
                Console::error('[Error] Message: ' . $message);
                Console::error('[Error] File: ' . $file);
                Console::error('[Error] Line: ' . $line);
            }

            switch ($code) {
                case 400: // Error allowed publicly
                case 401: // Error allowed publicly
                case 402: // Error allowed publicly
                case 403: // Error allowed publicly
                case 404: // Error allowed publicly
                case 406: // Error allowed publicly
                case 409: // Error allowed publicly
                case 412: // Error allowed publicly
                case 425: // Error allowed publicly
                case 429: // Error allowed publicly
                case 501: // Error allowed publicly
                case 503: // Error allowed publicly
                    $code = $error->getCode();
                    break;
                default:
                    $code = 500; // All other errors get the generic 500 server error status code
            }

            $output = ((App::isDevelopment())) ? [
                'message' => $message,
                'code' => $code,
                'file' => $file,
                'line' => $line,
                'trace' => $trace,
            ] : [
                'message' => $message,
                'code' => $code,
            ];

            $response
                ->addHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->addHeader('Expires', '0')
                ->addHeader('Pragma', 'no-cache')
                ->setStatusCode($code);

            $response->json($output);
        },
        ['utopia', 'error', 'request', 'response']
    );

$http->on(Constant::EVENT_START, function (Server $http) use ($payloadSize) {
    Console::success('Server started successfully (max payload is ' . number_format($payloadSize) . ' bytes)');
    Console::info("Master pid {$http->master_pid}, manager pid {$http->manager_pid}");

    // Listen for ctrl + c
    Process::signal(
        2,
        function () use ($http) {
            Console::log('Stop by Ctrl+C');
            $http->shutdown();
        }
    );
});

$http->on(Constant::EVENT_REQUEST, function (SwooleRequest $swooleRequest, SwooleResponse $swooleResponse) {
    $request = new Request($swooleRequest);
    $response = new Response($swooleResponse);

    $app = new App('UTC');

    $app->run($request, $response);
});

$http->start();
