<?php

declare(strict_types=1);

namespace Tests\E2E;

use Appwrite\SDK\Language\Dart;
use Appwrite\SDK\Language\PHP;
use Appwrite\SDK\Language\Python;
use Appwrite\SDK\Language\Web;
use Appwrite\SDK\SDK;
use PHPUnit\Framework\TestCase;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Parser;
use Utopia\OpenAPI\Specification;

final class EnumKeyTest extends TestCase
{
    private string $output;

    protected function setUp(): void
    {
        $this->output = \sys_get_temp_dir() . '/sdk-generator-enum-' . \bin2hex(\random_bytes(8));
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->output);
    }

    public function testResolvesSafeAndSemanticEnumKeys(): void
    {
        $parameters = $this->specification()->paths['/test']->operations['get']->parameters;

        foreach ([new Web(), new Dart(), new Python(), new PHP()] as $language) {
            $this->assertSame(['Value1', 'Value2', 'Test'], $language->resolveEnumKeys($parameters[0]));
            $this->assertSame(['Capital', 'Province', 'Test'], $language->resolveEnumKeys($parameters[1]));
            $this->assertSame(['Value123', 'Value2'], $language->resolveEnumKeys($parameters[2]));
        }
    }

    public function testEnumExamplesUseResolvedKeysAcrossCasingStyles(): void
    {
        $parameter = $this->specification()->paths['/test']->operations['get']->parameters[0];

        $this->assertSame('Localized.Value1', $this->enumExample(new Web(), $parameter));
        $this->assertSame('enums.Localized.value1', $this->enumExample(new Dart(), $parameter));
        $this->assertSame('Localized.VALUE1', $this->enumExample(new Python(), $parameter));
        $this->assertSame('Localized::VALUE1()', $this->enumExample(new PHP(), $parameter));
    }

    public function testGeneratesValidAndConsistentTypescriptEnumKeys(): void
    {
        $sdk = new SDK(new Web(), $this->specification());
        $sdk
            ->setName('Test')
            ->setVersion('1.0.0')
            ->setPlatform('client')
            ->setDescription('Test SDK')
            ->setShortDescription('Test SDK')
            ->setNamespace('test')
            ->setLicense('MIT')
            ->setLicenseContent('MIT');
        $sdk->generate($this->output);

        $this->assertFileContains('src/enums/localized.ts', "Value1 = 'រាជធានី'");
        $this->assertFileContains('src/enums/localized.ts', "Value2 = 'ខេត្ត'");
        $this->assertFileContains('src/enums/province-type.ts', "Capital = 'រាជធានី'");
        $this->assertFileContains('src/enums/province-type.ts', "Province = 'ខេត្ត'");
        $this->assertFileContains('src/enums/unsafe.ts', "Value123 = '123'");
        $this->assertFileContains('src/enums/unsafe.ts', "Value2 = '-'");
        $this->assertFileContains('docs/examples/test/list.md', 'Localized.Value1');
        $this->assertFileContains('docs/examples/test/list.md', 'ProvinceType.Capital');
        $this->assertFileContains('docs/examples/test/list.md', 'Unsafe.Value123');
    }

    private function specification(): Specification
    {
        return Parser::parse([
            'openapi' => '3.1.0',
            'info' => ['title' => 'Test', 'version' => '1.0.0'],
            'servers' => [['url' => 'https://example.com/v1']],
            'paths' => ['/test' => ['get' => [
                'operationId' => 'testList',
                'tags' => ['test'],
                'parameters' => [
                    ['name' => 'localized', 'in' => 'query', 'schema' => [
                        'type' => 'string',
                        'title' => 'Localized',
                        'enum' => ['រាជធានី', 'ខេត្ត', 'Test'],
                    ]],
                    ['name' => 'province', 'in' => 'query', 'schema' => [
                        'type' => 'string',
                        'title' => 'ProvinceType',
                        'oneOf' => [
                            ['const' => 'រាជធានី', 'title' => 'Capital'],
                            ['const' => 'ខេត្ត', 'title' => 'Province'],
                            ['const' => 'Test', 'title' => 'Test'],
                        ],
                    ]],
                    ['name' => 'unsafe', 'in' => 'query', 'schema' => [
                        'type' => 'string',
                        'title' => 'Unsafe',
                        'enum' => ['123', '-'],
                    ]],
                ],
                'responses' => ['200' => ['description' => 'ok']],
            ]]],
        ]);
    }

    private function enumExample(Web|Dart|Python|PHP $language, Parameter $parameter): string
    {
        foreach ($language->getFilters() as $filter) {
            if ($filter->getName() === 'enumExample') {
                return ($filter->getCallable())($parameter);
            }
        }

        $this->fail('Language does not define an enumExample filter.');
    }

    private function assertFileContains(string $relativePath, string $expected): void
    {
        $contents = \file_get_contents($this->output . '/' . $relativePath);
        $this->assertIsString($contents);
        $this->assertStringContainsString($expected, $contents);
    }

    private function removeDirectory(string $directory): void
    {
        if (!\is_dir($directory)) {
            return;
        }

        foreach (\scandir($directory) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory . '/' . $entry;
            if (\is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                \unlink($path);
            }
        }

        \rmdir($directory);
    }
}
