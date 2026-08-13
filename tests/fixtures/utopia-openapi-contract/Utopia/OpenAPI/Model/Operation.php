<?php

declare(strict_types=1);

namespace Utopia\OpenAPI\Model;

final readonly class Operation
{
    public function __construct(public string $id, public HttpMethod $method, public string $path, public array $tags, public string $summary, public string $description, public bool $deprecated, public array $parameters, public mixed $requestBody, public array $responses, public array $security, public array $extensions = [])
    {
    }
}
