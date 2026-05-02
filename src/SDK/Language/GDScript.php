<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class GDScript extends Language
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'GDScript';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords(): array
    {
        return [
            'if',
            'elif',
            'else',
            'for',
            'while',
            'match',
            'when',
            'break',
            'continue',
            'pass',
            'return',
            'class',
            'class_name',
            'extends',
            'is',
            'as',
            'in',
            'self',
            'super',
            'signal',
            'func',
            'static',
            'const',
            'enum',
            'var',
            'breakpoint',
            'preload',
            'await',
            'yield',
            'assert',
            'void',
            'PI',
            'TAU',
            'INF',
            'NAN',
            'and',
            'or',
            'not',
            'master',
            '@export',
            '@onready',
            'puppet',
            'remote',
            'remotesync',
            '@tool',
            '_ready',
            'name',
            'type',
            '_process',
            '_physics_process',
            '_input',
            '_unhandled_input',
            '_exit_tree',
            'get',
            'set',
            'Theme',
            'Button',
            'LineEdit',
            'TextureRect',
            'Label',
            'Control',
            'print',
            'prints',
            'printerr',
            'push_error',
            'push_warning',
            'len',
            'range',
            'load',
            'type_exists',
            'is_instance_valid',
            'randf',
            'randi',
            'randomize',
            'clamp',
            'lerp',
            'free',
            'call',
            'callv',
            'call_deferred',
            '@export_range',
            'connect',
            'disconnect',
            'emit_signal',
            'has_method',
            'has_signal',
            'get_meta',
            'set_meta',
            'has_meta',
            'to_string',
            'is_inside_tree',
            '_enter_tree',
            '_unhandled_key_input',
            '_notification',
            'add_child',
            'remove_child',
            'get_node',
            'get_parent',
            'queue_free',
            'duplicate',
            'get_tree',
            'Object',
            'RefCounted',
            'Node',
            'Node2D',
            'Node3D',
            'Callable',
            'Vector2',
            'Vector3',
            'Vector4',
            'Color',
            'Transform2D',
            'Transform3D',
            'RID',
            '@on_ready'
            // TODO: add more
        ];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides(): array
    {
        return [];
    }

    public function getStaticAccessOperator(): string
    {
        return '.';
    }

    public function getStringQuote(): string
    {
        return '"';
    }

    public function getArrayOf(string $elements): string
    {
        return '[' . $elements . ']';
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [];
    }

    /**
     * @param array $parameter
     * @param array $spec
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        // ARRAY TYPES
        if (($parameter['type'] ?? null) === self::TYPE_ARRAY) {

            // Array of models
            if (!empty($parameter['array']['model'])) {
                return 'Array[' . $this->toPascalCase($parameter['array']['model']) . ']';
            }

            // Array of enums
            if (isset($parameter['enumName'])) {
                return 'Array[String]';
            }

            if (!empty($parameter['enumValues'])) {
                return 'Array[String]';
            }

            return 'Array';
        }

        // MODEL TYPE
        if (!empty($parameter['model'])) {
            return $this->toPascalCase($parameter['model']);
        }

        // ENUM TYPE
        if (isset($parameter['enumName'])) {
            return 'String';
        }

        if (!empty($parameter['enumValues'])) {
            return 'String';
        }

        // PRIMITIVES
        return match ($parameter['type'] ?? '') {
            self::TYPE_INTEGER => 'int',
            self::TYPE_NUMBER => 'float',
            self::TYPE_STRING => 'String',
            self::TYPE_BOOLEAN => 'bool',
            self::TYPE_ARRAY => 'Array',
            self::TYPE_OBJECT => 'Dictionary',
            self::TYPE_FILE => 'RefCounted',
            default => 'Variant',
        };
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamDefault(array $param): string
    {
        $type = $param['type'] ?? '';
        $default = $param['default'] ?? '';
        $required = $param['required'] ?? '';

        if ($required) {
            return '';
        }

        $output = ' = ';

        if (empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= '0.0';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_OBJECT:
                case self::TYPE_FILE:
                    $output .= '{}';
                    break;
                default:
                    $output .= 'null';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                    if (is_array($default) || $default === '[]') {
                        $default = '0.0';
                    }
                    $output .= $default;
                    break;
                case self::TYPE_INTEGER:
                    if (is_array($default) || $default === '[]') {
                        $default = '0';
                    }
                    $output .= $default;
                    break;
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= $default;
                    break;
                case self::TYPE_FILE:
                    $output .= '{}';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$default}'";
                    break;
                default:
                    $output .= 'null';
                    break;
            }
        }

        return $output;
    }

    /**
     * @param array $param
     * @param string $lang
     * @return string
     */
    public function getParamExample(array $param, string $lang = ''): string
    {
        $type = $param['type'] ?? '';
        $example = $param['example'] ?? '';

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
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_OBJECT:
                    $output .= '{}';
                    break;
                case self::TYPE_FILE:
                    $output .= 'FileAccess.open("file.png", FileAccess.READ)';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= $example;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$example}'";
                    break;
                case self::TYPE_FILE:
                    $output .= 'FileAccess.open("file.png", FileAccess.READ)';
                    break;
            }
        }

        return $output;
    }

    public function getFilters(): array
    {
        return array_merge([
            new TwigFilter('caseEnumKey', function (string $value) {
                return $this->toUpperSnakeCase($value);
            }),
            new TwigFilter('uniqueSnake', function (string $value) {
                return $this->toUniqueSnake($value);
            }),
        ], parent::getFilters());
    }

    private function toUniqueSnake(string $value): string
    {
        $base = $this->toSnakeCase($value);
        $name = $this->escapeKeyword($base);
        return $name;
    }
}
