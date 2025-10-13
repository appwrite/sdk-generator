<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DotNet extends Language
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'DotNet';
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

    /**
     * @return array
     */
    public function getIdentifierOverrides(): array
    {
        return [
            'Jwt' => 'JWT',
            'Domain' => 'XDomain',
        ];
    }

    public function getPropertyOverrides(): array
    {
        return [
            'provider' => [
                'Provider' => 'MessagingProvider',
            ],
        ];
    }

    /**
     * @param array $parameter
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        if (isset($parameter['enumName'])) {
            return 'Appwrite.Enums.' . \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return 'Appwrite.Enums.' . \ucfirst($parameter['name']);
        }
        if (isset($parameter['items'])) {
            // Map definition nested type to parameter nested type
            $parameter['array'] = $parameter['items'];
        }
        return match ($parameter['type']) {
            self::TYPE_INTEGER => 'long',
            self::TYPE_NUMBER => 'double',
            self::TYPE_STRING => 'string',
            self::TYPE_BOOLEAN => 'bool',
            self::TYPE_FILE => 'InputFile',
            self::TYPE_ARRAY => (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type']))
                ? 'List<' . $this->getTypeName($parameter['array']) . '>'
                : 'List<object>',
            self::TYPE_OBJECT => 'object',
            default => $parameter['type']
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
                    if (\str_starts_with($example, '[')) {
                        $example = \substr($example, 1);
                    }
                    if (\str_ends_with($example, ']')) {
                        $example = \substr($example, 0, -1);
                    }
                    if (!empty($example)) {
                        $output .= 'new List<' . $this->getTypeName($param['array']) . '>() {' . $example . '}';
                    } else {
                        $output .= 'new List<' . $this->getTypeName($param['array']) . '>()';
                    }
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_FILE:
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                    $output .= $example;
                    break;
                case self::TYPE_OBJECT:
                    if ($example === '{}') {
                        $output .= '[object]';
                    } else {
                        $decoded = json_decode($example, true);
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

    /**
     * @return array
     */
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
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
                'template'      => 'dotnet/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}.sln',
                'template'      => 'dotnet/Package.sln',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/{{ spec.title | caseUcfirst }}.csproj',
                'template'      => 'dotnet/Package/Package.csproj.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/Client.cs',
                'template'      => 'dotnet/Package/Client.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/{{ spec.title | caseUcfirst }}Exception.cs',
                'template'      => 'dotnet/Package/Exception.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/ID.cs',
                'template'      => 'dotnet/Package/ID.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/Permission.cs',
                'template'      => 'dotnet/Package/Permission.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/Query.cs',
                'template'      => 'dotnet/Package/Query.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/Role.cs',
                'template'      => 'dotnet/Package/Role.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/Converters/ValueClassConverter.cs',
                'template'      => 'dotnet/Package/Converters/ValueClassConverter.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/Converters/ObjectToInferredTypesConverter.cs',
                'template'      => 'dotnet/Package/Converters/ObjectToInferredTypesConverter.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/Extensions/Extensions.cs',
                'template'      => 'dotnet/Package/Extensions/Extensions.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/Models/OrderType.cs',
                'template'      => 'dotnet/Package/Models/OrderType.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/Models/UploadProgress.cs',
                'template'      => 'dotnet/Package/Models/UploadProgress.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/Models/InputFile.cs',
                'template'      => 'dotnet/Package/Models/InputFile.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/Services/Service.cs',
                'template'      => 'dotnet/Package/Services/Service.cs.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '{{ spec.title | caseUcfirst }}/Services/{{service.name | caseUcfirst}}.cs',
                'template'      => 'dotnet/Package/Services/ServiceTemplate.cs.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '{{ spec.title | caseUcfirst }}/Models/{{ definition.name | caseUcfirst | overrideIdentifier }}.cs',
                'template'      => 'dotnet/Package/Models/Model.cs.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => '{{ spec.title | caseUcfirst }}/Enums/{{ enum.name | caseUcfirst | overrideIdentifier }}.cs',
                'template'      => 'dotnet/Package/Enums/Enum.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseUcfirst }}/Enums/IEnum.cs',
                'template'      => 'dotnet/Package/Enums/IEnum.cs.twig',
            ]
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('dotnetComment', function ($value) {
                $value = explode("\n", $value);
                foreach ($value as $key => $line) {
                    $value[$key] = "        /// " . wordwrap($line, 75, "\n        /// ");
                }
                return implode("\n", $value);
            }, ['is_safe' => ['html']]),
            new TwigFilter('caseEnumKey', function (string $value) {
                return $this->toPascalCase($value);
            }),
            new TwigFilter('overrideProperty', function (string $property, string $class) {
                if (isset($this->getPropertyOverrides()[$class][$property])) {
                    return $this->getPropertyOverrides()[$class][$property];
                }
                return $property;
            }),
        ];
    }

    /**
     * get sub_scheme, property_name and parse_value functions
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sub_schema', function (array $property) {
                $result = '';

                if (isset($property['sub_schema']) && !empty($property['sub_schema'])) {
                    if ($property['type'] === 'array') {
                        $result = 'List<' . $this->toPascalCase($property['sub_schema']) . '>';
                    } else {
                        $result = $this->toPascalCase($property['sub_schema']);
                    }
                } elseif (isset($property['enum']) && !empty($property['enum'])) {
                    $enumName = $property['enumName'] ?? $property['name'];
                    $result = $this->toPascalCase($enumName);
                } else {
                    $result = $this->getTypeName($property);
                }

                if (!($property['required'] ?? true)) {
                    $result .= '?';
                }

                return $result;
            }, ['is_safe' => ['html']]),
            new TwigFunction('property_name', function (array $definition, array $property) {
                $name = $property['name'];
                $name = \str_replace('$', '', $name);
                $name = $this->toPascalCase($name);
                if (\in_array($name, $this->getKeywords())) {
                    $name = '@' . $name;
                }
                return $name;
            }),
            new TwigFunction('parse_value', function (array $property, string $mapAccess, string $v) {
                $required = $property['required'] ?? false;

                // Handle sub_schema
                if (isset($property['sub_schema']) && !empty($property['sub_schema'])) {
                    $subSchema = \ucfirst($property['sub_schema']);

                    if ($property['type'] === 'array') {
                        $src = $required ? $mapAccess : $v;
                        return "{$src}.ToEnumerable().Select(it => {$subSchema}.From(map: (Dictionary<string, object>)it)).ToList()";
                    } else {
                        if ($required) {
                            return "{$subSchema}.From(map: (Dictionary<string, object>){$mapAccess})";
                        }
                        return "({$v} as Dictionary<string, object>) is { } obj ? {$subSchema}.From(map: obj) : null";
                    }
                }

                // Handle enum
                if (isset($property['enum']) && !empty($property['enum'])) {
                    $enumName = $property['enumName'] ?? $property['name'];
                    $enumClass = \ucfirst($enumName);

                    if ($required) {
                        return "new {$enumClass}({$mapAccess}.ToString())";
                    }
                    return "{$v} == null ? null : new {$enumClass}({$v}.ToString())";
                }

                // Handle arrays
                if ($property['type'] === 'array') {
                    $itemsType = $property['items']['type'] ?? 'object';
                    $src = $required ? $mapAccess : $v;

                    $selectExpression = match ($itemsType) {
                        'string' => 'x.ToString()',
                        'integer' => 'Convert.ToInt64(x)',
                        'number' => 'Convert.ToDouble(x)',
                        'boolean' => '(bool)x',
                        default => 'x'
                    };

                    return "{$src}.ToEnumerable().Select(x => {$selectExpression}).ToList()";
                }

                // Handle integer/number
                if ($property['type'] === 'integer' || $property['type'] === 'number') {
                    $convertMethod = $property['type'] === 'integer' ? 'Int64' : 'Double';

                    if ($required) {
                        return "Convert.To{$convertMethod}({$mapAccess})";
                    }
                    return "{$v} == null ? null : Convert.To{$convertMethod}({$v})";
                }

                // Handle boolean
                if ($property['type'] === 'boolean') {
                    $typeName = $this->getTypeName($property);

                    if ($required) {
                        return "({$typeName}){$mapAccess}";
                    }
                    return "({$typeName}?){$v}";
                }

                // Handle string type
                if ($required) {
                    return "{$mapAccess}.ToString()";
                }
                return "{$v}?.ToString()";
            }, ['is_safe' => ['html']]),
        ];
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
                $items = array_map(fn($item) => $this->formatCSharpValue($item, $indentLevel), $value);
                return 'new[] { ' . implode(', ', $items) . ' }';
            }
        } else {
            return 'null';
        }
    }
}
