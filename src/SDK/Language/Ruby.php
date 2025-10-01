<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Ruby extends Language
{
    protected $params = [
        'gemPackage' => 'gemName',
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setGemPackage(string $name): self
    {
        $this->setParam('gemPackage', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Ruby';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords(): array
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
            'path'
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
                'template'      => 'ruby/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'ruby/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'ruby/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Gemfile',
                'template'      => 'ruby/Gemfile.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '{{ spec.title | caseDash }}.gemspec',
                'template'      => 'ruby/gemspec.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}.rb',
                'template'      => 'ruby/lib/container.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}/client.rb',
                'template'      => 'ruby/lib/container/client.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}/permission.rb',
                'template'      => 'ruby/lib/container/permission.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}/role.rb',
                'template'      => 'ruby/lib/container/role.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}/id.rb',
                'template'      => 'ruby/lib/container/id.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}/query.rb',
                'template'      => 'ruby/lib/container/query.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}/service.rb',
                'template'      => 'ruby/lib/container/service.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}/input_file.rb',
                'template'      => 'ruby/lib/container/input_file.rb.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/{{ spec.title | caseDash }}/exception.rb',
                'template'      => 'ruby/lib/container/exception.rb.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/lib/{{ spec.title | caseDash}}/services/{{service.name | caseSnake}}.rb',
                'template'      => 'ruby/lib/container/services/service.rb.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
                'template'      => 'ruby/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'ruby/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/lib/{{ spec.title | caseDash }}/models/{{ definition.name | caseSnake }}.rb',
                'template'      => 'ruby/lib/container/models/model.rb.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'lib/{{ spec.title | caseSnake}}/enums/{{ enum.name | caseSnake }}.rb',
                'template'      => 'ruby/lib/container/enums/enum.rb.twig',
            ],
        ];
    }

    /**
     * @param array $parameter
     * @param array $nestedTypes
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        if (isset($parameter['enumName'])) {
            return \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return \ucfirst($parameter['name']);
        }
        return match ($parameter['type']) {
            self::TYPE_INTEGER => 'Integer',
            self::TYPE_NUMBER => 'Float',
            self::TYPE_STRING => 'String',
            self::TYPE_ARRAY => 'Array',
            self::TYPE_OBJECT => 'Hash',
            self::TYPE_BOOLEAN => '',
            default => $parameter['type'],
        };
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

        if ($required) {
            return ':';
        }

        $output = ': ';

        if (empty($default) && $default !== 0 && $default !== false) {
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
        } else {
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
    public function getParamExample(array $param): string
    {
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

        $output = '';

        if (empty($example) && $example !== 0 && $example !== false) {
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
                    $output .= "InputFile.from_path('dir/file.png')";
                    break;
            }
        } else {
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
                    $output .= "InputFile.from_path('dir/file.png')";
                    break;
            }
        }

        return $output;
    }

    /**
     * Converts JSON Object To Ruby Native Hash
     *
     * @return string
     * @var $data array
     */
    protected function jsonToHash(array $data, int $indent = 0): string
    {
        if (empty($data)) {
            return '{}';
        }

        $output = "{\n";
        $indentStr = str_repeat('  ', $indent + 4);
        $keys = array_keys($data);

        foreach ($data as $key => $node) {
            if (is_array($node)) {
                $value = $this->jsonToHash($node, $indent + 1);
            } elseif (is_bool($node)) {
                $value = $node ? 'true' : 'false';
            } elseif (is_string($node)) {
                $value = '"' . $node . '"';
            } else {
                $value = $node;
            }

            $output .= $indentStr . '"' . $key . '" => ' . $value;

            // Add comma if not the last item
            if ($key !== end($keys)) {
                $output .= ',';
            }

            $output .= "\n";
        }

        $output .= str_repeat('  ', $indent + 2) . '}';

        return $output;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('rubyComment', function ($value) {
                $value = explode("\n", $value);
                foreach ($value as $key => $line) {
                    $value[$key] = "        # " . wordwrap($line, 75, "\n        # ");
                }
                return implode("\n", $value);
            }, ['is_safe' => ['html']]),
            new TwigFilter('caseEnumKey', function (string $value) {
                return $this->toUpperSnakeCase($value);
            }),
        ];
    }
}
