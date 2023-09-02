<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Crystal extends Language
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Crystal';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords(): array
    {
        return [
            'abstract',
            'as',
            'asm',
            'begin',
            'break',
            'case',
            'class',
            'def',
            'do',
            'else',
            'elsif',
            'end',
            'enum',
            'extend',
            'false',
            'for',
            'fun',
            'if',
            'in',
            'include',
            'instance',
            'is_a?',
            'lib',
            'macro',
            'module',
            'next',
            'nil',
            'of',
            'out',
            'pointer',
            'record',
            'redo',
            'require',
            'return',
            'self',
            'sizeof',
            'struct',
            'super',
            'then',
            'true',
            'type',
            'typeof',
            'union',
            'unless',
            'until',
            'when',
            'while',
            'with',
            'yield',
            'path',
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
                'destination'   => 'README.md',
                'template'      => 'crystal/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'crystal/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'crystal/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shard.yml',
                'template'      => 'crystal/shard.yml.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseDash }}/client.cr',
                'template'      => 'crystal/src/client.cr.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseDash }}/permission.cr',
                'template'      => 'crystal/src/permission.cr.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseDash }}/role.cr',
                'template'      => 'crystal/src/role.cr.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseDash }}/id.cr',
                'template'      => 'crystal/src/id.cr.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseDash }}/query.cr',
                'template'      => 'crystal/src/query.cr.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseDash }}/service.cr',
                'template'      => 'crystal/src/service.cr.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseDash }}/input_file.cr',
                'template'      => 'crystal/src/input_file.cr.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseDash }}/exception.cr',
                'template'      => 'crystal/src/exception.cr.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/src/{{ spec.title | caseDash}}/services/{{service.name | caseDash}}.cr',
                'template'      => 'crystal/src/services/service.cr.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ spec.title | caseDash}}/extensions/mime_types.cr',
                'template'      => 'crystal/src/extensions/mime_types.cr.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'crystal/docs/example.md.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/src/{{ spec.title | caseDash }}/models/{{ definition.name | caseSnake }}.cr',
                'template'      => 'crystal/src/models/model.cr.twig',
            ],
        ];
    }

    /**
     * @param array $parameter
     * @return string
     */
    public function getTypeName(array $parameter): string
    {
        switch ($parameter['type']) {
            case self::TYPE_INTEGER:
                return 'Int64';
            case self::TYPE_NUMBER:
                return 'Float64';
            case self::TYPE_STRING:
                return 'String';
            case self::TYPE_ARRAY:
                if (!empty($parameter['items']['type'])) {
                    return 'Array(' . $this->getTypeName($parameter['items']) . ')';
                }
                return 'Array(Void)';
            case self::TYPE_OBJECT:
                return 'Hash';
            case self::TYPE_BOOLEAN:
                return 'Bool';
            case self::TYPE_FILE:
                return 'InputFile';
            default:
                return $parameter['type'];
        }
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
            return ':';
        }

        $output = ': ';

        if (empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'nil';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_OBJECT:
                    $output .= '{}';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$default}\"";
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
                case self::TYPE_BOOLEAN:
                    $output .= 'nil';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_OBJECT:
                    $output .= '{}';
                    break;
                case self::TYPE_FILE:
                    $output .= 'InputFile.from_path("dir/file.png")';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                    $output .= $example;
                    break;
                case self::TYPE_OBJECT:
                    $output .= $this->jsonToHash(json_decode($example, true));
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$example}\"";
                    break;
                case self::TYPE_FILE:
                    $output .= 'InputFile.from_path("dir/file.png")';
                    break;
            }
        }

        return $output;
    }

    /**
     * Converts JSON Object To Ruby Native Hash
     *
     * @return string
     * @var $data array
     */
    protected function jsonToHash(array $data): string
    {
        $output = '{';

        foreach ($data as $key => $node) {
            $value = (is_array($node)) ? $this->jsonToHash($node) : $node;
            $output .= '"' . $key . '" => ' . ((is_string($node)) ? '"' . $value . '"' : $value) . (($key !== array_key_last($data)) ? ', ' : '');
        }

        $output .= '}';

        return $output;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('crystalComment', function ($value) {
                $value = explode("\n", $value);
                foreach ($value as $key => $line) {
                    $value[$key] = "        # " . wordwrap($line, 75, "\n        # ");
                }
                return implode("\n", $value);
            }, ['is_safe' => ['html']]),
            new TwigFilter('cast', function ($value) {
                return match ($value['type']) {
                    self::TYPE_STRING => '.to_s',
                    self::TYPE_INTEGER => '.to_i',
                    self::TYPE_NUMBER => '.to_f',
                    self::TYPE_BOOLEAN => '.to_b',
                    default => '',
                };
            }, ['is_safe' => ['html']]),
            new TwigFilter('returnType', function (array $method, array $spec, string $generic = 'T') {
                return $this->getReturnType($method, $spec, $generic);
            }),
            new TwigFilter('modelType', function (array $property, array $spec, string $generic = 'T') {
                return $this->getModelType($property, $spec, $generic);
            }),
            new TwigFilter('propertyType', function (array $property, array $spec, string $generic = 'T') {
                return $this->getPropertyType($property, $spec, $generic);
            }),
            new TwigFilter('hasGenericType', function (string $model, array $spec) {
                return $this->hasGenericType($model, $spec);
            }),
        ];
    }

    protected function getReturnType(array $method, array $spec, string $generic = 'T'): string
    {
        if ($method['type'] === 'webAuth') {
            return '';
        }
        if ($method['type'] === 'location') {
            return 'Slice(UInt8)';
        }

        if (
            !\array_key_exists('responseModel', $method)
            || empty($method['responseModel'])
            || $method['responseModel'] === 'any'
        ) {
            return '';
        }

        $type = $this->toUpperCaseWords($method['responseModel']);

        if ($this->hasGenericType($method['responseModel'], $spec)) {
            $type .= '(' . $generic . ')';
        }

        return 'Models::' . $type;
    }

    protected function getModelType(array $definition, array $spec, string $generic = 'T'): string
    {
        if ($this->hasGenericType($definition['name'], $spec)) {
            return $this->toUpperCaseWords($definition['name']) . '(' . $generic . ')';
        }
        return $this->toUpperCaseWords($definition['name']);
    }

    protected function getPropertyType(array $property, array $spec, string $generic = 'T'): string
    {
        if (\array_key_exists('sub_schema', $property)) {
            $type = $this->toUpperCaseWords($property['sub_schema']);

            if ($this->hasGenericType($property['sub_schema'], $spec)) {
                $type .= '(' . $generic . ')';
            }

            if ($property['type'] === 'array') {
                $type = 'Array(' . $type . ')';
            }
        } else {
            $type = $this->getTypeName($property);
        }

        if (!$property['required']) {
            $type .= '?';
        }

        return $type;
    }
}
