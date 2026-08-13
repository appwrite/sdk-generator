<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appwrite\Spec\Model\Operation;
use Appwrite\Spec\OpenAPI3Reader;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class OpenAPI3ReaderTest extends TestCase
{
    public function testReadsOfficialOpenAPIFieldsIntoNativeModels(): void
    {
        $document = new OpenAPI3Reader(\file_get_contents(__DIR__ . '/../resources/spec-openapi3.json'))->read();

        $this->assertSame('Appwrite', $document->title);
        $this->assertSame('1.4.2', $document->version);
        $this->assertSame('http://mockapi/v1', $document->endpoint);
        $this->assertArrayHasKey('general', $document->services);
        $this->assertArrayHasKey('mock', $document->schemas);
        $this->assertArrayHasKey('Project', $document->securitySchemes);

        $operation = $this->getOperation($document->services['general']->operations, 'generalCreatePlayer');

        $this->assertSame('post', $operation->method);
        $this->assertSame('/mock/tests/general/models', $operation->path);
        $this->assertSame(['general'], $operation->tags);
        $this->assertNotNull($operation->requestBody);
        $this->assertArrayHasKey(200, $operation->responses);
        $this->assertSame(['Project', 'Key', 'JWT'], \array_keys($operation->security[0]->schemes));
    }

    public function testKeepsSecurityAlternativesSeparate(): void
    {
        $document = new OpenAPI3Reader([
            'openapi' => '3.1.0',
            'info' => ['title' => 'Example', 'version' => '1.0.0'],
            'security' => [
                ['Project' => [], 'Session' => []],
                ['Project' => [], 'JWT' => []],
            ],
            'paths' => [
                '/account' => [
                    'get' => [
                        'operationId' => 'accountGet',
                        'tags' => ['account'],
                        'responses' => ['200' => ['description' => 'OK']],
                    ],
                ],
                '/health' => [
                    'get' => [
                        'operationId' => 'healthGet',
                        'tags' => ['health'],
                        'security' => [],
                        'responses' => ['200' => ['description' => 'OK']],
                    ],
                ],
            ],
        ])->read();

        $account = $document->services['account']->operations[0];
        $this->assertCount(2, $account->security);
        $this->assertSame(['Project', 'Session'], \array_keys($account->security[0]->schemes));
        $this->assertSame(['Project', 'JWT'], \array_keys($account->security[1]->schemes));
        $this->assertSame([], $document->services['health']->operations[0]->security);
    }

    public function testIncludesPathLevelParametersOnOperations(): void
    {
        $document = new OpenAPI3Reader([
            'openapi' => '3.1.0',
            'info' => ['title' => 'Example', 'version' => '1.0.0'],
            'paths' => [
                '/users/{userId}' => [
                    'parameters' => [[
                        'name' => 'userId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ]],
                    'get' => [
                        'operationId' => 'usersGet',
                        'tags' => ['users'],
                        'parameters' => [
                            [
                                'name' => 'userId',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'Operation override.',
                                'schema' => ['type' => 'string'],
                            ],
                            [
                                'name' => 'queries',
                                'in' => 'query',
                                'schema' => ['type' => 'array'],
                            ],
                        ],
                        'responses' => ['200' => ['description' => 'OK']],
                    ],
                ],
            ],
        ])->read();

        $parameters = $document->services['users']->operations[0]->parameters;

        $this->assertSame(['userId', 'queries'], \array_column($parameters, 'name'));
        $this->assertSame('Operation override.', $parameters[0]['description']);
    }

    public function testRequiresAnOpenAPIVersion(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenAPI3Reader([
            'info' => ['title' => 'Example', 'version' => '1.0.0'],
            'paths' => [],
        ]);
    }

    /**
     * @param list<Operation> $operations
     */
    private function getOperation(array $operations, string $id): Operation
    {
        foreach ($operations as $operation) {
            if ($operation->id === $id) {
                return $operation;
            }
        }

        $this->fail("Operation $id not found");
    }
}
