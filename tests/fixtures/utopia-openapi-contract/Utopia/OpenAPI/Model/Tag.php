<?php

declare(strict_types=1);

namespace Utopia\OpenAPI\Model;

final readonly class Tag
{
    public function __construct(public string $name, public string $description)
    {
    }
}
