<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class DotNet extends Language {

    /**
     * @return string
     */
    public function getName()
    {
        return 'DotNet';
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
            'catch',
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
    public function getIdentifierOverrides()
    {
        return [
            'Jwt' => 'JWT',
            'Domain' => 'XDomain',
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
            case self::TYPE_FILE:
                return 'FileInfo';
            case self::TYPE_BOOLEAN:
                return 'bool';
            case self::TYPE_ARRAY:
                return 'List<object>';
            case self::TYPE_OBJECT:
                return 'object';
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
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                case self::TYPE_BOOLEAN:
                    $output .= 'null';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
            }
        }
        else {
            switch ($type) {
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$default}\"";
                    break;
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= 'null';
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
                    $output .= '""';
                    break;
                case self::TYPE_OBJECT:
                    $output .= '[object]';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[List<object>]';
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
                case self::TYPE_OBJECT:
                    $output .= '[object]';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$example}\"";
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
                'template'      => 'dotnet/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'dotnet/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'dotnet/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '.travis.yml',
                'template'      => 'dotnet/.travis.yml.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'dotnet/docs/example.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ spec.title | caseUcfirst }}.sln',
                'template'      => 'dotnet/src/Appwrite.sln',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/icon.png',
                'template'      => 'dotnet/icon.png',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ spec.title | caseUcfirst }}/{{ spec.title | caseUcfirst }}.csproj',
                'template'      => 'dotnet/src/Appwrite/Appwrite.csproj.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ spec.title | caseUcfirst }}/Client.cs',
                'template'      => 'dotnet/src/Appwrite/Client.cs.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ spec.title | caseUcfirst }}/Helpers/ExtensionMethods.cs',
                'template'      => 'dotnet/src/Appwrite/Helpers/ExtensionMethods.cs',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ spec.title | caseUcfirst }}/Helpers/Int32JsonConverter.cs',
                'template'      => 'dotnet/src/Appwrite/Helpers/Int32JsonConverter.cs',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ spec.title | caseUcfirst }}/Models/OrderType.cs',
                'template'      => 'dotnet/src/Appwrite/Models/OrderType.cs.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ spec.title | caseUcfirst }}/Models/Exception.cs',
                'template'      => 'dotnet/src/Appwrite/Models/Exception.cs.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ spec.title | caseUcfirst }}/Services/Service.cs',
                'template'      => 'dotnet/src/Appwrite/Services/Service.cs.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '/src/{{ spec.title | caseUcfirst }}/Services/{{service.name | caseUcfirst}}.cs',
                'template'      => 'dotnet/src/Appwrite/Services/ServiceTemplate.cs.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/src/{{ spec.title | caseUcfirst }}/Models/{{ definition.name | caseUcfirst | overrideIdentifier }}.cs',
                'template'      => 'dotnet/src/Appwrite/Models/Model.cs.twig',
                'minify'        => false,
            ]
        ];
    }
}

