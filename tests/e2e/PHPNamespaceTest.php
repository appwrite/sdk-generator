<?php

declare(strict_types=1);

namespace Tests\E2E;

use Appwrite\SDK\Language\PHP;
use Appwrite\SDK\SDK;
use PHPUnit\Framework\TestCase;
use Utopia\OpenAPI\Parser;

/**
 * The Manager SDK renames its root namespace, so every generated reference has to
 * follow it. A reference built from the spec title instead names a class the SDK
 * never writes, and nothing but loading the generated code notices.
 */
final class PHPNamespaceTest extends TestCase
{
    private const string NAMESPACE = 'Cloud\Platform';

    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = \sys_get_temp_dir() . '/sdk-generator-php-namespace';
        $this->removeDirectory($this->directory);

        $spec = \file_get_contents((string) \realpath(__DIR__ . '/../resources/spec-openapi3.json'));
        $this->assertNotFalse($spec);

        $sdk = new SDK(new PHP(), Parser::parse($spec));
        $sdk->setName('Manager')
            ->setNamespace(self::NAMESPACE)
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setDescription('Repo description goes here')
            ->setShortDescription('Repo short description goes here')
            ->setGitUserName('repoowner')
            ->setGitRepoName('reponame')
            ->setLicense('BSD-3-Clause')
            ->setLicenseContent('demo license')
            ->setChangelog('--changelog--')
            ->generate($this->directory);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->directory);

        parent::tearDown();
    }

    public function testEveryModelReferenceResolvesToAGeneratedClass(): void
    {
        $references = [];

        foreach ($this->files($this->directory) as $file) {
            \preg_match_all('/((?:\w+\\\\)+Models\\\\\\w+)/', (string) \file_get_contents($file), $matches);
            foreach ($matches[1] as $reference) {
                $references[$reference] = true;
            }
        }

        $this->assertNotEmpty($references, 'The spec generated no model references to check.');

        foreach (\array_keys($references) as $reference) {
            $this->assertStringStartsWith(self::NAMESPACE . '\Models\\', $reference);
            $this->assertFileExists($this->directory . '/src/' . \str_replace('\\', '/', $reference) . '.php');
        }
    }

    /**
     * @return list<string>
     */
    private function files(string $directory): array
    {
        $files = [];

        foreach (\array_diff(\scandir($directory) ?: [], ['.', '..']) as $entry) {
            $path = $directory . '/' . $entry;

            if (\is_dir($path)) {
                \array_push($files, ...$this->files($path));
                continue;
            }

            if (\str_ends_with($path, '.php')) {
                $files[] = $path;
            }
        }

        return $files;
    }

    private function removeDirectory(string $directory): void
    {
        if (!\is_dir($directory)) {
            return;
        }

        foreach (\array_diff(\scandir($directory) ?: [], ['.', '..']) as $entry) {
            $path = $directory . '/' . $entry;
            \is_dir($path) ? $this->removeDirectory($path) : \unlink($path);
        }

        \rmdir($directory);
    }
}
