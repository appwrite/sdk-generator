<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Go extends Language
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Go';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords(): array
    {
        return [
            'bool',
            'error',
            'for',
            'func',
            'go',
            'if',
            'import',
            'make',
            'map',
            'nil',
            'package',
            'range',
            'return',
            'string',
            'struct',
            'type',
            'var',
            'default'
        ];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'go.mod',
                'template'      => 'go/go.mod.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'example/main.go',
                'template'      => 'go/main.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'go/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'go/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'go/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'appwrite/appwrite.go',
                'template'      => 'go/appwrite.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'client/client.go',
                'template'      => 'go/client.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'file/inputFile.go',
                'template'      => 'go/inputFile.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'query/query.go',
                'template'      => 'go/query.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'permission/permission.go',
                'template'      => 'go/permission.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'role/role.go',
                'template'      => 'go/role.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'id/id.go',
                'template'      => 'go/id.go.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '{{ service.name | caseLower}}/{{service.name | caseDash}}.go',
                'template'      => 'go/services/service.go.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'go/docs/example.md.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => 'models/{{ definition.name | caseLower }}.go',
                'template'      => 'go/models/model.go.twig',
            ],
        ];
    }

    /**
     * @param array $parameter
     * @param array $nestedTypes
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        return match ($parameter['type']) {
            self::TYPE_INTEGER => 'int',
            self::TYPE_NUMBER => 'float64',
            self::TYPE_FILE => 'file.InputFile',
            self::TYPE_STRING => 'string',
            self::TYPE_BOOLEAN => 'bool',
            self::TYPE_OBJECT => 'interface{}',
            self::TYPE_ARRAY => '[]interface{}',
            default => $parameter['type'],
        };
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

        $output = ' = ';

        if (empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'null';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_OBJECT:
                    $output .= 'nil';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                    $output .= $default;
                    break;
                case self::TYPE_OBJECT:
                    $output .= "\"$default\"";
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "nil";
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
                    $output .= 'map[string]interface{}{}';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]interface{}{}';
                    break;
                case self::TYPE_FILE:
                    $output .= 'file.NewInputFile("/path/to/file.png", "file.png")';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $example;
                    break;
                case self::TYPE_ARRAY:
                    if (\str_starts_with($example, '[')) {
                        $example = \substr($example, 1);
                    }
                    if (\str_ends_with($example, ']')) {
                        $example = \substr($example, 0, -1);
                    }
                    $output .= 'interface{}{' . $example . '}';
                    break;
                case self::TYPE_OBJECT:
                    $output .= 'map[string]interface{}{}';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$example}\"";
                    break;
                case self::TYPE_FILE:
                    $output .= 'file.NewInputFile("/path/to/file.png", "file.png")';
                    break;
            }
        }

        return $output;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('godocComment', function ($value, $indent = 0) {
                $value = trim($value);
                $value = explode("\n", $value);
                $indent = \str_repeat(' ', $indent);
                foreach ($value as $key => $line) {
                    $value[$key] = "// " . wordwrap(trim($line), 75, "\n" . $indent . "// ");
                }
                return implode("\n" . $indent, $value);
            }, ['is_safe' => ['html']]),
            new TwigFilter('propertyType', function (array $property, array $spec, string $generic = 'interface{}') {
                return $this->getPropertyType($property, $spec, $generic);
            }),
            new TwigFilter('returnType', function (array $method, array $spec, string $namespace, string $generic = 'T') {
                return $this->getReturnType($method, $spec, $namespace, $generic);
            }),
            new TwigFilter('caseEnumKey', function (string $value) {
                return $this->toUpperSnakeCase($value);
            })
        ];
    }

    protected function getPropertyType(array $property, array $spec, string $generic = 'interface{}'): string
    {
        $type = $this->getTypeName($property);
        return $type;
    }

    protected function getReturnType(array $method, array $spec, string $namespace, string $generic = 'T'): string
    {
        if ($method['type'] === 'webAuth') {
            return 'bool';
        }
        if ($method['type'] === 'location') {
            return '[]byte';
        }

        if (
            !\array_key_exists('responseModel', $method)
            || empty($method['responseModel'])
            || $method['responseModel'] === 'any'
        ) {
            return 'interface{}';
        }

        $ret = ucfirst($method['responseModel']);

        return 'models.' . $ret;
    }
}
