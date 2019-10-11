<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class JS extends Language {

    protected $params = [
        'npmPackage' => 'packageName',
        'bowerPackage' => 'packageName',
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setNPMPackage($name)
    {
        $this->setParam('npmPackage', $name);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setBowerPackage($name)
    {
        $this->setParam('bowerPackage', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Javascript';
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
			'arguments',
			'await', // new in ECMAScript 5 and 6.
			'boolean',
			'break',
			'byte',
			'case',
			'catch',
			'char',
			'class', // new in ECMAScript 5 and 6.
			'const',
			'continue',
			'debugger',
			'default',
			'delete',
			'do',
			'double',
			'else',
			'enum', // new in ECMAScript 5 and 6.
			'eval',
			'export', // new in ECMAScript 5 and 6.
			'extends', // new in ECMAScript 5 and 6.
			'false',
			'final',
			'finally',
			'float',
			'for',
			'function',
			'goto',
			'if',
			'implements',
			'import', // new in ECMAScript 5 and 6.
			'in',
			'instanceof',
			'int',
			'interface',
			'let', // new in ECMAScript 5 and 6.
			'long',
			'native',
			'new',
			'null',
			'package',
			'private',
			'protected',
			'public',
			'return',
			'short',
			'static',
			'super', // new in ECMAScript 5 and 6.
			'switch',
			'synchronized',
			'this',
			'throw',
			'throws',
			'transient',
			'true',
			'try',
			'typeof',
			'var',
			'void',
			'volatile',
			'while',
			'with',
			'yield',
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
                'destination'   => 'src/sdk.js',
                'template'      => '/js/src/sdk.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/sdk.min.js',
                'template'      => '/js/src/sdk.js.twig',
                'minify'        => true,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => '/js/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => '/js/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package.json',
                'template'      => '/js/package.json.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => '/js/docs/example.md.twig',
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
                return 'number';
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

        $output = ' = ';

        if(empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'null';
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
                case self::TYPE_ARRAY:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$default}'";
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
                    $output .= 'null';
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
                    $output .= "document.getElementById('uploader').files[0]";
                    break;
            }
        }

        return $output;
    }
}