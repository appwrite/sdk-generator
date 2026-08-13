<?php

declare(strict_types=1);

namespace Utopia\OpenAPI\Model;

final readonly class Server
{
    public function __construct(public string $url)
    {
    }
}
