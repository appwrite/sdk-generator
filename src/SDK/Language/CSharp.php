<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class CSharp extends Language {

    protected $params = [
        'nugetPackage' => 'packageName',
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setNugetPackage($name)
    {
        $this->setParam('nugetPackage', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'CSharp';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords()
    {
        return [
            'abstract',
            'add',
            'alias',
            'as',
            'ascending',
            'async',
            'await',
            'base',
            'bool',
            'break',
            'by',
            'byte',
            'case',
            'catch'
            'char',
            'checked',
            'class',
            'const',
            'continue',
            'decimal',
            'default',
            'delegate',
            'do',
            'double',
            'descending',
            'dynamic',
            'else',
            'enum',
            'equals',
            'event',
            'explicit',
            'extern',
            'false',
            'finally',
            'fixed',
            'float',
            'for',
            'foreach',
            'from',
            'get',
            'global',
            'goto',
            'group',
            'if',
            'implicit',
            'in',
            'int',
            'interface',
            'internal',
            'into',
            'is',
            'join',
            'let',
            'lock',
            'long',
            'nameof',
            'namespace',
            'new',
            'null',
            'object',
            'on',
            'operator',
            'orderby',
            'out',
            'override',
            'params',
            'partial',
            'private',
            'protected',
            'public',
            'readonly',
            'ref',
            'remove',
            'return',
            'sbyte',
            'sealed',
            'select',
            'set',
            'short',
            'sizeof',
            'stackalloc',
            'static',
            'string',
            'struct',
            'switch',
            'this',
            'throw',
            'true',
            'try',
            'typeof',
            'uint',
            'ulong',
            'unchecked',
            'unmanaged', # Added in C# 7.3
            'unsafe',
            'ushort',
            'using',
            'using static',
            'value',
            'var',
            'virtual',
            'void',
            'volatile',
            'when',
            'where',
            'while',
            'yield'
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
                'template'      => '/csharp/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => '/csharp/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'setup.py',
                'template'      => '/csharp/Package.nuspec.twig',
                'minify'        => false,
            ],
            /*[
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseSnake}}/{{method.name | caseSnake}}.md',
                'template'      => '/csharp/docs/example.cs.twig',
                'minify'        => false,
            ],*/
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseSnake}}/src/sdk.cs',
                'template'      => '/csharp/src/sdk.cs.twig',
                'minify'        => false,
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
            case self::TYPE_NUMBER:
                return 'Int';
            break;
            case self::TYPE_FILE:
                return 'File';
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

        $output = '=';

        if(empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
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
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_ARRAY:
                var_dump( json_encode($default));
                    #$output .= json_encode($default);
                    $output .= '[]';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "'$default'";
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
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_FILE:
                    $output .= "document.getElementById('uploader').files[0]";
                    break;
            }
        }
        else {
            switch ($type) {
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
                case self::TYPE_FILE:
                    $output .= "document.getElementById('uploader').files[0]"; //TODO add file class
                    break;
            }
        }

        return $output;
    }
}