<?php

namespace Utopia\MockServer;

if (\file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use Utopia\Http\Http as App;
use Utopia\MockServer\Utopia\Exception;
use Utopia\MockServer\Utopia\File;
use Swoole\Http\Request as SwooleRequest;
use Utopia\Console\Console;
use Utopia\MockServer\Utopia\Response;
use Utopia\Http\Adapter\Swoole\Request;
use Utopia\Http\Adapter\Swoole\Response as UtopiaSwooleResponse;
use Utopia\MockServer\Utopia\HttpAdapter;
use Utopia\Http\Route;
use Utopia\Validator\JSON;
use Utopia\Validator\Text;
use Utopia\Validator\Integer;
use Utopia\Validator\ArrayList;
use Utopia\Validator\Assoc;
use Utopia\Validator\Host;
use Utopia\Validator\Nullable;
use Utopia\Validator\WhiteList;
use Utopia\MockServer\Utopia\Model\Player;
use Utopia\MockServer\Utopia\Validator\Player as PlayerValidator;
use Utopia\MockServer\Utopia\Realtime\Protocol as RealtimeProtocol;
use Utopia\WebSocket\Adapter\Swoole;
use Utopia\WebSocket\Server as WebSocketServer;

const APP_AUTH_TYPE_SESSION = 'Session';
const APP_AUTH_TYPE_JWT = 'JWT';
const APP_AUTH_TYPE_KEY = 'Key';
const APP_AUTH_TYPE_ADMIN = 'Admin';
const APP_LIMIT_ARRAY_PARAMS_SIZE = 100; // Default maximum of how many elements can there be in API parameter that expects array value
const APP_PLATFORM_SERVER = 'server';
const APP_PLATFORM_CLIENT = 'client';
const APP_PLATFORM_CONSOLE = 'console';
const APP_STORAGE_CACHE = '/storage/cache';

$payloadSize = 6 * (1024 * 1024); // 6MB
$workerNumber = swoole_cpu_num() * intval(App::getEnv('_APP_WORKER_PER_CORE', 6));

$adapter = new Swoole(host: '0.0.0.0', port: (int) App::getEnv('PORT', 80));
$adapter
    ->setPackageMaxLength($payloadSize)
    ->setWorkerNumber($workerNumber);

// Settings the adapter doesn't expose directly.
$adapter->getNative()->set([
    'open_http2_protocol' => true,
    'http_compression' => true,
    'http_compression_level' => 6,
    'buffer_output_size' => $payloadSize,
    'hook_flags' => SWOOLE_HOOK_ALL,
]);

$server = new WebSocketServer($adapter);

// Version Route for CLI
App::get('/v1/health/version')
    ->desc('Get version')
    ->groups(['api', 'health'])
    ->label('scope', 'public')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->inject('response')
    ->action(function (UtopiaSwooleResponse $response) {
        $response->json([ 'version' => '1.0.0' ]);
    });

// Mock Routes
App::get('/v1/ping')
    ->desc('Get version')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->inject('request')
    ->action(function (Request $request) {
        $projectId = $request->getHeaderLine('x-appwrite-project', '');

        if (empty($projectId)) {
            throw new Exception(Exception::GENERAL_MOCK, 'Missing project ID');
        }

        if ($projectId !== '123456') {
            throw new Exception(Exception::GENERAL_MOCK, 'Invalid project ID');
        }
    });

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
    ->action(function (Request $request, UtopiaSwooleResponse $response) {
        $res = [
            'x-sdk-name' => $request->getHeaderLine('x-sdk-name'),
            'x-sdk-platform' => $request->getHeaderLine('x-sdk-platform'),
            'x-sdk-language' => $request->getHeaderLine('x-sdk-language'),
            'x-sdk-version' => $request->getHeaderLine('x-sdk-version'),
            'accept' => $request->getHeaderLine('accept'),
        ];
        $res = array_map(function ($key, $value) {
            return $key . ': ' . $value;
        }, array_keys($res), $res);
        $res = implode("; ", $res);

        $response->json(['result' => $res]);
    });

App::get('/v1/mock/tests/general/mixed')
    ->inject('response')
    ->action(function ($response) {
        $response->setStatusCode(206)->json(['result' => 'mixed-model']);
    });

App::get('/v1/mock/tests/general/zone')
    ->inject('response')
    ->action(function ($response) {
        $response
            ->setStatusCode(203)
            ->setContentType('text/plain; charset=utf-8')
            ->send("; café zone\nwww 3600 IN A 192.0.2.1\n");
    });

App::post('/v1/mock/tests/general/zone-import')
    ->param('records', '', new Text(100), 'Zone records')
    ->param('zone', [], new File(), 'Optional zone file', optional: true, skipValidation: true)
    ->inject('request')
    ->inject('response')
    ->action(function (string $records, mixed $zone, Request $request, UtopiaSwooleResponse $response) {
        if ($records !== 'www 3600 IN A 192.0.2.1') {
            throw new Exception(Exception::GENERAL_MOCK, 'Wrong zone records');
        }
        if (!str_starts_with($request->getHeaderLine('content-type', ''), 'multipart/form-data; boundary=')) {
            throw new Exception(Exception::GENERAL_MOCK, 'Expected multipart with a boundary');
        }

        $file = $request->getFiles('zone');
        $hasFile = !empty($file);
        if ($hasFile) {
            $tmpName = is_array($file['tmp_name']) ? $file['tmp_name'][0] : $file['tmp_name'];
            $name = is_array($file['name']) ? $file['name'][0] : $file['name'];
            if ($name !== 'file.png' || md5(file_get_contents($tmpName)) !== 'd80e7e6999a3eb2ae0d631a96fe135a4') {
                throw new Exception(Exception::GENERAL_MOCK, 'Wrong zone file');
            }
        }

        $response
            ->setStatusCode(201)
            ->setContentType('text/plain; charset=utf-8')
            ->send('zone-import:' . ($hasFile ? 'with-file' : 'without-file') . "\n");
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
    ->inject('request')
    ->inject('response')
    ->action(function (Request $request, UtopiaSwooleResponse $response) {

        // Client SDKs build a URL for the browser, so credentials may arrive in
        // the query string instead of headers. The real API accepts both.
        $project = $request->getHeaderLine('x-appwrite-project', '') ?: $request->getParam('project', '');
        if (empty($project)) {
            throw new Exception(Exception::GENERAL_MOCK, 'Missing project ID');
        }
        $impersonate = $request->getHeaderLine('x-appwrite-impersonate-user-id', '') ?: $request->getParam('impersonateuserid', '');

        $response
            ->setContentType('text/plain')
            ->addHeader('Content-Disposition', 'attachment; filename="test.txt"')
            ->addHeader('Expires', \date('D, d M Y H:i:s', \time() + (60 * 60 * 24 * 45)) . ' GMT') // 45 days cache
            ->addHeader('X-Peak', \memory_get_peak_usage())
            ->send('GET:/v1/mock/tests/general/download:passed' . ($impersonate === '' ? '' : ':as:' . $impersonate));
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
    ->action(function (string $x, int $y, array $z, mixed $file, Request $request, UtopiaSwooleResponse $response) {

        $file = $request->getFiles('file');

        $contentRange = $request->getHeaderLine('content-range');

        $chunkSize = 5 * 1024 * 1024; // 5MB

        if (!empty($contentRange)) {
            $start = $request->getContentRangeStart();
            $end = $request->getContentRangeEnd();
            $size = $request->getContentRangeSize();
            $id = $request->getHeaderLine('x-appwrite-id', '');
            $file['size'] = (\is_array($file['size'])) ? $file['size'][0] : $file['size'];

            if (is_null($start) || is_null($end) || is_null($size) || $end >= $size) {
                throw new Exception(Exception::GENERAL_MOCK, 'Invalid content-range header');
            }

            if ($start > $end || $end > $size) {
                throw new Exception(Exception::GENERAL_MOCK, 'Invalid content-range header');
            }

            if ($start === 0 && $end === $size - 1 && $size === $chunkSize) {
                throw new Exception(Exception::GENERAL_MOCK, 'A file of exactly the chunk size must be sent in one request');
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
                    '$id' => 'newfileid',
                    'chunksTotal' => (int) ceil($size / ($end + 1 - $start)),
                    'chunksUploaded' => ceil($start / $chunkSize) + 1
                ]);
            } else {
                $chunksTotal = (int) ceil($size / $chunkSize);

                $response->json([
                    '$id' => 'newfileid',
                    'chunksTotal' => $chunksTotal,
                    'chunksUploaded' => $chunksTotal,
                    'result' => 'POST:/v1/mock/tests/general/upload:passed',
                ]);
            }
        } else {
            $file['tmp_name'] = (\is_array($file['tmp_name'])) ? $file['tmp_name'][0] : $file['tmp_name'];
            $file['name'] = (\is_array($file['name'])) ? $file['name'][0] : $file['name'];
            $file['size'] = (\is_array($file['size'])) ? $file['size'][0] : $file['size'];

            if ($file['name'] === 'boundary.bin') {
                if ($file['size'] !== $chunkSize) {
                    throw new Exception(Exception::GENERAL_MOCK, 'Wrong boundary file size');
                }

                return;
            }

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

App::post('/v1/mock/tests/general/optional-upload')
    ->desc('Send an optional attachment')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'optionalUpload')
    ->label('sdk.request.type', 'multipart/form-data')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('message', '', new Text(100), 'Conversation message')
    ->param('attachment', [], new File(), 'Optional attachment', optional: true, skipValidation: true)
    ->param('metadata', null, new JSON(), 'Optional metadata object', optional: true)
    ->inject('request')
    ->inject('response')
    ->action(function (string $message, mixed $attachment, array|string|null $metadata, Request $request, UtopiaSwooleResponse $response) {
        if ($message !== 'conversation without a required file') {
            throw new Exception(Exception::GENERAL_MOCK, 'Wrong conversation message');
        }
        if (!str_starts_with($request->getHeaderLine('content-type', ''), 'multipart/form-data; boundary=')) {
            throw new Exception(Exception::GENERAL_MOCK, 'Expected multipart with a boundary');
        }
        if ($request->getHeaderLine('content-range', '') !== '') {
            throw new Exception(Exception::GENERAL_MOCK, 'Optional attachment must not be chunked');
        }

        $file = $request->getFiles('attachment');
        $hasFile = !empty($file);
        if ($hasFile) {
            $tmpName = is_array($file['tmp_name']) ? $file['tmp_name'][0] : $file['tmp_name'];
            $name = is_array($file['name']) ? $file['name'][0] : $file['name'];
            if ($name !== 'file.png' || md5(file_get_contents($tmpName)) !== 'd80e7e6999a3eb2ae0d631a96fe135a4') {
                throw new Exception(Exception::GENERAL_MOCK, 'Wrong attachment');
            }
        }

        $hasMetadata = $metadata !== null;
        if ($hasMetadata) {
            $decoded = \is_string($metadata) ? \json_decode($metadata, true) : $metadata;
            if (\is_array($decoded)) {
                \ksort($decoded);
            }
            if ($decoded !== ['source' => 'sdk', 'uri' => 'café']) {
                throw new Exception(Exception::GENERAL_MOCK, 'Wrong metadata');
            }
        }

        $response->json([
            'result' => 'optional-upload:' . ($hasFile ? 'with-file' : 'without-file') . ($hasMetadata ? ':with-metadata' : ''),
        ]);
    });

App::put('/v1/mock/tests/general/upload-generic')
    ->desc('Upload Generic')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'uploadGeneric')
    ->label('sdk.request.type', 'multipart/form-data')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.mock', true)
    ->param('file', [], new File(), 'Sample file param', skipValidation: true)
    ->inject('request')
    ->inject('response')
    ->action(function (mixed $file, Request $request, UtopiaSwooleResponse $response) {
        $file = $request->getFiles('file');
        $tmpName = is_array($file['tmp_name'] ?? null) ? $file['tmp_name'][0] : ($file['tmp_name'] ?? '');
        if ($tmpName === '' || md5(file_get_contents($tmpName)) !== 'd80e7e6999a3eb2ae0d631a96fe135a4') {
            throw new Exception(Exception::GENERAL_MOCK, 'Wrong file');
        }

        // The SDK must hydrate the free-form preferences, not drop them.
        $response->json([
            'name' => 'John Doe',
            'prefs' => ['result' => 'PUT:/v1/mock/tests/general/upload-generic:passed'],
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
    ->action(function (UtopiaSwooleResponse $response) {
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

App::get('/v1/mock/tests/general/path/grant%2Fspecial%26id')
    ->desc('Path Param')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'getPath')
    ->label('sdk.description', 'Mock an encoded path parameter request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->action(function () {
    });

// Query values must arrive intact: a raw `&`, `+` or `#` splits, decodes to a space or truncates them.
App::get('/v1/mock/tests/general/query')
    ->desc('Query Param')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'getQuery')
    ->label('sdk.description', 'Mock a request with query values that need encoding.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('value', '', new Text(100), 'Query value.')
    ->param('values', [], new ArrayList(new Text(100), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Query values.')
    ->action(function (string $value, array $values) {
        if ($value !== 'a&b+c#d=e%f') {
            throw new Exception(Exception::GENERAL_MOCK, 'Query value was not encoded: ' . $value);
        }

        if ($values !== ['a&b', 'c+d#e']) {
            throw new Exception(Exception::GENERAL_MOCK, 'Query values were not encoded: ' . \implode(',', $values));
        }
    });

// Shared nullable-path e2e contract. Null is serialized literally by targets
// that previously could not compile nullable string interpolation.
// Use path parameters because the router drops literal "0" segments at registration.
App::get('/v1/mock/tests/general/path-validation/:id/:zero')
    ->desc('Path validation')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('id', '', new WhiteList(['0', 'null'], true), 'Nullable path value.')
    ->param('zero', '', new WhiteList(['0'], true), 'Zero path value.')
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
    ->action(function (UtopiaSwooleResponse $response, Request $request) {
        $response->addCookie('cookiename', 'cookieValue', \time() + 31536000, '/', $request->getHostname(), true, true, '');
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
        if ($request->getCookie('cookiename', '') !== 'cookieValue') {
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
    ->action(function (UtopiaSwooleResponse $response) {
        $response->noContent();
    });

App::get('/v1/mock/tests/general/list-rows')
    ->desc('List Rows')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'listRows')
    ->label('sdk.description', 'Mock a queryable list request.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('queries', [], new ArrayList(new Text(4096), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Array of query strings generated using the Query class provided by the SDK.')
    ->inject('response')
    ->action(function (array $queries, UtopiaSwooleResponse $response) {
        $response->json(['result' => \json_encode($queries)]);
    });

App::get('/v1/mock/tests/general/optional')
    ->desc('Get Optional')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'getOptional')
    ->label('sdk.description', 'Mock a request whose parameters are all optional.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('width', -1, new Integer(true), 'Sample optional numeric param', true)
    ->param('height', -1, new Integer(true), 'Sample optional numeric param', true)
    ->param('name', '', new Text(100), 'Sample optional string param', true)
    ->inject('response')
    ->action(function (int $width, int $height, string $name, UtopiaSwooleResponse $response) {
        $response->json(['result' => "width={$width},height={$height},name={$name}"]);
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
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->param('required', '', new Text(100), 'Sample string param')
    ->param('nullable', '', new Nullable(new Text(100)), 'Sample string param')
    ->param('optional', '', new Text(100), 'Sample string param', true)
    ->param('nullableInteger', null, new Nullable(new Integer()), 'Optional nullable integer param', true)
    ->inject('request')
    ->action(function (string $required, ?string $nullable, ?string $optional, ?int $nullableInteger, Request $request) {
        $params = $request->getParams();
        if (!\array_key_exists('nullable', $params) || $nullable !== null || !\array_key_exists('nullableInteger', $params) || $nullableInteger !== null) {
            throw new Exception(Exception::GENERAL_MOCK, 'nullable and nullableInteger must be sent as null', 400);
        }
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

App::post('/v1/mock/tests/general/models')
    ->desc('Create Player')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'createPlayer')
    ->label('sdk.description', 'Create a new player using a request model.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('player', [], new PlayerValidator(), 'Player object.', model: Player::class)
    ->action(function (array $player) {
    });

App::post('/v1/mock/tests/general/models/array')
    ->desc('Create Players')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'createPlayers')
    ->label('sdk.description', 'Create multiple players using an array of request models.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('players', [], new ArrayList(new PlayerValidator(), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Array of player objects.', model: Player::class)
    ->action(function (array $players) {
    });

App::post('/v1/mock/tests/general/documents')
    ->desc('Create Documents')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'createDocuments')
    ->label('sdk.description', 'Create documents from an array of plain objects.')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('documents', [], new ArrayList(new Assoc(), APP_LIMIT_ARRAY_PARAMS_SIZE), 'Array of document objects.')
    ->action(function (array $documents) {
        if ($documents === []) {
            throw new Exception(Exception::GENERAL_MOCK, 'Documents must not be empty');
        }

        foreach ($documents as $document) {
            if (!\is_array($document) || !isset($document['$id'])) {
                throw new Exception(Exception::GENERAL_MOCK, 'Each document must be an object with an $id');
            }
        }
    });

App::get('/v1/mock/tests/union')
    ->desc('Get Union')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->label('sdk.auth', [APP_AUTH_TYPE_SESSION, APP_AUTH_TYPE_KEY, APP_AUTH_TYPE_JWT])
    ->label('sdk.namespace', 'general')
    ->label('sdk.method', 'getUnion')
    ->label('sdk.description', 'Test union response types')
    ->label('sdk.response.code', Response::STATUS_CODE_OK)
    ->label('sdk.response.type', Response::CONTENT_TYPE_JSON)
    ->label('sdk.response.model', Response::MODEL_MOCK)
    ->label('sdk.mock', true)
    ->param('type', 'mock', new Text(4), 'Type of model to return (mock or stub)', true)
    ->inject('response')
    ->action(function (string $type, UtopiaSwooleResponse $response) {
        if ($type === 'stub') {
            $response->json([
                'data' => 'test-data',
                'type' => 'stub',
            ]);
        } else {
            $response->json([
                'type' => 'mock',
                'result' => 'GET:/v1/mock/tests/union:passed',
            ]);
        }
    });

App::get('/v1/mock/tests/general/preset')
    ->desc('Get Preset')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->inject('response')
    ->action(function (UtopiaSwooleResponse $response) {
        $response->json(['enabled' => false, 'verified' => null, 'scopes' => ['read', 'write'], 'settings' => new \stdClass()]);
    });

App::get('/v1/mock/tests/general/preset/settings')
    ->desc('Get Preset Settings')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->inject('response')
    ->action(function (UtopiaSwooleResponse $response) {
        $response->setContentType('application/json')->send('{}');
    });

App::get('/v1/mock/tests/general/preset/profile')
    ->desc('Get Preset Profile')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->inject('response')
    ->action(function (UtopiaSwooleResponse $response) {
        $response->setContentType('application/json')->send('{}');
    });

App::get('/v1/mock/tests/general/presets')
    ->desc('List Presets')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->inject('response')
    ->action(function (UtopiaSwooleResponse $response) {
        $response->json([
            'total' => 2,
            'presets' => [
                ['enabled' => false, 'settings' => new \stdClass()],
                ['name' => 'server', 'enabled' => true, 'settings' => new \stdClass()],
            ],
        ]);
    });

App::get('/v1/mock/tests/general/presets/empty')
    ->desc('List Empty Presets')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->inject('response')
    ->action(function (UtopiaSwooleResponse $response) {
        $response->json(['total' => 0]);
    });

App::get('/v1/mock/tests/compound')
    ->desc('Get compound union')
    ->groups(['mock'])
    ->label('scope', 'public')
    ->param('type', 'mock', new Text(16), 'Mock response scenario', true)
    ->inject('response')
    ->action(function (string $type, UtopiaSwooleResponse $response) {
        $payload = ['type' => 'mock', 'result' => 'compound-mock'];
        switch ($type) {
            case 'stub':
                $payload['format'] = 'stub';
                $payload['data'] = 'compound-stub';
                break;
            case 'unknown':
                $payload['format'] = 'unknown';
                break;
            case 'null':
                $payload['format'] = null;
                break;
            case 'boolean':
                $payload['type'] = true;
                break;
            case 'missing':
                unset($payload['type']);
                break;
        }
        $response->json($payload);
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
    ->action(function (UtopiaSwooleResponse $response) {

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
    ->action(function (string $clientId, array $scopes, string $state, string $success, string $failure, UtopiaSwooleResponse $response) {
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
    ->action(function (string $client_id, string $client_secret, string $grantType, string $redirectURI, string $code, string $refreshToken, UtopiaSwooleResponse $response) {
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
    ->action(function (string $token, UtopiaSwooleResponse $response) {
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
    ->action(function (UtopiaSwooleResponse $response) {

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
    ->action(function (UtopiaSwooleResponse $response) {

        $response
            ->setStatusCode(Response::STATUS_CODE_BAD_REQUEST)
            ->json([
                'result' => 'failure',
            ]);
    });

/**
 * Messaging providers and project platforms, for `appwrite init apns`. Kept per project in a file,
 * since the workers share no memory, so a second run sees what the first created.
 */
function apnsMockState(Request $request, callable $change): mixed
{
    $project = (string) $request->getHeaderLine('x-appwrite-project', 'default');

    return mockState('apns-' . $project, ['providers' => [], 'platforms' => []], $change);
}

function mockState(string $name, array $initial, callable $change): mixed
{
    $handle = \fopen(APP_STORAGE_CACHE . '/' . \preg_replace('/[^A-Za-z0-9_-]/', '', $name) . '.json', 'c+');
    if ($handle === false) {
        throw new Exception(Exception::GENERAL_MOCK, 'Failed to open the mock state', 500);
    }
    \flock($handle, LOCK_EX);
    $state = \json_decode((string) \stream_get_contents($handle), true) ?: $initial;
    $result = $change($state);
    \ftruncate($handle, 0);
    \rewind($handle);
    \fwrite($handle, \json_encode($state));
    \flock($handle, LOCK_UN);
    \fclose($handle);

    return $result;
}

function apnsMockProvider(string $id, array $params): array
{
    return [
        '$id' => $id,
        'name' => $params['name'] ?? '',
        'provider' => 'apns',
        'type' => 'push',
        'enabled' => (bool) ($params['enabled'] ?? true),
        'credentials' => [
            'authKeyId' => $params['authKeyId'] ?? '',
            'teamId' => $params['teamId'] ?? '',
            'bundleId' => $params['bundleId'] ?? '',
        ],
        'options' => ['sandbox' => (bool) ($params['sandbox'] ?? false)],
    ];
}

App::get('/v1/messaging/providers')
    ->inject('request')
    ->inject('response')
    ->action(function (Request $request, UtopiaSwooleResponse $response) {
        $kind = '';
        foreach ((array) $request->getParam('queries', []) as $query) {
            $decoded = \json_decode((string) $query, true);
            if (($decoded['attribute'] ?? '') === 'provider') {
                $kind = (string) ($decoded['values'][0] ?? '');
            }
        }
        $providers = apnsMockState($request, fn (array &$state) => \array_values(\array_filter(
            $state['providers'],
            fn (array $provider) => $kind === '' || $provider['provider'] === $kind
        )));
        $response->json(['total' => \count($providers), 'providers' => $providers]);
    });

App::post('/v1/messaging/providers/apns')
    ->inject('request')
    ->inject('response')
    ->action(function (Request $request, UtopiaSwooleResponse $response) {
        $params = $request->getParams();
        $provider = apnsMockState($request, function (array &$state) use ($params) {
            $provider = apnsMockProvider('provider' . \count($state['providers']), $params);
            $state['providers'][] = $provider;
            return $provider;
        });
        $response->setStatusCode(Response::STATUS_CODE_CREATED)->json($provider);
    });

App::patch('/v1/messaging/providers/apns/:providerId')
    ->param('providerId', '', new Text(36), 'Provider ID.')
    ->inject('request')
    ->inject('response')
    ->action(function (string $providerId, Request $request, UtopiaSwooleResponse $response) {
        $params = $request->getParams();
        $provider = apnsMockState($request, function (array &$state) use ($providerId, $params) {
            foreach ($state['providers'] as $index => $provider) {
                if ($provider['$id'] === $providerId) {
                    return $state['providers'][$index] = apnsMockProvider($providerId, $params);
                }
            }
            return null;
        });
        if ($provider === null) {
            throw new Exception(Exception::GENERAL_MOCK, 'Provider not found', 404);
        }
        $response->json($provider);
    });

App::get('/v1/project/platforms')
    ->inject('request')
    ->inject('response')
    ->action(function (Request $request, UtopiaSwooleResponse $response) {
        $platforms = apnsMockState($request, fn (array &$state) => $state['platforms']);
        $response->json(['total' => \count($platforms), 'platforms' => $platforms]);
    });

App::post('/v1/project/platforms/apple')
    ->inject('request')
    ->inject('response')
    ->action(function (Request $request, UtopiaSwooleResponse $response) {
        $params = $request->getParams();
        if (($params['bundleIdentifier'] ?? '') === '') {
            throw new Exception(Exception::GENERAL_MOCK, 'Missing bundleIdentifier', 400);
        }
        $platform = apnsMockState($request, function (array &$state) use ($params) {
            $platform = [
                '$id' => 'platform' . \count($state['platforms']),
                'type' => 'apple',
                'name' => $params['name'] ?? '',
                'bundleIdentifier' => $params['bundleIdentifier'],
            ];
            $state['platforms'][] = $platform;
            return $platform;
        });
        $response->setStatusCode(Response::STATUS_CODE_CREATED)->json($platform);
    });

/**
 * Apple's sign-in, App Store Connect and Developer portal, for `appwrite init apns` with
 * APPWRITE_APPLE_HOST set to /apple. Any password signs in, without two-factor, and the session
 * cookie carries the Apple ID. Each Apple ID has one team with room for two keys, and its own App
 * IDs; an App ID ending in .nopush already exists with Push Notifications off.
 */
const APPLE_MOCK_TEAM = 'E2ETEAM123';

function appleMockAccount(Request $request): string
{
    return \rawurldecode((string) $request->getCookie('myacinfo', ''));
}

function appleMockState(string $account, callable $change): mixed
{
    return mockState('apple-' . \md5($account), ['keys' => [], 'appIds' => []], $change);
}

function appleMockAppId(array $appId): array
{
    return [
        'type' => 'bundleIds',
        'id' => $appId['id'],
        'attributes' => ['identifier' => $appId['identifier'], 'seedId' => APPLE_MOCK_TEAM],
        'relationships' => ['bundleIdCapabilities' => ['data' => $appId['push'] ? [['type' => 'bundleIdCapabilities', 'id' => $appId['id'] . '_PUSH']] : []]],
    ];
}

function appleMockPortal(Request $request, UtopiaSwooleResponse $response, callable $handle): void
{
    $account = appleMockAccount($request);
    if ($account === '') {
        $response->setStatusCode(401)->json(['resultCode' => 1100, 'resultString' => 'Your session has expired']);
        return;
    }
    $response->json(appleMockState($account, $handle) + ['resultCode' => 0]);
}

App::get('/apple/olympus/v1/app/config')
    ->inject('response')
    ->action(function (UtopiaSwooleResponse $response) {
        $response->json(['authServiceKey' => 'e2e-widget-key']);
    });

App::get('/apple/olympus/v1/session')
    ->inject('request')
    ->inject('response')
    ->action(function (Request $request, UtopiaSwooleResponse $response) {
        $account = appleMockAccount($request);
        if ($account === '') {
            $response->setStatusCode(401)->json([]);
            return;
        }
        $response->json(['user' => ['emailAddress' => $account], 'provider' => ['providerId' => 1, 'name' => 'E2E Team']]);
    });

App::post('/apple/appleauth/auth/signin/init')
    ->inject('response')
    ->action(function (UtopiaSwooleResponse $response) {
        $response->json([
            'iteration' => 1000,
            'salt' => \base64_encode(\random_bytes(16)),
            'protocol' => 's2k',
            'b' => \base64_encode(\random_bytes(256)),
            'c' => 'e2e',
        ]);
    });

App::get('/apple/appleauth/auth/signin')
    ->inject('response')
    ->action(function (UtopiaSwooleResponse $response) {
        $response->noContent();
    });

App::post('/apple/appleauth/auth/signin/complete')
    ->inject('request')
    ->inject('response')
    ->action(function (Request $request, UtopiaSwooleResponse $response) {
        $account = (string) (\json_decode($request->getRawPayload(), true)['accountName'] ?? '');
        if ($account === '') {
            $response->setStatusCode(401)->json([]);
            return;
        }
        $response
            ->addCookie('myacinfo', \rawurlencode($account), \time() + 3600, '/')
            ->json([]);
    });

App::post('/apple/services-account/QH65B2/account/listTeams.action')
    ->inject('request')
    ->inject('response')
    ->action(function (Request $request, UtopiaSwooleResponse $response) {
        appleMockPortal($request, $response, fn () => [
            'teams' => [['teamId' => APPLE_MOCK_TEAM, 'name' => 'E2E Team', 'type' => 'Company/Organization']],
        ]);
    });

App::post('/apple/services-account/QH65B2/account/auth/key/list')
    ->inject('request')
    ->inject('response')
    ->action(function (Request $request, UtopiaSwooleResponse $response) {
        $response->addHeader('csrf', 'e2e-csrf')->addHeader('csrf_ts', (string) \time());
        appleMockPortal($request, $response, fn (array &$state) => [
            'keys' => \array_map(fn (array $key) => ['keyId' => $key['id'], 'keyName' => $key['name'], 'canRevoke' => true], $state['keys']),
        ]);
    });

App::post('/apple/services-account/QH65B2/account/auth/key/v2/create')
    ->inject('request')
    ->inject('response')
    ->action(function (Request $request, UtopiaSwooleResponse $response) {
        if ($request->getHeaderLine('csrf', '') === '') {
            $response->setStatusCode(403)->json([]);
            return;
        }
        $name = (string) (\json_decode($request->getRawPayload(), true)['name'] ?? '');
        appleMockPortal($request, $response, function (array &$state) use ($name) {
            if (\count($state['keys']) >= 2) {
                return ['resultCode' => 1, 'resultString' => 'There is a problem with the request entity', 'userString' => 'You have already reached the maximum allowed number of Keys for this service.'];
            }
            $key = \openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
            \openssl_pkey_export($key, $pem);
            $id = \strtoupper(\substr(\bin2hex(\random_bytes(5)), 0, 10));
            $state['keys'][] = ['id' => $id, 'name' => $name, 'p8' => $pem, 'downloaded' => false];
            return ['keys' => [['keyId' => $id, 'keyName' => $name, 'canRevoke' => true]]];
        });
    });

App::get('/apple/services-account/QH65B2/account/auth/key/download')
    ->inject('request')
    ->inject('response')
    ->action(function (Request $request, UtopiaSwooleResponse $response) {
        $account = appleMockAccount($request);
        $keyId = (string) $request->getParam('keyId', '');
        $p8 = $account === '' ? null : appleMockState($account, function (array &$state) use ($keyId) {
            foreach ($state['keys'] as $index => $key) {
                if ($key['id'] === $keyId && !$key['downloaded']) {
                    $state['keys'][$index]['downloaded'] = true;
                    return $key['p8'];
                }
            }
            return null;
        });
        if ($p8 === null) {
            $response->json(['resultCode' => 1, 'resultString' => 'The key cannot be downloaded']);
            return;
        }
        $response->setContentType('application/octet-stream')->send($p8);
    });

App::post('/apple/services-account/v1/bundleIds')
    ->inject('request')
    ->inject('response')
    ->action(function (Request $request, UtopiaSwooleResponse $response) {
        $account = appleMockAccount($request);
        if ($account === '') {
            $response->setStatusCode(401)->json([]);
            return;
        }
        $body = \json_decode($request->getRawPayload(), true) ?: [];
        if ($request->getHeaderLine('x-http-method-override', '') === 'GET') {
            \parse_str((string) ($body['urlEncodedQueryParams'] ?? ''), $query);
            $identifier = (string) ($query['filter']['identifier'] ?? '');
            $appIds = appleMockState($account, function (array &$state) use ($identifier) {
                if (\str_ends_with($identifier, '.nopush') && !isset($state['appIds'][$identifier])) {
                    $state['appIds'][$identifier] = ['id' => 'APPID' . \count($state['appIds']), 'identifier' => $identifier, 'push' => false];
                }
                return \array_values(\array_filter($state['appIds'], fn (array $appId) => \str_starts_with($appId['identifier'], $identifier)));
            });
            $response->json([
                'data' => \array_map(appleMockAppId(...), $appIds),
                'included' => \array_values(\array_map(fn (array $appId) => [
                    'type' => 'bundleIdCapabilities',
                    'id' => $appId['id'] . '_PUSH',
                    'attributes' => ['capabilityType' => 'PUSH_NOTIFICATIONS', 'enabled' => true],
                ], \array_filter($appIds, fn (array $appId) => $appId['push']))),
            ]);
            return;
        }
        $identifier = (string) ($body['data']['attributes']['identifier'] ?? '');
        $appId = appleMockState($account, function (array &$state) use ($identifier) {
            return $state['appIds'][$identifier] = ['id' => 'APPID' . \count($state['appIds']), 'identifier' => $identifier, 'push' => false];
        });
        $response->setStatusCode(Response::STATUS_CODE_CREATED)->json(['data' => appleMockAppId($appId)]);
    });

App::patch('/apple/services-account/v1/bundleIds/:id')
    ->param('id', '', new Text(36), 'App ID.')
    ->inject('request')
    ->inject('response')
    ->action(function (string $id, Request $request, UtopiaSwooleResponse $response) {
        $account = appleMockAccount($request);
        if ($account === '') {
            $response->setStatusCode(401)->json([]);
            return;
        }
        $body = \json_decode($request->getRawPayload(), true) ?: [];
        $push = ($body['data']['relationships']['bundleIdCapabilities']['data'][0]['relationships']['capability']['data']['id'] ?? '') === 'PUSH_NOTIFICATIONS';
        $appId = appleMockState($account, function (array &$state) use ($id, $push) {
            foreach ($state['appIds'] as $identifier => $appId) {
                if ($appId['id'] === $id) {
                    $state['appIds'][$identifier]['push'] = $appId['push'] || $push;
                    return $state['appIds'][$identifier];
                }
            }
            return null;
        });
        if ($appId === null) {
            $response->setStatusCode(404)->json(['errors' => [['status' => '404', 'detail' => 'No App ID ' . $id]]]);
            return;
        }
        $response->json(['data' => appleMockAppId($appId)]);
    });

App::shutdown()
    ->groups(['mock'])
    ->inject('route')
    ->inject('response')
    ->inject('request')
    ->action(function (Route $route, UtopiaSwooleResponse $response, Request $request) {

        $result = [];
        $routePath = $route->getPath();
        if ($routePath === '/v1/mock/tests/general/path-validation/:id/:zero') {
            $routePath = \parse_url($request->getURI(), PHP_URL_PATH);
        }
        $path   = APP_STORAGE_CACHE . '/tests.json';
        $handle = \fopen($path, 'c+');

        if ($handle === false) {
            throw new Exception(Exception::GENERAL_MOCK, 'Failed to lock results', 500);
        }

        try {
            if (!\flock($handle, LOCK_EX)) {
                throw new Exception(Exception::GENERAL_MOCK, 'Failed to lock results', 500);
            }

            $contents = \stream_get_contents($handle);
            $tests = ($contents !== false && $contents !== '') ? \json_decode($contents, true) : [];

            if (!\is_array($tests)) {
                throw new Exception(Exception::GENERAL_MOCK, 'Failed to read results', 500);
            }

            $result[$route->getMethod() . ':' . $routePath] = true;
            $tests = \array_merge($tests, $result);

            \ftruncate($handle, 0);
            \rewind($handle);

            if (!\fwrite($handle, \json_encode($tests))) {
                throw new Exception(Exception::GENERAL_MOCK, 'Failed to save results', 500);
            }

            \fflush($handle);
        } finally {
            \flock($handle, LOCK_UN);
            \fclose($handle);
        }

        $response->json(['result' => $route->getMethod() . ':' . $routePath . ':passed']);
    });

App::error()
    ->inject('utopia')
    ->inject('error')
    ->inject('request')
    ->inject('response')
    ->action(
        function ($utopia, $error, $request, $response) {
            $route = $utopia->match($request)?->route;

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
        }
    );

/**
 * Realtime WebSocket mock at /v1/realtime?project=<id>.
 *
 * Single Protocol instance is shared across worker invocations within the same
 * worker process; per-connection state lives inside it keyed by Swoole fd.
 */
$realtimeProtocol = new RealtimeProtocol();

$server->error(function (\Throwable $error, string $action) {
    Console::error("[ws:{$action}] " . $error->getMessage());
});

$server->onStart(function () use ($payloadSize) {
    Console::success('Server started successfully (max payload is ' . number_format($payloadSize) . ' bytes)');
});

$httpAdapter = new HttpAdapter($adapter->getNative());
$app = new App($httpAdapter, 'UTC');
$app->resources()->set('utopia', fn () => $app);
$httpAdapter->onRequest(fn (Request $request, UtopiaSwooleResponse $response) => $app->run($request, $response));

$server->onOpen(function (int $fd, SwooleRequest $swooleRequest) use ($server, $realtimeProtocol) {
    $path = (string) ($swooleRequest->server['request_uri'] ?? '');
    if ($path !== '/v1/realtime') {
        // Reject upgrades on any other path with a policy-violation close.
        $server->close($fd, 1008);
        return;
    }

    $project = (string) ($swooleRequest->get['project'] ?? '');
    if ($project === '') {
        $server->close($fd, 1008);
        return;
    }

    $realtimeProtocol->open($server, $fd, $project);
});

$server->onMessage(function (int $fd, string $data) use ($server, $realtimeProtocol) {
    $realtimeProtocol->message($server, $fd, $data);
});

$server->onClose(function (int $fd) use ($realtimeProtocol) {
    $realtimeProtocol->close($fd);
});

$server->start();
