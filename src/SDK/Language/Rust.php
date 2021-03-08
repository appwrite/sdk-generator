<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class Rust extends Language {

    /**
     * @return string
     */
    public function getName()
    {
        return 'Rust';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords()
    {
        return [
          "as",
          "break",
          "const",
          "continue",
          "crate",
          "else",
          "enum",
          "extern",
          "false",
          "fn",
          "for",
          "if",
          "impl",
          "in",
          "let",
          "loop",
          "match",
          "mod",
          "move",
          "mut",
          "pub",
          "ref",
          "return",
          "self",
          "Self",
          "static",
          "struct",
          "super",
          "trait",
          "true",
          "type",
          "unsafe",
          "use",
          "where",
          "while",
          "async",
          "await",
          "dyn",
          "abstract",
          "become",
          "box",
          "do",
          "final",
          "macro",
          "override",
          "priv",
          "typeof",
          "unsized",
          "virtual",
          "yield",
          "try"
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
                return 'u32';
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
              return 'Vec';
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
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

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
                    $output .= 'vec![]';
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
                'template'      => '/rust/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => '/rust/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => '/rust/LICENSE.twig',
                'minify'        => false,
            ],
	    [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => '/rust/docs/example.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/java/{{ sdk.namespace | caseSlash }}/Client.java',
                'template'      => '/java/src/main/java/io/appwrite/Client.java.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/java/{{ sdk.namespace | caseSlash }}/enums/OrderType.java',
                'template'      => '/java/src/main/java/io/appwrite/enums/OrderType.java.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/main/java/{{ sdk.namespace | caseSlash }}/services/Service.java',
                'template'      => '/java/src/main/java/io/appwrite/services/Service.java.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '/src/main/java/{{ sdk.namespace | caseSlash }}/services/{{service.name | caseUcfirst}}.java',
                'template'      => '/java/src/main/java/io/appwrite/services/ServiceTemplate.java.twig',
                'minify'        => false,
            ]
        ];
    }
}

