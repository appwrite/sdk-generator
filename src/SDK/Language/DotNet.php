<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\ArraySchema;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema;
use Utopia\OpenAPI\Model\StringSchema;
use Utopia\OpenAPI\Specification;
use Override;
use Appwrite\SDK\Language;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DotNet extends Language
{
    public function getName(): string
    {
        return 'DotNet';
    }

    /**
     * Get Language Keywords List
     */
    public function getKeywords(): array
    {
        return [
            'abstract',
            'add',
            'alias',
            'as',
            'ascending',
            'async',
            'await',
            'base',
            'bool',
            'break',
            'by',
            'byte',
            'case',
            'catch',
            'char',
            'checked',
            'class',
            'const',
            'continue',
            'decimal',
            'default',
            'delegate',
            'do',
            'double',
            'descending',
            'dynamic',
            'else',
            'enum',
            'equals',
            'event',
            'explicit',
            'extern',
            'false',
            'finally',
            'fixed',
            'float',
            'for',
            'foreach',
            'from',
            'get',
            'global',
            'goto',
            'group',
            'if',
            'implicit',
            'in',
            'int',
            'interface',
            'internal',
            'into',
            'is',
            'join',
            'let',
            'lock',
            'long',
            'nameof',
            'namespace',
            'new',
            'null',
            'object',
            'on',
            'operator',
            'orderby',
            'out',
            'override',
            'params',
            'partial',
            'private',
            'protected',
            'public',
            'readonly',
            'ref',
            'remove',
            'return',
            'sbyte',
            'sealed',
            'select',
            'set',
            'short',
            'sizeof',
            'stackalloc',
            'static',
            'string',
            'struct',
            'switch',
            'this',
            'throw',
            'true',
            'try',
            'typeof',
            'uint',
            'ulong',
            'unchecked',
            'unmanaged', # Added in C# 7.3
            'unsafe',
            'ushort',
            'using',
            'using static',
            'value',
            'var',
            'virtual',
            'void',
            'volatile',
            'when',
            'where',
            'while',
            'yield',
            'path'
        ];
    }

    public function getIdentifierOverrides(): array
    {
        return [
            'Jwt' => 'JWT',
            'Domain' => 'XDomain',
        ];
    }

    public function getStaticAccessOperator(): string
    {
        return '.';
    }

    #[Override]
    public function escapeKeyword(string $value): string
    {
        if (in_array($value, $this->getKeywords())) {
            return '@' . $value;
        }

        return $value;
    }

    public function getStringQuote(): string
    {
        return '"';
    }

    public function getArrayOf(string $elements): string
    {
        return 'new List<string> { ' . $elements . ' }';
    }

    #[Override]
    protected function transformPermissionAction(string $action): string
    {
        return ucfirst($action);
    }

    #[Override]
    protected function transformPermissionRole(string $role): string
    {
        return ucfirst($role);
    }

    public function getPropertyOverrides(): array
    {
        return [
            'provider' => [
                'Provider' => 'MessagingProvider',
            ],
        ];
    }

    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        $schema = $this->getSchema($parameter);
        if ($this->getTypedEnumSchema($parameter) instanceof Schema) {
            $type = 'Appwrite.Enums.' . $this->toPascalCase($this->getSchemaEnumName($parameter, $spec));
            return $schema instanceof ArraySchema ? 'List<' . $type . '>' : $type;
        }

        $model = $this->getSchemaModel($parameter);
        if ($model !== null) {
            $type = 'Appwrite.Models.' . $this->getTypeIdentifier($model);
            return $schema instanceof ArraySchema ? 'List<' . $type . '>' : $type;
        }

        return match ($this->getSchemaType($parameter)) {
            self::TYPE_INTEGER => 'long',
            self::TYPE_NUMBER => 'double',
            self::TYPE_STRING => 'string',
            self::TYPE_BOOLEAN => 'bool',
            self::TYPE_FILE => 'InputFile',
            self::TYPE_ARRAY => $this->isUntypedNestedArray($parameter, $schema)
                ? 'List<List<object>>'
                : 'List<' . $this->getTypeName($this->getArraySchema($parameter) ?? $schema, $spec) . '>',
            self::TYPE_OBJECT => 'object',
            default => 'object',
        };
    }

    public function getParamDefault(Schema|Parameter $param): string
    {
        $type       = $this->getSchemaType($param);
        $default    = $this->getSchemaDefault($param);
        $required   = ($param instanceof Parameter && $param->required);

        if ($required) {
            return '';
        }

        $output = ' = ';

        if (empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                case self::TYPE_BOOLEAN:
                    $output .= 'null';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$default}\"";
                    break;
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= 'null';
                    break;
            }
        }

        return $output;
    }

    public function getParamExample(Schema|Parameter $param, string $lang = ''): string
    {
        $type       = $this->getSchemaType($param);
        $example    = $this->getSchemaExample($param);

        $output = '';

        if (empty($example) && $example !== 0 && $example !== false) {
            switch ($type) {
                case self::TYPE_FILE:
                    $output .= 'InputFile.FromPath("./path-to-files/image.jpg")';
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
                    $output .= '[object]';
                    break;
                case self::TYPE_ARRAY:
                    if (\is_string($example) && \str_starts_with($example, '[')) {
                        $example = \substr($example, 1);
                    }
                    if (\is_string($example) && \str_ends_with($example, ']')) {
                        $example = \substr($example, 0, -1);
                    }
                    $itemSchema = $this->getArraySchema($param) ?? $this->getSchema($param);
                    if (!empty($example)) {
                        $output .= 'new List<' . $this->getTypeName($itemSchema) . '>() {' . $example . '}';
                    } else {
                        $output .= 'new List<' . $this->getTypeName($itemSchema) . '>()';
                    }
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
                    $output .= $this->isPermissionString($example) ? $this->getPermissionExample($example) : $example;
                    break;
                case self::TYPE_OBJECT:
                    if ($example === '{}') {
                        $output .= '[object]';
                    } else {
                        $decoded = json_decode((string) $example, true);
                        if ($decoded && is_array($decoded)) {
                            $csharpObject = $this->formatCSharpAnonymousObject($decoded, 1);
                            $output .= 'new ' . $csharpObject;
                        } else {
                            $output .= 'new ' . $example;
                        }
                    }
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$example}\"";
                    break;
            }
        }

        return $output;
    }

    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'dotnet/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'dotnet/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/icon.png',
                'template'      => 'dotnet/icon.png',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'dotnet/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'dotnet/README.md.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => 'dotnet/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}.sln',
                'template'      => 'dotnet/Package.sln',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Directory.Build.props',
                'template'      => 'dotnet/Directory.Build.props.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/{{ spec.info.title | caseUcfirst }}.csproj',
                'template'      => 'dotnet/Package/Package.csproj.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Client.cs',
                'template'      => 'dotnet/Package/Client.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/{{ spec.info.title | caseUcfirst }}Exception.cs',
                'template'      => 'dotnet/Package/Exception.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/ID.cs',
                'template'      => 'dotnet/Package/ID.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Permission.cs',
                'template'      => 'dotnet/Package/Permission.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Query.cs',
                'template'      => 'dotnet/Package/Query.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Operator.cs',
                'template'      => 'dotnet/Package/Operator.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Role.cs',
                'template'      => 'dotnet/Package/Role.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Converters/ValueClassConverter.cs',
                'template'      => 'dotnet/Package/Converters/ValueClassConverter.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Converters/ObjectToInferredTypesConverter.cs',
                'template'      => 'dotnet/Package/Converters/ObjectToInferredTypesConverter.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Extensions/Extensions.cs',
                'template'      => 'dotnet/Package/Extensions/Extensions.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Models/OrderType.cs',
                'template'      => 'dotnet/Package/Models/OrderType.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Models/UploadProgress.cs',
                'template'      => 'dotnet/Package/Models/UploadProgress.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Models/InputFile.cs',
                'template'      => 'dotnet/Package/Models/InputFile.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Services/Service.cs',
                'template'      => 'dotnet/Package/Services/Service.cs.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Services/{{service.name | caseUcfirst}}.cs',
                'template'      => 'dotnet/Package/Services/ServiceTemplate.cs.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Models/{{ definitionName | caseUcfirst | overrideIdentifier }}.cs',
                'template'      => 'dotnet/Package/Models/Model.cs.twig',
            ],
            [
                'scope'         => 'requestModel',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Models/{{ requestModelName | caseUcfirst | overrideIdentifier }}.cs',
                'template'      => 'dotnet/Package/Models/RequestModel.cs.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Enums/{{ enum.title | caseUcfirst | overrideIdentifier }}.cs',
                'template'      => 'dotnet/Package/Enums/Enum.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.info.title | caseUcfirst }}/Enums/IEnum.cs',
                'template'      => 'dotnet/Package/Enums/IEnum.cs.twig',
            ]
        ];
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('caseEnumKey', fn(string $value): string => $this->toPascalCase($value)),
            new TwigFilter('overrideProperty', fn(string $property, string $class) => $this->getPropertyOverrides()[$class][$property] ?? $property),
            new TwigFilter('propertyType', fn(Schema $property, ?Specification $spec = null): string => $this->getPropertyType($property, $spec)),
            new TwigFilter('toMapValue', fn(Schema $property, string $resolvedName, Specification $spec): string => $this->getToMapExpression($property, $resolvedName, $spec), ['is_safe' => ['html']]),
            new TwigFilter('enumExample', function (Schema|Parameter $param): string {
                $schema = $this->getSchema($param);
                $enumSchema = $this->getEnumSchema($param);
                $enumValues = $enumSchema->enum;
                if ($enumValues === []) {
                    return '';
                }

                $enumKeys = $enumSchema instanceof StringSchema ? $enumSchema->enumKeys : [];
                $enumName = $this->toPascalCase(($enumSchema instanceof StringSchema ? $enumSchema->enumName : null) ?? ($param instanceof Parameter ? $param->name : $enumSchema->title ?? ''));
                $example = $this->getSchemaExample($param);
                $isArray = $schema instanceof ArraySchema;

                $resolveKey = function ($value) use ($enumValues, $enumKeys): string {
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

                    $items = array_map(fn($value): string => $enumName . '.' . $resolveKey($value), $values);

                    return 'new List<' . $enumName . '> { ' . implode(', ', $items) . ' }';
                }

                $value = ($example !== null && $example !== '') ? $example : $enumValues[0];
                return $enumName . '.' . $resolveKey($value);
            }),
        ];
    }

    /**
     * Get property type for request models
     */
    protected function getPropertyType(Schema $property, ?Specification $spec = null, bool $fullyQualified = true): string
    {
        $type = $this->getTypeName($property, $spec);
        if ($this->getSchemaModel($property) !== null) {
            $type = \str_replace('Appwrite.Models.', '', $type);
        }
        return $fullyQualified ? $type : \str_replace('Appwrite.Enums.', '', $type);
    }

    /**
     * get sub_scheme and property_name functions
     * @return TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sub_schema', function (Schema $property, bool $required, Specification $spec): string {
                $result = $this->getPropertyType($property, $spec, true);
                return $required ? $result : $result . '?';
            }, ['is_safe' => ['html']]),
            new TwigFunction('property_name', fn(string $definition, string $property): string => $this->getPropertyName($definition, $property)),
        ];
    }

    /**
     * Generate property name for C# model
     */
    protected function getPropertyName(string $definition, string $property): string
    {
        $name = $property;
        $name = \str_replace('$', '', $name);
        $name = $this->toPascalCase($name);

        // Generated models expose a static From(...) factory. A property named
        // From would collide with that member; JsonPropertyName still preserves
        // the original JSON key.
        if ($name === $this->getTypeIdentifier($definition) || $name === 'From') {
            $name = 'X' . $name;
        }

        if (\in_array($name, $this->getKeywords())) {
            $name = '@' . $name;
        }
        return $name;
    }

    protected function getTypeIdentifier(string $value): string
    {
        $value = $this->toPascalCase($value);

        return $this->getIdentifierOverrides()[$value] ?? $value;
    }

    /**
     * Generate ToMap() value expression for a property.
     *
     * The caller passes the already-resolved C# identifier (produced via the
     * same template pipeline used by the declaration and constructor). This
     * filter never derives the name itself, so there is no risk of drift
     * between the declared property and the ToMap() reference.
     *
     * @param string $resolvedName  C# identifier already produced by the template
     */
    protected function getToMapExpression(Schema $property, string $resolvedName, Specification $spec): string
    {
        $nullable = $this->isSpecificationSchemaRequired($property, $spec) ? '' : '?';

        // Sub-schema serialization stays null-safe regardless of required,
        // defensively covering callers that build a model without going
        // through the constructor.
        if ($this->getSchemaModel($property) !== null) {
            return $property instanceof ArraySchema
                ? "{$resolvedName}?.Select(it => it.ToMap()).ToList()"
                : "{$resolvedName}?.ToMap()";
        }

        if ($property->enum !== []) {
            return "{$resolvedName}{$nullable}.Value";
        }

        return $resolvedName;
    }

    /**
     * Format a PHP array as a C# anonymous object
     */
    private function formatCSharpAnonymousObject(array $data, int $indentLevel = 0): string
    {
        $propertyIndent = str_repeat('    ', $indentLevel + 1);
        $properties = [];

        foreach ($data as $key => $value) {
            $formattedValue = $this->formatCSharpValue($value, $indentLevel + 2);
            $properties[] = $propertyIndent . $key . ' = ' . $formattedValue;
        }

        if (count($properties) === 1) {
            return '{ ' . trim($properties[0]) . ' }';
        }

        $baseIndent = str_repeat('    ', $indentLevel);
        return "{\n" . implode(",\n", $properties) . "\n" . $baseIndent . "}";
    }

    /**
     * Format a value for C# anonymous object property
     */
    private function formatCSharpValue($value, int $indentLevel = 0): string
    {
        if (is_string($value)) {
            return '"' . $value . '"';
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_numeric($value)) {
            return (string) $value;
        } elseif (is_array($value)) {
            if (array_keys($value) !== range(0, count($value) - 1)) {
                return $this->formatCSharpAnonymousObject($value, $indentLevel);
            } else {
                $items = array_map(fn($item): string => $this->formatCSharpValue($item, $indentLevel), $value);
                return 'new[] { ' . implode(', ', $items) . ' }';
            }
        } else {
            return 'null';
        }
    }
}
