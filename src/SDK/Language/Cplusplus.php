<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class Cplusplus extends Language {

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
        // Currently using https://en.cppreference.com/w/c/keyword as a source, but it's not clear
        // how much coverage we want/need
        return [
            'delete',
            'auto',
            'break',
            'case',
            'char',
            'const',
            'continue',
            'default',
            'do',
            'double',
            'else',
            'enum',
            'extern',
            'float',
            'for',
            'goto',
            'if',
            'inline',
            'int',
            'long',
            'register',
            'restrict',
            'return',
            'short',
            'signed',
            'sizeof',
            'static',
            'struct',
            'switch',
            'typedef',
            'union',
            'unsigned',
            'void',
            'volatile',
            'while',
			'alignas',
			'alignof',
			'bool',
			'complex',
			'imaginary',
			'noreturn',
			'static_assert',
			'thread_local',
        ];
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
                'destination'   => 'src/client.hpp',
                'template'      => 'c++/src/client.hpp.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/client.cpp',
                'template'      => 'c++/src/client.cpp.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/exception.hpp',
                'template'      => 'c++/src/exception.hpp.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/exception.cpp',
                'template'      => 'c++/src/exception.cpp.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/service.cpp',
                'template'      => 'c++/src/service.cpp.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/service.hpp',
                'template'      => 'c++/src/service.hpp.twig',
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
            /* TODO
             * case self::TYPE_FILE:
             *   return '\CurlFile';
             * case self::TYPE_OBJECT:
             *   return 'array';
             */
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
                /* TODO
                 *
                 * case self::TYPE_ARRAY:
                 * case self::TYPE_OBJECT:
                 *     $output .= 'null';
                 *     break;
                 * case self::TYPE_FILE:
                 *     $output .= '{}';
                 *     break;
                 */
            }
        }
        else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"$default\"";
                    break;
                /* TODO
                 * case self::TYPE_ARRAY:
                 * case self::TYPE_OBJECT:
                 * case self::TYPE_FILE:
                 *     #$output .= json_encode($default);
                 *     $output .= '{}';
                 *     break;
                 */
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
                /* TODO
                 * case self::TYPE_ARRAY:
                 *     $output .= '[]';
                 *     break;
                 * case self::TYPE_OBJECT:
                 *     $output .= '{}';
                 *     break;
                 * case self::TYPE_FILE:
                 *     $output .= "open('/path/to/file.png', 'rb')"; //TODO add file class
                 *     break;
                 */
            }
        }
        else {
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
