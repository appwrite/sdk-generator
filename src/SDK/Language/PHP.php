<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class PHP extends Language
{
    /**
     * @var array
     */
    protected $params = [
        'composerVendor' => 'vendor-name',
        'composerPackage' => 'package-name',
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setComposerVendor(string $name): self
    {
        $this->setParam('composerVendor', $name);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setComposerPackage(string $name): self
    {
        $this->setParam('composerPackage', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'PHP';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords(): array
    {
        return [
            '__halt_compiler',
            'abstract',
            'and',
            'array',
            'as',
            'break',
            'callable',
            'case',
            'catch',
            'class',
            'clone',
            'const',
            'continue',
            'declare',
            'default',
            'die',
            'do',
            'echo',
            'else',
            'elseif',
            'empty',
            'enddeclare',
            'endfor',
            'endforeach',
            'endif',
            'endswitch',
            'endwhile',
            'eval',
            'exit',
            'extends',
            'final',
            'for',
            'foreach',
            'function',
            'global',
            'goto',
            'if',
            'implements',
            'include',
            'include_once',
            'instanceof',
            'insteadof',
            'interface',
            'isset',
            'list',
            'namespace',
            'new',
            'or',
            'print',
            'private',
            'protected',
            'public',
            'require',
            'require_once',
            'return',
            'static',
            'switch',
            'throw',
            'trait',
            'try',
            'unset',
            'use',
            'var',
            'while',
            'xor',
            'path'
        ];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides(): array
    {
        return [
            'Function' => 'FunctionModel',
        ];
    }

    protected function applyIdentifierOverride(string $value): string
    {
        return $this->getIdentifierOverrides()[$value] ?? $value;
    }

    public function getStaticAccessOperator(): string
    {
        return '::';
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
        return [
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'php/README.md.twig',
                //'block'         => 'default',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.gitignore',
                'template'      => 'php/.gitignore',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'php/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'php/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'composer.json',
                'template'      => 'php/composer.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'phpunit.xml',
                'template'      => 'php/phpunit.xml.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'docs/{{ service.name | caseLower }}.md',
                'template'      => 'php/docs/service.md.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{ service.name | caseLower }}/{{ method.name | caseKebab }}.md',
                'template'      => 'php/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.namespace | caseNamespacePath }}/Client.php',
                'template'      => 'php/src/Client.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.namespace | caseNamespacePath }}/Permission.php',
                'template'      => 'php/src/Permission.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ spec.namespace | caseNamespacePath }}/PermissionTest.php',
                'template'      => 'php/tests/PermissionTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.namespace | caseNamespacePath }}/Role.php',
                'template'      => 'php/src/Role.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ spec.namespace | caseNamespacePath }}/RoleTest.php',
                'template'      => 'php/tests/RoleTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.namespace | caseNamespacePath }}/ID.php',
                'template'      => 'php/src/ID.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ spec.namespace | caseNamespacePath }}/IDTest.php',
                'template'      => 'php/tests/IDTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.namespace | caseNamespacePath }}/Query.php',
                'template'      => 'php/src/Query.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ spec.namespace | caseNamespacePath }}/QueryTest.php',
                'template'      => 'php/tests/QueryTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.namespace | caseNamespacePath }}/Operator.php',
                'template'      => 'php/src/Operator.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ spec.namespace | caseNamespacePath }}/OperatorTest.php',
                'template'      => 'php/tests/OperatorTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.namespace | caseNamespacePath }}/InputFile.php',
                'template'      => 'php/src/InputFile.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.namespace | caseNamespacePath }}/{{ spec.namespace | split(\'\\\\\') | last | caseUcfirst}}Exception.php',
                'template'      => 'php/src/Exception.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ spec.namespace | caseNamespacePath }}/Service.php',
                'template'      => 'php/src/Service.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ spec.namespace | caseNamespacePath }}/Models/ArraySerializable.php',
                'template'      => 'php/src/Models/ArraySerializable.php.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/src/{{ spec.namespace | caseNamespacePath }}/Services/{{service.name | caseUcfirst}}.php',
                'template'      => 'php/src/Services/Service.php.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/tests/{{ spec.namespace | caseNamespacePath }}/Services/{{service.name | caseUcfirst}}Test.php',
                'template'      => 'php/tests/Services/ServiceTest.php.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => '/src/{{ spec.namespace | caseNamespacePath }}/Enums/{{ enum.name | caseUcfirst | overrideIdentifier }}.php',
                'template'      => 'php/src/Enums/Enum.php.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/src/{{ spec.namespace | caseNamespacePath }}/Models/{{ definition.name | caseUcfirst | overrideIdentifier }}.php',
                'template'      => 'php/src/Models/Model.php.twig',
            ],
            [
                'scope'         => 'requestModel',
                'destination'   => '/src/{{ spec.namespace | caseNamespacePath }}/Models/{{ requestModel.name | caseUcfirst | overrideIdentifier }}.php',
                'template'      => 'php/src/Models/RequestModel.php.twig',
            ],
        ];
    }

    protected function normalizeNamespace(string $namespace): string
    {
        $segments = explode('\\', $namespace);
        $segments = array_map(function ($segment) {
            return ucfirst($this->toCamelCase($segment));
        }, $segments);

        return implode('\\', $segments);
    }

    protected function getModelClassName(string $modelName, array $spec, bool $fullyQualified = false): string
    {
        $className = $this->applyIdentifierOverride($this->toPascalCase($modelName));

        if (!$fullyQualified) {
            return $className;
        }

        return '\\' . $this->normalizeNamespace($spec['namespace'] ?? '') . '\\Models\\' . $className;
    }

    protected function getResponseModels(array $method, array $spec): array
    {
        $models = [];

        foreach ($method['responseModels'] ?? [] as $modelName) {
            if (empty($modelName) || $modelName === 'any') {
                continue;
            }

            $models[] = $this->getModelClassName($modelName, $spec, true);
        }

        if (empty($models) && !empty($method['responseModel']) && $method['responseModel'] !== 'any') {
            $models[] = $this->getModelClassName($method['responseModel'], $spec, true);
        }

        return array_values(array_unique($models));
    }

    /**
     * @param array $parameter
     * @param array $nestedTypes
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        if (
            ($parameter['type'] ?? null) === self::TYPE_ARRAY
            && (isset($parameter['enumName']) || !empty($parameter['enumValues']))
        ) {
            return 'array';
        }

        if (isset($parameter['enumName'])) {
            return $this->applyIdentifierOverride(\ucfirst($parameter['enumName']));
        }
        if (!empty($parameter['enumValues'])) {
            return $this->applyIdentifierOverride(\ucfirst($parameter['name']));
        }
        if (!empty($parameter['array']['model'])) {
            return 'array';
        }
        if (!empty($parameter['sub_schema'])) {
            $modelType = $this->applyIdentifierOverride($this->toPascalCase($parameter['sub_schema']));
            return ($parameter['type'] ?? null) === self::TYPE_ARRAY ? 'array' : $modelType;
        }
        if (!empty($parameter['model'])) {
            $modelType = $this->applyIdentifierOverride($this->toPascalCase($parameter['model']));
            return $parameter['type'] === self::TYPE_ARRAY ? 'array' : $modelType;
        }
        return match ($parameter['type']) {
            self::TYPE_STRING => 'string',
            self::TYPE_BOOLEAN => 'bool',
            self::TYPE_NUMBER => 'float',
            self::TYPE_INTEGER => 'int',
            self::TYPE_ARRAY,
            self::TYPE_OBJECT => 'array',
            self::TYPE_FILE => 'InputFile',
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
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
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
                    $output .= $this->jsonToAssoc(json_decode($default, true));
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

    /**
     * @param array $param
     * @param string $lang
     * @return string
     */
    public function getParamExample(array $param, string $lang = ''): string
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
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= '[]';
                    break;
                case self::TYPE_FILE:
                    $output .= "InputFile::withPath('file.png')";
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $example;
                    break;
                case self::TYPE_ARRAY:
                    $output .= $this->isPermissionString($example) ? $this->getPermissionExample($example) : $example;
                    break;
                case self::TYPE_OBJECT:
                    $output .= $this->jsonToAssoc(json_decode($example, true));
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$example}'";
                    break;
                case self::TYPE_FILE:
                    $output .= "InputFile::withPath('file.png')";
                    break;
            }
        }

        return $output;
    }

    /**
     * Converts JSON Object To PHP Native Assoc Array
     *
     * @var $data array
     */
    protected function jsonToAssoc(array $data, int $indent = 0): string
    {
        if (empty($data)) {
            return '[]';
        }

        $baseIndent = str_repeat('    ', $indent);
        $itemIndent = str_repeat('    ', $indent + 1);
        $output = "[\n";

        $keys = array_keys($data);
        foreach ($keys as $index => $key) {
            $node = $data[$key];

            if (is_array($node)) {
                $value = $this->jsonToAssoc($node, $indent + 1);
            } elseif (is_string($node)) {
                $value = '\'' . $node . '\'';
            } elseif (is_bool($node)) {
                $value = $node ? 'true' : 'false';
            } elseif (is_null($node)) {
                $value = 'null';
            } else {
                $value = $node;
            }

            $comma = ($index < count($keys) - 1) ? ',' : '';
            $output .= '    ' . $itemIndent . '\'' . $key . '\' => ' . $value . $comma . "\n";
        }

        $output .= $baseIndent . '    ]';

        return $output;
    }

    protected function getMockDefinitionPayload(string $definitionName, array $spec, int $indentLevel = 2): string
    {
        $definition = null;

        foreach ($spec['definitions'] ?? [] as $candidate) {
            if (($candidate['name'] ?? null) === $definitionName) {
                $definition = $candidate;
                break;
            }
        }

        if ($definition === null) {
            return 'array()';
        }

        $properties = array_values(array_filter(
            $definition['properties'] ?? [],
            fn (array $property) => (bool)($property['required'] ?? false)
        ));

        if (empty($properties)) {
            return 'array()';
        }

        $itemIndent = str_repeat('    ', $indentLevel);
        $closingIndent = str_repeat('    ', max(0, $indentLevel - 1));
        $lines = ['array('];

        foreach ($properties as $index => $property) {
            $lines[] = $itemIndent
                . '"' . $this->escapePhpString((string)($property['name'] ?? '')) . '" => '
                . $this->getMockPropertyValue($property, $spec, $indentLevel + 1)
                . ($index < count($properties) - 1 ? ',' : '');
        }

        $lines[] = $closingIndent . ')';

        return implode("\n", $lines);
    }

    protected function getMockPropertyValue(array $property, array $spec, int $indentLevel): string
    {
        if (!empty($property['sub_schema'])) {
            if (($property['type'] ?? null) === self::TYPE_ARRAY) {
                $itemIndent = str_repeat('    ', $indentLevel);
                $closingIndent = str_repeat('    ', max(0, $indentLevel - 1));

                return "array(\n"
                    . $itemIndent . $this->getMockDefinitionPayload((string)$property['sub_schema'], $spec, $indentLevel + 1) . "\n"
                    . $closingIndent . ')';
            }

            return $this->getMockDefinitionPayload((string)$property['sub_schema'], $spec, $indentLevel);
        }

        if (!empty($property['enum'])) {
            return '"' . $this->escapePhpString((string)$property['enum'][0]) . '"';
        }

        return match ($property['type'] ?? null) {
            self::TYPE_OBJECT, self::TYPE_ARRAY => 'array()',
            self::TYPE_BOOLEAN => 'true',
            self::TYPE_INTEGER => (($property['x-example'] ?? null) === null && ($property['example'] ?? null) === null)
                ? '1'
                : $this->formatPhpLiteral($property['example'] ?? $property['x-example']),
            self::TYPE_NUMBER => (($property['x-example'] ?? null) === null && ($property['example'] ?? null) === null)
                ? '1.0'
                : $this->formatPhpLiteral($property['example'] ?? $property['x-example']),
            self::TYPE_STRING => '"' . $this->escapePhpString(
                (string)(
                    ($property['example'] ?? null) !== null && ($property['example'] ?? '') !== ''
                        ? $property['example']
                        : '[' . strtoupper((string)($property['name'] ?? '')) . ']'
                )
            ) . '"',
            default => $this->formatPhpLiteral($property['example'] ?? null),
        };
    }

    protected function formatPhpLiteral(mixed $value): string
    {
        if (is_string($value)) {
            return '"' . $this->escapePhpString($value) . '"';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_array($value)) {
            return empty($value) ? 'array()' : var_export($value, true);
        }

        return (string)$value;
    }

    protected function escapePhpString(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('"', '\\"', $value);
        $value = str_replace('$', '\\$', $value);

        return $value;
    }

    protected function getReturn(array $method, array $spec = []): string
    {
        if (($method['emptyResponse'] ?? true) || $method['type'] === 'location' || $method['type'] === 'webAuth') {
            return 'string';
        }

        $responseModels = $this->getResponseModels($method, $spec);

        if (!empty($responseModels)) {
            return implode('|', $responseModels);
        }

        return 'array';
    }

    /**
     * Generate method parameters string for PHP method signatures
     *
     * @param array $method
     * @return string
     */
    protected function getMethodParameters(array $method): string
    {
        $params = [];

        foreach ($method['parameters']['all'] ?? [] as $parameter) {
            $nullable = (!($parameter['required'] ?? true)) || ($parameter['nullable'] ?? false);
            $nullablePrefix = $nullable ? '?' : '';

            $typeName = $this->getTypeName($parameter);
            $paramName = '$' . $this->escapeKeyword($this->toCamelCase($parameter['name'] ?? ''));
            $default = !($parameter['required'] ?? true) ? ' = null' : '';

            $params[] = $nullablePrefix . $typeName . ' ' . $paramName . $default;
        }

        $result = implode(', ', $params);

        // Add onProgress callback for multipart/form-data methods
        if (in_array('multipart/form-data', $method['consumes'] ?? [])) {
            $result .= ($result !== '' ? ', ' : '') . '?callable $onProgress = null';
        }

        return $result;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('getReturn', function ($value, array $spec = []) {
                return $this->getReturn($value, $spec);
            }),
            new TwigFilter('getResponseModels', function ($value, array $spec = []) {
                return $this->getResponseModels($value, $spec);
            }),
            new TwigFilter('mockDefinitionPayload', function (string $definitionName, array $spec, int $indentLevel = 2) {
                return $this->getMockDefinitionPayload($definitionName, $spec, $indentLevel);
            }, ['is_safe' => ['html']]),
            new TwigFilter('methodParameters', function ($value) {
                return $this->getMethodParameters($value);
            }),
            new TwigFilter('deviceInfo', function ($value) {
                return php_uname('s') . '; ' . php_uname('v') . '; ' . php_uname('m');
            }),
            new TwigFilter('caseEnumKey', function (string $value) {
                if (isset($this->getIdentifierOverrides()[$value])) {
                    $value = $this->getIdentifierOverrides()[$value];
                }
                $value = \preg_replace('/[^a-zA-Z0-9]/', '', $value);
                return $this->toUpperSnakeCase($value);
            }),
            new TwigFilter('hasBearerAuth', function (array $headers) {
                foreach ($headers as $header) {
                    if (isset($header['type']) && $header['type'] === 'bearer') {
                        return true;
                    }
                }
                return false;
            }),
            new TwigFilter('caseNamespace', function ($value) {
                $segments = explode('\\', $value);
                $segments = array_map(function ($segment) {
                    return ucfirst($this->toCamelCase($segment));
                }, $segments);
                return implode('\\', $segments);
            }),
            new TwigFilter('caseNamespacePath', function ($value) {
                $segments = explode('\\', $value);
                $segments = array_map(function ($segment) {
                    return ucfirst($this->toCamelCase($segment));
                }, $segments);
                return implode('/', $segments);
            }),
            new TwigFilter('escapeJson', function ($value) {
                // Escape backslashes for JSON strings
                return str_replace('\\', '\\\\', $value);
            }),
            new TwigFilter('enumExample', function (array $param) {
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
                        $cleaned = \preg_replace('/[^a-zA-Z0-9]/', '', $enumKeys[$index]);
                        return $this->toUpperSnakeCase($cleaned);
                    }
                    if ($index !== false && isset($enumValues[$index])) {
                        $cleaned = \preg_replace('/[^a-zA-Z0-9]/', '', $enumValues[$index]);
                        return $this->toUpperSnakeCase($cleaned);
                    }
                    $fallback = $enumKeys[0] ?? $enumValues[0] ?? $value;
                    $cleaned = \preg_replace('/[^a-zA-Z0-9]/', '', (string)$fallback);
                    return $this->toUpperSnakeCase($cleaned);
                };

                if ($isArray) {
                    $values = [];
                    if (\is_string($example) && $example !== '') {
                        $decoded = json_decode($example, true);
                        if (\is_array($decoded)) {
                            $values = $decoded;
                        }
                    } elseif (\is_array($example)) {
                        $values = $example;
                    }

                    if (empty($values)) {
                        $values = [$enumValues[0]];
                    }

                    $items = array_map(function ($value) use ($enumName, $resolveKey) {
                        return $enumName . '::' . $resolveKey($value) . '()';
                    }, $values);

                    return '[' . implode(', ', $items) . ']';
                }

                $value = ($example !== null && $example !== '') ? $example : $enumValues[0];
                return $enumName . '::' . $resolveKey($value) . '()';
            }),
        ];
    }
}
