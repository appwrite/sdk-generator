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

    public function getStaticAccessOperator(): string
    {
        return '.';
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
                'destination' => '{{ spec.title | caseSnake}}/utils/deprecated.py',
                'template' => 'python/package/utils/deprecated.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/utils/__init__.py',
                'template' => 'python/package/utils/__init__.py.twig',
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
                'destination' => '{{ spec.title | caseSnake}}/operator.py',
                'template' => 'python/package/operator.py.twig',
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
                'destination' => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
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
        $typeName = '';

        if (isset($parameter['enumName'])) {
            $typeName = \ucfirst($parameter['enumName']);
        } elseif (!empty($parameter['enumValues'])) {
            $typeName = \ucfirst($parameter['name']);
        } else {
            switch ($parameter['type'] ?? '') {
                case self::TYPE_FILE:
                    $typeName = 'InputFile';
                    break;
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $typeName = 'float';
                    break;
                case self::TYPE_BOOLEAN:
                    $typeName = 'bool';
                    break;
                case self::TYPE_STRING:
                    $typeName = 'str';
                    break;
                case self::TYPE_ARRAY:
                    if (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type'])) {
                        $typeName = 'List[' . $this->getTypeName($parameter['array']) . ']';
                    } else {
                        $typeName = 'List[Any]';
                    }
                    break;
                case self::TYPE_OBJECT:
                    $typeName = 'dict';
                    break;
                default:
                    $typeName = $parameter['type'];
                    break;
            }
        }

        if (!($parameter['required'] ?? true) || ($parameter['nullable'] ?? false)) {
            return 'Optional[' . $typeName . ']';
        }

        return $typeName;
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

        $hasExample = !empty($example) || $example === 0 || $example === false;

        if (!$hasExample) {
            return match ($type) {
                self::TYPE_ARRAY => '[]',
                self::TYPE_FILE => 'InputFile.from_path(\'file.png\')',
                self::TYPE_INTEGER, self::TYPE_NUMBER , self::TYPE_BOOLEAN => 'None',
                self::TYPE_OBJECT => '{}',
                self::TYPE_STRING => "''",
            };
        }

        return match ($type) {
            self::TYPE_ARRAY => $this->isPermissionString($example) ? $this->getPermissionExample($example) : $example,
            self::TYPE_FILE, self::TYPE_INTEGER, self::TYPE_NUMBER => $example,
            self::TYPE_BOOLEAN => ($example) ? 'True' : 'False',
            self::TYPE_OBJECT => ($example === '{}')
            ? '{}'
            : (($formatted = json_encode(json_decode($example, true), JSON_PRETTY_PRINT))
                ? preg_replace('/\n/', "\n    ", str_replace(['true', 'false'], ['True', 'False'], $formatted))
                : $example),
            self::TYPE_STRING => "'{$example}'",
        };
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('caseEnumKey', function (string $value) {
                return $this->toUpperSnakeCase($value);
            }),
            new TwigFilter('getPropertyType', function ($value, $method = []) {
                return $this->getTypeName($value, $method);
            }),
            new TwigFilter('formatParamValue', function (string $paramName, string $paramType, bool $isMultipartFormData) {
                if ($isMultipartFormData && $paramType === self::TYPE_BOOLEAN) {
                    return "str({$paramName}).lower() if type({$paramName}) is bool else {$paramName}";
                }
                return $paramName;
            }),
        ];
    }
}
