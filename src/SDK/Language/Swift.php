<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\Schema\AnySchema;
use Utopia\OpenAPI\Model\Schema\ArraySchema;
use Utopia\OpenAPI\Model\Schema\CompositeSchema;
use Utopia\OpenAPI\Model\Schema\ObjectSchema;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema\Schema;
use Utopia\OpenAPI\Specification;
use Override;
use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Swift extends Language
{
    public function getName(): string
    {
        return 'Swift';
    }

    /**
     * Get Language Keywords List
     */
    public function getKeywords(): array
    {
        return [
            "class",
            "deinit",
            "enum",
            "extension",
            "func",
            "import",
            "init",
            "internal",
            "let",
            "operator",
            "private",
            "protocol",
            "public",
            "static",
            "struct",
            "subscript",
            "typealias",
            "var",
            "break",
            "case",
            "continue",
            "default",
            "do",
            "else",
            "fallthrough",
            "for",
            "if",
            "in",
            "return",
            "switch",
            "where",
            "while",
            "as",
            "dynamicType",
            "false",
            "is",
            "nil",
            "self",
            "super",
            "true",
            "associativity",
            "convenience",
            "dynamic",
            "didSet",
            "final",
            "get",
            "infix",
            "inout",
            "lazy",
            "left",
            "mutating",
            "none",
            "nonmutating",
            "optional",
            "override",
            "postfix",
            "precedence",
            "prefix",
            "required",
            "right",
            "set",
            "unowned",
            "weak",
            "willSet",
            "Type"
        ];
    }

    public function getIdentifierOverrides(): array
    {
        return [
            'enum' => 'xenum'
        ];
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

    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'swift/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'swift/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'swift/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Package.swift',
                'template'      => 'swift/Package.swift.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => 'swift/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Tests/{{ spec.info.title | caseUcfirst}}Tests/Tests.swift',
                'template'      => 'swift/Tests/Tests.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/Client.swift',
                'template'      => 'swift/Sources/Client.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/Models/{{ spec.info.title | caseUcfirst}}Error.swift',
                'template'      => '/swift/Sources/Models/Error.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/Models/InputFile.swift',
                'template'      => 'swift/Sources/Models/InputFile.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/Permission.swift',
                'template'      => 'swift/Sources/Permission.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/Role.swift',
                'template'      => 'swift/Sources/Role.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/ID.swift',
                'template'      => 'swift/Sources/ID.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/Query.swift',
                'template'      => 'swift/Sources/Query.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/Operator.swift',
                'template'      => 'swift/Sources/Operator.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/Models/UploadProgress.swift',
                'template'      => 'swift/Sources/Models/UploadProgress.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/JSONCodable/Codable+JSON.swift',
                'template'      => 'swift/Sources/JSONCodable/Codable+JSON.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/Extensions/Cookie+Codable.swift',
                'template'      => 'swift/Sources/Extensions/Cookie+Codable.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/Extensions/HTTPClientRequest+Cookies.swift',
                'template'      => 'swift/Sources/Extensions/HTTPClientRequest+Cookies.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/Extensions/String+MimeTypes.swift',
                'template'      => 'swift/Sources/Extensions/String+MimeTypes.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/StreamingDelegate.swift',
                'template'      => 'swift/Sources/StreamingDelegate.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/Services/Service.swift',
                'template'      => 'swift/Sources/Service.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/DeviceInfo/iOS/IOSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/iOS/IOSDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/DeviceInfo/iOS/UIDevice+ModelName.swift',
                'template'      => 'swift/Sources/DeviceInfo/iOS/UIDevice+ModelName.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/DeviceInfo/Linux/LinuxDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/Linux/LinuxDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/DeviceInfo/macOS/MacOSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/macOS/MacOSDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/DeviceInfo/watchOS/WatchOSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/watchOS/WatchOSDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/DeviceInfo/watchOS/WKInterfaceDevice+ModelName.swift',
                'template'      => 'swift/Sources/DeviceInfo/watchOS/WKInterfaceDevice+ModelName.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/DeviceInfo/macOS/CwlSysCtl.swift',
                'template'      => 'swift/Sources/DeviceInfo/macOS/CwlSysCtl.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/DeviceInfo/Windows/WindowsDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/Windows/WindowsDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/DeviceInfo/OSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/OSDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/PackageInfo/Apple/PackageInfo+Apple.swift',
                'template'      => 'swift/Sources/PackageInfo/Apple/PackageInfo+Apple.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/PackageInfo/Linux/PackageInfo+Linux.swift',
                'template'      => 'swift/Sources/PackageInfo/Linux/PackageInfo+Linux.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/PackageInfo/Windows/PackageInfo+Windows.swift',
                'template'      => 'swift/Sources/PackageInfo/Windows/PackageInfo+Windows.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/PackageInfo/OSPackageInfo.swift',
                'template'      => 'swift/Sources/PackageInfo/OSPackageInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/PackageInfo/PackageInfo.swift',
                'template'      => 'swift/Sources/PackageInfo/PackageInfo.swift',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}/Services/{{service.name | caseUcfirst}}.swift',
                'template'      => 'swift/Sources/Services/Service.swift.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}Models/{{ definitionName | caseUcfirst }}.swift',
                'template'      => '/swift/Sources/Models/Model.swift.twig',
            ],
            [
                'scope'         => 'requestModel',
                'destination'   => '/Sources/{{ spec.info.title | caseUcfirst}}Models/{{ requestModelName | caseUcfirst }}.swift',
                'template'      => '/swift/Sources/Models/RequestModel.swift.twig',
            ],
            [
                'scope' => 'enum',
                'destination' => '/Sources/{{ spec.info.title | caseUcfirst}}Enums/{{ enum.title | caseUcfirst }}.swift',
                'template' => '/swift/Sources/Enums/Enum.swift.twig',
            ]
        ];
    }

    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null, bool $isProperty = false): string
    {
        $schema = $this->getSchema($parameter);
        $prefix = $spec?->info->title ?? '';
        $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
        if ($enumSchema->enum !== []) {
            $type = $prefix . 'Enums.' . $this->toPascalCase($this->getSchemaEnumName($parameter, $spec));
            return $schema instanceof ArraySchema ? '[' . $type . ']' : $type;
        }

        $model = $this->getSchemaModel($parameter);
        if ($model !== null) {
            $type = ($isProperty ? '' : $prefix . 'Models.') . $this->toPascalCase($model);
            return $schema instanceof ArraySchema ? '[' . $type . ']' : $type;
        }
        return match ($this->getSchemaType($parameter)) {
            self::TYPE_INTEGER => 'Int',
            self::TYPE_NUMBER => 'Double',
            self::TYPE_STRING => 'String',
            self::TYPE_FILE => 'InputFile',
            self::TYPE_BOOLEAN => 'Bool',
            // A union or untyped element has no Swift spelling, so the whole
            // array degrades to AnyCodable rather than each element being
            // rendered as a dictionary.
            self::TYPE_ARRAY => $this->hasConcreteItemsType($schema)
                ? '[' . $this->getTypeName($this->getArraySchema($parameter) ?? $schema, $spec) . ']'
                : '[AnyCodable]',
            self::TYPE_OBJECT => $isProperty ? '[String: AnyCodable]' : 'Any',
            default => 'Any',
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
                case self::TYPE_NUMBER:
                    $output = "0";
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_OBJECT:
                    $output .= 'nil';
                    break;
                default:
                    echo $type;
            }
        } else {
            switch ($type) {
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_NUMBER:
                    $output .= sprintf("%.1f", $default);
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$default}\"";
                    break;
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= 'nil';
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
                    $output .= 'InputFile.fromPath("file.png")';
                    break;
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= "0";
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
                    $output .= '[:]';
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
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$example}\"";
                    break;
                case self::TYPE_OBJECT:
                    $decoded = json_decode((string) $example, true);
                    if ($decoded && is_array($decoded)) {
                        $output .= $this->jsonToSwiftDict($decoded);
                    } else {
                        $output .= '[:]';
                    }
                    break;
            }
        }

        return $output;
    }

    /**
     * Converts JSON Object To Swift Native Dictionary
     */
    protected function jsonToSwiftDict(array $data, int $indent = 0): string
    {
        if ($data === []) {
            return '[:]';
        }

        $baseIndent = str_repeat('    ', $indent);
        $itemIndent = str_repeat('    ', $indent + 1);
        $output = "[\n";

        $keys = array_keys($data);
        foreach ($keys as $index => $key) {
            $node = $data[$key];

            if (is_array($node)) {
                $value = $this->jsonToSwiftDict($node, $indent + 1);
            } elseif (is_string($node)) {
                $value = '"' . $node . '"';
            } elseif (is_bool($node)) {
                $value = $node ? 'true' : 'false';
            } elseif (is_null($node)) {
                $value = 'nil';
            } else {
                $value = $node;
            }

            $comma = ($index < count($keys) - 1) ? ',' : '';
            $output .= '    ' . $itemIndent . '"' . $key . '": ' . $value . $comma . "\n";
        }

        return $output . ('    ' . $baseIndent . ']');
    }

    public function getModelToMapValue(Schema $property, string $propertyName, bool $required): string
    {
        $name = \in_array($propertyName, $this->getKeywords(), true) ? "`{$propertyName}`" : $propertyName;
        $name = \str_replace('$', '', $name);
        $nullAware = $required ? '' : '?';

        if ($this->getSchemaModel($property) !== null) {
            return $property instanceof ArraySchema
                ? "{$name}{$nullAware}.map { \$0.toMap() }"
                : "{$name}{$nullAware}.toMap()";
        }
        if ($property->enum !== []) {
            return "{$name}{$nullAware}.rawValue";
        }
        if ($property instanceof ArraySchema && $property->items->enum !== []) {
            return "{$name}{$nullAware}.map { \$0.rawValue }";
        }
        return $name;
    }

    protected function getReturnType(Operation $method, Specification $spec, string $generic = 'T'): string
    {
        $methodType = $method->extensions['x-appwrite']['type'] ?? '';
        if ($methodType === 'webAuth') {
            return 'String?';
        }
        if ($methodType === 'location') {
            return 'ByteBuffer';
        }

        $models = \array_values(\array_filter(
            $this->getOperationResponseModels($method),
            static fn(string $model): bool => $model !== 'any',
        ));
        if (\count($models) !== 1) {
            return 'Any';
        }
        $type = $spec->info->title . 'Models.' . $this->toPascalCase($models[0]);
        return $this->hasGenericSchemaType($models[0], $spec) ? $type . '<' . $generic . '>' : $type;
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('returnType', fn(Operation $method, Specification $spec, string $generic = 'T'): string => $this->getReturnType($method, $spec, $generic)),
            new TwigFilter('modelType', function (Schema $property, Specification $spec, string $generic = 'T : Codable'): string {
                $name = $this->getSpecificationSchemaName($property, $spec);
                $type = $this->toPascalCase($name);
                return $this->hasGenericSchemaType($name, $spec) ? $type . '<' . $generic . '>' : $type;
            }),
            new TwigFilter('propertyType', function (Schema $property, Specification $spec, string $generic = 'T'): string {
                $type = $this->getTypeName($property, $spec, true);
                $model = $this->getSchemaModel($property);
                if ($model !== null && $this->hasGenericSchemaType($model, $spec)) {
                    $modelType = $this->toPascalCase($model);
                    $type = \str_replace($modelType, $modelType . '<' . $generic . '>', $type);
                }
                return $type;
            }),
            new TwigFilter('isAnyCodableArray', fn(Schema $property, Specification $spec): bool => $property instanceof ArraySchema
                && $this->getSchemaType($property->items) === self::TYPE_OBJECT
                && $this->getSchemaModel($property) === null),
            new TwigFilter('isAnyCodableObject', fn(Schema $property, Specification $spec): bool => $this->getSchemaType($property) === self::TYPE_OBJECT && $this->getSchemaModel($property) === null),
            new TwigFilter('hasGenericType', fn(string $model, Specification $spec): bool => $this->hasGenericSchemaType($model, $spec)),
            new TwigFilter('escapeSwiftKeyword', function ($value) {
                if (\in_array($value, $this->getKeywords())) {
                    return "`{$value}`";
                }
                return $value;
            }),
            new TwigFilter('caseEnumKey', function (string $value): string {
                if (isset($this->getIdentifierOverrides()[$value])) {
                    $value = $this->getIdentifierOverrides()[$value];
                }
                return $this->toCamelCase($value);
            }),
            new TwigFilter('enumExample', function (Schema|Parameter $param): string {
                $schema = $this->getSchema($param);
                $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
                $enumValues = $enumSchema->enum;
                if ($enumValues === []) {
                    return '';
                }

                $enumKeys = $enumSchema->extensions['x-enum-keys'] ?? [];
                $example = $this->getSchemaExample($param);
                $isArray = $schema instanceof ArraySchema;

                $resolveKey = function ($value) use ($enumValues, $enumKeys): string {
                    $index = array_search($value, $enumValues, true);
                    if ($index !== false && isset($enumKeys[$index]) && $enumKeys[$index] !== '') {
                        return $this->toCamelCase($enumKeys[$index]);
                    }
                    if ($index !== false && isset($enumValues[$index])) {
                        return $this->toCamelCase($enumValues[$index]);
                    }
                    $fallback = $enumKeys[0] ?? $enumValues[0] ?? $value;
                    return $this->toCamelCase((string)$fallback);
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

                    $items = array_map(fn($value): string => '.' . $resolveKey($value), $values);

                    return '[' . implode(', ', $items) . ']';
                }

                $value = ($example !== null && $example !== '') ? $example : $enumValues[0];
                return '.' . $resolveKey($value);
            }),
            new TwigFilter('modelToMapValue', fn(Schema $property, string $name, bool $required): string => $this->getModelToMapValue($property, $name, $required), ['is_safe' => ['html']]),
        ];
    }
}
