<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class Python extends Language {

    protected $params = [
        'pipPackage' => 'packageName',
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setPipPackage($name)
    {
        $this->setParam('pipPackage', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Python';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords()
    {
        return [
            'False',
            'class',
            'finally',
            'is',
            'return',
            'None',
            'continue',
            'for',
            'lambda',
            'try',
            'True',
            'def',
            'from',
            'nonlocal',
            'while',
            'and',
            'del',
            'global',
            'not',
            'with',
            'as',
            'elif',
            'if',
            'or',
            'yield',
            'assert',
            'else',
            'import',
            'pass',
            'break',
            'except',
            'in',
            'raise',
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
                'template'      => '/python/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => '/python/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'setup.py',
                'template'      => '/python/setup.py.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'setup.cfg',
                'template'      => '/python/setup.cfg.twig',
                'minify'        => false,
            ],
            /*[
                'scope'         => 'service',
                'destination'   => 'docs/{{service.name | caseSnake}}.md',
                'template'      => '/python/docs/service.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => 'docs/{{service.name | caseSnake}}.md',
                'template'      => '/python/docs/service.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseSnake}}/{{method.name | caseSnake}}.md',
                'template'      => '/python/docs/example.md.twig',
                'minify'        => false,
            ],*/
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseSnake}}/__init__.py',
                'template'      => '/python/package/__init__.py.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseSnake}}/client.py',
                'template'      => '/python/package/client.py.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseSnake}}/service.py',
                'template'      => '/python/package/service.py.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseSnake}}/services/__init__.py',
                'template'      => '/python/package/services/__init__.py.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '{{ spec.title | caseSnake}}/services/{{service.name | caseSnake}}.py',
                'template'      => '/python/package/services/service.py.twig',
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
                    $output .= 'None';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                    $output .= '{}';
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
                    #$output .= json_encode($default);
                    $output .= '[]';
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
                    $output .= 'None';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                    $output .= '{}';
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
                    $output .= ($example) ? 'True' : 'False';
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