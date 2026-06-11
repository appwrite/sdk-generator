<?php

namespace Appwrite\Spec;

use Override;
use stdClass;

/**
 * OpenAPI 3 spec parser.
 *
 * This is the native parsing pipeline of the generator: every spec format is
 * represented as an OpenAPI 3 document. Legacy formats (Swagger 2) convert
 * their document into this shape at construction and inherit the pipeline.
 */
class OpenAPI3 extends Spec
{
    protected const string DEFAULT_CONTENT_TYPE = 'application/json';

    #[Override]
    public function getTitle(): string
    {
        return $this->getAttribute('info.title', '');
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->getAttribute('info.description', '');
    }

    #[Override]
    public function getNamespace(): string
    {
        $namespace = $this->getAttribute('info.namespace', '');
        return $namespace !== '' ? $namespace : $this->getTitle();
    }

    #[Override]
    public function getVersion(): string
    {
        return $this->getAttribute('info.version', '');
    }

    #[Override]
    public function getEndpoint(): string
    {
        return $this->getAttribute('servers.0.url', 'https://example.com');
    }

    #[Override]
    public function getEndpointDocs(): string
    {
        $servers = $this->getAttribute('servers', []);
        if (empty($servers)) {
            return '';
        }

        $server = $servers[0];
        foreach ($servers as $candidate) {
            if (isset($candidate['url']) && \str_contains((string) $candidate['url'], '{region}')) {
                $server = $candidate;
                break;
            }
        }

        return \preg_replace_callback('/\{([^}]+)\}/', fn(array $matches): string => '<' . \strtoupper($matches[1]) . '>', $server['url'] ?? '') ?? '';
    }

    #[Override]
    public function getLicenseName(): string
    {
        return $this->getAttribute('info.license.name', '');
    }

    #[Override]
    public function getLicenseURL(): string
    {
        return $this->getAttribute('info.license.url', '');
    }

    #[Override]
    public function getContactName(): string
    {
        return $this->getAttribute('info.contact.name', '');
    }

    #[Override]
    public function getContactURL(): string
    {
        return $this->getAttribute('info.contact.url', '');
    }

    #[Override]
    public function getContactEmail(): string
    {
        return $this->getAttribute('info.contact.email', '');
    }

    #[Override]
    public function getServices(): array
    {
        $list = [];

        $paths = $this->getAttribute('paths', []);
        $tags = $this->getAttribute('tags', []);

        foreach ($paths as $path) {
            foreach ($path as $method) {
                if (isset($method['tags'])) {
                    foreach ($method['tags'] as $tag) {
                        if (!array_key_exists((string) $tag, $list)) {
                            $methods = $this->getMethods($tag);
                            $list[$tag] = [
                                'name' => $tag,
                                'methods' => $methods,
                            ];
                        }
                    }
                }
            }
        }

        foreach ($tags as $tag) { // Fetch descriptions
            $name = $tag['name'];

            if (isset($list[$name])) {
                $list[$name]['description'] = $tag['description'] ?? '';
            }
        }

        return $list;
    }

