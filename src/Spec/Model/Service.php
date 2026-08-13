<?php

declare(strict_types=1);

namespace Appwrite\Spec\Model;

final readonly class Service
{
    /**
     * @param list<Operation> $operations
     */
    public function __construct(
        public string $name,
        public string $description,
        public array $operations,
    ) {
    }
}
