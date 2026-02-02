<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

abstract class JS extends Language
{
    protected $params = [
        'npmPackage' => 'packageName',
        'bowerPackage' => 'packageName',
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setNPMPackage(string $name): self
    {
        $this->setParam('npmPackage', $name);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setBowerPackage(string $name): self
    {
        $this->setParam('bowerPackage', $name);

        return $this;
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
            'arguments',
            'await', // new in ECMAScript 5 and 6.
            'boolean',
            'break',
            'byte',
            'case',
            'catch',
            'char',
            'class', // new in ECMAScript 5 and 6.
            'const',
            'continue',
            'debugger',
            'default',
            'delete',
            'do',
            'double',
            'else',
            'enum', // new in ECMAScript 5 and 6.
            'eval',
            'export', // new in ECMAScript 5 and 6.
            'extends', // new in ECMAScript 5 and 6.
            'false',
            'final',
            'finally',
            'float',
            'for',
            'function',
            'goto',
            'if',
            'implements',
            'import', // new in ECMAScript 5 and 6.
            'in',
            'instanceof',
            'int',
            'interface',
            'let', // new in ECMAScript 5 and 6.
            'long',
            'native',
            'new',
            'null',
            'package',
            'private',
            'protected',
            'public',
            'return',
            'short',
            'static',
            'super', // new in ECMAScript 5 and 6.
            'switch',
            'synchronized',
            'this',
            'throw',
            'throws',
            'transient',
            'true',
            'try',
            'typeof',
            'var',
            'void',
            'volatile',
            'while',
            'with',
            'yield',
            'path'
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
     * @param array $parameter
     * @param array $nestedTypes
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
            return $this->toPascalCase($parameter['array']['model']) . '[]';
        }
        if (!empty($parameter['model'])) {
            $modelType = $this->toPascalCase($parameter['model']);
            return $parameter['type'] === self::TYPE_ARRAY ? $modelType . '[]' : $modelType;
        }
        if (isset($parameter['items'])) {
            // Map definition nested type to parameter nested type
            $parameter['array'] = $parameter['items'];
        }
        switch ($parameter['type']) {
            case self::TYPE_INTEGER:
            case self::TYPE_NUMBER:
                return 'number';
            case self::TYPE_ARRAY:
                if (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type'])) {
                    return $this->getTypeName($parameter['array']) . '[]';
                }
                return 'any[]';
            case self::TYPE_FILE:
                return 'File';
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
                    $output .= "''";
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
                    $output .= "'{$default}'";
                    break;
            }
        }

        return $output;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('caseEnumKey', function (string $value) {
                return $this->toPascalCase($value);
            }),
            new TwigFilter('enumExample', function (array $param, string $prefix = '') {
                $enumValues = $param['enumValues'] ?? [];
                if (empty($enumValues)) {
                    return '';
                }

                $enumKeys = $param['enumKeys'] ?? [];
                $enumName = $this->toPascalCase($param['enumName'] ?? $param['name'] ?? '');
                $example = $param['example'] ?? null;
                $isArray = ($param['type'] ?? '') === self::TYPE_ARRAY;

                $resolveKey = function ($value) use ($enumValues, $enumKeys) {
                    $index = array_search($value, $enumValues, true);
                    if ($index !== false && isset($enumKeys[$index]) && $enumKeys[$index] !== '') {
                        return $this->toPascalCase($enumKeys[$index]);
                    }
                    if ($index !== false && isset($enumValues[$index])) {
                        return $this->toPascalCase($enumValues[$index]);
                    }
                    $fallback = $enumKeys[0] ?? $enumValues[0] ?? $value;
                    return $this->toPascalCase((string)$fallback);
                };

                if ($isArray) {
                    $values = [];
                    if (is_string($example) && $example !== '') {
                        $decoded = json_decode($example, true);
                        if (is_array($decoded)) {
                            $values = $decoded;
                        }
                    } elseif (is_array($example)) {
                        $values = $example;
                    }

                    if (empty($values)) {
                        $values = [$enumValues[0]];
                    }

                    $items = array_map(function ($value) use ($enumName, $prefix, $resolveKey) {
                        return $prefix . $enumName . '.' . $resolveKey($value);
                    }, $values);

                    return '[' . implode(', ', $items) . ']';
                }

                $value = ($example !== null && $example !== '') ? $example : $enumValues[0];
                return $prefix . $enumName . '.' . $resolveKey($value);
            }),
        ];
    }
}
