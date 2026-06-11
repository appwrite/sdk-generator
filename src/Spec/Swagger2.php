<?php

namespace Appwrite\Spec;

use Exception;

/**
 * Swagger 2 (legacy) spec parser.
 *
 * Converts the Swagger 2 document into its OpenAPI 3 equivalent at
 * construction time, then inherits the native OpenAPI 3 parsing pipeline.
 * This guarantees both formats produce an identical internal representation
 * (and therefore identical generated SDKs).
 */
class Swagger2 extends OpenAPI3
{
    /**
     * @param string $input
     * @throws Exception
     */
    public function __construct($input)
    {
        parent::__construct($input);

        $this->convertDocument();
    }

    /**
     * Convert the Swagger 2 document in-place to its OpenAPI 3 equivalent.
     */
    private function convertDocument(): void
    {
        if (isset($this['openapi'])) {
            return; // already an OpenAPI 3 document
        }

        $scheme = $this->getAttribute('schemes.0', 'https');
        $basePath = $this->getAttribute('basePath', '');

        $servers = [
            ['url' => $scheme . '://' . $this->getAttribute('host', 'example.com') . $basePath],
        ];

        $docsHost = $this->getAttribute('x-host-docs', '');
        if ($docsHost !== '') {
            $servers[] = ['url' => $scheme . '://' . \str_replace('<REGION>', '{region}', $docsHost) . $basePath];
        }

        $this['servers'] = $servers;

        $this['components'] = [
            'schemas' => $this->getAttribute('definitions', []),
            'securitySchemes' => $this->getAttribute('securityDefinitions', []),
        ];

        $paths = $this->getAttribute('paths', []);
        foreach ($paths as $pathName => $path) {
            foreach ($path as $verb => $method) {
                if (!\is_array($method)) {
                    continue;
                }

                $paths[$pathName][$verb] = $this->convertMethod($method);
            }
        }
        $this['paths'] = $paths;

        $this->exchangeArray($this->convertRefs($this->getArrayCopy()));
    }

    /**
     * Recursively rewrite definition references to component schema references,
     * in both array values and array keys (discriminator mappings use refs as keys).
     */
    private function convertRefs(mixed $node): mixed
    {
        if (\is_string($node)) {
            return \str_replace('#/definitions/', '#/components/schemas/', $node);
        }

        if (!\is_array($node)) {
            return $node;
        }

        $converted = [];
        foreach ($node as $key => $value) {
            if (\is_string($key) && \str_starts_with($key, '#/definitions/')) {
                $key = \str_replace('#/definitions/', '#/components/schemas/', $key);
            }

            $converted[$key] = $this->convertRefs($value);
        }

        return $converted;
    }

    /**
     * Convert an operation object: response schemas move into response content
     * keyed by the produced content types, and body/formData parameters become
     * the request body.
     */
    private function convertMethod(array $method): array
    {
        $consumes = $method['consumes'] ?? [];
        $produces = $method['produces'] ?? [];

        $hasContent = false;
        foreach ($method['responses'] ?? [] as $code => $response) {
            if (!isset($response['schema'])) {
                continue;
            }

            $schema = $response['schema'];
            unset($response['schema']);

            // A response without a produced content type still carries its
            // schema, under an empty content type
            foreach (($produces !== [] ? $produces : ['']) as $contentType) {
                $response['content'][$contentType] = ['schema' => $schema];
            }

            $method['responses'][$code] = $response;

            $hasContent = $hasContent || $produces !== [];
        }

        if (!$hasContent && $produces !== []) {
            // No response declares content (e.g. 204 No content); keep the
            // produced content type available in the x-appwrite metadata.
            $method['x-appwrite']['produces'] = $produces;
        }

        $parameters = [];
        $body = null;
        $formData = [];

        foreach ($method['parameters'] ?? [] as $parameter) {
            switch ($parameter['in'] ?? '') {
                case 'body':
                    $body = $parameter;
                    break;
                case 'formData':
                    $formData[] = $parameter;
                    break;
                default:
                    $parameters[] = $parameter;
            }
        }

        if ($body !== null) {
            $method['requestBody'] = [
                'content' => [
                    ($consumes[0] ?? static::DEFAULT_CONTENT_TYPE) => [
                        'schema' => $body['schema'] ?? [],
                    ],
                ],
            ];
        } elseif ($formData !== []) {
            $properties = [];
            $required = [];

            foreach ($formData as $parameter) {
                $name = $parameter['name'];

                if ($parameter['required'] ?? false) {
                    $required[] = $name;
                }

                unset($parameter['name'], $parameter['in'], $parameter['required'], $parameter['collectionFormat']);

                if (($parameter['type'] ?? '') === 'file') {
                    $parameter['type'] = 'string';
                    $parameter['format'] = 'binary';
                }

                $properties[$name] = $parameter;
            }

            $schema = [
                'type' => 'object',
                'properties' => $properties,
            ];

            if ($required !== []) {
                $schema['required'] = $required;
            }

            $method['requestBody'] = [
                'content' => [
                    'multipart/form-data' => [
                        'schema' => $schema,
                    ],
                ],
            ];
        }

        $method['parameters'] = $parameters;
        unset($method['consumes'], $method['produces']);

        return $method;
    }
}
