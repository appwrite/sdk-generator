<?php

declare(strict_types=1);

namespace Tests\E2E;

use Appwrite\SDK\Language;
use Appwrite\SDK\SDK;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\E2E\Runtime\Android;
use Tests\E2E\Runtime\Apple;
use Tests\E2E\Runtime\CLI;
use Tests\E2E\Runtime\Dart;
use Tests\E2E\Runtime\DotNet;
use Tests\E2E\Runtime\Flutter;
use Tests\E2E\Runtime\Go;
use Tests\E2E\Runtime\Kotlin;
use Tests\E2E\Runtime\Node;
use Tests\E2E\Runtime\PHP;
use Tests\E2E\Runtime\Python;
use Tests\E2E\Runtime\ReactNative;
use Tests\E2E\Runtime\Ruby;
use Tests\E2E\Runtime\Rust;
use Tests\E2E\Runtime\Swift;
use Tests\E2E\Runtime\Unity;
use Tests\E2E\Runtime\Web;
use Utopia\OpenAPI\Parser;

final class RuntimeTest extends TestCase
{
    private const string SPEC = __DIR__ . '/../resources/spec-openapi3.json';

    private const string OUTPUT = __DIR__ . '/sdks';

    private const string START = 'Test Started';

    private const string END = 'Test Ended';

    private const string RESPONSE = '/^(GET|POST|PUT|PATCH|DELETE):(\/v1\/\S*?):passed(?::as:\S+)?$/';

    private const string JOURNAL = '/storage/cache/journal.jsonl';

    private const array FAMILIES = [
        Android::class,
        Apple::class,
        CLI::class,
        Dart::class,
        DotNet::class,
        Flutter::class,
        Go::class,
        Kotlin::class,
        Node::class,
        PHP::class,
        Python::class,
        ReactNative::class,
        Ruby::class,
        Rust::class,
        Swift::class,
        Unity::class,
        Web::class,
    ];

    /**
     * @return iterable<string, array{Runtime}>
     */
    public static function runtimes(): iterable
    {
        foreach (self::FAMILIES as $family) {
            foreach ($family::runtimes() as $name => $runtime) {
                yield $name => [$runtime];
            }
        }
    }

    #[DataProvider('runtimes')]
    public function testRuntime(Runtime $runtime): void
    {
        $nonce = \bin2hex(\random_bytes(16));

        $this->start($nonce);

        try {
            $this->generate($runtime);
            $this->build($runtime);

            $output = $this->execute($runtime);
            $requests = $runtime->reachesServer ? $this->journal() : [];

            $this->assertOutput($runtime, $output, $nonce, $requests);
        } finally {
            \exec('cd ./mock-server && docker compose down 2>&1');
        }
    }

    private function start(string $nonce): void
    {
        $output = [];
        $status = 0;

        \exec(
            'cd ./mock-server && docker compose build 2>&1 && MOCK_NONCE=' . \escapeshellarg($nonce) . ' docker compose up -d --force-recreate 2>&1',
            $output,
            $status
        );

        $this->assertSame(0, $status, "Failed to start mock-server (exit {$status}):\n" . \implode("\n", $output));
    }

    private function generate(Runtime $runtime): void
    {
        foreach ($runtime->dependencies as $dependency) {
            $this->generateSdk(
                $dependency->language,
                $dependency->language->getName(),
                $runtime->platform,
                $dependency->version,
                $runtime->namespace,
                $dependency->owner,
                $dependency->repository,
                $dependency->directory,
            );
        }

        $this->generateSdk(
            $runtime->language,
            $runtime->name,
            $runtime->platform,
            $runtime->version,
            $runtime->namespace,
            'repoowner',
            'reponame',
            $runtime->directory,
        );
    }

    private function generateSdk(
        Language $language,
        string $name,
        string $platform,
        string $version,
        string $namespace,
        string $owner,
        string $repository,
        string $directory,
    ): void {
        $spec = \file_get_contents(self::SPEC);

        $this->assertNotEmpty($spec, 'Failed to read the spec');

        $sdk = new SDK($language, Parser::parse($spec));

        $sdk
            ->setName($name)
            ->setVersion($version)
            ->setPlatform($platform)
            ->setNamespace($namespace)
            ->setDescription('Repo description goes here')
            ->setShortDescription('Repo short description goes here')
            ->setCoverImage('https://github.com/appwrite/appwrite/raw/main/public/images/github.png')
            ->setWarning('**WORK IN PROGRESS - THIS IS JUST A TEST SDK**')
            ->setExamples('**EXAMPLES** <HTML>')
            ->setGitUserName($owner)
            ->setGitRepoName($repository)
            ->setLicense('BSD-3-Clause')
            ->setLicenseContent('demo license')
            ->setChangelog('--changelog--')
            ->setDefaultHeaders([
                'X-Appwrite-Response-Format' => '0.8.0',
            ])
            ->setExclude([
                'services' => [
                    ['name' => 'zzexcludedservice'],
                ],
                'methods' => [
                    ['name' => 'createExcludedGeneralFixture'],
                ],
            ])
            ->setTest('true');

        $path = self::OUTPUT . '/' . $directory;

        $this->removeDirectory($path);

        $sdk->generate($path);
    }

    private function build(Runtime $runtime): void
    {
        foreach ($runtime->build as $command) {
            echo "Build Executing: {$command}\n";

            $output = [];
            $status = 0;

            \exec($command . ' 2>&1', $output, $status);

            echo \implode("\n", $output) . "\n";

            $this->assertSame(0, $status, "Build command failed: {$command}");
        }
    }

