<?php

declare(strict_types=1);

namespace Tests\E2E;

use Appwrite\SDK\Language;

final readonly class Dependency
{
    public function __construct(
        public string $directory,
        public Language $language,
        public string $owner,
        public string $repository,
        public string $version,
    ) {
    }
}
