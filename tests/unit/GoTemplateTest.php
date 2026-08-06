<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appwrite\SDK\Language\Go;
use Appwrite\SDK\SDK;
use Appwrite\Spec\OpenAPI3;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Asserts on the request building the Go templates emit per method.
 *
 * The e2e suite covers what the mock server can observe, and it cannot observe
 * where a parameter was sent: a route ignores any parameter it does not declare,
 * so a path parameter repeated in the query string still answers ':passed'.
 *
 * Runtime behaviour is not asserted here. EncodePath and AddQueryParam are
 * covered by real Go tests in templates/go/client_test.go.twig, which pin what
 * they do rather than how they are spelled.
 */
final class GoTemplateTest extends TestCase
{
    private static string $directory = '';

    public static function setUpBeforeClass(): void
    {
        self::$directory = \sys_get_temp_dir() . '/sdk-generator-go-' . \bin2hex(\random_bytes(6));

        $spec = \file_get_contents(__DIR__ . '/../resources/spec-openapi3.json');
        $sdk = new SDK(new Go(), new OpenAPI3($spec));
        $sdk->setName('go')
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setNamespace('appwrite')
            ->setDescription('description')
            ->setShortDescription('short description')
            ->setGitUserName('repoowner')
            ->setGitRepoName('reponame')
            ->setLicense('BSD-3-Clause')
            ->setLicenseContent('demo license')
            ->generate(self::$directory);
    }

    public static function tearDownAfterClass(): void
    {
        if (!\is_dir(self::$directory)) {
            return;
        }

        $entries = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::$directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($entries as $entry) {
            $entry->isDir() ? \rmdir($entry->getPathname()) : \unlink($entry->getPathname());
        }

        \rmdir(self::$directory);
    }

    private function generated(string $path): string
    {
        $file = self::$directory . '/' . $path;
        $this->assertFileExists($file);

        return \file_get_contents($file);
    }

    /**
     * The source of one generated method, from its signature to its closing
     * brace. Fails with the signature it looked for rather than letting substr()
     * take a false and throw.
     */
    private function method(string $path, string $signature): string
    {
        $source = $this->generated($path);

        $start = \strpos($source, $signature);
        $this->assertNotFalse($start, \sprintf('Generated %s has no method "%s"', $path, $signature));

        $end = \strpos($source, "\n}\n", $start);
        $this->assertNotFalse($end, \sprintf('Method "%s" in %s is not terminated', $signature, $path));

        return \substr($source, $start, $end - $start);
    }

    public function testPathParametersAreEncoded(): void
    {
        $method = $this->method('general/general.go', 'func (srv *General) GetPath(');

        $this->assertStringContainsString(
            'strings.NewReplacer("{pathId}", client.EncodePath(PathId))',
            $method
        );
    }

    /**
     * A path parameter is already substituted into the URL. Sending it again put
     * it in the query string of a GET and in the body of a PATCH.
     */
    public function testPathParametersAreNotAlsoSentAsParameters(): void
    {
        $method = $this->method('general/general.go', 'func (srv *General) GetPath(');

        $this->assertStringContainsString('params := map[string]interface{}{}', $method);
        $this->assertStringNotContainsString('params["pathId"]', $method);
    }

    public function testBodyParametersAreStillSent(): void
    {
        $method = $this->method('general/general.go', 'func (srv *General) Upload(');

        $this->assertStringContainsString('params["x"]', $method);
        $this->assertStringContainsString('params["y"]', $method);
    }
}
