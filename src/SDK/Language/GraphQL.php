<?php

namespace Appwrite\SDK\Language;

use Twig\TwigFilter;

class GraphQL extends HTTP
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'GraphQL';
    }

    /**
     * @param $type
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        $type = '';

        switch ($parameter['type']) {
            case self::TYPE_INTEGER:
                $type = 'Int';
                break;
            case self::TYPE_STRING:
                $type = 'String';
                break;
            case self::TYPE_FILE:
                $type = 'InputFile';
                break;
            case self::TYPE_BOOLEAN:
                $type = 'Bool';
                break;
            case self::TYPE_ARRAY:
                if (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type'])) {
                    $type = '[' . $this->getTypeName($parameter['array']) . ']';
                    break;
                }
                $type = '[String]';
                break;
            case self::TYPE_OBJECT:
                $type = 'JSON';
                break;
        }

        if (empty($type)) {
            $type = $parameter['type'];
        }

        if ($parameter['required'] ?? false) {
            $type .= '!';
        }

        return $type;
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamDefault(array $param): string
    {
        $type       = $param['type'] ?? '';
        $default    = $param['default'] ?? '';
        $required   = $param['required'] ?? '';

        if ($required) {
            return '';
        }

        $output = '';

        if (empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_OBJECT:
                    $output .= '{}';
                    break;
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= '0';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_OBJECT:
                case self::TYPE_NUMBER:
                case self::TYPE_ARRAY:
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= '"' . $default . '"';
                    break;
            }
        }

        return $output;
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamExample(array $param): string
    {
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

        $output = '';

        if (empty($example) && $example !== 0 && $example !== false) {
            switch ($type) {
                case self::TYPE_FILE:
                    $output .= 'null';
                    break;
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= '0';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_OBJECT:
                    $output .= '"{}"';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_FILE:
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $example;
                    break;
                case self::TYPE_ARRAY:
                    $output .= is_array($example) ? json_encode($example) : $example;
                    break;
                case self::TYPE_STRING:
                case self::TYPE_OBJECT:
                    $output .= '"' . $example . '"';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
            }
        }

        return $output;
    }

    /**
     * @return string
     */
    public function getAdditionalPropertiesExamples(): string
    {
        return '    "username": "john_doe",
    "email": "john@example.com",
    "fullName": "John Doe",
    "age": 25,
    "isActive": true';
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/services/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => '/graphql/docs/services/example.md.twig',
                'exclude'       => [
                    'services'  => [['name' => 'graphql']],
                    'methods'   => [['type' => 'webAuth']],
                ],
            ],
            [
                'scope'         => 'definition',
                'destination'   => 'docs/examples/models/{{definition.name | caseLower}}.md',
                'template'      => '/graphql/docs/models/example.md.twig',
                'exclude'       => [
                    'services'  => [['name' => 'graphql']],
                    'methods'   => [['type' => 'webAuth']],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('additionalPropertiesExamples', function () {
                return $this->getAdditionalPropertiesExamples();
            }),
        ];
    }
}
