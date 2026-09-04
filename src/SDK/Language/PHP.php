<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\ArraySchema;
use Utopia\OpenAPI\Model\ObjectSchema;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema;
use Utopia\OpenAPI\Model\SecurityScheme;
use Utopia\OpenAPI\Model\SecuritySchemeType;
use Utopia\OpenAPI\Model\StringSchema;
use Utopia\OpenAPI\Specification;
use Override;
use Appwrite\SDK\Language;
use Twig\TwigFilter;

class PHP extends Language
{
    /**
     * @var array
     */
    #[Override]
    protected $params = [
        'composerVendor' => 'vendor-name',
        'composerPackage' => 'package-name',
    ];

    /**
     * @return $this
     */
    public function setComposerVendor(string $name): self
    {
        $this->setParam('composerVendor', $name);

        return $this;
    }

    /**
     * @return $this
     */
    public function setComposerPackage(string $name): self
    {
        $this->setParam('composerPackage', $name);

        return $this;
    }

    public function getName(): string
    {
        return 'PHP';
    }

    /**
     * Get Language Keywords List
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
                'scope'         => 'copy',
                'destination'   => 'phpstan.neon',
                'template'      => 'php/phpstan.neon',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'pint.json',
                'template'      => 'php/pint.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'rector.php',
                'template'      => 'php/rector.php',
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
                'destination'   => 'docs/examples/{{ service.name | caseLower }}/{{ (method | methodName) | caseKebab }}.md',
                'template'      => 'php/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ namespace | caseNamespacePath }}/Client.php',
                'template'      => 'php/src/Client.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ namespace | caseNamespacePath }}/ClientTest.php',
                'template'      => 'php/tests/ClientTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ namespace | caseNamespacePath }}/Permission.php',
                'template'      => 'php/src/Permission.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ namespace | caseNamespacePath }}/PermissionTest.php',
                'template'      => 'php/tests/PermissionTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ namespace | caseNamespacePath }}/Role.php',
                'template'      => 'php/src/Role.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ namespace | caseNamespacePath }}/RoleTest.php',
                'template'      => 'php/tests/RoleTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ namespace | caseNamespacePath }}/ID.php',
                'template'      => 'php/src/ID.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ namespace | caseNamespacePath }}/IDTest.php',
                'template'      => 'php/tests/IDTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ namespace | caseNamespacePath }}/Query.php',
                'template'      => 'php/src/Query.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ namespace | caseNamespacePath }}/QueryTest.php',
                'template'      => 'php/tests/QueryTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ namespace | caseNamespacePath }}/Operator.php',
                'template'      => 'php/src/Operator.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ namespace | caseNamespacePath }}/OperatorTest.php',
                'template'      => 'php/tests/OperatorTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ namespace | caseNamespacePath }}/InputFile.php',
                'template'      => 'php/src/InputFile.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ namespace | caseNamespacePath }}/{{ namespace | split(\'\\\\\') | last | caseUcfirst}}Exception.php',
                'template'      => 'php/src/Exception.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ namespace | caseNamespacePath }}/Service.php',
                'template'      => 'php/src/Service.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ namespace | caseNamespacePath }}/Models/ArraySerializable.php',
                'template'      => 'php/src/Models/ArraySerializable.php.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/src/{{ namespace | caseNamespacePath }}/Services/{{service.name | caseUcfirst}}.php',
                'template'      => 'php/src/Services/Service.php.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/tests/{{ namespace | caseNamespacePath }}/Services/{{service.name | caseUcfirst}}Test.php',
                'template'      => 'php/tests/Services/ServiceTest.php.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => '/src/{{ namespace | caseNamespacePath }}/Enums/{{ enum.title | caseUcfirst | overrideIdentifier }}.php',
                'template'      => 'php/src/Enums/Enum.php.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/src/{{ namespace | caseNamespacePath }}/Models/{{ definitionName | caseUcfirst | overrideIdentifier }}.php',
                'template'      => 'php/src/Models/Model.php.twig',
            ],
            [
                'scope'         => 'requestModel',
                'destination'   => '/src/{{ namespace | caseNamespacePath }}/Models/{{ requestModelName | caseUcfirst | overrideIdentifier }}.php',
                'template'      => 'php/src/Models/RequestModel.php.twig',
            ],
        ];
    }

    protected function normalizeNamespace(string $namespace): string
    {
        $segments = explode('\\', $namespace);
        $segments = array_map($this->toPascalCase(...), $segments);

        return implode('\\', $segments);
    }

    protected function getModelClassName(string $modelName, ?Specification $spec, bool $fullyQualified = false): string
    {
        $className = $this->applyIdentifierOverride($this->toPascalCase($modelName));

        if (!$fullyQualified) {
            return $className;
        }

        return '\\' . $this->normalizeNamespace($this->params['namespace'] ?? ($spec?->info->title ?? '')) . '\\Models\\' . $className;
    }

    protected function getResponseModels(Operation $method, ?Specification $spec): array
    {
        return \array_map(
            fn(string $model): string => $this->getModelClassName($model, $spec, true),
            \array_filter($this->getOperationResponseModels($method), static fn(string $model): bool => $model !== 'any'),
        );
    }

    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        $schema = $this->getSchema($parameter);
        if ($schema instanceof ArraySchema) {
            return 'array';
        }
        if ($this->usesEnumType($parameter)) {
            return $this->applyIdentifierOverride($this->toPascalCase($this->getSchemaEnumName($parameter, $spec)));
        }

        $model = $this->getSchemaModel($parameter);
        if ($model !== null) {
            return $this->applyIdentifierOverride($this->toPascalCase($model));
        }

        return match ($this->getSchemaType($parameter)) {
            self::TYPE_STRING => 'string',
            self::TYPE_BOOLEAN => 'bool',
            self::TYPE_NUMBER => 'float',
            self::TYPE_INTEGER => 'int',
            self::TYPE_ARRAY, self::TYPE_OBJECT => 'array',
            self::TYPE_FILE => 'InputFile',
            default => 'mixed',
        };
    }

    public function getParamDefault(Schema|Parameter $param): string
    {
        $type = $this->getSchemaType($param);
        $default = $this->getSchemaDefault($param);
        $required = $param instanceof Parameter && $param->required;

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
                    $output .= $default;
                    break;
                case self::TYPE_ARRAY:
                    $output .= \is_array($default) ? $this->jsonToAssoc($default) : (string) $default;
                    break;
                case self::TYPE_OBJECT:
                    $decoded = \is_array($default) ? $default : json_decode((string) $default, true);
                    $output .= $this->jsonToAssoc(\is_array($decoded) ? $decoded : []);
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

    public function getParamExample(Schema|Parameter $param, string $lang = ''): string
    {
        $type = $this->getSchemaType($param);
        $example = $this->getSchemaExample($param);

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
                    if ($this->isPermissionString($example)) {
                        $output .= $this->getPermissionExample($example);
                    } else {
                        $output .= \is_array($example) ? $this->jsonToAssoc($example) : (string) $example;
                    }
                    break;
                case self::TYPE_OBJECT:
                    $decoded = \is_array($example) ? $example : json_decode((string) $example, true);
                    $output .= $this->jsonToAssoc(\is_array($decoded) ? $decoded : []);
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
        if ($data === []) {
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

        return $output . ($baseIndent . '    ]');
    }

    protected function getMockDefinitionPayload(string $definitionName, Specification $spec, int $indentLevel = 2): string
    {
        $definition = $spec->schemas[$definitionName] ?? null;
        if (!$definition instanceof ObjectSchema) {
            return '[]';
        }

        $properties = \array_filter(
            $definition->properties,
            fn(Schema $property, string $name): bool => \in_array($name, $definition->required, true),
            ARRAY_FILTER_USE_BOTH,
        );

        if ($properties === []) {
            return '[]';
        }

        $itemIndent = str_repeat('    ', $indentLevel);
        $closingIndent = str_repeat('    ', max(0, $indentLevel - 1));
        $lines = ['['];

        $index = 0;
        foreach ($properties as $name => $property) {
            $lines[] = $itemIndent
                . '"' . $this->escapePhpString($name) . '" => '
                . $this->getMockPropertyValue($name, $property, $spec, $indentLevel + 1)
                . ($index < count($properties) - 1 ? ',' : '');
            $index++;
        }

        $lines[] = $closingIndent . ']';

        return implode("\n", $lines);
    }

    protected function getMockPropertyValue(string $name, Schema $property, Specification $spec, int $indentLevel): string
    {
        $model = $this->getSchemaModel($property);
        if ($model !== null) {
            if ($property instanceof ArraySchema) {
                $itemIndent = str_repeat('    ', $indentLevel);
                $closingIndent = str_repeat('    ', max(0, $indentLevel - 1));
                return "[\n" . $itemIndent . $this->getMockDefinitionPayload($model, $spec, $indentLevel + 1) . "\n" . $closingIndent . ']';
            }
            return $this->getMockDefinitionPayload($model, $spec, $indentLevel);
        }
        $enumSchema = $this->getEnumSchema($property);
        if ($this->usesEnumType($property)) {
            $example = '"' . $this->escapePhpString((string) $enumSchema->enum[0]) . '"';

            return $property instanceof ArraySchema ? '[' . $example . ']' : $example;
        }

        $example = $this->getSchemaExample($property);
        return match ($this->getSchemaType($property)) {
            self::TYPE_OBJECT, self::TYPE_ARRAY => '[]',
            self::TYPE_BOOLEAN => 'true',
            self::TYPE_INTEGER => $example === null ? '1' : $this->formatPhpLiteral($example),
            self::TYPE_NUMBER => $example === null ? '1.0' : $this->formatPhpLiteral($example),
            self::TYPE_STRING => '"' . $this->escapePhpString((string) ($example !== null && $example !== '' ? $example : '[' . strtoupper($name) . ']')) . '"',
            default => $this->formatPhpLiteral($example),
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
            return $value === [] ? '[]' : var_export($value, true);
        }

        return (string)$value;
    }

    protected function escapePhpString(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('"', '\\"', $value);

        return str_replace('$', '\\$', $value);
    }

    protected function getReturn(Operation $method, ?Specification $spec = null): string
    {
        if ((\count($method->responses) === 1 && isset($method->responses[204])) || \in_array($this->getMethodType($method, $spec), ['location', 'webAuth'], true)) {
            return 'string';
        }

        $responseModels = $this->getResponseModels($method, $spec);

        if ($responseModels !== []) {
            return implode('|', $responseModels);
        }

        return 'array';
    }

    /**
     * Generate method parameters string for PHP method signatures
     */
    protected function getMethodParameters(Operation $method): string
    {
        $params = [];

        foreach ($this->getOperationParameters($method) as $parameter) {
            $nullable = !$parameter->required || ($parameter->schema?->nullable ?? false);
            $nullablePrefix = $nullable ? '?' : '';

            $typeName = $this->getTypeName($parameter);
            $paramName = '$' . $this->escapeKeyword($this->toCamelCase($parameter->name));
            $default = $parameter->required ? '' : ' = null';

            $params[] = $nullablePrefix . $typeName . ' ' . $paramName . $default;
        }

        $result = implode(', ', $params);

        // Add onProgress callback for multipart/form-data methods
        if (isset($method->requestBody?->content['multipart/form-data'])) {
            $result .= ($result !== '' ? ', ' : '') . '?callable $onProgress = null';
        }

        return $result;
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('getReturn', fn(Operation $value, ?Specification $spec = null): string => $this->getReturn($value, $spec)),
            new TwigFilter('getResponseModels', fn(Operation $value, ?Specification $spec = null): array => $this->getResponseModels($value, $spec)),
            new TwigFilter('mockDefinitionPayload', fn(string $definitionName, Specification $spec, int $indentLevel = 2): string => $this->getMockDefinitionPayload($definitionName, $spec, $indentLevel), ['is_safe' => ['html']]),
            new TwigFilter('methodParameters', fn(Operation $value): string => $this->getMethodParameters($value)),
            new TwigFilter('deviceInfo', fn($value): string => php_uname('s') . '; ' . php_uname('v') . '; ' . php_uname('m')),
            new TwigFilter('caseEnumKey', function (string $value): string {
                if (isset($this->getIdentifierOverrides()[$value])) {
                    $value = $this->getIdentifierOverrides()[$value];
                }
                $value = \preg_replace('/[^a-zA-Z0-9]/', '', $value);
                return $this->toUpperSnakeCase($value);
            }),
            new TwigFilter('hasBearerAuth', fn(array $headers): bool => array_any($headers, static fn(SecurityScheme $header): bool => $header->type === SecuritySchemeType::HTTP && $header->scheme === 'bearer')),
            new TwigFilter('caseNamespace', function ($value): string {
                $segments = explode('\\', $value);
                $segments = array_map($this->toPascalCase(...), $segments);
                return implode('\\', $segments);
            }),
            new TwigFilter('caseNamespacePath', function ($value): string {
                $segments = explode('\\', $value);
                $segments = array_map($this->toPascalCase(...), $segments);
                return implode('/', $segments);
            }),
            new TwigFilter(
                'escapeJson',
                // Escape backslashes for JSON strings
                fn($value): string|array => str_replace('\\', '\\\\', $value)
            ),
            new TwigFilter('enumExample', function (Schema|Parameter $param): string {
                $schema = $this->getSchema($param);
                $enumSchema = $this->getEnumSchema($param);
                $enumValues = $enumSchema->enum;
                if ($enumValues === []) {
                    return '';
                }

                $enumKeys = $this->resolveEnumKeys($param);
                $enumName = $this->toPascalCase(($enumSchema instanceof StringSchema ? $enumSchema->enumName : null) ?? ($param instanceof Parameter ? $param->name : $enumSchema->title ?? ''));
                $example = $this->getSchemaExample($param);
                $isArray = $schema instanceof ArraySchema;

                $resolveKey = function ($value) use ($enumValues, $enumKeys): string {
                    $index = array_search($value, $enumValues, true);
                    if ($index !== false && isset($enumKeys[$index]) && $enumKeys[$index] !== '') {
                        $cleaned = \preg_replace('/[^a-zA-Z0-9]/', '', $enumKeys[$index]);
                        return $this->toUpperSnakeCase($cleaned);
                    }
                    if ($index !== false && isset($enumValues[$index])) {
                        $cleaned = \preg_replace('/[^a-zA-Z0-9]/', '', (string) $enumValues[$index]);
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

                    if ($values === []) {
                        $values = [$enumValues[0]];
                    }

                    $items = array_map(fn($value): string => $enumName . '::' . $resolveKey($value) . '()', $values);

                    return '[' . implode(', ', $items) . ']';
                }

                $value = ($example !== null && $example !== '') ? $example : $enumValues[0];
                return $enumName . '::' . $resolveKey($value) . '()';
            }),
        ];
    }
}
