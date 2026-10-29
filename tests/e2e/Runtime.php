<?php

declare(strict_types=1);

namespace Tests\E2E;

use Appwrite\SDK\Language;

final readonly class Runtime
{
    /**
     * @param list<string> $build
     * @param list<string> $expected
     */
    public function __construct(
        public string $directory,
        public Language $language,
        public string $name,
        public string $platform,
        public string $header,
        public array $build,
        public string $command,
        public array $expected,
        public string $version = '0.0.1',
        public string $namespace = 'appwrite',
        public bool $reachesServer = true,
    ) {
    }
}
