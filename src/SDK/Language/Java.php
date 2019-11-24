<?php

namespace Appwrite\SDK;

use Appwrite\SDK\Language;

class Java extends Language {

    protected $params = [
        'packageName' => 'packageName',
    ];

    /**
    * @param string $name
    * @return $this
    */
    public function setPackageName($name) {
        $this->setParam('packageName', $name);

        return $this;
    }

    /**
    * @return string
    */
    public function getName() {
        return 'Java';
    }

    /**
    * Get Language Keywords List
    *
    * @return array
    */
    public function getKeywords() {
        return [
            "abstract",
            "assert",
            "boolean",
            "break",
            "byte",
            "case",
            "catch"
            "char",
            "class",
            "const",
            "continue",
            "default",
            "do",
            "double",
            "else",
            "enum",
            "extends",
            "final",
            "finally",
            "float",
            "for",
            "goto",
            "if",
            "implements",
            "import",
            "instanceof",
            "int",
            "interface",
            "long",
            "native",
            "new",
            "package",
            "private",
            "protected",
            "public",
            "return",
            "short",
            "static",
            "strictfp",
            "super",
            "switch",
            "synchronized",
            "this",
            "throw",
            "throws",
            "transient",
            "try",
            "void",
            "volatile",
            "while",
            "true",
            "false",
            "null",
        ];
    }

    /**
    * @param $type
    * @return string
    */
    public function getTypeName($type) {
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
                return 'boolean';
                break;
        }

        return $type
    }

    /**
    * @param array $param
    * @return string
    */
    public function getParamDefault(array $param) {
        $type = (isset($param['type'])) ? $param['type'] : '';
        $default = (isset($param['default'])) ? $param['default'] : '';
        $required = (isset($param['required'])) ? $param['required'] : '';
        if($required) {
            return '';
        }

        $output = ' = ';

        if(empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'null';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]':
            }
        } else {
            switch($type) {
                case self::TYPE_NUMER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$default}\"";
                    break;
            }
        }
        return $output;
    }

    /**
    * @param array $param
    * @return string
    */
    public function getParamExample(array $param) {
        $type = (isset($param['type'])) ? $param['type'] : '';
        $example = (isset($param['example'])) ? $param['example'] : '';
        $output = '';

        if(empty($example) && $example !== 0 && $example !== false) {
            switch($type) {
                case self::TYPE_NUMER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                    $output .= $example;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$example}\"";
                    break;
                case self::TYPE_FILE:
                    $output .= "file";
                    break;
            }
        }
        return $output;
    }
}