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
                'destination'   => '{{ spec.title | caseLower}}/client.go',
                'template'      => 'go/client.go.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '{{ spec.title | caseLower}}/{{service.name | caseDash}}.go',
                'template'      => 'go/services/service.go.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'go/docs/example.md.twig',
            ],
        ];
    }

    /**
     * @param array $parameter
     * @param array $nestedTypes
     * @return string
     */
    public function getTypeName(array $parameter): string
    {
        switch ($parameter['type']) {
            case self::TYPE_INTEGER:
                return 'int';
            case self::TYPE_NUMBER:
                return 'float64';
            case self::TYPE_FILE:
            case self::TYPE_STRING:
                return 'string';
            case self::TYPE_BOOLEAN:
                return 'bool';
            case self::TYPE_OBJECT:
                return 'interface{}';
            case self::TYPE_ARRAY:
                return '[]interface{}';
        }

        return $parameter['type'];
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
                case self::TYPE_FILE:
                    $output .= "file";
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
                    $output .= 'nil';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$example}\"";
                    break;
                case self::TYPE_FILE:
                    $output .= "file";
                    break;
            }
        }

        return $output;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('godocComment', function ($value) {
                $value = explode("\n", $value);
                foreach ($value as $key => $line) {
                    $value[$key] = "// " . wordwrap($line, 75, "\n// ");
                }
                return implode("\n", $value);
            }, ['is_safe' => ['html']])
        ];
    }
}