    protected function parseMethod(string $methodName, string $pathName, array $method): array
    {
        $security = $this->getAttribute('components.securitySchemes', []);
        $methodAuth = $method['x-appwrite']['auth'] ?? [];
        $methodSecurity = $method['security'][0] ?? [];

        foreach ($methodAuth as $i => $node) {
            $methodAuth[$i] = (array_key_exists((string) $i, $security)) ? [...$security[$i], 'global' => $i !== 'Project'] : [];
        }
        foreach ($methodSecurity as $i => $node) {
            $methodSecurity[$i] = (array_key_exists((string) $i, $security)) ? [...$security[$i], 'global' => $i !== 'Project'] : [];
        }

        $methodSecurityHeaders = [];
        $methodSecurityQueries = [];
        $methodSecurityPathParams = [];
        foreach ($methodSecurity as $i => $node) {
            if (($node['x-appwrite']['location'] ?? '') === 'path') {
                $methodSecurityPathParams[$node['x-appwrite']['param'] ?? $node['name'] ?? (string) $i] = [
                    'key' => (string) $i,
                    'config' => $node['x-appwrite']['config'] ?? $node['name'] ?? '',
                ];
                continue;
            }

            if (($node['in'] ?? '') === 'header' && !($node['global'] ?? false)) {
                $methodSecurityHeaders[$i] = $node;
            }
            if (($node['in'] ?? '') === 'query') {
                $methodSecurityQueries[$i] = $node;
            }
        }

        $requestBody = $method['requestBody'] ?? [];
        $requestContentTypes = \array_keys($requestBody['content'] ?? []);

        if ($requestContentTypes !== []) {
            $consumes = $requestContentTypes;
        } elseif (\in_array($methodName, ['get', 'head'], true)) {
            $consumes = [];
        } else {
            // OpenAPI 3 has no way to express a request content type without a body
            $consumes = [static::DEFAULT_CONTENT_TYPE];
        }

        $produces = [];
        foreach ($method['responses'] ?? [] as $response) {
            foreach (\array_keys($response['content'] ?? []) as $contentType) {
                if ($contentType !== '' && !\in_array($contentType, $produces, true)) {
                    $produces[] = $contentType;
                }
            }
        }
        if ($produces === []) {
            // No response declares content (e.g. 204 No content); the produced
            // content type is preserved in the x-appwrite metadata.
            $produces = $method['x-appwrite']['produces'] ?? [];
        }

        $responseModel = '';
        $responseModels = [];
        $responseDiscriminator = [];
        $responses = $method['responses'] ?? [];
        $emptyResponse = true;
        foreach ($responses as $code => $desc) {
            if ($code != '204') {
                $emptyResponse = false;
            }

            $schema = $this->getResponseSchema($desc);

            if ($schema === []) {
                continue;
            }

            // Check for single model reference
            if (isset($schema['$ref'])) {
                $responseModel = $this->normalizeSchemaRef($schema['$ref']);
            }

            // check for union types
            $union = $schema['oneOf'] ?? $schema['x-oneOf'] ?? null;
            if (\is_array($union)) {
                $responseModels = \array_map(
                    fn(array $unionSchema): string => $this->normalizeSchemaRef($unionSchema['$ref'] ?? ''),
                    $union
                );

                $responseDiscriminator = $this->parseUnionDiscriminator($schema, $union);

                // set to first model
                // for backward compatibility
                if ($responseModels !== []) {
                    $responseModel = $responseModels[0];
                }
            }
        }

        $output = [
            'method' => $methodName,
            'path' => $pathName,
            'fullPath' => parse_url($this->getEndpoint(), PHP_URL_PATH) . $pathName,
            'name' => $method['x-appwrite']['method'] ?? ($method['operationId'] ?? ''),
            'packaging' => $method['x-appwrite']['packaging'] ?? false,
            'title' => $method['summary'] ?? '',
            'description' => $method['description'] ?? '',
            'auth' => [$methodAuth] ?? [],
            'securityHeaders' => $methodSecurityHeaders,
            'securityQueries' => $methodSecurityQueries,
            'securityPathParams' => $methodSecurityPathParams,
            'consumes' => $consumes,
            'produces' => $produces,
            'cookies' => $method['x-appwrite']['cookies'] ?? false,
            'platforms' => $method['x-appwrite']['platforms'] ?? [],
            'consoleOnly' => $method['x-appwrite']['consoleOnly'] ?? false,
            'type' => $method['x-appwrite']['type'] ?? false,
            'deprecated' => $method['deprecated'] ?? false,
            'headers' => [],
            'parameters' => [
                'all' => [],
                'headers' => [],
                'path' => [],
                'query' => [],
                'body' => [],
            ],
            'emptyResponse' => $emptyResponse,
            'responseModel' => $responseModel,
            'responseModels' => $responseModels,
            'responseDiscriminator' => $responseDiscriminator,
        ];

        if ($method['x-appwrite']['deprecated'] ?? false) {
            $output['since'] = $method['x-appwrite']['deprecated']['since'] ?? '';
            $output['replaceWith'] = $method['x-appwrite']['deprecated']['replaceWith'] ?? '';
        }

        if ($output['type'] == 'graphql') {
            $output['headers']['x-sdk-graphql'] = 'true';
        }

        foreach ($consumes as $consume) {
            $output['headers']['content-type'] = $consume;
        }

        if (!empty($produces)) {
            $output['headers']['accept'] = implode(', ', $produces);
        }

        foreach ($method['parameters'] ?? [] as $parameter) {
            // Flatten the parameter schema into the parameter itself
            $parameter = [...$parameter, ...($parameter['schema'] ?? [])];

            $param = [
                'name' => $parameter['name'] ?? null,
                'type' => $parameter['type'] ?? null,
                'class' => $parameter['x-class'] ?? null,
                'description' => $parameter['description'] ?? '',
                'required' => $parameter['required'] ?? false,
                'nullable' => $parameter['x-nullable'] ?? false,
                'default' => $parameter['default'] ?? null,
                'example' => $parameter['x-example'] ?? null,
                'isUploadID' => $parameter['x-upload-id'] ?? false,
                'format' => $parameter['format'] ?? null,
                'array' => [
                    'type' => $parameter['items']['type'] ?? '',
                ],
            ];

            if ($param['type'] === 'object' && is_array($param['default'])) {
                $param['default'] = (empty($param['default'])) ? new stdClass() : $param['default'];
            }

            $param['default'] = (is_array($param['default']) || $param['default'] instanceof stdClass) ? json_encode($param['default']) : $param['default'];
            if (isset($parameter['enum'])) {
                $param['enumValues'] = $parameter['enum'];
                $param['enumName'] = $parameter['x-enum-name'] ?? $param['name'];
                $param['enumKeys'] = $parameter['x-enum-keys'] ?? [];
            } elseif (($param['type'] ?? null) === 'array' && isset($parameter['items']['enum'])) {
                $param['enumValues'] = $parameter['items']['enum'];
                $param['enumName'] = $parameter['items']['x-enum-name'] ?? $param['name'];
                $param['enumKeys'] = $parameter['items']['x-enum-keys'] ?? [];
            }

            switch ($parameter['in'] ?? '') {
                case 'header':
                    $output['parameters']['header'][] = $param;
                    break;
                case 'path':
                    if (isset($methodSecurityPathParams[$param['name']])) {
                        $param['source'] = 'security';
                        $param['security'] = $methodSecurityPathParams[$param['name']]['key'];
                        $param['config'] = $methodSecurityPathParams[$param['name']]['config'];
                        $output['parameters']['path'][] = $param;

                        continue 2;
                    }

                    $output['parameters']['path'][] = $param;
                    break;
                case 'query':
                    $output['parameters']['query'][] = $param;
                    break;
            }

            $output['parameters']['all'][] = $param;
        }

        $bodySchema = $requestBody['content'][$requestContentTypes[0] ?? '']['schema'] ?? [];
        $bodyProperties = $bodySchema['properties'] ?? [];
        $bodyRequired = $bodySchema['required'] ?? [];

        if (\in_array('multipart/form-data', $consumes, true)) {
            foreach ($bodyProperties as $key => $value) {
                $param = [
                    'name' => $key,
                    'type' => $value['type'] ?? null,
                    'class' => $value['x-class'] ?? null,
                    'description' => $value['description'] ?? '',
                    'required' => (in_array($key, $bodyRequired)),
                    // Multipart parameters carry no nullability information
                    'nullable' => false,
                    'default' => $value['default'] ?? null,
                    'example' => $value['x-example'] ?? null,
                    'isUploadID' => $value['x-upload-id'] ?? false,
                    'format' => $value['format'] ?? null,
                    'array' => [
                        'type' => $value['items']['type'] ?? '',
                    ],
                ];

                if (($param['type'] ?? '') === 'string' && ($param['format'] ?? '') === 'binary') {
                    $param['type'] = 'file';
                    $param['format'] = null;
                }

                if ($param['type'] === 'object' && is_array($param['default'])) {
                    $param['default'] = (empty($param['default'])) ? new stdClass() : $param['default'];
                }

                $param['default'] = (is_array($param['default']) || $param['default'] instanceof stdClass) ? json_encode($param['default']) : $param['default'];

                if (isset($value['enum'])) {
                    $param['enumValues'] = $value['enum'];
                    $param['enumName'] = $value['x-enum-name'] ?? $param['name'];
                    $param['enumKeys'] = $value['x-enum-keys'] ?? [];
                } elseif (($param['type'] ?? null) === 'array' && isset($value['items']['enum'])) {
                    $param['enumValues'] = $value['items']['enum'];
                    $param['enumName'] = $value['items']['x-enum-name'] ?? $param['name'];
                    $param['enumKeys'] = $value['items']['x-enum-keys'] ?? [];
                }

                $output['parameters']['body'][] = $param;
                $output['parameters']['all'][] = $param;
            }
        } else {
            foreach ($bodyProperties as $key => $value) {
                $bodyParam = [
                    'name' => $key,
                    'type' => $value['type'] ?? null,
                    'class' => null,
                    'description' => $value['description'] ?? '',
                    'required' => (in_array($key, $bodyRequired)),
                    'nullable' => $value['x-nullable'] ?? false,
                    'default' => null,
                    'example' => $value['x-example'] ?? null,
                    'isUploadID' => $value['x-upload-id'] ?? false,
                    'model' => $value['x-model'] ?? null,
                    'format' => $value['format'] ?? null,
                    'array' => [
                        'type' => $value['items']['type'] ?? '',
                        'model' => isset($value['items']['$ref']) ? $this->normalizeSchemaRef($value['items']['$ref']) : null,
                    ],
                ];

                $default = $value['default'] ?? null;
                if ($bodyParam['type'] === 'object' && is_array($default)) {
                    $default = ($default === []) ? new stdClass() : $default;
                }

                if (isset($value['enum'])) {
                    $bodyParam['enumValues'] = $value['enum'];
                    $bodyParam['enumName'] = $value['x-enum-name'] ?? $bodyParam['name'];
                    $bodyParam['enumKeys'] = $value['x-enum-keys'] ?? [];
                } elseif (($bodyParam['type'] ?? null) === 'array' && isset($value['items']['enum'])) {
                    $bodyParam['enumValues'] = $value['items']['enum'];
                    $bodyParam['enumName'] = $value['items']['x-enum-name'] ?? $bodyParam['name'];
                    $bodyParam['enumKeys'] = $value['items']['x-enum-keys'] ?? [];
                }

                $bodyParam['default'] = (\is_array($default) || $default instanceof stdClass) ? json_encode($default) : $default;

                $output['parameters']['body'][] = $bodyParam;
                $output['parameters']['all'][] = $bodyParam;
            }
        }

        usort($output['parameters']['all'], fn(array $a, array $b): int => (int) $b['required'] - (int) $a['required']);

        return $output;
    }

