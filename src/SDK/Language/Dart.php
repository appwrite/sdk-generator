<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Dart extends Language
{
    /**
     * @var array
     */
    protected $params = [
        'packageName' => 'packageName',
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setPackageName(string $name): self
    {
        $this->setParam('packageName', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Dart';
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

    /**
     * @return array
     */
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

    /**
     * @param array $parameter
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        if (isset($parameter['enumName'])) {
            return 'enums.' . \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return 'enums.' . \ucfirst($parameter['name']);
        }
        switch ($parameter['type'] ?? '') {
            case self::TYPE_INTEGER:
                return 'int';
            case self::TYPE_STRING:
                return 'String';
            case self::TYPE_FILE:
            case self::TYPE_PAYLOAD:
                return 'Payload';
            case self::TYPE_BOOLEAN:
                return 'bool';
            case self::TYPE_ARRAY:
                if (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type'])) {
                    return 'List<' . $this->getTypeName($parameter['array']) . '>';
                }
                return 'List';
            case self::TYPE_OBJECT:
                return 'Map';
            case self::TYPE_NUMBER:
                return 'double';
            default:
                return $parameter['type'];
        }
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
                case self::TYPE_FILE:
                case self::TYPE_PAYLOAD:
                    $output .= 'Payload';
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
                    $output .= "Payload.fromFile('../../resources/file.png')";
                    break;
                case self::TYPE_PAYLOAD:
                    $output .= "Payload.fromJson({ 'x': 'y' })";
                    break;
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= '0';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
                case self::TYPE_OBJECT:
                    $output .= '{}';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_OBJECT:
                case self::TYPE_FILE:
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                    $output .= $example;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$example}'";
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
                'destination'   => '/lib/services/{{service.name | caseDash}}.dart',
                'template'      => 'dart/lib/services/service.dart.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/lib/src/models/{{definition.name | caseSnake }}.dart',
                'template'      => 'dart/lib/src/models/model.dart.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'dart/docs/example.md.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/test/services/{{service.name | caseDash}}_test.dart',
                'template'      => 'dart/test/services/service_test.dart.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/test/src/models/{{definition.name | caseSnake }}_test.dart',
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
                'destination'   => '/test/src/payload_test.dart',
                'template'      => 'dart/test/src/payload_test.dart.twig',
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
                'destination'   => 'lib/payload.dart',
                'template'      => 'dart/lib/payload.dart.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'lib/src/enums/{{ enum.name | caseSnake }}.dart',
                'template'      => 'dart/lib/src/enums/enum.dart.twig',
            ],
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('dartComment', function ($value) {
                $value = explode("\n", $value);
                foreach ($value as $key => $line) {
                    $value[$key] = "    /// " . wordwrap($value[$key], 75, "\n    /// ");
                }
                return implode("\n", $value);
            }, ['is_safe' => ['html']]),
            new TwigFilter('caseEnumKey', function (string $value) {
                return $this->toCamelCase($value);
            }),
        ];
    }
}
