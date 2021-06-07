<?php

namespace Appwrite\Spec;

use stdClass;

class Swagger2 extends Spec {

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
                if(isset($method['tags'])) {
                    foreach ($method['tags'] as $tag) {
                        if(!array_key_exists($tag, $list)) {
                            $list[$tag] = [
                                'name' => $tag,
                                'methods' => $this->getMethods($tag),
                            ];
                        }
                    }
                }
            }
        }

        foreach($tags as $tag) { // Fetch descriptions
            $name = $tag['name'];

            if(isset($list[$name])) {
                $list[$name]['description'] = $tag['description'] ?? '';
            }
        }

        return $list;
    }

    /**
     * @param string $service
     * @return array
     */
    public function getMethods($service)
    {
        $list = [];

        $security = $this->getAttribute('securityDefinitions', []);
        $paths = $this->getAttribute('paths', []);

        foreach ($paths as $pathName => $path) {
            foreach ($path as $methodName => $method) {
                $auth = $method['x-appwrite']['auth'] ?? [];

                if(isset($method['tags']) && is_array($method['tags']) && in_array($service, $method['tags'])) {
                    if(isset($auth) && is_array($auth)) {
                        foreach($auth as $i => $node) {
                            $auth[$i] = (array_key_exists($i, $security)) ? $security[$i] : [];
                        }
                    }

                    $output = [
                        'method' => $methodName,
                        'path' => $pathName,
                        'fullPath' => parse_url($this->getEndpoint(), PHP_URL_PATH).$pathName,
                        'name' => $method['x-appwrite']['method'] ?? ($method['operationId'] ?? ''),
                        'packaging' => $method['x-appwrite']['packaging'] ?? false,
                        'title' => $method['summary'] ?? '',
                        'description' => $method['description'] ?? '',
                        'security' => [$auth] ?? [],
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
                        ]
                    ];

                    if(isset($method['consumes']) && is_array($method['consumes'])) {
                        foreach ($method['consumes'] as $consume) {
                            $output['headers']['content-type'] = $consume;
                        }
                    }

                    $method['parameters'] = (isset($method['parameters']) && is_array($method['parameters'])) ? $method['parameters'] : [];

                    foreach ($method['parameters'] as $parameter) {
                        $param = [
                            'name'          => $parameter['name'] ?? null,
                            'type'          => $parameter['type'] ?? null,
                            'description'   => $parameter['description'] ?? '',
                            'required'      => $parameter['required'] ?? false,
                            'default'       => $parameter['default'] ?? null,
                            'example'       => $parameter['x-example'] ?? null,
                            'array'         => [
                                'type' => $parameter['items']['type'] ?? '',
                            ],
                        ];

                        if($param['type'] === 'object' && is_array($param['default'])) {
                            $param['default'] = (empty($param['default'])) ? new stdClass() : $param['default'];
                        }

                        $param['default'] = (is_array($param['default'])) ? json_encode($param['default']): $param['default'];

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
                                    $param['name'] = $key;
                                    $param['type'] = $value['type'] ?? null;
                                    $param['description'] = $value['description'] ?? '';
                                    $param['required'] = (in_array($key, $bodyRequired));
                                    $param['default'] = $value['default'] ?? null;
                                    $param['example'] = $value['x-example'] ?? null;
                                    $param['array'] = [
                                        'type' => $value['items']['type'] ?? '',
                                    ];
                                    if($value['type'] === 'object' && is_array($value['default'])) {
                                        $value['default'] = (empty($value['default'])) ? new stdClass() : $value['default'];
                                    }

                                    $param['default'] = (is_array($value['default']) || $value['default'] instanceof stdClass) ? json_encode($value['default']): $value['default'];

                                    $output['parameters']['body'][] = $param;
                                    $output['parameters']['all'][] = $param;
                                }

                                continue 2;
                                
                            break;
                        }

                        $output['parameters']['all'][] = $param;
                    }

                    usort($output['parameters']['all'], function ($a, $b) {
                        return $b['required'] - $a['required'];
                    });

                    $list[] = $output;
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

            if(isset($definition['in']) && $definition['in'] === 'header') {
                $list[] = [
                    'key'           => $key,
                    'name'          => $definition['name'],
                    'description'   => $definition['description'],
                ];
            }
        }

        return $list;
    }
}
