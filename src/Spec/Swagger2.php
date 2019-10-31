<?php

namespace Appwrite\Spec;

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

        return $list;
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
                if(isset($method['tags']) && is_array($method['tags']) && in_array($service, $method['tags'])) {
                    $output = [
                        'method' => $methodName,
                        'path' => $pathName,
                        'name' => isset($method['operationId']) ? $method['operationId'] : '',
                        'title' => isset($method['summary']) ? $method['summary'] : '',
                        'description' => isset($method['description']) ? $method['description'] : '',
                        'security' => isset($method['security']) ? $method['security'] : [],
                        'consumes' => isset($method['consumes']) ? $method['consumes'] : [],
                        'cookies' => (isset($method['extensions']) && isset($method['extensions']['cookies'])) ? $method['extensions']['cookies'] : false,
                        'location' => (isset($method['extensions']) && isset($method['extensions']['location'])) ? $method['extensions']['location'] : false,
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

                    $method['parameters'] = (is_array($method['parameters'])) ? $method['parameters'] : [];

                    foreach ($method['parameters'] as $parameter) {
                        $param = [
                            'name'          => $parameter['name'],
                            'type'          => $parameter['type'],
                            'description'   => $parameter['description'],
                            'required'      => (int)$parameter['required'],
                            'default'       => (isset($parameter['default'])) ? $parameter['default'] : null,
                            'example'       => (isset($parameter['x-example'])) ? $parameter['x-example'] : null,
                            'array'         => [
                                'type' => (isset($parameter['items']) && $parameter['items']['type']) ? $parameter['items']['type'] : '',
                            ],
                        ];

                        $param['default'] = (is_array($param['default'])) ? json_encode($param['default']): $param['default'];

                        $output['parameters']['all'][] = $param;

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
                            case 'body':
                            case 'formData':
                                $output['parameters']['body'][] = $param;
                            break;
                        }
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