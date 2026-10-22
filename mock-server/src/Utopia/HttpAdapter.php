<?php

namespace Utopia\MockServer\Utopia;

use Swoole\Http\Server;
use Utopia\DI\Container;
use Utopia\Http\Adapter\Swoole\Server as SwooleAdapter;
use Utopia\Http\TrustedHeaders;

// Reuse the WebSocket listener for HTTP, including the adapter's per-request context.
final class HttpAdapter extends SwooleAdapter
{
    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->resources = new Container();
        $this->trusted = new TrustedHeaders();
    }
}
