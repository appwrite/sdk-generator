<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class PHP extends Language
{
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
    public function setComposerVendor(string $name): self
    {
        $this->setParam('composerVendor', $name);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setComposerPackage(string $name): self
    {
        $this->setParam('composerPackage', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'PHP';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords(): array
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
            'xor',
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

    public function getStaticAccessOperator(): string
    {
        return '::';
    }

    public function getStringQuote(): string
    {
        return '"';
    }

    public function getArrayOf(string $elements): string
    {
        return '[' . $elements . ']';
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
                'template'      => 'php/README.md.twig',
                //'block'         => 'default',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'php/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'php/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'composer.json',
                'template'      => 'php/composer.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'phpunit.xml',
                'template'      => 'php/phpunit.xml.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'docs/{{service.name | caseLower}}.md',
                'template'      => 'php/docs/service.md.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
                'template'      => 'php/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/Client.php',
                'template'      => 'php/src/Client.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/Permission.php',
                'template'      => 'php/src/Permission.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ spec.title | caseUcfirst}}/PermissionTest.php',
                'template'      => 'php/tests/PermissionTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/Role.php',
                'template'      => 'php/src/Role.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ spec.title | caseUcfirst}}/RoleTest.php',
                'template'      => 'php/tests/RoleTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/ID.php',
                'template'      => 'php/src/ID.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ spec.title | caseUcfirst}}/IDTest.php',
                'template'      => 'php/tests/IDTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/Query.php',
                'template'      => 'php/src/Query.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ spec.title | caseUcfirst}}/QueryTest.php',
                'template'      => 'php/tests/QueryTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/Operator.php',
                'template'      => 'php/src/Operator.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tests/{{ spec.title | caseUcfirst}}/OperatorTest.php',
                'template'      => 'php/tests/OperatorTest.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/InputFile.php',
                'template'      => 'php/src/InputFile.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/{{ spec.title | caseUcfirst}}/{{ spec.title | caseUcfirst}}Exception.php',
                'template'      => 'php/src/Exception.php.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/{{ spec.title | caseUcfirst}}/Service.php',
                'template'      => 'php/src/Service.php.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/src/{{ spec.title | caseUcfirst}}/Services/{{service.name | caseUcfirst}}.php',
                'template'      => 'php/src/Services/Service.php.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/tests/{{ spec.title | caseUcfirst}}/Services/{{service.name | caseUcfirst}}Test.php',
                'template'      => 'php/tests/Services/ServiceTest.php.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => '/src/{{ spec.title | caseUcfirst}}/Enums/{{ enum.name | caseUcfirst }}.php',
                'template'      => 'php/src/Enums/Enum.php.twig',
            ],
            [
                'scope'         => 'requestModel',
                'destination'   => '/src/{{ spec.title | caseUcfirst}}/Models/{{ requestModel.name | caseUcfirst }}.php',
                'template'      => 'php/src/Models/RequestModel.php.twig',
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
        if (!empty($parameter['array']['model'])) {
            return 'array';
        }
        if (!empty($parameter['model'])) {
            $modelType = $this->toPascalCase($parameter['model']);
            return $parameter['type'] === self::TYPE_ARRAY ? 'array' : $modelType;
        }
        return match ($parameter['type']) {
            self::TYPE_STRING => 'string',
            self::TYPE_BOOLEAN => 'bool',
            self::TYPE_NUMBER => 'float',
            self::TYPE_INTEGER => 'int',
            self::TYPE_ARRAY,
            self::TYPE_OBJECT => 'array',
            self::TYPE_FILE => 'InputFile',
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
            return '';
        }

        $output = ' = ';

        if (empty($default) && $default !== 0 && $default !== false) {
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
        } else {
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
     * @param string $lang
     * @return string
     */
    public function getParamExample(array $param, string $lang = ''): string
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
                case self::TYPE_OBJECT:
                    $output .= '[]';
                    break;
                case self::TYPE_FILE:
                    $output .= "InputFile::withPath('file.png')";
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $example;
                    break;
                case self::TYPE_ARRAY:
                    $output .= $this->isPermissionString($example) ? $this->getPermissionExample($example) : $example;
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
    protected function jsonToAssoc(array $data, int $indent = 0): string
    {
        if (empty($data)) {
            return '[]';
        }

        $baseIndent = str_repeat('    ', $indent);
        $itemIndent = str_repeat('    ', $indent + 1);
        $output = "[\n";

        $keys = array_keys($data);
        foreach ($keys as $index => $key) {
            $node = $data[$key];

            if (is_array($node)) {
                $value = $this->jsonToAssoc($node, $indent + 1);
            } elseif (is_string($node)) {
                $value = '\'' . $node . '\'';
            } elseif (is_bool($node)) {
                $value = $node ? 'true' : 'false';
            } elseif (is_null($node)) {
                $value = 'null';
            } else {
                $value = $node;
            }

            $comma = ($index < count($keys) - 1) ? ',' : '';
            $output .= '    ' . $itemIndent . '\'' . $key . '\' => ' . $value . $comma . "\n";
        }

        $output .= $baseIndent . '    ]';

        return $output;
    }

    protected function getReturn(array $method): string
    {
        if (($method['emptyResponse'] ?? true) || $method['type'] === 'location' || $method['type'] === 'webAuth') {
            return 'string';
        }

        return 'array';
    }

    /**
     * Generate method parameters string for PHP method signatures
     *
     * @param array $method
     * @return string
     */
    protected function getMethodParameters(array $method): string
    {
        $params = [];

        foreach ($method['parameters']['all'] ?? [] as $parameter) {
            $nullable = (!($parameter['required'] ?? true)) || ($parameter['nullable'] ?? false);
            $nullablePrefix = $nullable ? '?' : '';

            $typeName = $this->getTypeName($parameter);
            $paramName = '$' . $this->escapeKeyword($this->toCamelCase($parameter['name'] ?? ''));
            $default = !($parameter['required'] ?? true) ? ' = null' : '';

            $params[] = $nullablePrefix . $typeName . ' ' . $paramName . $default;
        }

        $result = implode(', ', $params);

        // Add onProgress callback for multipart/form-data methods
        if (in_array('multipart/form-data', $method['consumes'] ?? [])) {
            $result .= ($result !== '' ? ', ' : '') . '?callable $onProgress = null';
        }

        return $result;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('getReturn', function ($value) {
                return $this->getReturn($value);
            }),
            new TwigFilter('methodParameters', function ($value) {
                return $this->getMethodParameters($value);
            }),
            new TwigFilter('deviceInfo', function ($value) {
                return php_uname('s') . '; ' . php_uname('v') . '; ' . php_uname('m');
            }),
            new TwigFilter('caseEnumKey', function (string $value) {
                if (isset($this->getIdentifierOverrides()[$value])) {
                    $value = $this->getIdentifierOverrides()[$value];
                }
                $value = \preg_replace('/[^a-zA-Z0-9]/', '', $value);
                return $this->toUpperSnakeCase($value);
            }),
        ];
    }
}
