<?php

declare(strict_types=1);

namespace Appwrite\Spec\Model;

final readonly class Operation
{
    /**
     * @param list<string> $tags
     * @param list<array<string, mixed>> $parameters
     * @param array<string, mixed>|null $requestBody
     * @param array<int|string, array<string, mixed>> $responses
     * @param list<SecurityRequirement> $security
     */
    public function __construct(
        public string $id,
        public string $method,
        public string $path,
        public array $tags,
        public string $summary,
        public string $description,
        public bool $deprecated,
        public array $parameters,
        public ?array $requestBody,
        public array $responses,
        public array $security,
    ) {
    }
}
