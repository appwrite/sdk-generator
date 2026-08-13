<?php

declare(strict_types=1);

namespace Utopia\OpenAPI\Model;

final readonly class License
{
    public function __construct(public ?string $name, public ?string $url)
    {
    }
}
