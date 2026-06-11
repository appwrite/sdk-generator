<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appwrite\Spec\Swagger2;
use PHPUnit\Framework\TestCase;

final class Swagger2SpecTest extends TestCase
{
    private Swagger2 $spec;

    protected function setUp(): void
    {
        $this->spec = new Swagger2(\file_get_contents(__DIR__ . '/../resources/spec-swagger2.json'));
    }

    private function getMethod(string $service, string $name): array
    {
        foreach ($this->spec->getMethods($service) as $method) {
            if ($method['name'] === $name) {
                return $method;
            }
        }

        $this->fail("Method $name not found in service $service");
    }

    public function testEndpoint(): void
    {
        $this->assertSame('http://mockapi/v1', $this->spec->getEndpoint());
        $this->assertSame('http://<REGION>.mockapi/v1', $this->spec->getEndpointDocs());
    }

    public function testGlobalHeadersComeFromSecurityDefinitions(): void
    {
        $headers = \array_column($this->spec->getGlobalHeaders(), 'name', 'key');

        $this->assertSame('X-Appwrite-Project', $headers['Project']);
        $this->assertSame('X-Appwrite-Key', $headers['Key']);
    }

    public function testObjectPropertyWithAllOfReferenceResolvesSubSchema(): void
    {
        $properties = $this->spec->getDefinitions()['unionContainer']['properties'];

        $this->assertSame('mock', $properties['mock']['sub_schema']);
        $this->assertSame('object', $properties['mock']['type']);
    }

    public function testObjectPropertyWithLegacyItemsReferenceResolvesSubSchema(): void
    {
        $properties = $this->spec->getDefinitions()['unionContainer']['properties'];

        $this->assertSame('mock', $properties['legacyMock']['sub_schema']);
    }

    public function testObjectPropertyWithOneOfUnionResolvesSubSchemas(): void
    {
        $properties = $this->spec->getDefinitions()['unionContainer']['properties'];

        $this->assertSame(['unionMock', 'unionStub'], $properties['options']['sub_schemas']);
    }

    public function testObjectPropertyWithLegacyItemsOneOfUnionResolvesSubSchemas(): void
    {
        $properties = $this->spec->getDefinitions()['unionContainer']['properties'];

        $this->assertSame(['unionMock', 'unionStub'], $properties['legacyOptions']['sub_schemas']);
    }

    public function testArrayPropertyWithItemsReferenceResolvesSubSchema(): void
    {
        $properties = $this->spec->getDefinitions()['unionContainer']['properties'];

        $this->assertSame('mock', $properties['mocks']['sub_schema']);
        $this->assertSame('array', $properties['mocks']['type']);
    }

    public function testArrayPropertyWithItemsAnyOfUnionResolvesSubSchemas(): void
    {
        $properties = $this->spec->getDefinitions()['unionContainer']['properties'];

        $this->assertSame(['unionMock', 'unionStub'], $properties['entries']['sub_schemas']);
    }

    public function testScalarPropertyHasNoSubSchema(): void
    {
        $properties = $this->spec->getDefinitions()['mock']['properties'];

        $this->assertArrayNotHasKey('sub_schema', $properties['result']);
        $this->assertArrayNotHasKey('sub_schemas', $properties['result']);
    }

    public function testModelPropertyEnum(): void
    {
        $properties = $this->spec->getDefinitions()['mock']['properties'];

        $this->assertSame(['active', 'inactive', 'pending', 'completed', 'cancelled'], $properties['status']['enum']);
        $this->assertSame('MockStatus', $properties['status']['enumName']);
    }

    public function testRequestModelsAreExcludedFromDefinitions(): void
    {
        $definitions = $this->spec->getDefinitions();
        $requestModels = $this->spec->getRequestModels();

        $this->assertArrayHasKey('player', $requestModels);
        $this->assertArrayNotHasKey('player', $definitions);
        $this->assertArrayHasKey('unionContainer', $definitions);
    }

    public function testUnionResponseResolvesModelsAndDiscriminator(): void
    {
        $method = $this->getMethod('general', 'getUnion');

        $this->assertSame(['unionMock', 'unionStub'], $method['responseModels']);
        $this->assertSame('unionMock', $method['responseModel']);
        $this->assertSame(
            ['unionMock' => ['type' => 'mock'], 'unionStub' => ['type' => 'stub']],
            $method['responseDiscriminator']
        );
    }

    public function testMultipartMethodHasFileParameter(): void
    {
        $method = $this->getMethod('general', 'upload');

        $this->assertSame(['multipart/form-data'], $method['consumes']);

        $file = null;
        foreach ($method['parameters']['body'] as $parameter) {
            if ($parameter['name'] === 'file') {
                $file = $parameter;
            }
        }

        $this->assertNotNull($file);
        $this->assertSame('file', $file['type']);
        $this->assertTrue($file['required']);
    }

    public function testEmptyResponseMethod(): void
    {
        $method = $this->getMethod('general', 'empty');

        $this->assertTrue($method['emptyResponse']);
        $this->assertSame('', $method['responseModel']);
        $this->assertSame([], $method['produces']);
    }

    public function testRequestEnumsAreCollected(): void
    {
        $enums = [];
        foreach ($this->spec->getRequestEnums() as $enum) {
            $enums[$enum['name']] = $enum;
        }

        $this->assertArrayHasKey('mockType', $enums);
        $this->assertNotEmpty($enums['mockType']['enum']);
    }
}
