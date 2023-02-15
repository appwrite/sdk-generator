<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Exception;

class Elixir extends Language {

    protected $params = [
        'pipPackage' => 'packageName',
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setPipPackage($name): self
    {
        $this->setParam('pipPackage', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Elixir';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords(): array
    {
        return [
            'false',
            'is',
            'nil',
            'for',
            'try',
            'true',
            'def',
            'alias',
            'and',
            'del',
            'not',
            'with',
            'as',
            'case',
            'if',
            'or',
            'assert',
            'else',
            'import',
            'when',
            'in',
            'throw',
        ];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'elixir/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'elixir/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'elixir/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'mix.exs',
                'template'      => 'elixir/mix.exs.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '.formatter.exs',
                'template'      => 'elixir/.formatter.exs.twig',
                'minify'        => false,
            ],
            /*[
                'scope'         => 'service',
                'destination'   => 'docs/{{service.name | caseSnake}}.md',
                'template'      => 'elixir/docs/service.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => 'docs/{{service.name | caseSnake}}.md',
                'template'      => 'elixir/docs/service.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseSnake}}/{{method.name | caseSnake}}.md',
                'template'      => 'elixir/docs/example.md.twig',
                'minify'        => false,
            ],*/
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseSnake}}/client.ex',
                'template'      => 'elixir/lib/appwrite/client.ex.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseSnake}}/permission.ex',
                'template'      => 'elixir/lib/appwrite/permission.ex.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseSnake}}/role.ex',
                'template'      => 'elixir/lib/appwrite/role.ex.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseSnake}}/id.ex',
                'template'      => 'elixir/lib/appwrite/id.ex.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseSnake}}/query.ex',
                'template'      => 'elixir/lib/appwrite/query.ex.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseSnake}}/exception.ex',
                'template'      => 'elixir/lib/appwrite/exception.ex.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseSnake}}/input_file.ex',
                'template'      => 'elixir/lib/appwrite/input_file.ex.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => 'lib/{{ spec.title | caseSnake}}/services/{{service.name | caseSnake}}.ex',
                'template'      => 'elixir/lib/appwrite/services/service.ex.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'elixir/docs/example.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '.travis.yml',
                'template'      => 'elixir/.travis.yml.twig',
                'minify'        => false,
            ],
        ];
    }

    /**
     * @param array $parameter
     * @return string
     * @throws Exception
     */
    public function getTypeName(array $parameter): string
    {
        throw new Exception('Method not supported for Elixir SDKs');
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamDefault(array $param): string
    {
        $type       = $param['type'] ?? '';
        $default    = $param['default'] ?? '';
        $required   = $param['required'] ?? '';

        if($required) {
            return '';
        }

        $output = '=';

        if(empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'None';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_OBJECT:
                case self::TYPE_FILE:
                    $output .= '{}';
                    break;
            }
        }
        else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= $default;
                    break;
                case self::TYPE_FILE:
                    #$output .= json_encode($default);
                    $output .= '{}';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'True' : 'False';
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
    public function getParamExample(array $param): string
    {
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

        $output = '';

        if(empty($example) && $example !== 0 && $example !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'None';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_OBJECT:
                    $output .= '{}';
                    break;
                case self::TYPE_FILE:
                    $output .= "InputFile.from_path('file.png')";
                    break;
            }
        }
        else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= $example;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'True' : 'False';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$example}'";
                    break;
                case self::TYPE_FILE:
                    $output .= "InputFile.from_path('file.png')";
                    break;
            }
        }

        return $output;
    }
}
