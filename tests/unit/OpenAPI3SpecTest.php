<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appwrite\Spec\OpenAPI3;
use Appwrite\Spec\Spec;
use Appwrite\Spec\Swagger2;
use PHPUnit\Framework\TestCase;

final class OpenAPI3SpecTest extends TestCase
{
    private OpenAPI3 $spec;

    protected function setUp(): void
    {
        $this->spec = new OpenAPI3(\file_get_contents(__DIR__ . '/../resources/spec-openapi3.json'));
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

    /**
     * Everything the SDK generator consumes from a Spec.
     */
    private function dumpSpec(Spec $spec): array
    {
        $services = [];
        foreach ($spec->getServices() as $name => $service) {
            $service['methods'] = $spec->getMethods($name);
            $services[$name] = $service;
        }

        return [
            'title' => $spec->getTitle(),
            'description' => $spec->getDescription(),
            'namespace' => $spec->getNamespace(),
            'version' => $spec->getVersion(),
            'endpoint' => $spec->getEndpoint(),
            'endpointDocs' => $spec->getEndpointDocs(),
            'basePath' => \parse_url($spec->getEndpoint(), PHP_URL_PATH) ?: '',
            'licenseName' => $spec->getLicenseName(),
            'licenseURL' => $spec->getLicenseURL(),
            'contactName' => $spec->getContactName(),
            'contactURL' => $spec->getContactURL(),
            'contactEmail' => $spec->getContactEmail(),
            'globalHeaders' => $spec->getGlobalHeaders(),
            'services' => $services,
            'definitions' => $spec->getDefinitions(),
            'requestModels' => $spec->getRequestModels(),
            'requestEnums' => $spec->getRequestEnums(),
            'responseEnums' => $spec->getResponseEnums(),
            'requestModelEnums' => $spec->getRequestModelEnums(),
            'allEnums' => $spec->getAllEnums(),
        ];
    }

    /**
     * The OpenAPI 3 spec must parse into the exact same internal representation
     * as its Swagger 2 counterpart, so both formats generate identical SDKs.
     */
    public function testParsesIdenticallyToSwagger2(): void
    {
        $swagger2 = new Swagger2(\file_get_contents(__DIR__ . '/../resources/spec-swagger2.json'));

        $expected = $this->dumpSpec($swagger2);
        $actual = $this->dumpSpec($this->spec);

        foreach ($expected as $section => $value) {
            $this->assertEquals($value, $actual[$section], "Section '$section' differs between Swagger 2 and OpenAPI 3");
        }
    }

    public function testEndpointComesFromServers(): void
    {
        $this->assertSame('http://mockapi/v1', $this->spec->getEndpoint());
        $this->assertSame('http://<REGION>.mockapi/v1', $this->spec->getEndpointDocs());
    }

    public function testGlobalHeadersComeFromSecuritySchemes(): void
    {
        $headers = \array_column($this->spec->getGlobalHeaders(), 'name', 'key');

        $this->assertSame('X-Appwrite-Project', $headers['Project']);
        $this->assertSame('X-Appwrite-Key', $headers['Key']);
    }

    public function testPathLocatedProjectAuthUsesPublicSetterName(): void
    {
        $spec = new OpenAPI3(\json_encode([
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Appwrite',
                'description' => 'Test spec',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => 'https://example.com/v1'],
            ],
            'tags' => [
                ['name' => 'oauth2'],
            ],
            'paths' => [
                '/projects/{project_id}/oauth2/grants/{grant_id}' => [
                    'get' => [
                        'summary' => 'Get Grant',
                        'operationId' => 'oauth2GetGrant',
                        'tags' => ['oauth2'],
                        'x-appwrite' => [
                            'method' => 'getGrant',
                            'auth' => [
                                'ProjectPath' => [],
                            ],
                        ],
                        'security' => [
                            [
                                'ProjectPath' => [],
                            ],
                        ],
                        'parameters' => [
                            [
                                'name' => 'project_id',
                                'description' => 'Project ID.',
                                'required' => true,
                                'in' => 'path',
                                'schema' => ['type' => 'string'],
                            ],
                            [
                                'name' => 'grant_id',
                                'description' => 'Grant ID.',
                                'required' => true,
                                'in' => 'path',
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'OK',
                            ],
                        ],
                    ],
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'ProjectPath' => [
                        'type' => 'apiKey',
                        'name' => 'project',
                        'description' => 'Your project ID',
                        'in' => 'query',
                        'x-appwrite' => [
                            'location' => 'path',
                            'param' => 'project_id',
                            'demo' => '<YOUR_PROJECT_ID>',
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $methods = $spec->getMethods('oauth2');

        $this->assertSame('getGrant', $methods[0]['name']);
        $this->assertArrayHasKey('Project', $methods[0]['auth'][0]);
        $this->assertArrayNotHasKey('ProjectPath', $methods[0]['auth'][0]);
        $this->assertSame('project', $methods[0]['parameters']['path'][0]['config']);
        $this->assertSame('security', $methods[0]['parameters']['path'][0]['source']);
    }

    public function testRequestBodyBecomesBodyParameters(): void
    {
        $method = $this->getMethod('foo', 'post');

        $this->assertSame(['application/json'], $method['consumes']);
        $this->assertSame('application/json', $method['headers']['content-type']);
        $this->assertNotEmpty($method['parameters']['body']);

        foreach ($method['parameters']['body'] as $parameter) {
            $this->assertNotEmpty($parameter['name']);
        }
    }

    public function testQueryParameterSchemaIsFlattened(): void
    {
        $method = $this->getMethod('general', 'getUnion');

        [$type] = $method['parameters']['query'];

        $this->assertSame('type', $type['name']);
        $this->assertSame('string', $type['type']);
        $this->assertSame('mock', $type['example']);
        $this->assertSame('mock', $type['default']);
    }

    public function testMultipartRequestBodyBecomesFileParameter(): void
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

    public function testResponseModelComesFromResponseContent(): void
    {
        $method = $this->getMethod('general', 'upload');

        $this->assertSame('mock', $method['responseModel']);
        $this->assertSame('application/json', $method['headers']['accept']);
        $this->assertFalse($method['emptyResponse']);
    }

    public function testOneOfResponseResolvesUnionModels(): void
    {
        $method = $this->getMethod('general', 'getUnion');

        $this->assertSame(['unionMock', 'unionStub'], $method['responseModels']);
        $this->assertSame('unionMock', $method['responseModel']);
        $this->assertSame(
            ['unionMock' => ['type' => 'mock'], 'unionStub' => ['type' => 'stub']],
            $method['responseDiscriminator']
        );
    }

    public function testNoContentResponseHasNoAcceptHeader(): void
    {
        $method = $this->getMethod('general', 'empty');

        $this->assertTrue($method['emptyResponse']);
        $this->assertSame('', $method['responseModel']);
        $this->assertSame([], $method['produces']);
        $this->assertArrayNotHasKey('accept', $method['headers']);
    }

    public function testObjectPropertyWithAllOfReferenceResolvesSubSchema(): void
    {
        $properties = $this->spec->getDefinitions()['unionContainer']['properties'];

        $this->assertSame('mock', $properties['mock']['sub_schema']);
        $this->assertSame('object', $properties['mock']['type']);
    }

    public function testAllOfWrappedUnionResolvesSubSchemas(): void
    {
        $properties = $this->spec->getDefinitions()['unionContainer']['properties'];

        $this->assertSame(['unionMock', 'unionStub'], $properties['options']['sub_schemas']);
        $this->assertArrayNotHasKey('allOf', $properties['options']);
    }

    public function testItemsUnionsResolveSubSchemas(): void
    {
        $properties = $this->spec->getDefinitions()['unionContainer']['properties'];

        $this->assertSame(['unionMock', 'unionStub'], $properties['entries']['sub_schemas']);
        $this->assertSame(['unionMock', 'unionStub'], $properties['legacyOptions']['sub_schemas']);
    }

    public function testArrayPropertyWithItemsReferenceResolvesSubSchema(): void
    {
        $properties = $this->spec->getDefinitions()['unionContainer']['properties'];

        $this->assertSame('mock', $properties['mocks']['sub_schema']);
        $this->assertSame('array', $properties['mocks']['type']);
    }

    public function testNullableBodyParameterIsPreserved(): void
    {
        $method = $this->getMethod('general', 'nullable');

        $parameters = \array_column($method['parameters']['body'], null, 'name');

        $this->assertTrue($parameters['nullable']['nullable']);
        $this->assertFalse($parameters['required']['nullable']);
    }

    public function testNullablePropertiesConvertToXNullable(): void
    {
        foreach ($this->spec->getDefinitions() as $definition) {
            foreach ($definition['properties'] as $property) {
                $this->assertArrayNotHasKey('nullable', $property);
            }
        }
    }

    public function testRequestModelsAreExcludedFromDefinitions(): void
    {
        $definitions = $this->spec->getDefinitions();
        $requestModels = $this->spec->getRequestModels();

        $this->assertArrayHasKey('player', $requestModels);
        $this->assertArrayNotHasKey('player', $definitions);
        $this->assertArrayHasKey('unionContainer', $definitions);
    }

    public function testModelPropertyEnum(): void
    {
        $properties = $this->spec->getDefinitions()['mock']['properties'];

        $this->assertSame(['active', 'inactive', 'pending', 'completed', 'cancelled'], $properties['status']['enum']);
        $this->assertSame('MockStatus', $properties['status']['enumName']);
    }
}
