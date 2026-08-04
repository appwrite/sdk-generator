<?php

declare(strict_types=1);

namespace Tests\Unit;

use Iterator;
use Appwrite\SDK\Language\Dart;
use Appwrite\SDK\Language\Flutter;
use Appwrite\SDK\SDK;
use Appwrite\Spec\OpenAPI3;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Location methods return raw bytes instead of a model, so they render from
 * their own request template. Those templates have to attach the same security
 * headers as every other method.
 */
final class LocationMethodTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = \sys_get_temp_dir() . '/sdk-generator-location-' . \uniqid();
    }

    protected function tearDown(): void
    {
        $this->remove($this->directory);
    }

    public static function dartFamilyProvider(): Iterator
    {
        yield 'dart' => ['dart_appwrite', new Dart()];
        yield 'flutter' => ['appwrite', new Flutter()];
    }

    /**
     * The Project security scheme is not global, so setProject() only populates
     * client.config — the X-Appwrite-Project header has to be attached per
     * request. Sending the project ID as a query parameter instead fails with
     * project_id_missing (403) whenever a project API key is used, because the
     * server pairs the key against that header specifically.
     */
    #[DataProvider('dartFamilyProvider')]
    public function testLocationMethodSendsProjectAsHeader(string $packageName, Dart|Flutter $language): void
    {
        $method = $this->generateLocationMethod($packageName, $language);

        $this->assertStringContainsString("'X-Appwrite-Project': client.config['project'] ?? ''", $method);
        $this->assertStringContainsString('headers: apiHeaders', $method);
        $this->assertStringNotContainsString("'project': client.config['project']", $method);
    }

    /**
     * Generate the SDK and return the source of the one location method in the
     * fixture spec — general.download().
     */
    private function generateLocationMethod(string $packageName, Dart|Flutter $language): string
    {
        $language->setPackageName($packageName);

        $sdk = new SDK($language, new OpenAPI3(\file_get_contents(__DIR__ . '/../resources/spec-openapi3.json')));
        $sdk
            ->setName('appwrite')
            ->setNamespace('appwrite')
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setDescription('Repo description goes here')
            ->setShortDescription('Repo short description goes here')
            ->setLicense('BSD-3-Clause')
            ->setLicenseContent('demo license')
            ->setGitUserName('repoowner')
            ->setGitRepoName('reponame');

        $sdk->generate($this->directory);

        $service = $this->directory . '/lib/services/general.dart';
        $this->assertFileExists($service);

        $source = \file_get_contents($service);
        $start = \strpos($source, 'Future<Uint8List> download(');
        $this->assertNotFalse($start, 'Location method general.download() was not generated.');

        $end = \strpos($source, 'return res.data;', $start);
        $this->assertNotFalse($end, 'Location method general.download() has no response.');

        return \substr($source, $start, $end - $start);
    }

    private function remove(string $path): void
    {
        if (!\file_exists($path)) {
            return;
        }

        foreach (\array_diff(\scandir($path), ['.', '..']) as $entry) {
            $child = $path . '/' . $entry;
            \is_dir($child) ? $this->remove($child) : \unlink($child);
        }

        \rmdir($path);
    }
}
