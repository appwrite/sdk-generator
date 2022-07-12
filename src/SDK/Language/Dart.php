<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Dart extends Language {

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
    public function setPackageName($name)
    {
        $this->setParam('packageName', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Dart';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords()
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
    public function getIdentifierOverrides()
    {
        return ['Function' => 'Func', 'default' => 'xdefault', 'required' => 'xrequired', 'async' => 'xasync'];
    }

    /**
     * @param $type
     * @return string
     */
    public function getTypeName($type)
    {
        switch ($type) {
            case self::TYPE_INTEGER:
                return 'int';
            break;
            case self::TYPE_STRING:
                return 'String';
            break;
            case self::TYPE_FILE:
                return 'InputFile';
            break;
            case self::TYPE_BOOLEAN:
                return 'bool';
            break;
            case self::TYPE_ARRAY:
            	return 'List';
	        case self::TYPE_OBJECT:
		        return 'Map';
            case self::TYPE_NUMBER:
                return 'double';
            break;
        }

        return $type;
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamDefault(array $param)
    {
        $type       = $param['type'] ?? '';
        $default    = $param['default'] ?? '';
        $required   = $param['required'] ?? '';

        if($required) {
            return '';
        }

        $output = ' = ';

        if(empty($default) && $default !== 0 && $default !== false) {
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
        }
        else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_OBJECT:
                case self::TYPE_ARRAY:
                    $output .= 'const '.$default;
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
     * @return string
     */
    public function getParamExample(array $param)
    {
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

        $output = '';

        if(empty($example) && $example !== 0 && $example !== false) {
            switch ($type) {
                case self::TYPE_FILE:
                    $output .= 'InputFile(path: \'./path-to-files/image.jpg\', filename: \'image.jpg\')';
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
        }
        else {
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
    public function getFiles()
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'dart/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/README.md',
                'template'      => 'dart/example/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'dart/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'dart/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client.dart',
                'template'      => 'dart/lib/src/client.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_base.dart',
                'template'      => 'dart/lib/src/client_base.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_browser.dart',
                'template'      => 'dart/lib/src/client_browser.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_io.dart',
                'template'      => 'dart/lib/src/client_io.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_mixin.dart',
                'template'      => 'dart/lib/src/client_mixin.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_stub.dart',
                'template'      => 'dart/lib/src/client_stub.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/exception.dart',
                'template'      => 'dart/lib/src/exception.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/upload_progress.dart',
                'template'      => 'dart/lib/src/upload_progress.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/response.dart',
                'template'      => 'dart/lib/src/response.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/query.dart',
                'template'      => 'dart/lib/query.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/{{ language.params.packageName }}.dart',
                'template'      => 'dart/lib/package.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/pubspec.yaml',
                'template'      => 'dart/pubspec.yaml.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/client_io.dart',
                'template'      => 'dart/lib/client_io.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/client_browser.dart',
                'template'      => 'dart/lib/client_browser.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/service.dart',
                'template'      => 'dart/lib/src/service.dart.twig',
                'minify'        => false,
            ],
            [
				'scope'         => 'default',
				'destination'   => '/lib/src/enums.dart',
				'template'      => 'dart/lib/src/enums.dart.twig',
				'minify'        => false,
			],
            [
				'scope'         => 'default',
				'destination'   => '/lib/models.dart',
				'template'      => 'dart/lib/models.dart.twig',
				'minify'        => false,
			],
            [
                'scope'         => 'service',
                'destination'   => '/lib/services/{{service.name | caseDash}}.dart',
                'template'      => 'dart/lib/services/service.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/lib/src/models/{{definition.name | caseSnake }}.dart',
                'template'      => 'dart/lib/src/models/model.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'dart/docs/example.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '.travis.yml',
                'template'      => 'dart/.travis.yml.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/src/input_file.dart',
                'template'      => 'dart/lib/src/input_file.dart.twig',
                'minify'        => false,
            ],
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('dartComment', function ($value) {
                $value = explode("\n", $value);
                foreach ($value as $key => $line) {
                    $value[$key] = "     /// " . wordwrap($value[$key], 75, "\n     /// ");
                }
                return implode("\n", $value);
            }, ['is_safe' => ['html']]),
        ];
    }
}