    /**
     * Get the schema of the first response content entry.
     */
    protected function getResponseSchema(array $response): array
    {
        if (isset($response['schema'])) {
            return $response['schema'];
        }

        foreach ($response['content'] ?? [] as $media) {
            if (isset($media['schema'])) {
                return $media['schema'];
            }
        }

        return [];
    }

    protected function normalizeSchemaRef(string $ref): string
    {
        return str_replace(
            ['#/components/schemas/', '#/definitions/'],
            '',
            $ref
        );
    }

    protected function parseUnionDiscriminator(array $schema, ?array $union = null): array
    {
        $discriminator = $schema['x-discriminator'] ?? $schema['discriminator'] ?? null;

        if (!\is_array($discriminator)) {
            return [];
        }

        $cases = [];
        $mapping = [];

        if (\is_array($discriminator['x-mapping'] ?? null)) {
            foreach ($discriminator['x-mapping'] as $ref => $conditions) {
                if (!\is_array($conditions)) {
                    continue;
                }

                $modelName = $this->normalizeSchemaRef((string) $ref);

                if ($modelName === '') {
                    continue;
                }

                $mapping[$modelName] = \array_filter(
                    $conditions,
                    fn($value): bool => $value !== null
                );
            }
        }

        if (
            $mapping === []
            && isset($discriminator['propertyName'], $discriminator['mapping'])
            && \is_array($discriminator['mapping'])
        ) {
            foreach ($discriminator['mapping'] as $value => $ref) {
                $modelName = $this->normalizeSchemaRef((string) $ref);

                if ($modelName === '') {
                    continue;
                }

                $mapping[$modelName] = [
                    $discriminator['propertyName'] => $value,
                ];
            }
        }

        if ($mapping === []) {
            return [];
        }

        foreach ($union ?? $schema['oneOf'] ?? $schema['x-oneOf'] ?? [] as $unionSchema) {
            $modelName = $this->normalizeSchemaRef($unionSchema['$ref'] ?? '');

            if ($modelName === '' || !isset($mapping[$modelName])) {
                continue;
            }

            $cases[$modelName] = $mapping[$modelName];
        }

        if ($cases === []) {
            $cases = $mapping;
        }

        uksort($cases, fn(string $left, string $right): int => \count($cases[$right]) <=> \count($cases[$left]));

        return $cases;
    }

