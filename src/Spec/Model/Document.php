<?php

declare(strict_types=1);

namespace Appwrite\Spec\Model;

final readonly class Document
{
    /**
     * @param array<string, Service> $services
     * @param array<string, array<string, mixed>> $schemas
     * @param array<string, array<string, mixed>> $securitySchemes
     * @param list<SecurityRequirement> $security
     */
    public function __construct(
        public string $title,
        public string $description,
        public string $version,
        public string $endpoint,
        public array $services,
        public array $schemas,
        public array $securitySchemes,
        public array $security,
    ) {
    }
}
