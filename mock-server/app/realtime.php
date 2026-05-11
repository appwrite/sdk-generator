<?php

namespace Utopia\MockServer;

use Swoole\Http\Request as SwooleRequest;
use Utopia\CLI\Console;
use Utopia\MockServer\Utopia\Realtime\Protocol as RealtimeProtocol;
use Utopia\WebSocket\Server as WebSocketServer;

/**
 * Realtime WebSocket mock at /v1/realtime?project=<id>.
 *
 * Expects $http (WebSocketServer) to be in scope from the including file.
 *
 * Single Protocol instance is shared across worker invocations within the same
 * worker process; per-connection state lives inside it keyed by Swoole fd.
 */

/** @var WebSocketServer $http */

$realtimeProtocol = new RealtimeProtocol();

$http->error(function (\Throwable $error, string $action) {
    Console::error("[ws:{$action}] " . $error->getMessage());
});

$http->onOpen(function (int $fd, SwooleRequest $swooleRequest) use ($http, $realtimeProtocol) {
    $path = (string) ($swooleRequest->server['request_uri'] ?? '');
    if ($path !== '/v1/realtime') {
        // Reject upgrades on any other path with a policy-violation close.
        $http->close($fd, 1008);
        return;
    }

    $project = (string) ($swooleRequest->get['project'] ?? '');
    if ($project === '') {
        $http->close($fd, 1008);
        return;
    }

    $realtimeProtocol->open($http, $fd, $project);
});

$http->onMessage(function (int $fd, string $data) use ($http, $realtimeProtocol) {
    $realtimeProtocol->message($http, $fd, $data);
});

$http->onClose(function (int $fd) use ($realtimeProtocol) {
    $realtimeProtocol->close($fd);
});