    /**
     * @param string $service
     */
    #[Override]
    public function getMethods($service): array
    {
        $list = [];
        $paths = $this->getAttribute('paths', []);

        foreach ($paths as $pathName => $path) {
            foreach ($path as $methodName => $method) {
                $isCurrentService = isset($method['tags']) && is_array($method['tags']) && in_array($service, $method['tags']);

                if (!$isCurrentService) {
                    if (!empty($method['x-appwrite']['methods'] ?? [])) {
                        foreach ($method['x-appwrite']['methods'] as $additionalMethod) {
                            // has multiple namespaced methods!
                            $targetNamespace = $additionalMethod['namespace'] ?? null;

                            if ($targetNamespace === $service) {
                                $list[] = $this->handleAdditionalMethods($methodName, $pathName, $method, $additionalMethod);
                            }
                        }
                    }
                    continue;
                }

                if (empty($method['x-appwrite']['methods'] ?? [])) {
                    $list[] = $this->parseMethod($methodName, $pathName, $method);
                    continue;
                }

                foreach ($method['x-appwrite']['methods'] as $additionalMethod) {
                    $targetNamespace = $this->getTargetNamespace($additionalMethod, $service);

                    if ($targetNamespace === $service) {
                        $list[] = $this->handleAdditionalMethods($methodName, $pathName, $method, $additionalMethod);
                    }
                }
            }
        }

        return $list;
    }

