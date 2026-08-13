<?php

declare(strict_types=1);

namespace Appwrite\Spec;

use Appwrite\Spec\Model\Document;
use Appwrite\Spec\Model\Operation;
use Appwrite\Spec\Model\SecurityRequirement;
use Appwrite\Spec\Model\Service;
use InvalidArgumentException;
use JsonException;

/**
 * Reads an OpenAPI document into format-independent SDK generator models.
 *
 * This reader intentionally consumes only official OpenAPI fields. Legacy
 * vendor-extension behavior remains in OpenAPI3 until each concern migrates to
 * these models.
 */
final class OpenAPI3Reader
{
    private const array HTTP_METHODS = [
        'delete',
        'get',
        'head',
        'options',
        'patch',
        'post',
        'put',
        'trace',
    ];

    /** @var array<string, mixed> */
    private array $document;

    /**
     * @param array<string, mixed>|string $input
     * @throws JsonException
     */
    public function __construct(array|string $input)
    {
        $this->document = \is_string($input)
            ? \json_decode($input, true, 512, JSON_THROW_ON_ERROR)
            : $input;

        if (!\is_string($this->document['openapi'] ?? null)) {
            throw new InvalidArgumentException('The document must declare an OpenAPI version.');
        }
    }

    public function read(): Document
    {
        $info = $this->document['info'] ?? [];
        $operations = $this->readOperations();
        $descriptions = [];

        foreach ($this->document['tags'] ?? [] as $tag) {
            if (\is_array($tag) && \is_string($tag['name'] ?? null)) {
                $descriptions[$tag['name']] = (string) ($tag['description'] ?? '');
            }
        }

        $services = [];
        foreach ($operations as $operation) {
            foreach ($operation->tags as $tag) {
                $services[$tag] ??= new Service($tag, $descriptions[$tag] ?? '', []);
                $services[$tag] = new Service(
                    $tag,
                    $services[$tag]->description,
                    [...$services[$tag]->operations, $operation],
                );
            }
        }

        return new Document(
            title: (string) ($info['title'] ?? ''),
            description: (string) ($info['description'] ?? ''),
            version: (string) ($info['version'] ?? ''),
            endpoint: (string) ($this->document['servers'][0]['url'] ?? 'https://example.com'),
            services: $services,
            schemas: $this->readMap($this->document['components']['schemas'] ?? []),
            securitySchemes: $this->readMap($this->document['components']['securitySchemes'] ?? []),
            security: $this->readSecurity($this->document['security'] ?? []),
        );
    }

    /** @return list<Operation> */
    private function readOperations(): array
    {
        $operations = [];

        foreach ($this->document['paths'] ?? [] as $path => $pathItem) {
            if (!\is_string($path) || !\is_array($pathItem)) {
                continue;
            }

            $pathParameters = $this->readList($pathItem['parameters'] ?? []);

            foreach (self::HTTP_METHODS as $method) {
                $operation = $pathItem[$method] ?? null;
                if (!\is_array($operation)) {
                    continue;
                }

                $security = \array_key_exists('security', $operation)
                    ? $operation['security']
                    : ($this->document['security'] ?? []);

                $operations[] = new Operation(
                    id: (string) ($operation['operationId'] ?? ''),
                    method: $method,
                    path: $path,
                    tags: \array_values(\array_filter($operation['tags'] ?? [], \is_string(...))),
                    summary: (string) ($operation['summary'] ?? ''),
                    description: (string) ($operation['description'] ?? ''),
                    deprecated: ($operation['deprecated'] ?? false) === true,
                    parameters: $this->mergeParameters(
                        $pathParameters,
                        $this->readList($operation['parameters'] ?? []),
                    ),
                    requestBody: \is_array($operation['requestBody'] ?? null) ? $operation['requestBody'] : null,
                    responses: $this->readMap($operation['responses'] ?? []),
                    security: $this->readSecurity($security),
                );
            }
        }

        return $operations;
    }

    /**
     * Operation parameters override path parameters with the same name and
     * location, as required by OpenAPI.
     *
     * @param list<array<string, mixed>> $pathParameters
     * @param list<array<string, mixed>> $operationParameters
     * @return list<array<string, mixed>>
     */
    private function mergeParameters(array $pathParameters, array $operationParameters): array
    {
        $parameters = [];

        foreach ([...$pathParameters, ...$operationParameters] as $parameter) {
            $reference = $parameter['$ref'] ?? null;
            $key = \is_string($reference)
                ? $reference
                : ($parameter['in'] ?? '') . "\0" . ($parameter['name'] ?? '');
            $parameters[$key] = $parameter;
        }

        return \array_values($parameters);
    }

    /** @return list<SecurityRequirement> */
    private function readSecurity(mixed $requirements): array
    {
        if (!\is_array($requirements)) {
            return [];
        }

        $result = [];
        foreach ($requirements as $requirement) {
            if (!\is_array($requirement)) {
                continue;
            }

            $schemes = [];
            foreach ($requirement as $name => $scopes) {
                if (!\is_string($name) || !\is_array($scopes)) {
                    continue;
                }
                $schemes[$name] = \array_values(\array_filter($scopes, \is_string(...)));
            }
            $result[] = new SecurityRequirement($schemes);
        }

        return $result;
    }

    /** @return list<array<string, mixed>> */
    private function readList(mixed $value): array
    {
        return \is_array($value)
            ? \array_values(\array_filter($value, \is_array(...)))
            : [];
    }

    /** @return array<int|string, array<string, mixed>> */
    private function readMap(mixed $value): array
    {
        if (!\is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $key => $item) {
            if (\is_array($item)) {
                $result[$key] = $item;
            }
        }
        return $result;
    }
}
