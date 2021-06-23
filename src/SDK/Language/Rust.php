<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class Rust extends Language {
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
          "type",
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
            case self::TYPE_OBJECT:
                return 'Option<HashMap<String, crate::client::ParamType>>';
            break;
            case self::TYPE_INTEGER:
                return 'i64';
            break;
            case self::TYPE_STRING:
                return '&str';
            break;
            case self::TYPE_FILE:
                return 'std::path::PathBuf';
            break;
            case self::TYPE_BOOLEAN:
                return 'bool';
            break;
            case self::TYPE_ARRAY:
              return '&[&str]';
            case self::TYPE_NUMBER:
                return 'f64';
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
        return "";
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
                    $output .= 'std::path::PathBuf::from("./path-to-files/image.jpg")';
                    break;
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= '0';
                break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= 'String::new()';
                    break;
                case self::TYPE_OBJECT:
                    $output .= 'new Object()';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '&[]';
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
                    $output .= sprintf('"%s"', $example);
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
                'destination'   => 'Cargo.toml',
                'template'      => '/rust/Cargo.toml.twig',
                'minify'        => false,
            ],
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
              'scope'         => 'method',
              'destination'   => 'src/lib.rs',
              'template'      => '/rust/src/lib.rs.twig',
              'minify'        => false,
            ],
            [
              'scope'         => 'method',
              'destination'   => 'src/client.rs',
              'template'      => '/rust/src/client.rs.twig',
              'minify'        => false,
            ],
            [
              'scope'         => 'method',
              'destination'   => 'src/services/mod.rs',
              'template'      => '/rust/src/services/mod.rs.twig',
              'minify'        => false,
            ],
            [
              'scope'         => 'method',
              'destination'   => 'examples/foo_test.rs',
              'template'      => '/rust/examples/foo_test.rs.twig',
              'minify'        => false,
            ],
            [
              'scope'         => 'method',
              'destination'   => 'src/services/exception.rs',
              'template'      => '/rust/src/services/exception.rs.twig',
              'minify'        => false,
            ],
            [
              'scope'         => 'service',
              'destination'   => 'src/services/{{service.name | caseDash}}.rs',
              'template'      => '/rust/src/services/service.rs.twig',
              'minify'        => false,
          ],
        ];
    }
}

