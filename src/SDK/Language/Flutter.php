<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class Flutter extends Language {

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
        return 'Flutter';
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
        ];
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
                return 'MultipartFile';
            break;
            case self::TYPE_BOOLEAN:
                return 'bool';
            break;
            case self::TYPE_ARRAY:
            	return 'List';
			case self::TYPE_OBJECT:
				return 'dynamic';
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
                    $output .= '{}';
                    break;
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= '0';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
            }
        }
        else {
            switch ($type) {
                case self::TYPE_OBJECT:
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
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
                    $output .= 'await MultipartFile.fromFile(\'./path-to-files/image.jpg\', \'image.jpg\')';
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
                'template'      => '/flutter/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/README.md',
                'template'      => '/flutter/example/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => '/flutter/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => '/flutter/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/client.dart',
                'template'      => '/flutter/lib/client.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/{{ language.params.packageName }}.dart',
                'template'      => '/flutter/lib/package.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/pubspec.yaml',
                'template'      => '/flutter/pubspec.yaml.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/service.dart',
                'template'      => '/flutter/lib/service.dart.twig',
                'minify'        => false,
            ],
            [
				'scope'         => 'default',
				'destination'   => '/lib/enums.dart',
				'template'      => '/flutter/lib/enums.dart.twig',
				'minify'        => false,
			],
            [
                'scope'         => 'service',
                'destination'   => '/lib/services/{{service.name | caseDash}}.dart',
                'template'      => '/flutter/lib/services/service.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => '/flutter/docs/example.md.twig',
                'minify'        => false,
            ],
        ];
    }
}

