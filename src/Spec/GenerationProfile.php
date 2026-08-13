<?php

namespace Appwrite\Spec;

use InvalidArgumentException;
use JsonException;

/**
 * Applies SDK-generation policy to a portable OpenAPI document.
 *
 * OpenAPI describes the HTTP API, while a generation profile describes which
 * part of that API belongs in a particular SDK and preserves generator-only
 * compatibility details such as method aliases. The source OpenAPI document
 * remains free of vendor extensions.
 */
final class GenerationProfile
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

    private array $profile;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(array|string $profile)
    {
        if (\is_string($profile)) {
            try {
                $profile = \json_decode($profile, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new InvalidArgumentException('Failed to parse generation profile: ' . $exception->getMessage(), previous: $exception);
            }
        }

        if (($profile['version'] ?? null) !== '1.0') {
            throw new InvalidArgumentException('Generation profile version must be 1.0.');
        }

        if (!isset($profile['platforms']) || !\is_array($profile['platforms'])) {
            throw new InvalidArgumentException('Generation profile must define platforms.');
        }

        $this->profile = $profile;
    }

    /**
     * @param array<string, mixed> $document
     * @return array<string, mixed>
     */
    public function apply(array $document, string $platform): array
    {
        $config = $this->profile['platforms'][$platform] ?? null;
        if (!\is_array($config)) {
            throw new InvalidArgumentException("Generation profile does not define platform '{$platform}'.");
        }

        $document = $this->applySchemas($document, $this->profile['schemas'] ?? []);
        $document = $this->selectSchemas($document, $config['schemas'] ?? null);
        $document = $this->applySecuritySchemes($document, $config['securitySchemes'] ?? null);
        $allowedSecurity = \array_fill_keys(\array_keys($document['components']['securitySchemes'] ?? []), true);
        $include = $config['include'] ?? null;
        $include = \is_array($include) ? \array_fill_keys($include, true) : null;
        $operations = \is_array($this->profile['operations'] ?? null) ? $this->profile['operations'] : [];
        $platformOperations = \is_array($config['operations'] ?? null) ? $config['operations'] : [];

        foreach ($document['paths'] ?? [] as $path => $pathItem) {
            if (!\is_array($pathItem)) {
                continue;
            }

            foreach ($pathItem as $method => $operation) {
                if (!\in_array(\strtolower((string) $method), self::HTTP_METHODS, true) || !\is_array($operation)) {
                    continue;
                }

                $operationId = $operation['operationId'] ?? null;
                if (!\is_string($operationId) || $operationId === '') {
                    if ($include !== null) {
                        unset($document['paths'][$path][$method]);
                    }
                    continue;
                }

                if ($include !== null && !isset($include[$operationId])) {
                    unset($document['paths'][$path][$method]);
                    continue;
                }

                $operation = $this->filterSecurity($operation, $allowedSecurity);
                $operationConfig = $operations[$operationId] ?? [];
                $platformOperation = $platformOperations[$operationId] ?? [];
                if (\is_array($operationConfig) && \is_array($platformOperation)) {
                    $operation = $this->applyOperation(
                        $operation,
                        \array_replace_recursive($operationConfig, $platformOperation),
                    );
                }

                $document['paths'][$path][$method] = $operation;
            }

            if (empty($document['paths'][$path])) {
                unset($document['paths'][$path]);
            }
        }

        if ($include !== null) {
            $document = $this->orderOperations($document, $include);
        }

        return $document;
    }

    /**
     * @param array<string, mixed> $document
     * @param array<string, int|bool> $include
     * @return array<string, mixed>
     */
    private function orderOperations(array $document, array $include): array
    {
        $order = \array_flip(\array_keys($include));

        foreach ($document['paths'] ?? [] as $path => $pathItem) {
            if (!\is_array($pathItem)) {
                continue;
            }

            \uksort($pathItem, function (string $left, string $right) use ($pathItem, $order): int {
                $leftId = $pathItem[$left]['operationId'] ?? null;
                $rightId = $pathItem[$right]['operationId'] ?? null;

                return ($order[$leftId] ?? PHP_INT_MAX) <=> ($order[$rightId] ?? PHP_INT_MAX);
            });
            $document['paths'][$path] = $pathItem;
        }

        \uasort($document['paths'], function (array $left, array $right) use ($order): int {
            $position = function (array $pathItem) use ($order): int {
                $positions = [];
                foreach ($pathItem as $operation) {
                    if (\is_array($operation) && isset($operation['operationId'], $order[$operation['operationId']])) {
                        $positions[] = $order[$operation['operationId']];
                    }
                }

                return $positions === [] ? PHP_INT_MAX : \min($positions);
            };

            return $position($left) <=> $position($right);
        });

        return $document;
    }

    /**
     * @param array<string, mixed> $document
     * @param array<string, mixed> $schemas
     * @return array<string, mixed>
     */
    private function applySchemas(array $document, array $schemas): array
    {
        foreach ($schemas as $name => $config) {
            if (!\is_array($config) || !isset($document['components']['schemas'][$name])) {
                continue;
            }

            if (($config['requestModel'] ?? false) === true) {
                $document['components']['schemas'][$name]['x-request-model'] = true;
            }

            foreach ($config['properties'] ?? [] as $property => $metadata) {
                if (!\is_array($metadata) || !isset($document['components']['schemas'][$name]['properties'][$property])) {
                    continue;
                }

                $target = &$document['components']['schemas'][$name]['properties'][$property];
                $this->applyParameterMetadata($target, $metadata);
                unset($target);
            }
        }

        return $document;
    }

    /**
     * @param array<string, mixed> $document
     * @return array<string, mixed>
     */
    private function selectSchemas(array $document, mixed $config): array
    {
        if (!\is_array($config)) {
            return $document;
        }

        $schemas = $document['components']['schemas'] ?? [];
        $selected = [];
        foreach ($config as $name) {
            if (\is_string($name) && isset($schemas[$name])) {
                $selected[$name] = $schemas[$name];
            }
        }
        $document['components']['schemas'] = $selected;

        return $document;
    }

    /**
     * @param array<string, mixed> $document
     * @return array<string, mixed>
     */
    private function applySecuritySchemes(array $document, mixed $config): array
    {
        if (!\is_array($config)) {
            return $document;
        }

        $schemes = $document['components']['securitySchemes'] ?? [];
        $selected = [];

        foreach ($config as $key => $value) {
            $name = \is_int($key) ? $value : $key;
            if (!\is_string($name) || !isset($schemes[$name]) || !\is_array($schemes[$name])) {
                continue;
            }

            $profile = \is_array($value) ? $value : [];
            $definition = $profile['definition'] ?? $schemes[$name];
            if (!\is_array($definition)) {
                continue;
            }

            $metadata = [];
            if (isset($profile['setter']) && \is_string($profile['setter'])) {
                $metadata['setter'] = $profile['setter'];
            }
            if (isset($profile['location']) && \is_array($profile['location'])) {
                $metadata['location'] = $profile['location']['in'] ?? null;
                $metadata['param'] = $profile['location']['parameter'] ?? null;
                $metadata['config'] = $profile['location']['config'] ?? null;
                $metadata = \array_filter($metadata, fn(mixed $value): bool => $value !== null);
            }
            foreach (['demo', 'optional'] as $key) {
                if (\array_key_exists($key, $profile)) {
                    $metadata[$key] = $profile[$key];
                }
            }

            if ($metadata !== []) {
                $definition['x-appwrite'] = \array_merge($definition['x-appwrite'] ?? [], $metadata);
            }

            $selected[$name] = $definition;
        }

        $document['components'] ??= [];
        $document['components']['securitySchemes'] = $selected;

        return $document;
    }

    /**
     * @param array<string, mixed> $operation
     * @param array<string, bool> $allowed
     * @return array<string, mixed>
     */
    private function filterSecurity(array $operation, array $allowed): array
    {
        if (!isset($operation['security']) || !\is_array($operation['security'])) {
            return $operation;
        }

        $requirements = [];
        foreach ($operation['security'] as $requirement) {
            if (!\is_array($requirement)) {
                continue;
            }

            if ($requirement === [] || \array_diff_key($requirement, $allowed) === []) {
                $requirements[] = $requirement;
            }
        }

        $operation['security'] = $requirements;

        return $operation;
    }

    /**
     * @param array<string, mixed> $operation
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function applyOperation(array $operation, array $config): array
    {
        $metadata = [];

        foreach (['packaging', 'cookies', 'type', 'consoleOnly', 'produces', 'platforms'] as $key) {
            if (\array_key_exists($key, $config)) {
                $metadata[$key] = $config[$key];
            }
        }

        if (isset($config['name']) && \is_string($config['name'])) {
            $metadata['method'] = $config['name'];
        }

        if (isset($config['auth']) && \is_array($config['auth'])) {
            $metadata['auth'] = \array_fill_keys($config['auth'], []);
        }

        if (isset($config['security']) && \is_array($config['security'])) {
            $operation['security'] = [\array_fill_keys($config['security'], [])];
        }

        if (isset($config['methods']) && \is_array($config['methods'])) {
            $metadata['methods'] = \array_map(function (array $method): array {
                if (isset($method['auth']) && \array_is_list($method['auth'])) {
                    $method['auth'] = \array_fill_keys($method['auth'], []);
                }

                return $method;
            }, $config['methods']);
        }

        if (isset($config['deprecation']) && \is_array($config['deprecation'])) {
            $operation['deprecated'] = true;
            $metadata['deprecated'] = $config['deprecation'];
        }

        foreach ($config['responseMetadata'] ?? [] as $code => $responseMetadata) {
            if (!\is_array($responseMetadata) || !isset($operation['responses'][$code])) {
                continue;
            }

            foreach ($operation['responses'][$code]['content'] ?? [] as $contentType => $media) {
                if (isset($media['schema']) && \is_array($media['schema'])) {
                    if (isset($responseMetadata['discriminator']) && \is_array($responseMetadata['discriminator'])) {
                        $media['schema']['discriminator'] = $responseMetadata['discriminator'];
                    }
                    $operation['responses'][$code]['content'][$contentType] = $media;
                }
            }
        }

        foreach ($config['parameters'] ?? [] as $name => $parameterMetadata) {
            if (!\is_array($parameterMetadata)) {
                continue;
            }

            foreach ($operation['parameters'] ?? [] as $index => $parameter) {
                if (($parameter['name'] ?? null) === $name) {
                    if (isset($parameter['schema']) && \is_array($parameter['schema'])) {
                        $this->applyParameterMetadata($parameter['schema'], $parameterMetadata);
                    } else {
                        $this->applyParameterMetadata($parameter, $parameterMetadata);
                    }
                    $operation['parameters'][$index] = $parameter;
                }
            }

            foreach ($operation['requestBody']['content'] ?? [] as $contentType => $media) {
                if (isset($media['schema']['properties'][$name])) {
                    $this->applyParameterMetadata($media['schema']['properties'][$name], $parameterMetadata);
                    $operation['requestBody']['content'][$contentType] = $media;
                }
            }
        }

        if (!empty($metadata)) {
            $operation['x-appwrite'] = \array_merge($operation['x-appwrite'] ?? [], $metadata);
        }

        return $operation;
    }

    /**
     * @param array<string, mixed> $target
     * @param array<string, mixed> $metadata
     */
    private function applyParameterMetadata(array &$target, array $metadata): void
    {
        foreach (
            [
            'class' => 'x-class',
            'uploadId' => 'x-upload-id',
            'model' => 'x-model',
            'enumName' => 'x-enum-name',
            'enumKeys' => 'x-enum-keys',
            ] as $key => $extension
        ) {
            if (\array_key_exists($key, $metadata)) {
                $target[$extension] = $metadata[$key];
            }
        }

        if (isset($metadata['discriminator']) && \is_array($metadata['discriminator'])) {
            $target['discriminator'] = $metadata['discriminator'];
        }

        if (isset($metadata['items']) && \is_array($metadata['items'])) {
            $target['items'] ??= [];
            $this->applyParameterMetadata($target['items'], $metadata['items']);
        }
    }
}