    /**
     * @param $methodName
     * @param $pathName
     * @param $method
     * @param $additionalMethod
     */
    private function handleAdditionalMethods(string $methodName, string $pathName, array $method, array $additionalMethod): array
    {
        $duplicatedMethod = $method;
        $duplicatedMethod['x-appwrite']['method'] = $additionalMethod['name'];
        $duplicatedMethod['x-appwrite']['auth'] = $additionalMethod['auth'] ?? [];

        if (isset($additionalMethod['deprecated'])) {
            $duplicatedMethod['deprecated'] = $additionalMethod['deprecated'];
            $duplicatedMethod['x-appwrite']['deprecated'] = $additionalMethod['deprecated'];
        } else {
            // remove inherited deprecations!
            unset($duplicatedMethod['deprecated']);
            unset($duplicatedMethod['x-appwrite']['deprecated']);
        }

        // The replacement responses keep the content type the parent produces
        $produces = '';
        foreach ($method['responses'] ?? [] as $response) {
            foreach (\array_keys($response['content'] ?? []) as $contentType) {
                $produces = $contentType;
                break 2;
            }
        }

        // Update Response
        $responses = $additionalMethod['responses'];
        $convertedResponse = [];

        foreach ($responses as $desc) {
            $code = $desc['code'];
            if (isset($desc['model'])) {
                if (\is_array($desc['model'])) {
                    $convertedResponse[$code] = [
                        'content' => [
                            $produces => [
                                'schema' => [
                                    'oneOf' => \array_map(fn($model): array => [
                                        '$ref' => $model,
                                    ], $desc['model']),
                                ],
                            ],
                        ],
                    ];
                    continue;
                }

                $convertedResponse[$code] = [
                    'content' => [
                        $produces => [
                            'schema' => [
                                '$ref' => $desc['model'],
                            ],
                        ],
                    ],
                ];
            }
        }

        $duplicatedMethod['responses'] = $convertedResponse;

        // Remove non-whitelisted parameters on the request body, also set required.
        if (!empty($additionalMethod['parameters'])) {
            $whitelistedParams = $additionalMethod['parameters'];

            foreach ($duplicatedMethod['requestBody']['content'] ?? [] as $contentType => $media) {
                if (empty($media['schema']['properties'])) {
                    continue;
                }

                foreach ($media['schema']['properties'] as $paramName => $value) {
                    if (!in_array($paramName, $whitelistedParams)) {
                        unset($duplicatedMethod['requestBody']['content'][$contentType]['schema']['properties'][$paramName]);
                    }
                }

                $duplicatedMethod['requestBody']['content'][$contentType]['schema']['required'] = $additionalMethod['required'] ?? [];
            }
        }

        // Overwrite description and name if method has one
        if (!empty($additionalMethod['name'])) {
            $duplicatedMethod['summary'] = $additionalMethod['name'];
        }

        if (!empty($additionalMethod['description'])) {
            $duplicatedMethod['description'] = $additionalMethod['description'];
        }

        return $this->parseMethod($methodName, $pathName, $duplicatedMethod);
    }

