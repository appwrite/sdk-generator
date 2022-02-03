<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class Cplusplus extends Language
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'C++';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords()
    {
        // TODO: Evaluate this list.
        // Referred to https://en.cppreference.com/w/cpp/keyword as a source,
        // This should cover all the keywords for C++17
        return [
            'and',
            'and_eq',
            'asm',
            'auto',
            'bitand',
            'bitor',
            'bool',
            'break',
            'case',
            'catch',
            'char',
            'class',
            'compl',
            'const',
            'const_cast',
            'continue',
            'default',
            'delete',
            'do',
            'double',
            'dynamic_cast',
            'else',
            'enum',
            'explicit',
            'export',
            'extern',
            'false',
            'float',
            'for',
            'friend',
            'goto',
            'if',
            'inline',
            'int',
            'long',
            'mutable',
            'namespace',
            'new',
            'not',
            'not_eq',
            'operator',
            'or',
            'or_eq',
            'private',
            'protected',
            'public',
            'register',
            'reinterpret_cast',
            'return',
            'short',
            'signed',
            'sizeof',
            'static',
            'static_cast',
            'struct',
            'switch',
            'template',
            'this',
            'throw',
            'true',
            'try',
            'typedef',
            'typeid',
            'typename',
            'union',
            'unsigned',
            'using',
            'virtual',
            'void',
            'volatile',
            'wchar_t',
            'while',
            'xor',
            'xor_eq'

        ];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides()
    {
        return ['delete' => 'xdelete'];
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
                'template'      => 'c++/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'c++/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'c++/LICENSE.twig',
                'minify'        => false,
            ],
            /*[
                'scope'         => 'service',
                'destination'   => 'docs/{{service.name | caseSnake}}.md',
                'template'      => 'c++/docs/service.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => 'docs/{{service.name | caseSnake}}.md',
                'template'      => 'c++/docs/service.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseSnake}}/{{method.name | caseSnake}}.md',
                'template'      => 'c++/docs/example.md.twig',
                'minify'        => false,
            ],*/
            [
                'scope'         => 'default',
                'destination'   => 'src/include/client.hpp',
                'template'      => 'c++/src/include/client.hpp.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/lib/client.cpp',
                'template'      => 'c++/src/lib/client.cpp.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/include/exception.hpp',
                'template'      => 'c++/src/include/exception.hpp.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/lib/exception.cpp',
                'template'      => 'c++/src/lib/exception.cpp.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/services/service.cpp',
                'template'      => 'c++/src/services/service.cpp.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/services/service.hpp',
                'template'      => 'c++/src/services/service.hpp.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => 'src/services/{{service.name | caseSnake}}.cpp',
                'template'      => 'c++/src/services/service.cpp.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => 'src/services/{{service.name | caseSnake}}.hpp',
                'template'      => 'c++/src/services/service.hpp.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'c++/docs/example.md.twig',
                'minify'        => false,
            ],
            [
                'scope' => 'copy',
                'destination' => 'src/include/json.hpp',
                'template' => 'c++/src/include/json.hpp',
                'minify' => false
            ]
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
            case self::TYPE_NUMBER:
                return 'double';
            case self::TYPE_STRING:
                return 'string';
            case self::TYPE_BOOLEAN:
                return 'bool';
            case self::TYPE_OBJECT:
                return 'json';
            case self::TYPE_FILE:
                return 'fstream';
            case self::TYPE_ARRAY:
                return 'std::vector';
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

        if ($required) {
            return '';
        }

        $output = ' = ';

        if (empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= '0';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= '{}';
                    break;
                case self::TYPE_FILE:
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_ARRAY:
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"$default\"";
                    break;
                case self::TYPE_OBJECT:
                    $output .= $default;
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

        if (empty($example) && $example !== 0 && $example !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= '0';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '{}';
                    break;
                case self::TYPE_OBJECT:
                    $output .= '{}';
                    break;
                    /* TODO
                 * case self::TYPE_FILE:
                 *     $output .= "open('/path/to/file.png', 'rb')"; //TODO add file class
                 *     break;
                 */
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"$example\"";
                    break;
                    /* TODO
                 * case self::TYPE_ARRAY:
                 * case self::TYPE_OBJECT:
                 *     $output .= $example;
                 *     break;
                 * case self::TYPE_FILE:
                 *     $output .= "open('/path/to/file.png', 'rb')"; //TODO add file class
                 *     break;
                 */
            }
        }

        return $output;
    }
}
