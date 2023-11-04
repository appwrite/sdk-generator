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
                'destination' => 'pyproject.toml',
                'template' => 'python/pyproject.toml.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/__init__.py',
                'template' => 'python/package/__init__.py.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/__init__.py',
                'template'      => 'python/test/__init__.py.twig',
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
                'scope'         => 'default',
                'destination'   => 'test/test_permission.py',
                'template'      => 'python/test/test_permission.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/role.py',
                'template' => 'python/package/role.py.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/test_role.py',
                'template'      => 'python/test/test_role.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/id.py',
                'template' => 'python/package/id.py.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/test_id.py',
                'template'      => 'python/test/test_id.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/query.py',
                'template' => 'python/package/query.py.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/test_query.py',
                'template'      => 'python/test/test_query.py.twig',
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
                'destination' => '{{ spec.title | caseSnake}}/models/__init__.py',
                'template' => 'python/package/models/__init__.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ spec.title | caseSnake}}/models/base_model.py',
                'template' => 'python/package/models/base_model.py.twig',
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
                'scope'         => 'default',
                'destination'   => 'test/services/__init__.py',
                'template'      => 'python/test/services/__init__.py.twig',
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
                'scope'         => 'service',
                'destination'   => 'test/services/test_{{service.name | caseSnake}}.py',
                'template'      => 'python/test/services/test_service.py.twig',
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
            [
                'scope' => 'requestModel',
                'destination' => '{{ spec.title | caseSnake}}/models/{{ requestModel.name | caseSnake }}.py',
                'template' => 'python/package/models/request_model.py.twig',
            ],
            [
                'scope' => 'definition',
                'destination' => '{{ spec.title | caseSnake}}/models/{{ definition.name | caseSnake }}.py',
                'template' => 'python/package/models/model.py.twig',
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

        if (
            ($parameter['type'] ?? null) === self::TYPE_ARRAY
            && (isset($parameter['enumName']) || !empty($parameter['enumValues']))
        ) {
            $enumType = isset($parameter['enumName'])
                ? \ucfirst($parameter['enumName'])
                : \ucfirst($parameter['name']);

            $typeName = 'List[' . $enumType . ']';
        } elseif (isset($parameter['enumName'])) {
            $typeName = \ucfirst($parameter['enumName']);
        } elseif (!empty($parameter['enumValues'])) {
            $typeName = \ucfirst($parameter['name']);
        } elseif (!empty($parameter['array']['model'])) {
            $typeName = 'List[' . $this->toPascalCase($parameter['array']['model']) . ']';
        } elseif (!empty($parameter['model'])) {
            $modelType = $this->toPascalCase($parameter['model']);
            $typeName = $parameter['type'] === self::TYPE_ARRAY ? 'List[' . $modelType . ']' : $modelType;
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
                    $typeName = 'Dict[str, Any]';
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
     * @param string $lang
     * @return string
     */
    public function getParamExample(array $param, string $lang = ''): string
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

    protected function getRequestModelDefinition(string $modelName, array $spec): ?array
    {
        foreach (($spec['requestModels'] ?? []) as $requestModel) {
            if (($requestModel['name'] ?? '') === $modelName) {
                return $requestModel;
            }
        }

        return null;
    }

    protected function getDocsModelTypeName(string $modelName, string $serviceName = ''): string
    {
        $modelType = $this->getModelName($modelName);

        if ($serviceName !== '' && $modelType === $this->getModelName($serviceName)) {
            return $modelType . 'Model';
        }

        return $modelType;
    }

    protected function getRequestModelPropertyExample(array $property, array $spec, string $serviceName = ''): string
    {
        if (!empty($property['sub_schema'])) {
            $example = $this->getRequestModelInstanceExample($property['sub_schema'], $spec, $serviceName);

            return ($property['type'] ?? '') === self::TYPE_ARRAY ? '[' . $example . ']' : $example;
        }

        if (!empty($property['sub_schemas'])) {
            $example = $this->getRequestModelInstanceExample($property['sub_schemas'][0], $spec, $serviceName);

            return ($property['type'] ?? '') === self::TYPE_ARRAY ? '[' . $example . ']' : $example;
        }

        return $this->getParamExample($property);
    }

    protected function getRequestModelInstanceExample(string $modelName, array $spec, string $serviceName = ''): string
    {
        $requestModel = $this->getRequestModelDefinition($modelName, $spec);

        if ($requestModel === null) {
            return $this->getDocsModelTypeName($modelName, $serviceName) . '()';
        }

        $arguments = [];

        foreach (($requestModel['properties'] ?? []) as $property) {
            $arguments[] = $this->getModelFieldName($property, $requestModel['properties']) . ' = ' . $this->getRequestModelPropertyExample($property, $spec, $serviceName);
        }

        return $this->getDocsModelTypeName($modelName, $serviceName) . '(' . implode(', ', $arguments) . ')';
    }

    protected function getRequestModelExample(array $parameter, array $spec, string $serviceName = ''): string
    {
        $modelName = $parameter['model'] ?? (($parameter['array'] ?? [])['model'] ?? null);

        if (empty($modelName)) {
            return $this->getParamExample($parameter);
        }

        $example = $this->getRequestModelInstanceExample($modelName, $spec, $serviceName);

        if (($parameter['type'] ?? '') === self::TYPE_ARRAY) {
            return '[' . $example . ']';
        }

        return $example;
    }

    protected function getModelName(string $name): string
    {
        return $this->toPascalCase($name);
    }

    protected function getUnionType(array $types): string
    {
        $types = array_values(array_unique(array_filter($types)));

        if (empty($types)) {
            return 'Any';
        }

        if (count($types) === 1) {
            return $types[0];
        }

        return 'Union[' . implode(', ', $types) . ']';
    }

    protected function normalizeModelFieldName(string $name): string
    {
        $name = ltrim($name, '$');
        $name = $this->toSnakeCase($name);

        if ($name === '') {
            $name = 'value';
        }

        return $this->escapeKeyword($name);
    }

    protected function getModelFieldName(array $property, array $properties): string
    {
        $propertyName = $property['name'] ?? 'value';
        $baseName = $this->normalizeModelFieldName($propertyName);
        $index = 0;

        foreach ($properties as $candidate) {
            if ($this->normalizeModelFieldName($candidate['name'] ?? 'value') !== $baseName) {
                continue;
            }

            $index++;

            if (($candidate['name'] ?? null) === $propertyName) {
                break;
            }
        }

        if ($index <= 1) {
            return $baseName;
        }

        return $baseName . '_' . $index;
    }

    protected function getModelPropertyType(array $property, string $ownerName = ''): string
    {
        $wrapNullable = function (string $type) use ($property): string {
            if ($property['nullable'] ?? false) {
                return 'Optional[' . $type . ']';
            }

            return $type;
        };

        if (!empty($property['sub_schemas'])) {
            $unionType = $this->getUnionType(array_map(
                fn($schema) => $this->getModelName($schema),
                $property['sub_schemas']
            ));

            return $wrapNullable(($property['type'] ?? '') === self::TYPE_ARRAY
                ? 'List[' . $unionType . ']'
                : $unionType);
        }

        if (!empty($property['sub_schema'])) {
            $modelType = $this->getModelName($property['sub_schema']);

            return $wrapNullable(($property['type'] ?? '') === self::TYPE_ARRAY
                ? 'List[' . $modelType . ']'
                : $modelType);
        }

        if (
            ($property['type'] ?? null) === self::TYPE_ARRAY
            && !empty($property['items']['enum'])
        ) {
            $enumType = $this->getModelName($property['x-enum-name'] ?? (($ownerName ?: 'Model') . ucfirst($property['name'] ?? 'Value')));

            return $wrapNullable('List[' . $enumType . ']');
        }

        if (!empty($property['enum'])) {
            $enumType = $this->getModelName($property['enumName'] ?? $property['x-enum-name'] ?? (($ownerName ?: 'Model') . ucfirst($property['name'] ?? 'Value')));

            return $wrapNullable(($property['type'] ?? '') === self::TYPE_ARRAY
                ? 'List[' . $enumType . ']'
                : $enumType);
        }

        if (($property['type'] ?? '') === self::TYPE_OBJECT) {
            return $wrapNullable('Dict[str, Any]');
        }

        return $this->getTypeName(array_merge($property, [
            'required' => true,
            'nullable' => $property['nullable'] ?? false,
        ]));
    }

    protected function getServiceModelTypeName(string $modelName, string $serviceName): string
    {
        $modelType = $this->getModelName($modelName);

        if ($modelType === $this->getModelName($serviceName)) {
            return $modelType . 'Model';
        }

        return $modelType;
    }

    protected function getServicePropertyType(array $parameter, string $serviceName): string
    {
        if (!empty($parameter['array']['model'])) {
            $typeName = 'List[' . $this->getServiceModelTypeName($parameter['array']['model'], $serviceName) . ']';
        } elseif (!empty($parameter['model'])) {
            $modelType = $this->getServiceModelTypeName($parameter['model'], $serviceName);
            $typeName = ($parameter['type'] ?? '') === self::TYPE_ARRAY ? 'List[' . $modelType . ']' : $modelType;
        } else {
            return $this->getTypeName($parameter);
        }

        if (!($parameter['required'] ?? true) || ($parameter['nullable'] ?? false)) {
            return 'Optional[' . $typeName . ']';
        }

        return $typeName;
    }

    protected function getResponseType(array $method, string $serviceName = ''): string
    {
        if (($method['type'] ?? null) === 'webAuth') {
            return 'str';
        }

        if (($method['type'] ?? null) === 'location') {
            return 'bytes';
        }

        if (!empty($method['responseModels']) && count($method['responseModels']) > 1) {
            $types = array_map(fn($model) => $this->getDocsModelTypeName($model, $serviceName), array_filter(
                $method['responseModels'],
                fn($model) => !empty($model) && $model !== 'any'
            ));

            return $this->getUnionType($types);
        }

        if (!empty($method['responseModel']) && $method['responseModel'] !== 'any') {
            return $this->getDocsModelTypeName($method['responseModel'], $serviceName);
        }

        return 'Dict[str, Any]';
    }

    /**
     * Check if a model or any of its sub-schemas has additionalProperties
     *
     * @param string|null $model
     * @param array $spec
     * @return bool
     */
    protected function hasGenericType(?string $model, array $spec): bool
    {
        if (empty($model) || $model === 'any' || !array_key_exists($model, $spec['definitions'] ?? [])) {
            return false;
        }

        $modelDef = $spec['definitions'][$model];

        // Check if model has additionalProperties (dynamic fields)
        if (!empty($modelDef['additionalProperties'])) {
            return true;
        }

        // Recursively check sub-schemas
        foreach ($modelDef['properties'] ?? [] as $property) {
            if (!\array_key_exists('sub_schema', $property) || !$property['sub_schema']) {
                continue;
            }
            if ($this->hasGenericType($property['sub_schema'], $spec)) {
                return true;
            }
        }

        return false;
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
            new TwigFilter('hasGenericType', function (string $model, array $spec) {
                return $this->hasGenericType($model, $spec);
            }),
            new TwigFilter('hasGenericTypeProperty', function (array $properties, array $spec) {
                foreach ($properties as $property) {
                    if (!empty($property['sub_schema']) && $this->hasGenericType($property['sub_schema'], $spec)) {
                        return true;
                    }
                }
                return false;
            }),
            new TwigFilter('getServicePropertyType', function (array $value, string $serviceName) {
                return $this->getServicePropertyType($value, $serviceName);
            }),
            new TwigFilter('getModelPropertyType', function (array $value, string $ownerName = '') {
                return $this->getModelPropertyType($value, $ownerName);
            }),
            new TwigFilter('getModelFieldName', function (array $value, array $properties) {
                return $this->getModelFieldName($value, $properties);
            }),
            new TwigFilter('getResponseType', function (array $method, string $serviceName = '') {
                return $this->getResponseType($method, $serviceName);
            }),
            new TwigFilter('formatParamValue', function (string $paramName, string $paramType, bool $isMultipartFormData) {
                if ($isMultipartFormData && $paramType === self::TYPE_BOOLEAN) {
                    return "str({$paramName}).lower() if type({$paramName}) is bool else {$paramName}";
                }
                return $paramName;
            }),
            new TwigFilter('enumExample', function (array $param) {
                $enumValues = $param['enumValues'] ?? [];
                if (empty($enumValues)) {
                    return '';
                }

                $enumKeys = $param['enumKeys'] ?? [];
                $enumName = $this->toPascalCase($param['enumName'] ?? $param['name'] ?? '');
                $example = $param['example'] ?? null;
                $isArray = ($param['type'] ?? '') === self::TYPE_ARRAY;

                $resolveKey = function ($value) use ($enumValues, $enumKeys) {
                    $index = array_search($value, $enumValues, true);
                    if ($index !== false && isset($enumKeys[$index]) && $enumKeys[$index] !== '') {
                        return $this->toUpperSnakeCase($enumKeys[$index]);
                    }
                    if ($index !== false && isset($enumValues[$index])) {
                        return $this->toUpperSnakeCase($enumValues[$index]);
                    }
                    $fallback = $enumKeys[0] ?? $enumValues[0] ?? $value;
                    return $this->toUpperSnakeCase((string)$fallback);
                };

                if ($isArray) {
                    $values = [];
                    if (\is_string($example) && $example !== '') {
                        $decoded = json_decode($example, true);
                        if (\is_array($decoded)) {
                            $values = $decoded;
                        }
                    } elseif (\is_array($example)) {
                        $values = $example;
                    }

                    if (empty($values)) {
                        $values = [$enumValues[0]];
                    }

                    $items = array_map(function ($value) use ($enumName, $resolveKey) {
                        return $enumName . '.' . $resolveKey($value);
                    }, $values);

                    return '[' . implode(', ', $items) . ']';
                }

                $value = ($example !== null && $example !== '') ? $example : $enumValues[0];
                return $enumName . '.' . $resolveKey($value);
            }),
            new TwigFilter('requestModelExample', function (array $parameter, array $spec, string $serviceName = '') {
                return $this->getRequestModelExample($parameter, $spec, $serviceName);
            }),
        ];
    }
}
