<?php

declare(strict_types=1);

namespace Tests;

use Appwrite\Spec\Swagger2;
use PHPUnit\Framework\TestCase;

final class Swagger2SpecTest extends TestCase
{
    private Swagger2 $spec;

    protected function setUp(): void
    {
        $this->spec = new Swagger2(\json_encode([
            'definitions' => [
                'user' => [
                    'description' => 'User',
                    'type' => 'object',
                    'properties' => [
                        'prefs' => [
                            'type' => 'object',
                            'description' => 'User preferences as a key-value object',
                            'x-example' => null,
                            'allOf' => [
                                ['$ref' => '#/definitions/preferences'],
                            ],
                        ],
                        'legacyPrefs' => [
                            'type' => 'object',
                            'description' => 'User preferences as a key-value object',
                            'x-example' => null,
                            'items' => [
                                '$ref' => '#/definitions/preferences',
                            ],
                        ],
                        'hashOptions' => [
                            'type' => 'object',
                            'description' => 'Password hashing algorithm options.',
                            'x-example' => null,
                            'x-oneOf' => [
                                ['$ref' => '#/definitions/algoArgon2'],
                                ['$ref' => '#/definitions/algoScrypt'],
                            ],
                        ],
                        'legacyHashOptions' => [
                            'type' => 'object',
                            'description' => 'Password hashing algorithm options.',
                            'x-example' => null,
                            'items' => [
                                'x-oneOf' => [
                                    ['$ref' => '#/definitions/algoArgon2'],
                                    ['$ref' => '#/definitions/algoScrypt'],
                                ],
                            ],
                        ],
                        'targets' => [
                            'type' => 'array',
                            'description' => 'User targets.',
                            'x-example' => null,
                            'items' => [
                                '$ref' => '#/definitions/target',
                            ],
                        ],
                        'attributes' => [
                            'type' => 'array',
                            'description' => 'Mixed attributes.',
                            'x-example' => null,
                            'items' => [
                                'x-anyOf' => [
                                    ['$ref' => '#/definitions/attributeString'],
                                    ['$ref' => '#/definitions/attributeInteger'],
                                ],
                            ],
                        ],
                        'name' => [
                            'type' => 'string',
                            'description' => 'User name.',
                            'x-example' => 'John Doe',
                        ],
                    ],
                ],
                'createUser' => [
                    'description' => 'Create user request',
                    'type' => 'object',
                    'x-request-model' => true,
                    'properties' => [
                        'prefs' => [
                            'type' => 'object',
                            'description' => 'User preferences as a key-value object',
                            'x-example' => null,
                            'allOf' => [
                                ['$ref' => '#/definitions/preferences'],
                            ],
                        ],
                        'hashOptions' => [
                            'type' => 'object',
                            'description' => 'Password hashing algorithm options.',
                            'x-example' => null,
                            'x-oneOf' => [
                                ['$ref' => '#/definitions/algoArgon2'],
                                ['$ref' => '#/definitions/algoScrypt'],
                            ],
                        ],
                    ],
                ],
                'preferences' => ['type' => 'object', 'properties' => []],
                'algoArgon2' => ['type' => 'object', 'properties' => []],
                'algoScrypt' => ['type' => 'object', 'properties' => []],
                'target' => ['type' => 'object', 'properties' => []],
                'attributeString' => ['type' => 'object', 'properties' => []],
                'attributeInteger' => ['type' => 'object', 'properties' => []],
            ],
        ]));
    }

    public function testObjectPropertyWithAllOfReferenceResolvesSubSchema(): void
    {
        $properties = $this->spec->getDefinitions()['user']['properties'];

        $this->assertSame('preferences', $properties['prefs']['sub_schema']);
        $this->assertSame('object', $properties['prefs']['type']);
    }

    public function testObjectPropertyWithLegacyItemsReferenceResolvesSubSchema(): void
    {
        $properties = $this->spec->getDefinitions()['user']['properties'];

        $this->assertSame('preferences', $properties['legacyPrefs']['sub_schema']);
    }

    public function testObjectPropertyWithOneOfUnionResolvesSubSchemas(): void
    {
        $properties = $this->spec->getDefinitions()['user']['properties'];

        $this->assertSame(['algoArgon2', 'algoScrypt'], $properties['hashOptions']['sub_schemas']);
    }

    public function testObjectPropertyWithLegacyItemsOneOfUnionResolvesSubSchemas(): void
    {
        $properties = $this->spec->getDefinitions()['user']['properties'];

        $this->assertSame(['algoArgon2', 'algoScrypt'], $properties['legacyHashOptions']['sub_schemas']);
    }

    public function testArrayPropertyWithItemsReferenceResolvesSubSchema(): void
    {
        $properties = $this->spec->getDefinitions()['user']['properties'];

        $this->assertSame('target', $properties['targets']['sub_schema']);
        $this->assertSame('array', $properties['targets']['type']);
    }

    public function testArrayPropertyWithItemsAnyOfUnionResolvesSubSchemas(): void
    {
        $properties = $this->spec->getDefinitions()['user']['properties'];

        $this->assertSame(['attributeString', 'attributeInteger'], $properties['attributes']['sub_schemas']);
    }

    public function testScalarPropertyHasNoSubSchema(): void
    {
        $properties = $this->spec->getDefinitions()['user']['properties'];

        $this->assertArrayNotHasKey('sub_schema', $properties['name']);
        $this->assertArrayNotHasKey('sub_schemas', $properties['name']);
    }

    public function testRequestModelPropertiesResolveSubSchemas(): void
    {
        $properties = $this->spec->getRequestModels()['createUser']['properties'];

        $this->assertSame('preferences', $properties['prefs']['sub_schema']);
        $this->assertSame(['algoArgon2', 'algoScrypt'], $properties['hashOptions']['sub_schemas']);
    }

    public function testRequestModelsAreExcludedFromDefinitions(): void
    {
        $this->assertArrayNotHasKey('createUser', $this->spec->getDefinitions());
    }
}
