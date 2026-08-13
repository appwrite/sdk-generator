<?php

declare(strict_types=1);

namespace Utopia\OpenAPI;

final readonly class Specification
{
    public function __construct(public Version $version, public object $info, public array $servers, public array $tags, public array $paths, public array $schemas, public array $securitySchemes, public array $security, public array $extensions = [])
    {
    }
}
