<?php

declare(strict_types=1);

namespace Utopia\OpenAPI\Model;

final readonly class Contact
{
    public function __construct(public ?string $name, public ?string $url, public ?string $email)
    {
    }
}
