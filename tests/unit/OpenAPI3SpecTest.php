<?php

declare(strict_types=1);

namespace Tests\Unit;

use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\RequestBody;
use PHPUnit\Framework\TestCase;
use Utopia\OpenAPI\Model\Schema\ArraySchema;
use Utopia\OpenAPI\Model\Schema\CompositeSchema;
use Utopia\OpenAPI\Model\Schema\ObjectSchema;
use Utopia\OpenAPI\Model\Schema\ReferenceSchema;
use Utopia\OpenAPI\Model\Schema\StringSchema;
use Utopia\OpenAPI\Parser;
use Utopia\OpenAPI\Specification;

final class OpenAPI3SpecTest extends TestCase
{
    private Specification $spec;

    protected function setUp(): void
    {
        $this->spec = Parser::parse(\file_get_contents(__DIR__ . '/../resources/spec-openapi3.json'));
    }

    public function testParsesBothFormatsIntoCanonicalSpecifications(): void
    {
        $swagger = Parser::parse(\file_get_contents(__DIR__ . '/../resources/spec-swagger2.json'));

        $this->assertSame($swagger->info->title, $this->spec->info->title);
        $this->assertSame($swagger->info->version, $this->spec->info->version);
        $this->assertSame(\array_keys($swagger->schemas), \array_keys($this->spec->schemas));
        $this->assertSame(
            \array_map(static fn(Operation $operation): string => $operation->id, $swagger->operations()),
            \array_map(static fn(Operation $operation): string => $operation->id, $this->spec->operations()),
        );
    }

    public function testMetadataUsesTypedDtos(): void
    {
        $this->assertSame('Appwrite', $this->spec->info->title);
        $this->assertSame('1.4.2', $this->spec->info->version);
        $this->assertSame('http://mockapi/v1', $this->spec->servers[0]->url);
        $this->assertSame('Appwrite Team', $this->spec->info->contact?->name);
        $this->assertSame('BSD-3-Clause', $this->spec->info->license?->name);
    }

    public function testOperationsUseTypedParametersAndRequestBodies(): void
    {
        $operation = $this->operation('fooPost');
        $this->assertInstanceOf(RequestBody::class, $operation->requestBody);
        $this->assertArrayHasKey('application/json', $operation->requestBody->content);
        $this->assertInstanceOf(ObjectSchema::class, $operation->requestBody->content['application/json']->schema);

        $union = $this->operation('unionGet');
        $this->assertInstanceOf(StringSchema::class, $union->parameters[0]->schema);
        $this->assertSame('mock', $union->parameters[0]->schema->default);
        $this->assertSame('mock', $union->parameters[0]->schema->extensions['x-example']);
    }

    public function testMultipartAndResponsesUseTypedSchemas(): void
    {
        $upload = $this->operation('generalUpload');
        $body = $upload->requestBody?->content['multipart/form-data']->schema;
        $this->assertInstanceOf(ObjectSchema::class, $body);
        $this->assertInstanceOf(StringSchema::class, $body->properties['file']);
        $this->assertSame('binary', $body->properties['file']->format);

        $response = $upload->responses['200']->content['application/json']->schema;
        $this->assertInstanceOf(ReferenceSchema::class, $response);
        $this->assertSame('#/components/schemas/mock', $response->reference);
    }

    public function testModelsAndUnionsRemainTyped(): void
    {
        $this->assertInstanceOf(ObjectSchema::class, $this->spec->schemas['mock']);
        $this->assertTrue($this->spec->schemas['player']->extensions['x-request-model']);

        $container = $this->spec->schemas['unionContainer'];
        $this->assertInstanceOf(ObjectSchema::class, $container);
        $this->assertInstanceOf(ArraySchema::class, $container->properties['entries']);
        $this->assertInstanceOf(CompositeSchema::class, $container->properties['entries']->items);
    }

    private function operation(string $id): Operation
    {
        foreach ($this->spec->operations() as $operation) {
            if ($operation->id === $id) {
                return $operation;
            }
        }

        $this->fail("Operation {$id} not found");
    }
}
