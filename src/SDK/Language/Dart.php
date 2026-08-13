<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\Schema\ArraySchema;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema\Schema;
use Utopia\OpenAPI\Specification;
use Override;
use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Dart extends Language
{
    /**
     * @var array
     */
    #[Override]
    protected $params = [
        'packageName' => 'packageName',
    ];

    /**
     * @return $this
     */
    public function setPackageName(string $name): self
    {
        $this->setParam('packageName', $name);

        return $this;
    }

    public function getName(): string
    {
        return 'Dart';
    }

    /**
     * Get Language Keywords List
     */
    public function getKeywords(): array
    {
        return [
            "abstract",
            "dynamic",
            "implements",
            "show",
            "as",
            "else",
            "import",
            "static",
            "assert",
            "enum",
            "in",
            "super",
            "async",
            "export",
            "interface",
            "switch",
            "await",
            "extends",
            "is",
            "sync",
            "break",
            "external",
            "library",
            "this",
            "case",
            "factory",
            "mixin",
            "throw",
            "catch",
            "false",
            "new",
            "true",
            "class",
            "final",
            "null",
            "try",
            "const",
            "finally",
            "on",
            "typedef",
            "continue",
            "for",
            "operator",
            "var",
            "covariant",
            "Function",
            "part",
            "void",
            "default",
            "get",
            "rethrow",
            "while",
            "deferred",
            "hide",
            "return",
            "with",
            "do",
            "if",
            "set",
            "yield",
            "required",
            "extension",
            "late"
        ];
    }

    public function getIdentifierOverrides(): array
    {
        return [
            'Function' => 'Func',
            'default' => 'xdefault',
            'required' => 'xrequired',
            'async' => 'xasync',
            'enum' => 'xenum',
        ];
    }

    public function getStaticAccessOperator(): string
    {
        return '.';
    }

    public function getStringQuote(): string
    {
        return "'";
    }

    public function getArrayOf(string $elements): string
    {
        return '[' . $elements . ']';
    }

    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        $schema = $this->getSchema($parameter);
        $model = $this->getSchemaModel($parameter);
        if ($model !== null) {
            $type = 'models.' . $this->toPascalCase($model);
            return $schema instanceof ArraySchema ? 'List<' . $type . '>' : $type;
        }
        return match ($this->getSchemaType($parameter)) {
            self::TYPE_INTEGER => 'int',
            self::TYPE_STRING => 'String',
            self::TYPE_FILE => 'InputFile',
            self::TYPE_BOOLEAN => 'bool',
            self::TYPE_ARRAY => 'List<' . $this->getTypeName($this->getArraySchema($parameter) ?? $schema) . '>',
            self::TYPE_OBJECT => 'Map',
            self::TYPE_NUMBER => 'double',
            default => 'dynamic',
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
                case self::TYPE_OBJECT:
                    $output .= 'const {}';
                    break;
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= '0';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_ARRAY:
                    $output .= 'const []';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_OBJECT:
                case self::TYPE_ARRAY:
                    $output .= 'const ' . $default;
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
        $type       = $this->getSchemaType($param);
        $example    = $this->getSchemaExample($param);

        $hasExample = !empty($example) || $example === 0 || $example === false;

        if (!$hasExample) {
            return match ($type) {
                self::TYPE_OBJECT => '{}',
                self::TYPE_ARRAY => '[]',
                self::TYPE_BOOLEAN => 'false',
                self::TYPE_FILE => 'InputFile(path: \'./path-to-files/image.jpg\', filename: \'image.jpg\')',
                self::TYPE_INTEGER, self::TYPE_NUMBER => '0',
                self::TYPE_STRING => "''",
            };
        }

        return match ($type) {
            self::TYPE_ARRAY => $this->isPermissionString($example) ? $this->getPermissionExample($example) : $example,
            self::TYPE_FILE, self::TYPE_INTEGER, self::TYPE_NUMBER => $example,
            self::TYPE_BOOLEAN => ($example) ? 'true' : 'false',
            self::TYPE_OBJECT => ($decoded = json_decode((string) $example, true)) !== null
            ? (empty($decoded) && $example === '{}'
                ? '{}'
                : preg_replace('/\n/', "\n    ", json_encode($decoded, JSON_PRETTY_PRINT)))
            : $example,
            self::TYPE_STRING => "'{$example}'",
        };
    }

    public function getModelToMapValue(Schema $property, string $propertyName, bool $required): string
    {
        $name = $this->escapeKeyword($propertyName);
        $nullAware = $required ? '' : '?';

        if ($this->getSchemaModel($property) !== null) {
            return $property instanceof ArraySchema
                ? "{$name}{$nullAware}.map((p) => p.toMap()).toList()"
                : "{$name}{$nullAware}.toMap()";
        }

        if ($property->enum !== []) {
            return "{$name}{$nullAware}.value";
        }

        return $name;
    }

    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'dart/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/README.md',
                'template'      => 'dart/example/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'dart/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'dart/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client.dart',
                'template'      => 'dart/lib/src/client.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_base.dart',
                'template'      => 'dart/lib/src/client_base.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_browser.dart',
                'template'      => 'dart/lib/src/client_browser.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_io.dart',
                'template'      => 'dart/lib/src/client_io.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_mixin.dart',
                'template'      => 'dart/lib/src/client_mixin.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_stub.dart',
                'template'      => 'dart/lib/src/client_stub.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/exception.dart',
                'template'      => 'dart/lib/src/exception.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/upload_progress.dart',
                'template'      => 'dart/lib/src/upload_progress.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/response.dart',
                'template'      => 'dart/lib/src/response.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/permission.dart',
                'template'      => 'dart/lib/permission.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/role.dart',
                'template'      => 'dart/lib/role.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/id.dart',
                'template'      => 'dart/lib/id.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/query.dart',
                'template'      => 'dart/lib/query.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/operator.dart',
                'template'      => 'dart/lib/operator.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/{{ language.params.packageName }}.dart',
                'template'      => 'dart/lib/package.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/pubspec.yaml',
                'template'      => 'dart/pubspec.yaml.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/analysis_options.yaml',
                'template'      => 'dart/analysis_options.yaml.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/client_io.dart',
                'template'      => 'dart/lib/client_io.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/client_browser.dart',
                'template'      => 'dart/lib/client_browser.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/service.dart',
                'template'      => 'dart/lib/src/service.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/enums.dart',
                'template'      => 'dart/lib/src/enums.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/models/model.dart',
                'template'      => 'dart/lib/src/models/model_base.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/models.dart',
                'template'      => 'dart/lib/models.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/enums.dart',
                'template'      => 'dart/lib/enums.dart.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/lib/services/{{service.name | caseSnake}}.dart',
                'template'      => 'dart/lib/services/service.dart.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/lib/src/models/{{definitionName | caseSnake }}.dart',
                'template'      => 'dart/lib/src/models/model.dart.twig',
            ],
            [
                'scope'         => 'requestModel',
                'destination'   => '/lib/src/models/{{requestModelName | caseSnake }}.dart',
                'template'      => 'dart/lib/src/models/request_model.dart.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => 'dart/docs/example.md.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/test/services/{{service.name | caseSnake}}_test.dart',
                'template'      => 'dart/test/services/service_test.dart.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/test/src/models/{{definitionName | caseSnake }}_test.dart',
                'template'      => 'dart/test/src/models/model_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/id_test.dart',
                'template'      => 'dart/test/id_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/permission_test.dart',
                'template'      => 'dart/test/permission_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/query_test.dart',
                'template'      => 'dart/test/query_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/operator_test.dart',
                'template'      => 'dart/test/operator_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/role_test.dart',
                'template'      => 'dart/test/role_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/enums_test.dart',
                'template'      => 'dart/test/src/enums_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/upload_progress_test.dart',
                'template'      => 'dart/test/src/upload_progress_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/exception_test.dart',
                'template'      => 'dart/test/src/exception_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/input_file_test.dart',
                'template'      => 'dart/test/src/input_file_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/response_test.dart',
                'template'      => 'dart/test/src/response_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'dart/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/format.yml',
                'template'      => 'dart/.github/workflows/format.yml.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/test.yml',
                'template'      => 'dart/.github/workflows/test.yml',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/src/input_file.dart',
                'template'      => 'dart/lib/src/input_file.dart.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'lib/src/enums/{{ enum.title | caseSnake }}.dart',
                'template'      => 'dart/lib/src/enums/enum.dart.twig',
            ],
        ];
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('caseEnumKey', fn(string $value): string => $this->toCamelCase($value)),
            new TwigFilter('enumExample', function (Schema|Parameter $param): string {
                $schema = $this->getSchema($param);
                $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
                $enumValues = $enumSchema->enum;
                if ($enumValues === []) {
                    return '';
                }

                $enumKeys = $enumSchema->extensions['x-enum-keys'] ?? [];
                $enumName = $this->toCamelCase($enumSchema->extensions['x-enum-name'] ?? ($param instanceof Parameter ? $param->name : $enumSchema->title ?? ''));
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
                    if (is_string($example) && $example !== '') {
                        $decoded = json_decode($example, true);
                        if (is_array($decoded)) {
                            $values = $decoded;
                        }
                    } elseif (is_array($example)) {
                        $values = $example;
                    }

                    if ($values === []) {
                        $values = [$enumValues[0]];
                    }

                    $items = array_map(fn($value): string => 'enums.' . \ucfirst($enumName) . '.' . $resolveKey($value), $values);

                    return '[' . implode(', ', $items) . ']';
                }

                $value = ($example !== null && $example !== '') ? $example : $enumValues[0];
                return 'enums.' . \ucfirst($enumName) . '.' . $resolveKey($value);
            }),
            new TwigFilter('modelToMapValue', fn(Schema $property, string $name, bool $required): string => $this->getModelToMapValue($property, $name, $required), ['is_safe' => ['html']]),
        ];
    }
}
