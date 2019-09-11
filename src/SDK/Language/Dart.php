<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class Dart extends Language {

    protected $params = [
        'packageName' => 'dart_appwrite',
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
                return 'File';
            break;
            case self::TYPE_BOOLEAN:
                return 'bool';
            break;
            case self::TYPE_ARRAY:
                 return 'array';
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
        $type       = (isset($param['type'])) ? $param['type'] : '';
        $default    = (isset($param['default'])) ? $param['default'] : '';
        $required   = (isset($param['required'])) ? $param['required'] : '';

        if($required) {
            return '';
        }

        $output = ' = ';

        if(empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_STRING:
                case self::TYPE_BOOLEAN:
                case self::TYPE_ARRAY:
                    $output .= 'null';
                    break;
            }
        }
        else {
            switch ($type) {
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
        $type       = (isset($param['type'])) ? $param['type'] : '';
        $example    = (isset($param['example'])) ? $param['example'] : '';

        $output = '';

        if(empty($example) && $example !== 0 && $example !== false) {
            switch ($type) {
                case self::TYPE_FILE:
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'null';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
            }
        }
        else {
            switch ($type) {
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
                'destination'   => '/lib/client.dart',
                'template'      => '/dart/lib/client.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/pubspec.yaml',
                'template'      => '/dart/pubspec.yaml.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/service.dart',
                'template'      => '/dart/lib/service.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '/lib/services/{{service.name | caseDash}}.dart',
                'template'      => '/dart/lib/services/service.dart.twig',
                'minify'        => false,
            ],
        ];
    }
}

