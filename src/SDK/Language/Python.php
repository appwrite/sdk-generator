<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Exception;
use Twig\TwigFilter;

class Python extends Language
{
    protected $params = [
        'pipPackage' => 'packageName',
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setPipPackage(string $name): self
    {
        $this->setParam('pipPackage', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Python';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords(): array
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
            'async'
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
                'scope' => 'default',
                'destination' => 'README.md',
                'template' => 'python/README.md.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'CHANGELOG.md',
                'template' => 'python/CHANGELOG.md.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'LICENSE',
                'template' => 'python/LICENSE.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'setup.py',
                'template' => 'python/setup.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'setup.cfg',
                'template' => 'python/setup.cfg.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'requirements.txt',
                'template' => 'python/requirements.txt.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/__init__.py',
                'template' => 'python/package/__init__.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/client.py',
                'template' => 'python/package/client.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/permission.py',
                'template' => 'python/package/permission.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/role.py',
                'template' => 'python/package/role.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/id.py',
                'template' => 'python/package/id.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/query.py',
                'template' => 'python/package/query.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/exception.py',
                'template' => 'python/package/exception.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/input_file.py',
                'template' => 'python/package/input_file.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/service.py',
                'template' => 'python/package/service.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/services/__init__.py',
                'template' => 'python/package/services/__init__.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/encoders/__init__.py',
                'template' => 'python/package/services/__init__.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/enums/__init__.py',
                'template' => 'python/package/services/__init__.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/encoders/value_class_encoder.py',
                'template' => 'python/package/encoders/value_class_encoder.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/encoders/__init__.py',
                'template' => 'python/package/encoders/__init__.py.twig',
            ],
            [
                'scope' => 'service',
                'destination' => '{{ spec.title | caseSnake}}/services/{{service.name | caseSnake}}.py',
                'template' => 'python/package/services/service.py.twig',
            ],
            [
                'scope' => 'method',
                'destination' => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template' => 'python/docs/example.md.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '.github/workflows/publish.yml',
                'template' => 'python/.github/workflows/publish.yml.twig',
            ],
            [
                'scope' => 'enum',
                'destination' => '{{ spec.title | caseSnake}}/enums/{{ enum.name | caseSnake }}.py',
                'template' => 'python/package/enums/enum.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/enums/__init__.py',
                'template' => 'python/package/enums/__init__.py.twig',
            ],
        ];
    }

    /**
     * @param array $parameter
     * @return string
     * @throws Exception
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        if (isset($parameter['enumName'])) {
            return \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return \ucfirst($parameter['name']);
        }
        return match ($parameter['type'] ?? '') {
            self::TYPE_FILE => 'InputFile',
            self::TYPE_NUMBER,
            self::TYPE_INTEGER => 'float',
            self::TYPE_BOOLEAN => 'bool',
            self::TYPE_STRING => 'str',
            self::TYPE_ARRAY => 'list',
            self::TYPE_OBJECT => 'dict',
            default => $parameter['type'],
        };
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamDefault(array $param): string
    {
        $type = $param['type'] ?? '';
        $default = $param['default'] ?? '';
        $required = $param['required'] ?? '';

        if ($required) {
            return '';
        }

        $output = '=';

        if (empty($default) && $default !== 0 && $default !== false) {
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
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= $default;
                    break;
                case self::TYPE_FILE:
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
        $type = $param['type'] ?? '';
        $example = $param['example'] ?? '';

        $output = '';

        if (empty($example) && $example !== 0 && $example !== false) {
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
        } else {
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

    public function getFilters(): array
    {
        return [
            new TwigFilter('caseEnumKey', function (string $value) {
                return $this->toUpperSnakeCase($value);
            }),
        ];
    }
}
