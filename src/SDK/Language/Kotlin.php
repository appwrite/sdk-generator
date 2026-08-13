<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\Schema\ArraySchema;
use Utopia\OpenAPI\Model\Schema\ObjectSchema;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema\Schema;
use Utopia\OpenAPI\Specification;
use Override;
use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Kotlin extends Language
{
    public function getName(): string
    {
        return 'Kotlin';
    }

    /**
     * Get Language Keywords List
     */
    public function getKeywords(): array
    {
        return [
            "abstract",
            "actual",
            "annotation",
            "as",
            "assert",
            "break",
            "case",
            "catch",
            "class",
            "companion",
            "const",
            "constructor",
            "continue",
            "crossinline",
            "delegate",
            "do",
            "dynamic",
            "else",
            "enum",
            "expect",
            "field",
            "final",
            "finally",
            "for",
            "fun",
            "if",
            "import",
            "in",
            "inner",
            "infix",
            "init",
            "inline",
            "interface",
            "internal",
            "is",
            "it",
            "lateinit",
            "noinine",
            "object",
            "open",
            "operator",
            "out",
            "override",
            "package",
            "protected",
            "private",
            "public",
            "reified",
            "return",
            "sealed",
            "suspend",
            "super",
            "switch",
            "synchronized",
            "tailrec",
            "this",
            "throw",
            "transient",
            "try",
            "typealias",
            "vararg",
            "when",
            "where",
            "while",
            "path"
        ];
    }

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
        return 'listOf(' . $elements . ')';
    }

    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        $schema = $this->getSchema($parameter);
        $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
        if ($enumSchema->enum !== []) {
            $type = 'io.appwrite.enums.' . $this->toPascalCase($this->getSchemaEnumName($parameter, $spec));
            return $schema instanceof ArraySchema ? 'List<' . $type . '>' : $type;
        }

        $model = $this->getSchemaModel($parameter);
        if ($model !== null) {
            $type = 'io.appwrite.models.' . $this->toPascalCase($model);
            return $schema instanceof ArraySchema ? 'List<' . $type . '>' : $type;
        }
        return match ($this->getSchemaType($parameter)) {
            self::TYPE_INTEGER => 'Long',
            self::TYPE_NUMBER => 'Double',
            self::TYPE_STRING => 'String',
            self::TYPE_FILE => 'InputFile',
            self::TYPE_BOOLEAN => 'Boolean',
            self::TYPE_ARRAY => 'List<' . $this->getTypeName($this->getArraySchema($parameter) ?? $schema) . '>',
            self::TYPE_OBJECT => 'Any',
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
                    $output .= '-1';
                    break;
                case self::TYPE_NUMBER:
                    $output .= '1.0';
                    break;
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= 'null';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
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
                    $output .= 'null';
                    break;
            }
        }

        return $output;
    }

    /**
     * @param string $lang Language variant: 'kotlin' (default) or 'java'
     */
    public function getParamExample(Schema|Parameter $param, string $lang = 'kotlin'): string
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
                    $output .= '0';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"\"";
                    break;
                case self::TYPE_OBJECT:
                    $output .= $lang === 'java'
                        ? 'Map.of("a", "b")'
                        : 'mapOf( "a" to "b" )';
                    break;
                case self::TYPE_ARRAY:
                    $output .= $lang === 'java' ? 'List.of()' : 'listOf()';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_OBJECT:
                    $decoded = json_decode((string) $example, true);
                    if ($decoded && is_array($decoded)) {
                        if ($lang === 'java') {
                            $output .= $this->getJavaMapExample($decoded);
                        } else {
                            $output .= $this->getKotlinMapExample($decoded);
                        }
                    } else {
                        $output .= $lang === 'java'
                            ? 'Map.of("a", "b")'
                            : 'mapOf( "a" to "b" )';
                    }
                    break;
                case self::TYPE_FILE:
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $example;
                    break;
                case self::TYPE_ARRAY:
                    if ($this->isPermissionString($example)) {
                        $output .= $this->getPermissionExample($example, $lang);
                    } else {
                        $output .= $this->getArrayExample($example, $lang);
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
     * Generate Kotlin-style map initialization
     *
     * @param int $indentLevel Indentation level for nested maps
     */
    protected function getKotlinMapExample(array $data, int $indentLevel = 0): string
    {
        $mapEntries = [];
        $baseIndent = str_repeat('    ', $indentLevel + 2);

        foreach ($data as $key => $value) {
            $formattedKey = '"' . $key . '"';
            if (is_string($value)) {
                $formattedValue = '"' . $value . '"';
            } elseif (is_bool($value)) {
                $formattedValue = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $formattedValue = 'null';
            } elseif (is_array($value)) {
                // Check if it's an associative array (object) or indexed array
                $isObject = !array_is_list($value);
                if ($isObject) {
                    $formattedValue = $this->getKotlinMapExample($value, $indentLevel + 1);
                } else {
                    $formattedValue = $this->getArrayExample(json_encode($value), 'kotlin');
                }
            } else {
                $formattedValue = (string)$value;
            }
            $mapEntries[] = $baseIndent . $formattedKey . ' to ' . $formattedValue;
        }

        if (count($mapEntries) > 0) {
            $closeIndent = str_repeat('    ', $indentLevel + 1);
            return "mapOf(\n" . implode(",\n", $mapEntries) . "\n" . $closeIndent . ")";
        } else {
            return 'mapOf( "a" to "b" )';
        }
    }

    /**
     * Generate Java-style map initialization using Map.of()
     *
     * @param int $indentLevel Indentation level for nested maps
     */
    protected function getJavaMapExample(array $data, int $indentLevel = 0): string
    {
        $mapEntries = [];
        $baseIndent = str_repeat('    ', $indentLevel + 2);

        foreach ($data as $key => $value) {
            $formattedKey = '"' . $key . '"';
            if (is_string($value)) {
                $formattedValue = '"' . $value . '"';
            } elseif (is_bool($value)) {
                $formattedValue = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $formattedValue = 'null';
            } elseif (is_array($value)) {
                // Check if it's an associative array (object) or indexed array
                $isObject = !array_is_list($value);
                if ($isObject) {
                    $formattedValue = $this->getJavaMapExample($value, $indentLevel + 1);
                } else {
                    $formattedValue = $this->getArrayExample(json_encode($value), 'java');
                }
            } else {
                $formattedValue = (string)$value;
            }
            $mapEntries[] = $baseIndent . $formattedKey . ', ' . $formattedValue;
        }

        if (count($mapEntries) > 0) {
            $closeIndent = str_repeat('    ', $indentLevel + 1);
            return "Map.of(\n" . implode(",\n", $mapEntries) . "\n" . $closeIndent . ")";
        } else {
            return 'Map.of("a", "b")';
        }
    }

    /**
     * Generate array example for the given language
     *
     * @param string $example Array example like '[1, 2, 3]' or '[{"key": "value"}]'
     * @param string $lang Language variant: 'kotlin' or 'java'
     */
    protected function getArrayExample(string $example, string $lang = 'kotlin'): string
    {
        // Try to decode as JSON to handle arrays of objects
        $decoded = json_decode($example, true);
        if ($decoded && is_array($decoded)) {
            $arrayItems = [];
            foreach ($decoded as $item) {
                if (is_array($item)) {
                    // Check if it's an associative array (object) or indexed array (nested array)
                    $isObject = !array_is_list($item);
                    if ($isObject) {
                        // It's an object/map, convert it
                        $arrayItems[] = $lang === 'java' ? $this->getJavaMapExample($item) : $this->getKotlinMapExample($item);
                    } else {
                        // It's a nested array, recursively convert it
                        $arrayItems[] = $this->getArrayExample(json_encode($item), $lang);
                    }
                } elseif (is_string($item)) {
                    // Primitive value
                    $arrayItems[] = '"' . $item . '"';
                } elseif (is_bool($item)) {
                    $arrayItems[] = $item ? 'true' : 'false';
                } elseif (is_null($item)) {
                    $arrayItems[] = 'null';
                } else {
                    $arrayItems[] = (string)$item;
                }
            }
            return $lang === 'java'
                ? 'List.of(' . implode(', ', $arrayItems) . ')'
                : 'listOf(' . implode(', ', $arrayItems) . ')';
        }

        // Fallback to old behavior for non-JSON arrays
        if (\str_starts_with($example, '[')) {
            $example = \substr($example, 1);
        }
        if (\str_ends_with($example, ']')) {
            $example = \substr($example, 0, -1);
        }
        return $lang === 'java'
            ? 'List.of(' . $example . ')'
            : 'listOf(' . $example . ')';
    }

    /**
     * Generate permission example for the given language
     *
     * @param string $example Permission string like '["read(\"any\")"]'
     * @param string $lang Language variant: 'kotlin' or 'java'
     */
    #[Override]
    public function getPermissionExample(string $example, string $lang = 'kotlin'): string
    {
        $permissions = [];
        $staticOp = $this->getStaticAccessOperator();
        $quote = $this->getStringQuote();
        $prefix = $this->getPermissionPrefix();

        foreach ($this->extractPermissionParts($example) as $permission) {
            $args = [];
            if ($permission['id'] !== null) {
                $args[] = $quote . $permission['id'] . $quote;
            }
            if ($permission['innerRole'] !== null) {
                $args[] = $quote . $permission['innerRole'] . $quote;
            }
            $argsString = implode(', ', $args);

            $action = $permission['action'];
            $role = $permission['role'];
            $action = $this->transformPermissionAction($action);
            $role = $this->transformPermissionRole($role);

            $permissions[] = $prefix . 'Permission' . $staticOp . $action . '(' . $prefix . 'Role' . $staticOp . $role . '(' . $argsString . '))';
        }

        $permissionsString = implode(', ', $permissions);

        // For Java, use List.of() instead of listOf()
        if ($lang === 'java') {
            return 'List.of(' . $permissionsString . ')';
        }
        return 'listOf(' . $permissionsString . ')';
    }

    public function getFiles(): array
    {
        return [
            // Config for root project
            [
                'scope'         => 'copy',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => '/kotlin/.github/workflows/publish.yml',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/kotlin/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => '/kotlin/docs/kotlin/example.md.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/java/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => '/kotlin/docs/java/example.md.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradle/wrapper/gradle-wrapper.jar',
                'template'      => 'kotlin/gradle/wrapper/gradle-wrapper.jar',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradle/wrapper/gradle-wrapper.properties',
                'template'      => '/kotlin/gradle/wrapper/gradle-wrapper.properties',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.gitignore',
                'template'      => '/kotlin/.gitignore',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'build.gradle.kts',
                'template'      => '/kotlin/build.gradle.kts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => '/kotlin/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradle.properties',
                'template'      => '/kotlin/gradle.properties',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradlew',
                'template'      => '/kotlin/gradlew',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradlew.bat',
                'template'      => '/kotlin/gradlew.bat',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE.md',
                'template'      => '/kotlin/LICENSE.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => '/kotlin/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'settings.gradle.kts',
                'template'      => '/kotlin/settings.gradle.kts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/Client.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/Client.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/Permission.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/Permission.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/Role.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/Role.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/ID.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/ID.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/Query.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/Query.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/Operator.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/Operator.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/coroutines/Callback.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/coroutines/Callback.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/exceptions/{{spec.info.title | caseUcfirst}}Exception.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/exceptions/Exception.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/extensions/JsonExtensions.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/extensions/JsonExtensions.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/extensions/TypeExtensions.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/extensions/TypeExtensions.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/services/Service.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/services/Service.kt.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/services/{{service.name | caseUcfirst}}.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/services/ServiceTemplate.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/models/InputFile.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/models/InputFile.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/models/UploadProgress.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/models/UploadProgress.kt.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/models/{{ definitionName | caseUcfirst }}.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/models/Model.kt.twig',
            ],
            [
                'scope'         => 'requestModel',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/models/{{ requestModelName | caseUcfirst }}.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/models/RequestModel.kt.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/enums/{{ enum.title | caseUcfirst }}.kt',
                'template'      => '/kotlin/src/main/kotlin/io/appwrite/enums/Enum.kt.twig',
            ],
        ];
    }

    protected function getPropertyAssignment(Schema $property, Specification $spec): string
    {
        $propertyName = '';
        $required = false;
        foreach ($spec->schemas as $model) {
            if (!$model instanceof ObjectSchema) {
                continue;
            }
            foreach ($model->properties as $name => $candidate) {
                if ($candidate === $property) {
                    $propertyName = $name;
                    $required = \in_array($name, $model->required, true);
                    break 2;
                }
            }
        }

        $mapKey = 'map["' . \str_replace('$', '\\$', $propertyName) . '"]';
        $model = $this->getSchemaModel($property);
        if ($model !== null) {
            $class = $this->toPascalCase($model);
            $nestedType = $this->hasGenericSchemaType($model, $spec) ? ', nestedType' : '';
            if ($property instanceof ArraySchema) {
                $cast = $required ? 'as' : 'as?';
                $safeCall = $required ? '' : '?';
                return '(' . $mapKey . ' ' . $cast . ' List<Map<String, Any>>)' . $safeCall
                    . '.map { ' . $class . '.from(map = it' . $nestedType . ') }';
            }
            if (!$required) {
                return '(' . $mapKey . ' as? Map<String, Any>)?.let { '
                    . $class . '.from(map = it' . $nestedType . ') }';
            }
            return $class . '.from(map = ' . $mapKey . ' as Map<String, Any>' . $nestedType . ')';
        }

        $enumSchema = $property instanceof ArraySchema ? $property->items : $property;
        if ($enumSchema->enum !== []) {
            $enumClass = $this->toPascalCase($this->getSchemaEnumName($property, $spec));
            if ($property instanceof ArraySchema) {
                $cast = $required ? 'as' : 'as?';
                $safeCall = $required ? '' : '?';
                return '(' . $mapKey . ' ' . $cast . ' List<String>)' . $safeCall
                    . '.map { value -> ' . $enumClass . '.values().first { it.value == value } }';
            }
            return $enumClass . '.values().find { it.value == '
                . ($required ? $mapKey . ' as String' : '(' . $mapKey . ' as? String)') . ' }'
                . ($required ? '!!' : '');
        }

        $nullable = $required ? '' : '?';
        return match ($this->getSchemaType($property)) {
            self::TYPE_INTEGER => '(' . $mapKey . ' as' . $nullable . ' Number)' . ($required ? '' : '?') . '.toLong()',
            self::TYPE_NUMBER => '(' . $mapKey . ' as' . $nullable . ' Number)' . ($required ? '' : '?') . '.toDouble()',
            default => $mapKey . ' as' . $nullable . ' ' . $this->getTypeName($property, $spec),
        };
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('returnType', function (Operation $method, Specification $spec, string $namespace, string $generic = 'T'): string {
                $methodType = $method->extensions['x-appwrite']['type'] ?? '';
                if ($methodType === 'webAuth') {
                    return 'String';
                }
                if ($methodType === 'location') {
                    return 'ByteArray';
                }

                $models = \array_values(\array_filter(
                    $this->getOperationResponseModels($method),
                    static fn(string $model): bool => $model !== 'any',
                ));
                if (\count($models) !== 1) {
                    return 'Any';
                }
                $type = $namespace . '.models.' . $this->toPascalCase($models[0]);
                return $this->hasGenericSchemaType($models[0], $spec) ? $type . '<' . $generic . '>' : $type;
            }),
            new TwigFilter('modelType', function (Schema $property, Specification $spec, string $generic = 'T'): string {
                $name = $this->getSpecificationSchemaName($property, $spec);
                $type = $this->toPascalCase($name);
                return $this->hasGenericSchemaType($name, $spec) ? $type . '<' . $generic . '>' : $type;
            }),
            new TwigFilter('propertyType', function (Schema $property, Specification $spec, string $generic = 'T'): string {
                $type = $this->getTypeName($property, $spec);
                $model = $this->getSchemaModel($property);
                if ($this->hasGenericSchemaType($model, $spec)) {
                    $modelType = 'io.appwrite.models.' . $this->toPascalCase((string) $model);
                    $type = \str_replace($modelType, $modelType . '<' . $generic . '>', $type);
                }
                return $this->isSpecificationSchemaRequired($property, $spec) ? $type : $type . '?';
            }),
            new TwigFilter('hasGenericType', fn(string $model, Specification $spec): bool => $this->hasGenericSchemaType($model, $spec)),
            new TwigFilter('caseEnumKey', function (string $value): string {
                if (isset($this->getIdentifierOverrides()[$value])) {
                    $value = $this->getIdentifierOverrides()[$value];
                }
                return $this->toUpperSnakeCase($value);
            }),
            new TwigFilter('propertyAssignment', fn(Schema $property, Specification $spec): string => $this->getPropertyAssignment($property, $spec)),
            new TwigFilter('javaParamExample', fn(Schema|Parameter $param): string => $this->getParamExample($param, 'java'), ['is_safe' => ['html']]),
            new TwigFilter('enumExample', fn(Schema|Parameter $param, string $lang = 'kotlin'): string => $this->getEnumExample($param, $lang)),
            new TwigFilter('javaEnumExample', fn(Schema|Parameter $param): string => $this->getEnumExample($param, 'java')),
        ];
    }

    /**
     * Generate enum example for Kotlin/Java
     *
     * @param string $lang 'kotlin' or 'java'
     */
    protected function getEnumExample(Schema|Parameter $param, string $lang = 'kotlin'): string
    {
        $schema = $this->getSchema($param);
        $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
        $enumValues = $enumSchema->enum;
        if ($enumValues === []) {
            return '';
        }

        $enumKeys = $enumSchema->extensions['x-enum-keys'] ?? [];
        $enumName = $this->toPascalCase($enumSchema->extensions['x-enum-name'] ?? ($param instanceof Parameter ? $param->name : $enumSchema->title ?? ''));
        $example = $this->getSchemaExample($param);
        $isArray = $schema instanceof ArraySchema;

        $resolveKey = function ($value) use ($enumValues, $enumKeys): string {
            $index = array_search($value, $enumValues, true);
            if ($index !== false && isset($enumKeys[$index]) && $enumKeys[$index] !== '') {
                return $this->toUpperSnakeCase($enumKeys[$index]);
            }
            if ($index !== false && isset($enumValues[$index])) {
                return $this->toUpperSnakeCase($enumValues[$index]);
            }
            $fallback = $enumKeys[0] ?? $enumValues[0] ?? $value;
            return $this->toUpperSnakeCase((string)$fallback);
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

            $listOf = $lang === 'java' ? 'List.of' : 'listOf';
            return $listOf . '(' . implode(', ', $items) . ')';
        }

        $value = ($example !== null && $example !== '') ? $example : $enumValues[0];
        return $enumName . '.' . $resolveKey($value);
    }
}
