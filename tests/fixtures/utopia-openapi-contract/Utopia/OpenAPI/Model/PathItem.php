<?php

declare(strict_types=1);

namespace Utopia\OpenAPI\Model;

final readonly class PathItem
{
    public function __construct(public string $path, public array $operations, public array $parameters, public array $extensions = [])
    {
    }
}