    #[Override]
    public function getTargetNamespace(array $method, string $service): string
    {
        return $method['namespace'] ?? $service;
    }

    #[Override]
    public function getGlobalHeaders(): array
    {
        $list = [];

        $security = $this->getAttribute('components.securitySchemes', []);

        foreach ($security as $key => $definition) {
            if (isset($definition['in']) && $definition['in'] === 'header') {
                $list[] = [
                    'key' => $key,
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    // Project is stored on the client, but each method's security decides
                    // whether it is sent as X-Appwrite-Project or injected into the path.
                    'global' => $key !== 'Project',
                ];
            } elseif (
                isset($definition['type']) && $definition['type'] === 'http' &&
                      isset($definition['scheme']) && $definition['scheme'] === 'bearer'
            ) {
                $list[] = [
                    'key' => $key,
                    'name' => 'Authorization',
                    'description' => $definition['description'] ?? 'Bearer token authentication',
                    'type' => 'bearer',
                    'global' => true,
                ];
            }
        }

        return $list;
    }

    /**
     * Normalize a schema property: nullability and union keywords use the
     * x- prefixed representation the templates consume.
     */
    protected function normalizeSchemaProperty(array $property): array
    {
        if (isset($property['nullable'])) {
            $property['x-nullable'] = $property['nullable'];
            unset($property['nullable']);
        }

        // A union on an object property is wrapped in allOf
        if (isset($property['allOf']) && \count($property['allOf']) === 1 && isset($property['allOf'][0]['oneOf'])) {
            $union = $property['allOf'][0];
            unset($property['allOf']);

            $property['oneOf'] = $union['oneOf'];

            if (isset($union['discriminator'])) {
                $property['discriminator'] = $union['discriminator'];
            }
        }

        foreach (['oneOf' => 'x-oneOf', 'anyOf' => 'x-anyOf', 'discriminator' => 'x-discriminator'] as $keyword => $target) {
            if (isset($property[$keyword])) {
                $property[$target] = $property[$keyword];
                unset($property[$keyword]);
            }

            if (isset($property['items'][$keyword])) {
                $property['items'][$target] = $property['items'][$keyword];
                unset($property['items'][$keyword]);
            }
        }

        return $property;
    }

