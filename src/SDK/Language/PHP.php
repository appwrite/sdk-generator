<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class PHP extends Language {

    /**
     * @var array
     */
    protected $params = [
        'composerVendor' => 'vendor-name',
        'composerPackage' => 'package-name',
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setComposerVendor($name)
    {
        $this->setParam('composerVendor', $name);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setComposerPackage($name)
    {
        $this->setParam('composerPackage', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'PHP';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords()
    {
        return [
            '__halt_compiler',
			'abstract',
			'and',
			'array',
			'as',
			'break',
			'callable',
			'case',
			'catch',
			'class',
			'clone',
			'const',
			'continue',
			'declare',
			'default',
			'die',
			'do',
			'echo',
			'else',
			'elseif',
			'empty',
			'enddeclare',
			'endfor',
			'endforeach',
			'endif',
			'endswitch',
			'endwhile',
			'eval',
			'exit',
			'extends',
			'final',
			'for',
			'foreach',
			'function',
			'global',
			'goto',
			'if',
			'implements',
			'include',
			'include_once',
			'instanceof',
			'insteadof',
			'interface',
			'isset',
			'list',
			'namespace',
			'new',
			'or',
			'print',
			'private',
			'protected',
			'public',
			'require',
			'require_once',
			'return',
			'static',
			'switch',
			'throw',
			'trait',
			'try',
			'unset',
			'use',
			'var',
			'while',
			'xor'
        ];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides()
    {
        return [];
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
                'template'      => 'php/README.md.twig',
                'minify'        => false,
                //'block'         => 'default',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'php/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'php/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'composer.json',
                'template'      => 'php/composer.json.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => 'docs/{{service.name | caseLower}}.md',
                'template'      => 'php/docs/service.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'php/docs/example.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/Client.php',
                'template'      => 'php/src/Client.php.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/Query.php',
                'template'      => 'php/src/Query.php.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/InputFile.php',
                'template'      => 'php/src/InputFile.php.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/{{ spec.title | caseUcfirst}}Exception.php',
                'template'      => 'php/src/Exception.php.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ spec.title | caseUcfirst}}/Service.php',
                'template'      => 'php/src/Service.php.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '/src/{{ spec.title | caseUcfirst}}/Services/{{service.name | caseUcfirst}}.php',
                'template'      => 'php/src/Services/Service.php.twig',
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
            case self::TYPE_BOOLEAN:
                $type = 'bool';
                break;
            case self::TYPE_NUMBER:
            case self::TYPE_INTEGER:
                $type = 'int';
                break;
            case self::TYPE_OBJECT:
                $type = 'array';
                break;
            case self::TYPE_FILE:
                $type = 'InputFile';
                break;
        }

        return $type . ' ';
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
                case self::TYPE_BOOLEAN:
                    $output .= 'null';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
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
                case self::TYPE_OBJECT:
                    $output .= $this->jsonToAssoc(json_decode($default, true));
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
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

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
                case self::TYPE_OBJECT:
                    $output .= '[]';
                    break;
                case self::TYPE_FILE:
                    $output .= "InputFile::withPath('file.png')";
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
                case self::TYPE_OBJECT:
                    $output .= $this->jsonToAssoc(json_decode($example, true));
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$example}'";
                    break;
                case self::TYPE_FILE:
                    $output .= "InputFile::withPath('file.png')";
                    break;
            }
        }

        return $output;
    }

    /**
     * Converts JSON Object To PHP Native Assoc Array
     * 
     * @var $data array
     */
    protected function jsonToAssoc(array $data):string
    {
        $output = '[';
        
        foreach($data as $key => $node) {
            $value = (is_array($node)) ? $this->jsonToAssoc($node) : $node;
            $output .= '\''.$key.'\' => '.((is_string($node)) ? '\''.$value.'\'' : $value).(($key !== array_key_last($data)) ? ', ' : '');
        }

        $output .= ']';

        return $output;
    }

    protected function getReturn(array $method): string
    {
        if(($method['emptyResponse'] ?? true) || $method['type'] === 'location') {
            return 'string';
        }

        return 'array';
    }

    public function getFilters(): array {
        return [
            new TwigFilter('getReturn', function($value) {
                return $this->getReturn($value);
            })
        ];
    }
}
