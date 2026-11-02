<?php

declare(strict_types=1);

namespace Tests\E2E;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

final class DriverTest extends TestCase
{
    private const string DRIVERS = __DIR__ . '/languages';

    private const string RESPONSE = '/\b(?:GET|POST|PUT|PATCH|DELETE):\/v1\/[^\s\'"`]*:passed/';

    private const string VERDICT = '/:(?:passed|failed|rejected)\b|!$/';

    private const array INPUTS = [
        'unique()',
        'custom_id',
        'files',
        'presences',
        'stub',
        'www 3600 IN A 192.0.2.1',
        '',
    ];

    public function testDriversDoNotPrintServerResponses(): void
    {
        $found = [];
        foreach ($this->drivers() as $path => $source) {
            if (\preg_match_all(self::RESPONSE, $source, $matches) > 0) {
                $found[] = $path . ': ' . \implode(', ', \array_unique($matches[0]));
            }
        }

        $this->assertSame([], $found, 'Drivers must print what the mock server returned, not a copy of it');
    }

    public function testDriversDoNotHardcodeExpectedLines(): void
    {
        $lines = [];
        foreach (new ReflectionClass(Output::class)->getConstants() as $value) {
            \array_push($lines, ...(\is_array($value) ? $value : [$value]));
        }

        $lines = \array_filter(
            \array_unique($lines),
            fn (string $line): bool => !\in_array($line, self::INPUTS, true) && \preg_match(self::VERDICT, $line) !== 1
        );

        $found = [];
        foreach ($this->drivers() as $path => $source) {
            foreach ($lines as $line) {
                foreach (['"', "'", '`'] as $quote) {
                    if (\str_contains($source, $quote . $line . $quote)) {
                        $found[] = "{$path}: {$quote}{$line}{$quote}";
                    }
                }
            }
        }

        $this->assertSame([], $found, 'Drivers must print what the SDK produced, not the line the harness expects');
    }

    /**
     * @return array<string, string>
     */
    private function drivers(): array
    {
        $drivers = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::DRIVERS, RecursiveDirectoryIterator::SKIP_DOTS));

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $drivers[\substr($file->getPathname(), \strlen(self::DRIVERS) + 1)] = (string) \file_get_contents($file->getPathname());
        }

        \ksort($drivers);

        return $drivers;
    }
}