    /**
     * Parse a components schema into a model definition.
     */
    protected function parseModel(string $key, array $schema): array
    {
        $model = [
            'name' => $key,
            'properties' => $schema['properties'] ?? [],
            'description' => $schema['description'] ?? '',
            'required' => $schema['required'] ?? [],
            'additionalProperties' => $schema['additionalProperties'] ?? [],
        ];

        foreach ($model['properties'] as $name => $def) {
            $def = $this->normalizeSchemaProperty($def);

            $def['name'] = $name;
            $def['description'] ??= '';
            $def['example'] = $def['x-example'] ?? null;
            $def['required'] = in_array($name, $model['required']);

            if (isset($def['$ref'])) {
                $def['sub_schema'] = $this->normalizeSchemaRef($def['$ref']);
            }

            foreach ($def['allOf'] ?? [] as $nested) {
                //nested model reference merged with sibling keywords
                if (isset($nested['$ref'])) {
                    $def['sub_schema'] = $this->normalizeSchemaRef($nested['$ref']);
                    break;
                }
            }

            if (isset($def['items']['$ref'])) {
                //nested model
                $def['sub_schema'] = $this->normalizeSchemaRef($def['items']['$ref']);
            }

            if (isset($def['items']['x-anyOf'])) {
                //nested model
                $def['sub_schemas'] = \array_map(fn(array $schema): string => $this->normalizeSchemaRef($schema['$ref']), $def['items']['x-anyOf']);
            }

            if (isset($def['items']['x-oneOf'])) {
                //nested model
                $def['sub_schemas'] = \array_map(fn(array $schema): string => $this->normalizeSchemaRef($schema['$ref']), $def['items']['x-oneOf']);
            }

            if (isset($def['x-anyOf'])) {
                //nested model union on an object property
                $def['sub_schemas'] = \array_map(fn(array $schema): string => $this->normalizeSchemaRef($schema['$ref']), $def['x-anyOf']);
            }

            if (isset($def['x-oneOf'])) {
                //nested model union on an object property
                $def['sub_schemas'] = \array_map(fn(array $schema): string => $this->normalizeSchemaRef($schema['$ref']), $def['x-oneOf']);
            }

            if (isset($def['enum'])) {
                // enum property
                $def['enumName'] = $def['x-enum-name'] ?? ucfirst($key) . ucfirst((string) $name);
                $def['enumKeys'] = $def['x-enum-keys'] ?? [];
            } elseif (($def['type'] ?? null) === 'array' && isset($def['items']['enum'])) {
                $def['enumValues'] = $def['items']['enum'];
                $def['enumName'] = $def['items']['x-enum-name'] ?? ucfirst($key) . ucfirst((string) $name);
                $def['enumKeys'] = $def['items']['x-enum-keys'] ?? [];
            }

            $model['properties'][$name] = $def;
        }

        return $model;
    }

    #[Override]
    public function getDefinitions(): array
    {
        $list = [];
        foreach ($this->getAttribute('components.schemas', []) as $key => $schema) {
            if ($key == 'any') {
                continue;
            }
            if ($schema['x-request-model'] ?? false) {
                continue;
            }
            $list[$key] = $this->parseModel($key, $schema);
        }
        return $list;
    }

