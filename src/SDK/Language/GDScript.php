<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

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
            'else',
            'elif',
            'for',
            'not',
            'and',
            'or',
            'while',
            'match',
            'master',
            'break',
            'continue',
            'return',
            'load',
            'func',
            'class',
            'class_name',
            'extends',
            'is',
            'in',
            'as',
            'self',
            'super',
            'signal',
            'await',
            'var',
            'const',
            'enum',
            'static',
            'breakpoint',
            'assert',
            '@export',
            '@onready',
            'pass',
            'preload',
            'puppet',
            'remote',
            'remotesync',
            'static',
            '@tool',
            'void',
            'PI',
            'TAU',
            'INF',
            'NAN',
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
        if (isset($parameter['enumName'])) {
            return \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return \ucfirst($parameter['name']);
        }
        if (!empty($parameter['array']['model'])) {
            return 'Array';
        }
        if (!empty($parameter['model'])) {
            return $parameter['type'] === self::TYPE_ARRAY ? 'Array' : $this->toPascalCase($parameter['model']);
        }
        return match ($parameter['type']) {
            self::TYPE_INTEGER => 'int',
            self::TYPE_NUMBER => 'float',
            self::TYPE_STRING => 'String',
            self::TYPE_FILE => 'FileAccess', // or PackedByteArray depending on usage
            self::TYPE_BOOLEAN => 'bool',
            self::TYPE_ARRAY => 'Array',
            self::TYPE_OBJECT => 'Dictionary',
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
                    $output .= '0.0';
                    break;
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
                default:
                    $output .= 'null';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$default}\"";
                    break;
                case self::TYPE_OBJECT:
                    $output .= $default; // Should be formatted as GDScript dictionary
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
                    $output .= "\"{$example}\"";
                    break;
                case self::TYPE_FILE:
                    $output .= 'FileAccess.open("file.png", FileAccess.READ)';
                    break;
            }
        }

        return $output;
    }
}
