<?php

namespace Appwrite\Spec;

use stdClass;

class Swagger2 extends Spec
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getAttribute('info.title', '');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getAttribute('info.description', '');
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->getAttribute('info.namespace', '');
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->getAttribute('info.version', '');
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->getAttribute('schemes.0', 'https') .
        '://' . $this->getAttribute('host', 'example.com') .
        $this->getAttribute('basePath', '');
    }

    /**
     * @return string
     */
    public function getLicenseName()
    {
        return $this->getAttribute('info.license.name', '');
    }

    /**
     * @return string
     */
    public function getLicenseURL()
    {
        return $this->getAttribute('info.license.url', '');
    }

    /**
     * @return string
     */
    public function getContactName()
    {
        return $this->getAttribute('info.contact.name', '');
    }

    /**
     * @return string
     */
    public function getContactURL()
    {
        return $this->getAttribute('info.contact.url', '');
    }

    /**
     * @return string
     */
    public function getContactEmail()
    {
        return $this->getAttribute('info.contact.email', '');
    }

    /**
     * @return array
     */
    public function getServices()
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

        $responses = $method['responses'];
        $responseModel = '';
        $emptyResponse = true;
        foreach ($responses as $code => $desc) {
            if ($code != '204') {
                $emptyResponse = false;
            }
            if (isset($desc['schema']) && isset($desc['schema']['$ref'])) {
                $responseModel = $desc['schema']['$ref'];
                if (!empty($responseModel)) {
                    $responseModel = str_replace('#/definitions/', '', $responseModel);
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
        ];

        if ($output['type'] == 'graphql') {
            $output['headers']['x-sdk-graphql'] = "true";
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
                $param['enumName'] = $parameter['x-enum-name'];
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
                        $temp['array'] = [
                            'type' => $value['items']['type'] ?? '',
                        ];
                        if ($value['type'] === 'object' && is_array($value['default'])) {
                            $value['default'] = (empty($value['default'])) ? new stdClass() : $value['default'];
                        }

                        if (isset($value['enum'])) {
                            $temp['enumValues'] = $value['enum'];
                            $temp['enumName'] = $value['x-enum-name'];
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
    public function getMethods($service)
    {
        $list = [];
        $paths = $this->getAttribute('paths', []);

        foreach ($paths as $pathName => $path) {
            foreach ($path as $methodName => $method) {
                if (!(isset($method['tags']) && is_array($method['tags']) && in_array($service, $method['tags']))) {
                    continue;
                }

                if (empty($method['x-appwrite']['methods'] ?? [])) {
                    $list[] = $this->parseMethod($methodName, $pathName, $method);
                    continue;
                }

                foreach ($method['x-appwrite']['methods'] as $additionalMethod) {
                    $duplicatedMethod = $method;
                    $duplicatedMethod['x-appwrite']['method'] = $additionalMethod['name'];

                    // Update Response
                    $responses = $duplicatedMethod['responses'];
                    $convertedResponse = [];

                    foreach ($responses as $code => $desc) {
                        if (!isset($desc['schema']) || !isset($desc['schema']['x-oneOf'])) {
                            continue;
                        }

                        foreach ($desc['schema']['x-oneOf'] as $oneOf) {
                            if (!isset($oneOf['$ref']) || !str_ends_with($oneOf['$ref'], $additionalMethod['response'])) {
                                continue;
                            }

                            $convertedResponse[$code] = [
                                'description' => $desc['description'],
                                'schema' => $oneOf,
                            ];
                        }
                    }

                    $duplicatedMethod['responses'] = $convertedResponse;

                    // Remove non-whitelisted parameters on body parameters, also set required.
                    $handleParams = function (&$params) use ($additionalMethod) {
                        if (isset($additionalMethod['parameters'])) {
                            foreach ($params as $key => $param) {
                                if (empty($param['in']) || $param['in'] !== 'body' || empty($param['schema']['properties'])) {
                                    continue;
                                }

                                $whitelistedParams = $additionalMethod['parameters'] ?? [];

                                foreach ($param['schema']['properties'] as $paramName => $value) {
                                    if (!in_array($paramName, $whitelistedParams)) {
                                        unset($param['schema']['properties'][$paramName]);
                                    }
                                }

                                $param['schema']['required'] = $additionalMethod['required'] ?? [];
                                $params[$key] = $param;
                            }
                        }

                        return;
                    };

                    $handleParams($duplicatedMethod['parameters']);

                    // Overwrite description and name if method has one
                    if (!empty($additionalMethod['name'])) {
                        $duplicatedMethod['summary'] = $additionalMethod['name'];
                    }

                    if (!empty($additionalMethod['description'])) {
                        $duplicatedMethod['description'] = $additionalMethod['description'];
                    }

                    $list[] = $this->parseMethod($methodName, $pathName, $duplicatedMethod);
                }
            }
        }

        return $list;
    }

    /**
     * @return array
     */
    public function getGlobalHeaders()
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

    public function getDefinitions()
    {
        $list = [];
        $definition = $this->getAttribute('definitions', []);
        foreach ($definition as $key => $schema) {
            if ($key == 'any') {
                continue;
            }
            $sch = [
                "name" => $key,
                "properties" => $schema['properties'] ?? [],
                "description" => $schema['description'] ?? [],
                "required" => $schema['required'] ?? [],
                "additionalProperties" => $schema['additionalProperties'] ?? []
            ];
            if (isset($sch['properties'])) {
                foreach ($sch['properties'] as $name => $def) {
                    $sch['properties'][$name]['name'] = $name;
                    $sch['properties'][$name]['description'] = $def['description'];
                    $sch['properties'][$name]['example'] = $def['x-example'];
                    $sch['properties'][$name]['required'] =  in_array($name, $sch['required']);
                    if (isset($def['items']['$ref'])) {
                        //nested model
                        $sch['properties'][$name]['sub_schema'] = str_replace('#/definitions/', '', $def['items']['$ref']);
                    }

                    if (isset($def['items']['x-anyOf'])) {
                        //nested model
                        $sch['properties'][$name]['sub_schemas'] = \array_map(fn($schema) => str_replace('#/definitions/', '', $schema['$ref']), $def['items']['x-anyOf']);
                    }

                    if (isset($def['items']['x-oneOf'])) {
                        //nested model
                        $sch['properties'][$name]['sub_schemas'] = \array_map(fn($schema) => str_replace('#/definitions/', '', $schema['$ref']), $def['items']['x-oneOf']);
                    }
                }
            }
            $list[$key] = $sch;
        }
        return $list;
    }

    /**
     * @return array
     */
    public function getEnums(): array
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
}
