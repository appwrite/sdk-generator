<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appwrite\Spec\GenerationProfile;
use Appwrite\Spec\OpenAPI3;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class GenerationProfileTest extends TestCase
{
    private array $document;
    private array $profile;

    protected function setUp(): void
    {
        $this->document = [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Example',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => 'https://example.com/v1'],
            ],
            'tags' => [
                ['name' => 'account', 'description' => 'Account API'],
                ['name' => 'users', 'description' => 'Users API'],
            ],
            'paths' => [
                '/account' => [
                    'get' => [
                        'operationId' => 'accountGet',
                        'summary' => 'Get account',
                        'tags' => ['account'],
                        'security' => [
                            ['Project' => [], 'Session' => []],
                            ['Project' => [], 'Key' => []],
                        ],
                        'parameters' => [
                            [
                                'name' => 'statuses',
                                'in' => 'query',
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'string',
                                        'enum' => ['active', 'blocked'],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'OK',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'oneOf' => [
                                                ['$ref' => '#/components/schemas/account'],
                                            ],
                                            'discriminator' => [
                                                'propertyName' => 'status',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/users' => [
                    'get' => [
                        'operationId' => 'usersList',
                        'summary' => 'List users',
                        'tags' => ['users'],
                        'security' => [
                            ['Project' => [], 'Key' => []],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                        ],
                    ],
                ],
            ],
            'components' => [
                'schemas' => [
                    'account' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => [
                                'type' => ['string', 'null'],
                                'title' => 'AccountStatus',
                                'enum' => ['active', 'blocked'],
                                'example' => 'active',
                            ],
                        ],
                    ],
                ],
                'securitySchemes' => [
                    'Project' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-Project',
                        'description' => 'Project ID',
                    ],
                    'ProjectPath' => [
                        'type' => 'apiKey',
                        'in' => 'query',
                        'name' => 'project',
                        'description' => 'Project ID',
                    ],
                    'Session' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-Session',
                        'description' => 'Session',
                    ],
                    'Key' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-Key',
                        'description' => 'API key',
                    ],
                ],
            ],
        ];

        $this->profile = [
            'version' => '1.0',
            'schemas' => [
                'account' => [
                    'requestModel' => true,
                    'properties' => [
                        'status' => [
                            'enumName' => 'Status',
                            'enumKeys' => ['ACTIVE', 'BLOCKED'],
                        ],
                    ],
                ],
            ],
            'operations' => [
                'accountGet' => [
                    'parameters' => [
                        'statuses' => [
                            'items' => [
                                'enumName' => 'Status',
                                'enumKeys' => ['ACTIVE', 'BLOCKED'],
                            ],
                        ],
                    ],
                    'responseMetadata' => [
                        '200' => [
                            'discriminator' => [
                                'propertyName' => 'status',
                                'x-mapping' => [
                                    '#/components/schemas/account' => ['status' => 'active'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'platforms' => [
                'client' => [
                    'schemas' => ['account'],
                    'securitySchemes' => [
                        'Project' => [],
                        'ProjectPath' => [
                            'setter' => 'Project',
                            'location' => [
                                'in' => 'path',
                                'parameter' => 'project_id',
                                'config' => 'project',
                            ],
                            'demo' => '<PROJECT_ID>',
                        ],
                        'Session' => [],
                    ],
                    'include' => ['accountGet'],
                    'operations' => [
                        'accountGet' => [
                            'name' => 'get',
                            'auth' => ['Project'],
                            'security' => ['Project', 'Session'],
                            'cookies' => true,
                            'platforms' => ['client'],
                            'methods' => [
                                [
                                    'name' => 'getLegacy',
                                    'namespace' => 'account',
                                    'auth' => ['Project'],
                                    'parameters' => [],
                                    'required' => [],
                                    'responses' => [],
                                    'description' => 'Legacy account method.',
                                ],
                                [
                                    'name' => 'get',
                                    'namespace' => 'account',
                                    'auth' => ['Project'],
                                    'parameters' => [],
                                    'required' => [],
                                    'responses' => [],
                                    'description' => 'Get account.',
                                ],
                            ],
                        ],
                    ],
                ],
                'server' => [
                    'securitySchemes' => [
                        'Project' => [],
                        'Key' => [
                            'definition' => [
                                'type' => 'http',
                                'scheme' => 'bearer',
                                'bearerFormat' => 'JWT',
                                'description' => 'Manager token',
                            ],
                        ],
                    ],
                    'include' => ['usersList'],
                ],
            ],
        ];
    }

    public function testFiltersPortableDocumentForPlatform(): void
    {
        $projected = new GenerationProfile($this->profile)->apply($this->document, 'client');

        $this->assertArrayHasKey('/account', $projected['paths']);
        $this->assertArrayNotHasKey('/users', $projected['paths']);
        $this->assertSame(['Project', 'ProjectPath', 'Session'], \array_keys($projected['components']['securitySchemes']));
        $this->assertSame(
            [['Project' => [], 'Session' => []]],
            $projected['paths']['/account']['get']['security']
        );
        $this->assertSame('get', $projected['paths']['/account']['get']['x-appwrite']['method']);
        $this->assertSame(['Project' => []], $projected['paths']['/account']['get']['x-appwrite']['auth']);
        $this->assertSame('Project', $projected['components']['securitySchemes']['ProjectPath']['x-appwrite']['setter']);
        $this->assertTrue($projected['components']['schemas']['account']['x-request-model']);
        $this->assertSame('Status', $projected['components']['schemas']['account']['properties']['status']['x-enum-name']);
        $this->assertSame(
            'Status',
            $projected['paths']['/account']['get']['parameters'][0]['schema']['items']['x-enum-name'],
        );
        $this->assertArrayHasKey(
            'x-mapping',
            $projected['paths']['/account']['get']['responses']['200']['content']['application/json']['schema']['discriminator'],
        );
        $this->assertArrayNotHasKey('x-appwrite', $this->document['paths']['/account']['get']);
    }

    public function testAppliesSecuritySchemeDefinitionOverride(): void
    {
        $projected = new GenerationProfile($this->profile)->apply($this->document, 'server');

        $this->assertSame([
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
            'description' => 'Manager token',
        ], $projected['components']['securitySchemes']['Key']);
    }

    public function testOpenApiParserConsumesProfileWithoutChangingLegacyContract(): void
    {
        $spec = new OpenAPI3(
            \json_encode($this->document, JSON_THROW_ON_ERROR),
            'client',
            $this->profile,
        );
        $methods = $spec->getMethods('account');

        $this->assertCount(2, $methods);
        $this->assertSame(['getLegacy', 'get'], \array_column($methods, 'name'));
        $this->assertSame(['Project'], \array_keys($methods[1]['auth'][0]));
        $this->assertSame(['Project', 'Session'], $methods[1]['security']);
        $this->assertSame(['client'], $methods[1]['platforms']);
        $this->assertTrue($methods[1]['cookies']);
        $this->assertSame(['account'], \array_keys($spec->getServices()));
    }

    public function testStandardOperationIdAndSecurityWorkWithoutProfile(): void
    {
        $spec = new OpenAPI3(\json_encode($this->document, JSON_THROW_ON_ERROR));
        $method = $spec->getMethods('account')[0];

        $this->assertSame('get', $method['name']);
        $this->assertCount(2, $method['auth']);
        $this->assertSame(['Project', 'Session'], \array_keys($method['auth'][0]));
        $this->assertSame(['Project', 'Key'], \array_keys($method['auth'][1]));
        $this->assertSame(['Project', 'Session'], $method['security']);

        $property = $spec->getDefinitions()['account']['properties']['status'];
        $this->assertSame('string', $property['type']);
        $this->assertTrue($property['x-nullable']);
        $this->assertSame('AccountStatus', $property['enumName']);
        $this->assertSame('active', $property['example']);
    }

    public function testPlatformListsReplaceGlobalLists(): void
    {
        $profile = $this->profile;
        $profile['operations']['accountGet']['auth'] = ['Project', 'Session'];
        $profile['operations']['accountGet']['methods'] = [
            ['name' => 'globalOne'],
            ['name' => 'globalTwo'],
        ];
        $profile['operations']['accountGet']['platforms'] = ['client', 'server'];
        $profile['platforms']['client']['operations']['accountGet']['auth'] = ['Project'];
        $profile['platforms']['client']['operations']['accountGet']['methods'] = [
            ['name' => 'clientOnly'],
        ];
        $profile['platforms']['client']['operations']['accountGet']['platforms'] = ['client'];

        $projected = new GenerationProfile($profile)->apply($this->document, 'client');
        $metadata = $projected['paths']['/account']['get']['x-appwrite'];

        $this->assertSame(['Project'], \array_keys($metadata['auth']));
        $this->assertSame(['clientOnly'], \array_column($metadata['methods'], 'name'));
        $this->assertSame(['client'], $metadata['platforms']);
    }

    public function testRejectsUnsupportedProfileVersion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Generation profile version must be 1.0.');

        new GenerationProfile(['version' => '2.0', 'platforms' => []]);
    }

    public function testRejectsUnknownPlatform(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Generation profile does not define platform 'console'.");

        new GenerationProfile($this->profile)->apply($this->document, 'console');
    }
}