    /**
     * @return list<string>
     */
    private function execute(Runtime $runtime): array
    {
        echo "Env Executing: {$runtime->command}\n";

        $output = [];
        $status = 0;

        \exec($runtime->command, $output, $status);

        echo \implode("\n", $output) . "\n";

        $this->assertSame(0, $status, "The test command exited with {$status}: {$runtime->command}");

        return $output;
    }

    /**
     * @return array<string, array{method: string, path: string, route: ?string}>
     */
    private function journal(): array
    {
        $output = [];
        $status = 0;

        \exec('docker exec mockapi cat ' . self::JOURNAL, $output, $status);

        $this->assertSame(0, $status, 'Failed to read the mock server journal');

        $requests = [];
        foreach (\array_filter($output) as $line) {
            $request = \json_decode($line, true, flags: JSON_THROW_ON_ERROR);
            $requests[$request['id']] = $request;
        }

        return $requests;
    }

    /**
     * @param list<string> $output
     * @param array<string, array{method: string, path: string, route: ?string}> $requests
     */
    private function assertOutput(Runtime $runtime, array $output, string $nonce, array $requests): void
    {
        $start = \array_search(self::START, $output, true);

        $this->assertNotFalse($start, 'The driver never printed "' . self::START . '"');

        $headers = $runtime->identity ?? "x-sdk-name: {$runtime->name}; x-sdk-platform: {$runtime->platform}; x-sdk-language: {$runtime->header}; x-sdk-version: {$runtime->version}";
        $expected = [$headers, ...$runtime->expected];
        $actual = \array_slice($output, $start + 1);

        $end = \array_search(self::END, $actual, true);
        if ($end !== false) {
            $actual = \array_slice($actual, 0, $end);
        }

        $stamps = [];
        foreach ($actual as $index => $line) {
            if (\preg_match('/^(.*):' . $nonce . '\.([0-9a-f]+)$/', $line, $match) === 1) {
                $actual[$index] = $match[1];
                $stamps[$index] = $match[2];
            }
        }

        foreach ($expected as $index => $line) {
            if ($line === Output::HEADERS_RESPONSE) {
                $expected[$index] = $headers . '; accept: application/json, text/plain';
                continue;
            }

            if (!isset($actual[$index])) {
                continue;
            }

            if ($line === Output::QUERY_TRANSPORT_RESPONSE) {
                $this->assertQueriesReachedTheServer($actual[$index]);
                $actual[$index] = $line;
            } elseif (\str_starts_with($line, '{') && Json::same($line, $actual[$index])) {
                $actual[$index] = $line;
            } elseif ($line === 'unique()' && \strlen($actual[$index]) === 20 && $actual[$index] !== 'unique()') {
                $actual[$index] = $line;
            }
        }

        $this->assertSame($expected, $actual, 'The driver output differs from the expected lines');

        if ($runtime->reachesServer) {
            $this->assertResponsesCameFromTheServer($expected, $stamps, $requests);
        }
    }

    /**
     * @param list<string> $expected
     * @param array<int, string> $stamps
     * @param array<string, array{method: string, path: string, route: ?string}> $requests
     */
    private function assertResponsesCameFromTheServer(array $expected, array $stamps, array $requests): void
    {
        $used = [];

        foreach ($expected as $index => $line) {
            if (\preg_match(self::RESPONSE, $line, $match) !== 1) {
                continue;
            }

            $this->assertArrayHasKey(
                $index,
                $stamps,
                "Line {$index} \"{$line}\" is a mock server response, but it does not carry this run's nonce, so the driver printed it rather than the server returning it"
            );

            $id = $stamps[$index];
            $request = $requests[$id] ?? null;

            $this->assertNotNull($request, "Line {$index} \"{$line}\" names request {$id}, which the mock server never received");
            $this->assertArrayNotHasKey($id, $used, "Line {$index} \"{$line}\" repeats the response to request {$id}, already printed on line " . ($used[$id] ?? ''));
            $this->assertSame($match[1], $request['method'], "Line {$index} \"{$line}\" was returned for a {$request['method']} request");
            $this->assertContains($match[2], [$request['path'], $request['route']], "Line {$index} \"{$line}\" was returned for {$request['path']}");

            $used[$id] = $index;
        }
    }

    private function assertQueriesReachedTheServer(string $echo): void
    {
        $received = \json_decode($echo, true);

        $this->assertIsArray(
            $received,
            "The SDK did not print the query list the mock server echoes back, it printed: {$echo}"
        );
        $this->assertCount(
            \count(Output::QUERY_TRANSPORT_QUERIES),
            $received,
            'The server received a different number of queries than the SDK was given'
        );

        foreach (Output::QUERY_TRANSPORT_QUERIES as $position => $expected) {
            $this->assertTrue(
                Json::same($expected, (string) $received[$position]),
                "Query {$position} did not reach the server as the helper described it"
            );
        }
    }

    private function removeDirectory(string $directory): void
    {
        if (!\is_dir($directory)) {
            return;
        }

        foreach (\scandir($directory) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $directory . '/' . $file;

            if (\is_dir($path) && !\is_link($path)) {
                $this->removeDirectory($path);
            } else {
                \unlink($path);
            }
        }

        \rmdir($directory);
    }
}
