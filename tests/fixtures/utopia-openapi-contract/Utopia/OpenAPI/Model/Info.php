<?php

declare(strict_types=1);

namespace Utopia\OpenAPI\Model;

final readonly class Info
{
    public function __construct(public string $title, public string $description, public string $version, public ?string $termsOfService, public ?Contact $contact, public ?License $license, public array $extensions = [])
    {
    }
}
