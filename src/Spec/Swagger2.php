<?php

namespace Appwrite\Spec;

use stdClass;

class Swagger2 extends Spec
{
    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getAttribute('info.title', '');
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getAttribute('info.description', '');
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->getAttribute('info.namespace', '');
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->getAttribute('info.version', '');
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->getAttribute('schemes.0', 'https') .
        '://' . $this->getAttribute('host', 'example.com') .
        $this->getAttribute('basePath', '');
    }

    /**
     * @return string
     */
    public function getEndpointDocs(): string
    {
        return $this->getAttribute('schemes.0', 'https') .
        '://' . $this->getAttribute('x-host-docs', 'example.com') .
        $this->getAttribute('basePath', '');
    }

    /**
     * @return string
     */
    public function getLicenseName(): string
    {
        return $this->getAttribute('info.license.name', '');
    }

    /**
     * @return string
     */
    public function getLicenseURL(): string
    {
        return $this->getAttribute('info.license.url', '');
    }

    /**
     * @return string
     */
    public function getContactName(): string
    {
        return $this->getAttribute('info.contact.name', '');
    }

    /**
     * @return string
     */
    public function getContactURL(): string
    {
        return $this->getAttribute('info.contact.url', '');
    }

    /**
     * @return string
     */
    public function getContactEmail(): string
    {
        return $this->getAttribute('info.contact.email', '');
    }

    /**
     * @return array
     */
    public function getServices(): array
    {
        $list = [];

        $paths = $this->getAttribute('paths', []);
        $tags = $this->getAttribute('tags', []);

        foreach ($paths as $path) {
            foreach ($path as $method) {
                if (isset($method['tags'])) {
                    foreach ($method['tags'] as $tag) {
                        if (!array_key_exists($tag, $list)) {
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
        $security = $this->getAttribute('securityDefinitions', []);
        $methodAuth = $method['x-appwrite']['auth'] ?? [];
        $methodSecurity = $method['security'][0] ?? [];

        foreach ($methodAuth as $i => $node) {
            $methodAuth[$i] = (array_key_exists($i, $security)) ? $security[$i] : [];
        }
        foreach ($methodSecurity as $i => $node) {
            $methodSecurity[$i] = (array_key_exists($i, $security)) ? $security[$i] : [];
        }

        $responseModel = '';
        $responseModels = [];
        $responses = $method['responses'];
        $emptyResponse = true;
        foreach ($responses as $code => $desc) {
            if ($code != '204') {
                $emptyResponse = false;
            }
            // Check for single model reference
            if (isset($desc['schema']) && isset($desc['schema']['$ref'])) {
                $responseModel = $desc['schema']['$ref'];
                if (!empty($responseModel)) {
                    $responseModel = str_replace('#/definitions/', '', $responseModel);
                }
            }

            // check for union types
            if (isset($desc['schema']['x-oneOf'])) {
                $responseModels = \array_map(
                    fn($schema) => str_replace('#/definitions/', '', $schema['$ref']),
                    $desc['schema']['x-oneOf']
                );

                // set to first model
                // for backward compatibility
                if (!empty($responseModels)) {
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
            'security' => [$methodSecurity] ?? [],
            'consumes' => $method['consumes'] ?? [],
            'cookies' => $method['x-appwrite']['cookies'] ?? false,
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
        ];

        if ($method['x-appwrite']['deprecated'] ?? false) {
            $output['since'] = $method['x-appwrite']['deprecated']['since'] ?? '';
            $output['replaceWith'] = $method['x-appwrite']['deprecated']['replaceWith'] ?? '';
        }

        if ($output['type'] == 'graphql') {
            $output['headers']['x-sdk-graphql'] = 'true';
        }

        if (isset($method['consumes']) && is_array($method['consumes'])) {
            foreach ($method['consumes'] as $consume) {
                $output['headers']['content-type'] = $consume;
            }
        }

        $method['parameters'] = (isset($method['parameters']) && is_array($method['parameters'])) ? $method['parameters'] : [];

        foreach ($method['parameters'] as $parameter) {
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
                $param['enumKeys'] = $parameter['x-enum-keys'];
            }

            switch ($parameter['in']) {
                case 'header':
                    $output['parameters']['header'][] = $param;
                    break;
                case 'path':
                    $output['parameters']['path'][] = $param;
                    break;
                case 'query':
                    $output['parameters']['query'][] = $param;
                    break;
                case 'formData':
                    $output['parameters']['body'][] = $param;
                    break;
                case 'body':
                    $bodyProperties = $parameter['schema']['properties'] ?? [];
                    $bodyRequired = $parameter['schema']['required'] ?? [];

                    foreach ($bodyProperties as $key => $value) {
                        $temp = $param;
                        $temp['name'] = $key;
                        $temp['type'] = $value['type'] ?? null;
                        $temp['description'] = $value['description'] ?? '';
                        $temp['required'] = (in_array($key, $bodyRequired));
                        $temp['example'] = $value['x-example'] ?? null;
                        $temp['isUploadID'] = $value['x-upload-id'] ?? false;
                        $temp['nullable'] = $value['x-nullable'] ?? false;
                        $temp['model'] = $value['x-model'] ?? null;
                        $temp['array'] = [
                            'type' => $value['items']['type'] ?? '',
                            'model' => isset($value['items']['$ref']) ? str_replace('#/definitions/', '', $value['items']['$ref']) : null,
                        ];
                        if ($value['type'] === 'object' && is_array($value['default'])) {
                            $value['default'] = (empty($value['default'])) ? new stdClass() : $value['default'];
                        }

                        if (isset($value['enum'])) {
                            $temp['enumValues'] = $value['enum'];
                            $temp['enumName'] = $value['x-enum-name'] ?? $temp['name'];
                            $temp['enumKeys'] = $value['x-enum-keys'];
                        }

                        $temp['default'] = (is_array($value['default']) || $value['default'] instanceof stdClass) ? json_encode($value['default']) : $value['default'];

                        $output['parameters']['body'][] = $temp;
                        $output['parameters']['all'][] = $temp;
                    }

                    continue 2;
            }

            $output['parameters']['all'][] = $param;
        }

        usort($output['parameters']['all'], function ($a, $b) {
            return $b['required'] - $a['required'];
        });

        return $output;
    }

    /**
     * @param string $service
     * @return array
     */
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
     * @return array
     */
    private function handleAdditionalMethods($methodName, $pathName, $method, $additionalMethod): array
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

        // Update Response
        $responses = $additionalMethod['responses'];
        $convertedResponse = [];

        foreach ($responses as $desc) {
            $code = $desc['code'];
            if (isset($desc['model'])) {
                if (\is_array($desc['model'])) {
                    $convertedResponse[$code] = [
                        'schema' => [
                            'x-oneOf' => \array_map(fn($model) => [
                                '$ref' => $model,
                            ], $desc['model']),
                        ],
                    ];
                    continue;
                }

                $convertedResponse[$code] = [
                    'schema' => [
                        '$ref' => $desc['model'],
                    ],
                ];
            }
        }

        $duplicatedMethod['responses'] = $convertedResponse;

        // Remove non-whitelisted parameters on body parameters, also set required.
        $handleParams = function (&$params) use ($additionalMethod) {
            if (!empty($additionalMethod['parameters'])) {
                foreach ($params as $key => $param) {
                    if (empty($param['in']) || $param['in'] !== 'body' || empty($param['schema']['properties'])) {
                        continue;
                    }

                    $whitelistedParams = $additionalMethod['parameters'];

                    foreach ($param['schema']['properties'] as $paramName => $value) {
                        if (!in_array($paramName, $whitelistedParams)) {
                            unset($param['schema']['properties'][$paramName]);
                        }
                    }

                    $param['schema']['required'] = $additionalMethod['required'] ?? [];
                    $params[$key] = $param;
                }
            }
        };

        $handleParams($duplicatedMethod['parameters']);

        // Overwrite description and name if method has one
        if (!empty($additionalMethod['name'])) {
            $duplicatedMethod['summary'] = $additionalMethod['name'];
        }

        if (!empty($additionalMethod['description'])) {
            $duplicatedMethod['description'] = $additionalMethod['description'];
        }

        return $this->parseMethod($methodName, $pathName, $duplicatedMethod);
    }

    /**
     * @param array $method
     * @param string $service
     * @return string
     */
    public function getTargetNamespace(array $method, string $service): string
    {
        return $method['namespace'] ?? $service;
    }

    /**
     * @return array
     */
    public function getGlobalHeaders(): array
    {
        $list = [];

        $security = $this->getAttribute('securityDefinitions', []);

        foreach ($security as $key => $definition) {
            if (isset($definition['in']) && $definition['in'] === 'header') {
                $list[] = [
                    'key' => $key,
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                ];
            }
        }

        return $list;
    }

    public function getDefinitions(): array
    {
        $list = [];
        $definition = $this->getAttribute('definitions', []);
        foreach ($definition as $key => $schema) {
            if ($key == 'any') {
                continue;
            }
            if ($schema['x-request-model'] ?? false) {
                continue;
            }
            $model = [
                'name' => $key,
                'properties' => $schema['properties'] ?? [],
                'description' => $schema['description'] ?? '',
                'required' => $schema['required'] ?? [],
                'additionalProperties' => $schema['additionalProperties'] ?? []
            ];
            if (isset($model['properties'])) {
                foreach ($model['properties'] as $name => $def) {
                    $model['properties'][$name]['name'] = $name;
                    $model['properties'][$name]['description'] = $def['description'] ?? '';
                    $model['properties'][$name]['example'] = $def['x-example'] ?? null;
                    $model['properties'][$name]['required'] =  in_array($name, $model['required']);
                    if (isset($def['items']['$ref'])) {
                        //nested model
                        $model['properties'][$name]['sub_schema'] = str_replace('#/definitions/', '', $def['items']['$ref']);
                    }

                    if (isset($def['items']['x-anyOf'])) {
                        //nested model
                        $model['properties'][$name]['sub_schemas'] = \array_map(fn($schema) => str_replace('#/definitions/', '', $schema['$ref']), $def['items']['x-anyOf']);
                    }

                    if (isset($def['items']['x-oneOf'])) {
                        //nested model
                        $model['properties'][$name]['sub_schemas'] = \array_map(fn($schema) => str_replace('#/definitions/', '', $schema['$ref']), $def['items']['x-oneOf']);
                    }

                    if (isset($def['enum'])) {
                        // enum property
                        $model['properties'][$name]['enum'] = $def['enum'];
                        $model['properties'][$name]['enumName'] = $def['x-enum-name'] ?? ucfirst($key) . ucfirst($name);
                        $model['properties'][$name]['enumKeys'] = $def['x-enum-keys'] ?? [];
                    }
                }
            }
            $list[$key] = $model;
        }
        return $list;
    }

    /**
     * Get request model definitions
     *
     * @return array
     */
    public function getRequestModels(): array
    {
        $list = [];
        $definition = $this->getAttribute('definitions', []);
        foreach ($definition as $key => $schema) {
            if (!($schema['x-request-model'] ?? false)) {
                continue;
            }
            $model = [
                'name' => $key,
                'properties' => $schema['properties'] ?? [],
                'description' => $schema['description'] ?? '',
                'required' => $schema['required'] ?? [],
                'additionalProperties' => $schema['additionalProperties'] ?? []
            ];
            if (isset($model['properties'])) {
                foreach ($model['properties'] as $name => $def) {
                    $model['properties'][$name]['name'] = $name;
                    $model['properties'][$name]['description'] = $def['description'] ?? '';
                    $model['properties'][$name]['example'] = $def['x-example'] ?? null;
                    $model['properties'][$name]['required'] = in_array($name, $model['required']);
                    if (isset($def['items']['$ref'])) {
                        $model['properties'][$name]['sub_schema'] = str_replace('#/definitions/', '', $def['items']['$ref']);
                    }

                    if (isset($def['items']['x-anyOf'])) {
                        $model['properties'][$name]['sub_schemas'] = \array_map(fn($schema) => str_replace('#/definitions/', '', $schema['$ref']), $def['items']['x-anyOf']);
                    }

                    if (isset($def['items']['x-oneOf'])) {
                        $model['properties'][$name]['sub_schemas'] = \array_map(fn($schema) => str_replace('#/definitions/', '', $schema['$ref']), $def['items']['x-oneOf']);
                    }

                    if (isset($def['enum'])) {
                        $model['properties'][$name]['enum'] = $def['enum'];
                        $model['properties'][$name]['enumName'] = $def['x-enum-name'] ?? ucfirst($key) . ucfirst($name);
                        $model['properties'][$name]['enumKeys'] = $def['x-enum-keys'] ?? [];
                    }
                }
            }
            $list[$key] = $model;
        }
        return $list;
    }

    /**
     * @return array
     */
    public function getRequestEnums(): array
    {
        $list = [];

        foreach ($this->getServices() as $key => $service) {
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

    /**
     * @return array
     */
    public function getResponseEnums(): array
    {
        $list = [];
        $definitions = $this->getDefinitions();

        foreach ($definitions as $modelName => $model) {
            if (isset($model['properties']) && is_array($model['properties'])) {
                foreach ($model['properties'] as $propertyName => $property) {
                    if (isset($property['enum'])) {
                        $enumName = $property['x-enum-name'] ?? ucfirst($modelName) . ucfirst($propertyName);

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
                        $enumName = $property['x-enum-name'] ?? ucfirst($modelName) . ucfirst($propertyName);

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

    /**
     * @return array
     */
    public function getAllEnums(): array
    {
        $list = [];
        foreach ($this->getRequestEnums() as $enum) {
            $list[$enum['name']] = $enum;
        }
        foreach ($this->getResponseEnums() as $enum) {
            $list[$enum['name']] = $enum;
        }

        return \array_values($list);
    }
}