    /**
     * Get request model definitions
     */
    #[Override]
    public function getRequestModels(): array
    {
        $list = [];
        foreach ($this->getAttribute('components.schemas', []) as $key => $schema) {
            if (!($schema['x-request-model'] ?? false)) {
                continue;
            }
            $list[$key] = $this->parseModel($key, $schema);
        }
        return $list;
    }

    #[Override]
    public function getRequestEnums(): array
    {
        $list = [];

        foreach (array_keys($this->getServices()) as $key) {
            foreach ($this->getMethods($key) as $method) {
                if (isset($method['parameters']) && is_array($method['parameters'])) {
                    foreach ($method['parameters']['all'] as $parameter) {
                        $enumName = $parameter['enumName'] ?? $parameter['name'];

                        if (isset($parameter['enumValues']) && !\in_array($enumName, $list)) {
                            $list[$enumName] = [
                                'name' => $enumName,
                                'enum' => $parameter['enumValues'],
                                'keys' => $parameter['enumKeys'],
                            ];
                        }
                    }
                }
            }
        }

        return \array_values($list);
    }

    #[Override]
    public function getResponseEnums(): array
    {
        $list = [];
        $definitions = $this->getDefinitions();

        foreach ($definitions as $modelName => $model) {
            if (isset($model['properties']) && is_array($model['properties'])) {
                foreach ($model['properties'] as $propertyName => $property) {
                    if (isset($property['enum'])) {
                        $enumName = $property['x-enum-name'] ?? ucfirst((string) $modelName) . ucfirst((string) $propertyName);

                        if (!isset($list[$enumName])) {
                            $list[$enumName] = [
                                'name' => $enumName,
                                'enum' => $property['enum'],
                                'keys' => $property['x-enum-keys'] ?? [],
                            ];
                        }
                    }

                    // array of enums
                    if ((($property['type'] ?? null) === 'array') && isset($property['items']['enum'])) {
                        $enumName = $property['items']['x-enum-name']
                            ?? $property['enumName']
                            ?? ucfirst((string) $modelName) . ucfirst((string) $propertyName);

                        if (!isset($list[$enumName])) {
                            $list[$enumName] = [
                                'name' => $enumName,
                                'enum' => $property['items']['enum'],
                                'keys' => $property['items']['x-enum-keys'] ?? [],
                            ];
                        }
                    }
                }
            }
        }

        return \array_values($list);
    }

    #[Override]
    public function getRequestModelEnums(): array
    {
        $list = [];

        foreach ($this->getRequestModels() as $modelName => $model) {
            if (!isset($model['properties']) || !is_array($model['properties'])) {
                continue;
            }

            foreach ($model['properties'] as $propertyName => $property) {
                if (isset($property['enum'])) {
                    $enumName = $property['enumName'] ?? ucfirst((string) $modelName) . ucfirst((string) $propertyName);

                    if (!isset($list[$enumName])) {
                        $list[$enumName] = [
                            'name' => $enumName,
                            'enum' => $property['enum'],
                            'keys' => $property['enumKeys'] ?? [],
                        ];
                    }
                }

                if ((($property['type'] ?? null) === 'array') && isset($property['enumValues'])) {
                    $enumName = $property['enumName'] ?? ucfirst((string) $modelName) . ucfirst((string) $propertyName);

                    if (!isset($list[$enumName])) {
                        $list[$enumName] = [
                            'name' => $enumName,
                            'enum' => $property['enumValues'],
                            'keys' => $property['enumKeys'] ?? [],
                        ];
                    }
                }
            }
        }

        return \array_values($list);
    }

    #[Override]
    public function getAllEnums(): array
    {
        $list = [];
        foreach ($this->getRequestEnums() as $enum) {
            $list[$enum['name']] = $enum;
        }
        foreach ($this->getRequestModelEnums() as $enum) {
            $list[$enum['name']] = $enum;
        }
        foreach ($this->getResponseEnums() as $enum) {
            $list[$enum['name']] = $enum;
        }

        return \array_values($list);
    }
}
