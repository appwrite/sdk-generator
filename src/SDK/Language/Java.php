<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class Java extends Language {

    /**
     * @return string
     */
    public function getName()
    {
        return 'Java';
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
            "assert",
            "boolean",
            "break",
            "byte",
            "case",
            "catch",
            "char",
            "class",
            "continue",
            "const",
            "default",
            "do",
            "double",
            "else",
            "enum",
            "extends",
            "goto",
            "final",
            "finally",
            "float",
            "for",
            "if",
            "implements",
            "import",
            "instanceof",
            "interface",
            "int",
            "long",
            "new",
            "native",
            "package",
            "protected",
            "private",
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
            "while"
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
                return 'boolean';
            break;
            case self::TYPE_ARRAY:
            	return 'List';
			case self::TYPE_OBJECT:
				return 'Object';
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
        return '';
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
                    $output .= 'new File("./path-to-files/image.jpg")';
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
                    $output .= 'new Object()';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '{}';
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
                'template'      => '/java/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => '/java/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => '/java/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/java/{{ spec.namespace | caseSlash }}/Client.java',
                'template'      => '/java/src/main/java/io/appwrite/Client.java.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/java/{{ spec.namespace | caseSlash }}/enums/OrderType.java',
                'template'      => '/java/src/main/java/io/appwrite/enums/OrderType.java.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/pom.xml',
                'template'      => '/java/pom.xml.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/java/{{ spec.namespace | caseSlash }}/services/Service.java',
                'template'      => '/java/src/main/java/io/appwrite/services/Service.java.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '/src/main/java/{{ spec.namespace | caseSlash }}/services/{{service.name | caseUcfirst}}.java',
                'template'      => '/java/src/main/java/io/appwrite/services/ServiceTemplate.java.twig',
                'minify'        => false,
            ]
        ];
    }
}

