<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Kotlin extends Language
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Kotlin';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
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

    /**
     * @return array
     */
    public function getIdentifierOverrides(): array
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
            return 'io.appwrite.enums.' . \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return 'io.appwrite.enums.' . \ucfirst($parameter['name']);
        }
        return match ($parameter['type']) {
            self::TYPE_INTEGER => 'Long',
            self::TYPE_NUMBER => 'Double',
            self::TYPE_STRING => 'String',
            self::TYPE_PAYLOAD,
            self::TYPE_FILE => 'Payload',
            self::TYPE_BOOLEAN => 'Boolean',
            self::TYPE_ARRAY => (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type']))
                ? 'List<' . $this->getTypeName($parameter['array']) . '>'
                : 'List<Any>',
            self::TYPE_OBJECT => 'Any',
            default => $parameter['type'],
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
     * @param array $param
     * @return string
     */
    public function getParamExample(array $param): string
    {
        $type = $param['type'] ?? '';
        $example = $param['example'] ?? '';

        $output = '';

        if (empty($example) && $example !== 0 && $example !== false) {
            switch ($type) {
                case self::TYPE_PAYLOAD:
                    $output .= 'payload.fromString("<BODY>")';
                    break;
                case self::TYPE_FILE:
                    $output .= 'payload.fromPath("file.png")';
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
                    $output .= 'mapOf( "a" to "b" )';
                    break;
                case self::TYPE_ARRAY:
                    $output .= 'listOf()';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_OBJECT:
                    $output .= 'mapOf( "a" to "b" )';
                    break;
                case self::TYPE_FILE:
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $example;
                    break;
                case self::TYPE_ARRAY:
                    if (\str_starts_with($example, '[')) {
                        $example = \substr($example, 1);
                    }
                    if (\str_ends_with($example, ']')) {
                        $example = \substr($example, 0, -1);
                    }
                    $output .= 'listOf(' . $example . ')';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_PAYLOAD:
                    $output .= 'payload.fromString("<BODY>")';
                    break;
                case self::TYPE_STRING:
                    $output .= '"{$example}"';
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
            // Config for root project
            [
                'scope' => 'copy',
                'destination' => '.github/workflows/publish.yml',
                'template' => '/kotlin/.github/workflows/publish.yml',
            ],
            [
                'scope' => 'method',
                'destination' => 'docs/examples/kotlin/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template' => '/kotlin/docs/kotlin/example.md.twig',
            ],
            [
                'scope' => 'method',
                'destination' => 'docs/examples/java/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template' => '/kotlin/docs/java/example.md.twig',
            ],
            [
                'scope' => 'copy',
                'destination' => 'gradle/wrapper/gradle-wrapper.jar',
                'template' => 'kotlin/gradle/wrapper/gradle-wrapper.jar',
            ],
            [
                'scope' => 'copy',
                'destination' => 'gradle/wrapper/gradle-wrapper.properties',
                'template' => '/kotlin/gradle/wrapper/gradle-wrapper.properties',
            ],
            [
                'scope' => 'copy',
                'destination' => 'scripts/configure.gradle',
                'template' => '/kotlin/scripts/configure.gradle',
            ],
            [
                'scope' => 'copy',
                'destination' => 'scripts/publish.gradle',
                'template' => '/kotlin/scripts/publish.gradle',
            ],
            [
                'scope' => 'copy',
                'destination' => 'scripts/setup.gradle',
                'template' => '/kotlin/scripts/setup.gradle',
            ],
            [
                'scope' => 'copy',
                'destination' => '.gitignore',
                'template' => '/kotlin/.gitignore',
            ],
            [
                'scope' => 'default',
                'destination' => 'build.gradle',
                'template' => '/kotlin/build.gradle.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'CHANGELOG.md',
                'template' => '/kotlin/CHANGELOG.md.twig',
            ],
            [
                'scope' => 'copy',
                'destination' => 'gradle.properties',
                'template' => '/kotlin/gradle.properties',
            ],
            [
                'scope' => 'copy',
                'destination' => 'gradlew',
                'template' => '/kotlin/gradlew',
            ],
            [
                'scope' => 'copy',
                'destination' => 'gradlew.bat',
                'template' => '/kotlin/gradlew.bat',
            ],
            [
                'scope' => 'default',
                'destination' => 'LICENSE.md',
                'template' => '/kotlin/LICENSE.md.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'README.md',
                'template' => '/kotlin/README.md.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'settings.gradle',
                'template' => '/kotlin/settings.gradle.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/Client.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/Client.kt.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/Permission.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/Permission.kt.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/Role.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/Role.kt.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/ID.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/ID.kt.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/Query.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/Query.kt.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/coroutines/Callback.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/coroutines/Callback.kt.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/exceptions/{{spec.title | caseUcfirst}}Exception.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/exceptions/Exception.kt.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/extensions/JsonExtensions.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/extensions/JsonExtensions.kt.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/extensions/TypeExtensions.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/extensions/TypeExtensions.kt.twig',
                'minify' => false,
            ],
            [
                'scope' => 'default',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/services/Service.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/services/Service.kt.twig',
            ],
            [
                'scope' => 'service',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/services/{{service.name | caseUcfirst}}.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/services/ServiceTemplate.kt.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/models/Payload.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/models/Payload.kt.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/models/UploadProgress.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/models/UploadProgress.kt.twig',
            ],
            [
                'scope' => 'definition',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/models/{{ definition.name | caseUcfirst }}.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/models/Model.kt.twig',
            ],
            [
                'scope' => 'enum',
                'destination' => '/src/main/kotlin/{{ sdk.namespace | caseSlash }}/enums/{{ enum.name | caseUcfirst }}.kt',
                'template' => '/kotlin/src/main/kotlin/io/appwrite/enums/Enum.kt.twig',
            ],
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('returnType', function (array $method, array $spec, string $namespace, string $generic = 'T') {
                return $this->getReturnType($method, $spec, $namespace, $generic);
            }),
            new TwigFilter('modelType', function (array $property, array $spec, string $generic = 'T') {
                return $this->getModelType($property, $spec, $generic);
            }),
            new TwigFilter('propertyType', function (array $property, array $spec, string $generic = 'T') {
                return $this->getPropertyType($property, $spec, $generic);
            }),
            new TwigFilter('hasGenericType', function (string $model, array $spec) {
                return $this->hasGenericType($model, $spec);
            }),
            new TwigFilter('caseEnumKey', function (string $value) {
                if (isset($this->getIdentifierOverrides()[$value])) {
                    $value = $this->getIdentifierOverrides()[$value];
                }
                return $this->toUpperSnakeCase($value);
            }),
        ];
    }

    protected function getReturnType(array $method, array $spec, string $namespace, string $generic = 'T'): string
    {
        if ($method['type'] === 'webAuth') {
            return 'String';
        }
        if ($method['type'] === 'location') {
            return 'ByteArray';
        }

        if (
            !\array_key_exists('responseModel', $method)
            || empty($method['responseModel'])
            || $method['responseModel'] === 'any'
        ) {
            return 'Any';
        }

        $ret = $this->toPascalCase($method['responseModel']);

        if ($this->hasGenericType($method['responseModel'], $spec)) {
            $ret .= '<' . $generic . '>';
        }

        return $namespace . '.models.' . $ret;
    }

    protected function getModelType(array $definition, array $spec, string $generic = 'T'): string
    {
        if ($this->hasGenericType($definition['name'], $spec)) {
            return $this->toPascalCase($definition['name']) . '<' . $generic . '>';
        }
        return $this->toPascalCase($definition['name']);
    }

    protected function getPropertyType(array $property, array $spec, string $generic = 'T'): string
    {
        if (str_contains($property['description'] ?? '', 'HTTP response body. This will return empty unless execution')) {
            return 'Payload';
        }

        if (\array_key_exists('sub_schema', $property)) {
            $type = $this->toPascalCase($property['sub_schema']);

            if ($this->hasGenericType($property['sub_schema'], $spec)) {
                $type .= '<' . $generic . '>';
            }

            if ($property['type'] === 'array') {
                $type = 'List<' . $type . '>';
            }
        } else {
            $type = $this->getTypeName($property);
        }

        if (!$property['required']) {
            $type .= '?';
        }

        return $type;
    }

    protected function hasGenericType(?string $model, array $spec): string
    {
        if (empty($model) || $model === 'any') {
            return false;
        }

        $model = $spec['definitions'][$model];

        if ($model['additionalProperties']) {
            return true;
        }

        foreach ($model['properties'] as $property) {
            if (!\array_key_exists('sub_schema', $property) || !$property['sub_schema']) {
                continue;
            }

            return $this->hasGenericType($property['sub_schema'], $spec);
        }

        return false;
    }
}
