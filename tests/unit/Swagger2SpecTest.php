<?php

declare(strict_types=1);

namespace Tests\Unit;

use Utopia\OpenAPI\Model\Operation;
use PHPUnit\Framework\TestCase;
use Utopia\OpenAPI\Model\ParameterLocation;
use Utopia\OpenAPI\Model\Schema\ObjectSchema;
use Utopia\OpenAPI\Model\Schema\ReferenceSchema;
use Utopia\OpenAPI\Model\SecuritySchemeType;
use Utopia\OpenAPI\Parser;
use Utopia\OpenAPI\Specification;
use Utopia\OpenAPI\Version;

final class Swagger2SpecTest extends TestCase
{
    private Specification $spec;

    protected function setUp(): void
    {
        $this->spec = Parser::parse(\file_get_contents(__DIR__ . '/../resources/spec-swagger2.json'));
    }

    public function testSwaggerProducesCanonicalSpecification(): void
    {
        $this->assertSame(Version::V2, $this->spec->version);
        $this->assertSame('2.0', $this->spec->sourceVersion);
        $this->assertSame('http://mockapi/v1', $this->spec->servers[0]->url);
        $this->assertInstanceOf(ObjectSchema::class, $this->spec->schemas['mock']);
    }

    public function testSecurityDefinitionsBecomeTypedSchemes(): void
    {
        $project = $this->spec->securitySchemes['Project'];
        $this->assertSame(SecuritySchemeType::API_KEY, $project->type);
        $this->assertSame(ParameterLocation::HEADER, $project->location);
        $this->assertSame('X-Appwrite-Project', $project->name);
    }

    public function testBodyAndResponseSchemasAreNormalized(): void
    {
        $operation = $this->operation('fooPost');
        $body = $operation->requestBody?->content['application/json']->schema;
        $this->assertInstanceOf(ObjectSchema::class, $body);

        $response = $operation->responses['200']->content['application/json']->schema;
        $this->assertInstanceOf(ReferenceSchema::class, $response);
        $this->assertSame('#/definitions/mock', $response->reference);
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
