<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class Ruby extends Language {

    protected $params = [
        'gemPackage' => 'gemName',
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setGemPackage($name)
    {
        $this->setParam('gemPackage', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Ruby';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords()
    {
        return [
            'BEGIN',
			'END',
			'alias',
			'and',
			'begin',
			'break',
			'case',
			'class',
			'def',
			'defined?',
			'do',
			'else',
			'module',
			'next',
			'nil',
			'not',
			'or',
			'redo',
			'rescue',
			'retry',
			'return',
			'self',
			'super',
			'then',
			'elsif',
			'end',
			'false',
			'ensure',
			'for',
			'if',
			'true',
			'undef',
			'unless',
			'until',
			'when',
			'while',
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
                'template'      => 'ruby/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'ruby/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'ruby/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Gemfile',
                'template'      => 'ruby/Gemfile.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseDash }}.gemspec',
                'template'      => 'ruby/gemspec.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}.rb',
                'template'      => 'ruby/lib/container.rb.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}/client.rb',
                'template'      => 'ruby/lib/container/client.rb.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}/service.rb',
                'template'      => 'ruby/lib/container/service.rb.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}/file.rb',
                'template'      => 'ruby/lib/container/file.rb.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}/exception.rb',
                'template'      => 'ruby/lib/container/exception.rb.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '/lib/{{ spec.title | caseDash}}/services/{{service.name | caseDash}}.rb',
                'template'      => 'ruby/lib/container/services/service.rb.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'ruby/docs/example.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '.travis.yml',
                'template'      => 'ruby/.travis.yml.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/lib/{{ spec.title | caseDash }}/models/{{ definition.name | caseSnake }}.rb',
                'template'      => 'ruby/lib/container/models/model.rb.twig',
                'minify'        => false,
            ],
        ];
    }

    /**
     * @param $type
     * @return string
     */
    public function getTypeName($type, $method = [])
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
        $type       = $param['type'] ?? '';
        $default    = $param['default'] ?? '';
        $required   = $param['required'] ?? '';

        if($required) {
            return ':';
        }

        $output = ': ';

        if(empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'nill';
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
                    $output .= '[]';
                break;
                case self::TYPE_OBJECT:
                    $output .= '{}';
                    break;
                case self::TYPE_FILE:
                    $output .= "File.new()";
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
                    $output .= $this->jsonToHash(json_decode($example, true));
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$example}'";
                    break;
                case self::TYPE_FILE:
                    $output .= "File.new()";
                    break;
            }
        }

        return $output;
    }

    /**
     * Converts JSON Object To Ruby Native Hash
     * 
     * @var $data array
     */
    protected function jsonToHash(array $data):string
    {
        $output = '{';
        
        foreach($data as $key => $node) {
            $value = (is_array($node)) ? $this->jsonToHash($node) : $node;
            $output .= '"'.$key.'" => '.((is_string($node)) ? '"'.$value.'"' : $value).(($key !== array_key_last($data)) ? ', ' : '');
        }

        $output .= '}';

        return $output;
    }
}
