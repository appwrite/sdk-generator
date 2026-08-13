<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\Schema\ArraySchema;
use Utopia\OpenAPI\Model\Schema\ObjectSchema;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema\Schema;
use Utopia\OpenAPI\Model\Tag;
use Utopia\OpenAPI\Specification;
use stdClass;
use Override;
use Appwrite\SDK\Language;
use Exception;
use Twig\TwigFilter;

class Python extends Language
{
    #[Override]
    protected $params = [
        'pipPackage' => 'packageName',
    ];

    /**
     * @return $this
     */
    public function setPipPackage(string $name): self
    {
        $this->setParam('pipPackage', $name);

        return $this;
    }

    public function getName(): string
    {
        return 'Python';
    }

    /**
     * Get Language Keywords List
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
                'destination' => '{{ namespace | caseSnake}}/__init__.py',
                'template' => 'python/package/__init__.py.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/__init__.py',
                'template'      => 'python/test/__init__.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/utils/deprecated.py',
                'template' => 'python/package/utils/deprecated.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/utils/__init__.py',
                'template' => 'python/package/utils/__init__.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/client.py',
                'template' => 'python/package/client.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/permission.py',
                'template' => 'python/package/permission.py.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/test_permission.py',
                'template'      => 'python/test/test_permission.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/role.py',
                'template' => 'python/package/role.py.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/test_role.py',
                'template'      => 'python/test/test_role.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/id.py',
                'template' => 'python/package/id.py.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/test_id.py',
                'template'      => 'python/test/test_id.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/query.py',
                'template' => 'python/package/query.py.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/test_query.py',
                'template'      => 'python/test/test_query.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/operator.py',
                'template' => 'python/package/operator.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'test/test_operator.py',
                'template' => 'python/test/test_operator.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/exception.py',
                'template' => 'python/package/exception.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/input_file.py',
                'template' => 'python/package/input_file.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/service.py',
                'template' => 'python/package/service.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/models/__init__.py',
                'template' => 'python/package/models/__init__.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/models/base_model.py',
                'template' => 'python/package/models/base_model.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/services/__init__.py',
                'template' => 'python/package/services/__init__.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/encoders/__init__.py',
                'template' => 'python/package/services/__init__.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/enums/__init__.py',
                'template' => 'python/package/services/__init__.py.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/services/__init__.py',
                'template'      => 'python/test/services/__init__.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/encoders/value_class_encoder.py',
                'template' => 'python/package/encoders/value_class_encoder.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/encoders/__init__.py',
                'template' => 'python/package/encoders/__init__.py.twig',
            ],
            [
                'scope' => 'service',
                'destination' => '{{ namespace | caseSnake}}/services/{{service.name | caseSnake}}.py',
                'template' => 'python/package/services/service.py.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'test/services/test_{{service.name | caseSnake}}.py',
                'template'      => 'python/test/services/test_service.py.twig',
            ],
            [
                'scope' => 'method',
                'destination' => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template' => 'python/docs/example.md.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '.github/workflows/publish.yml',
                'template' => 'python/.github/workflows/publish.yml.twig',
            ],
            [
                'scope' => 'enum',
                'destination' => '{{ namespace | caseSnake}}/enums/{{ enum.title | caseSnake }}.py',
                'template' => 'python/package/enums/enum.py.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '{{ namespace | caseSnake}}/enums/__init__.py',
                'template' => 'python/package/enums/__init__.py.twig',
            ],
            [
                'scope' => 'requestModel',
                'destination' => '{{ namespace | caseSnake}}/models/{{ requestModelName | caseSnake }}.py',
                'template' => 'python/package/models/request_model.py.twig',
            ],
            [
                'scope' => 'definition',
                'destination' => '{{ namespace | caseSnake}}/models/{{ definitionName | caseSnake }}.py',
                'template' => 'python/package/models/model.py.twig',
            ],
        ];
    }

    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        $schema = $this->getSchema($parameter);
        $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
        if ($enumSchema->enum !== []) {
            $typeName = $this->toPascalCase($this->getSchemaEnumName($parameter, $spec));
            if ($schema instanceof ArraySchema) {
                $typeName = 'List[' . $typeName . ']';
            }
        } elseif (($models = $this->getSchemaModels($parameter)) !== []) {
            $typeName = \count($models) > 1
                ? 'Union[' . \implode(', ', \array_map($this->toPascalCase(...), $models)) . ']'
                : $this->toPascalCase($models[0]);
            if ($schema instanceof ArraySchema) {
                $typeName = 'List[' . $typeName . ']';
            }
        } else {
            $typeName = match ($this->getSchemaType($parameter)) {
                self::TYPE_FILE => 'InputFile',
                self::TYPE_NUMBER, self::TYPE_INTEGER => 'float',
                self::TYPE_BOOLEAN => 'bool',
                self::TYPE_STRING => 'str',
                self::TYPE_ARRAY => 'List[' . $this->getTypeName($this->getArraySchema($parameter) ?? $schema) . ']',
                self::TYPE_OBJECT => 'Dict[str, Any]',
                default => 'Any',
            };
        }

        if (($parameter instanceof Parameter && !$parameter->required) || $schema->nullable) {
            return 'Optional[' . $typeName . ']';
        }
        return $typeName;
    }

    public function getParamDefault(Schema|Parameter $param): string
    {
        $type = $this->getSchemaType($param);
        $default = $this->getSchemaDefault($param);
        $required = ($param instanceof Parameter && $param->required);

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

    public function getParamExample(Schema|Parameter $param, string $lang = ''): string
    {
        $type = $this->getSchemaType($param);
        $example = $this->getSchemaExample($param);

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
            : (($formatted = json_encode(json_decode((string) $example, true), JSON_PRETTY_PRINT))
                ? preg_replace('/\n/', "\n    ", str_replace(['true', 'false'], ['True', 'False'], $formatted))
                : $example),
            self::TYPE_STRING => "'{$example}'",
        };
    }

    protected function hasGenericType(string $model, Specification $spec): bool
    {
        return $this->hasGenericSchemaType($model, $spec);
    }

    /** @param array<string, Schema> $properties */
    protected function hasGenericTypeProperty(array $properties, Specification $spec): bool
    {
        return array_any($properties, function (Schema $property) use ($spec): bool {
            $model = $this->getSchemaModel($property);
            return $model !== null && $this->hasGenericType($model, $spec);
        });
    }

    protected function normalizeModelFieldName(string $name): string
    {
        $name = $this->toSnakeCase(\ltrim($name, '$'));
        return $this->escapeKeyword($name !== '' ? $name : 'value');
    }

    /** @param array<string, Schema> $properties */
    protected function getModelFieldName(Schema $property, array $properties): string
    {
        $propertyName = 'value';
        foreach ($properties as $name => $candidate) {
            if ($candidate === $property) {
                $propertyName = $name;
                break;
            }
        }

        $baseName = $this->normalizeModelFieldName($propertyName);
        $index = 0;
        foreach ($properties as $name => $candidate) {
            if ($this->normalizeModelFieldName($name) !== $baseName) {
                continue;
            }
            $index++;
            if ($candidate === $property) {
                break;
            }
        }

        return $index <= 1 ? $baseName : $baseName . '_' . $index;
    }

    protected function getServiceEnumName(Parameter $parameter, Specification $spec): string
    {
        $enumName = $this->toPascalCase($this->getSchemaEnumName($parameter, $spec));
        foreach (\array_keys($spec->schemas) as $modelName) {
            if ($this->toPascalCase($modelName) === $enumName) {
                return $enumName . 'Enum';
            }
        }

        return $enumName;
    }

    protected function getServicePropertyType(Schema|Parameter $value, Specification $spec): string
    {
        $type = $this->getTypeName($value, $spec);
        if (!$value instanceof Parameter || $this->getSchema($value)->enum === [] && !($this->getSchema($value) instanceof ArraySchema && $this->getSchema($value)->items->enum !== [])) {
            return $type;
        }

        $enumName = $this->toPascalCase($this->getSchemaEnumName($value, $spec));
        return \str_replace($enumName, $this->getServiceEnumName($value, $spec), $type);
    }

    protected function getDocsModelTypeName(string $modelName, string $serviceName = ''): string
    {
        $modelType = $this->toPascalCase($modelName);
        return $serviceName !== '' && $modelType === $this->toPascalCase($serviceName)
            ? $modelType . 'Model'
            : $modelType;
    }

    protected function getRequestModelPropertyExample(Schema $property, Specification $spec, string $serviceName): string
    {
        $models = $this->getSchemaModels($property);
        if ($models !== []) {
            $example = $this->getRequestModelInstanceExample($models[0], $spec, $serviceName);
            return $property instanceof ArraySchema ? '[' . $example . ']' : $example;
        }

        return $this->getParamExample($property);
    }

    protected function getRequestModelInstanceExample(string $modelName, Specification $spec, string $serviceName): string
    {
        $model = $spec->schemas[$modelName] ?? null;
        if (!$model instanceof ObjectSchema) {
            return $this->getDocsModelTypeName($modelName, $serviceName) . '()';
        }

        $arguments = [];
        foreach ($model->properties as $property) {
            $arguments[] = $this->getModelFieldName($property, $model->properties)
                . ' = ' . $this->getRequestModelPropertyExample($property, $spec, $serviceName);
        }

        return $this->getDocsModelTypeName($modelName, $serviceName) . '(' . \implode(', ', $arguments) . ')';
    }

    protected function getRequestModelExample(Parameter $parameter, Specification $spec, string $serviceName): string
    {
        $modelName = $this->getSchemaModel($parameter);
        if ($modelName === null) {
            return $this->getParamExample($parameter);
        }

        $example = $this->getRequestModelInstanceExample($modelName, $spec, $serviceName);
        return $this->getSchema($parameter) instanceof ArraySchema ? '[' . $example . ']' : $example;
    }

    /** @param array<string, true> $visited */
    protected function getResponseModelExample(?string $modelName, Specification $spec, array $visited = []): object
    {
        if ($modelName === null || isset($visited[$modelName])) {
            return new stdClass();
        }

        $model = $spec->schemas[$modelName] ?? null;
        if (!$model instanceof ObjectSchema) {
            return new stdClass();
        }

        $visited[$modelName] = true;
        $result = [];
        foreach ($model->required as $propertyName) {
            $property = $model->properties[$propertyName] ?? null;
            if (!$property instanceof Schema) {
                continue;
            }

            $example = $this->getSchemaExample($property);
            $hasExample = $example !== null && $example !== '';
            $enumValues = $property instanceof ArraySchema ? $property->items->enum : $property->enum;
            $result[$propertyName] = match ($this->getSchemaType($property)) {
                self::TYPE_OBJECT => ($models = $this->getSchemaModels($property)) !== []
                    ? $this->getResponseModelExample($models[0], $spec, $visited)
                    : new stdClass(),
                self::TYPE_ARRAY => [],
                self::TYPE_STRING => $hasExample ? $example : ($enumValues[0] ?? ''),
                self::TYPE_BOOLEAN => $hasExample ? (bool) $example : true,
                self::TYPE_NUMBER => (float) ($hasExample ? $example : 0),
                self::TYPE_INTEGER => (int) ($hasExample ? $example : 0),
                default => $example,
            };
        }

        return (object) $result;
    }

    /** @param array<string, true> $visited @return list<string> */
    protected function getAdditionalPropertiesExpectationLines(?string $modelName, Specification $spec, string $target, array $visited = []): array
    {
        if ($modelName === null || isset($visited[$modelName])) {
            return [];
        }

        $model = $spec->schemas[$modelName] ?? null;
        if (!$model instanceof ObjectSchema) {
            return [];
        }

        $visited[$modelName] = true;
        $lines = [];
        if ($model->additionalProperties && $model->properties !== []) {
            $lines[] = $target . "['data'] = {}";
        }

        foreach ($model->required as $propertyName) {
            $property = $model->properties[$propertyName] ?? null;
            if (!$property instanceof Schema || $property instanceof ArraySchema) {
                continue;
            }
            $nestedModel = $this->getSchemaModel($property);
            if ($nestedModel === null) {
                continue;
            }
            $escapedName = \str_replace(["\\", "'"], ["\\\\", "\\'"], $propertyName);
            $lines = [
                ...$lines,
                ...$this->getAdditionalPropertiesExpectationLines(
                    $nestedModel,
                    $spec,
                    $target . "['" . $escapedName . "']",
                    $visited,
                ),
            ];
        }

        return $lines;
    }

    protected function getAdditionalPropertiesExpectations(?string $modelName, Specification $spec, string $target): string
    {
        return \implode("\n", \array_map(
            static fn(string $line): string => '        ' . $line,
            $this->getAdditionalPropertiesExpectationLines($modelName, $spec, $target),
        ));
    }

    protected function toPythonValue(mixed $value): string
    {
        $json = json_encode($value, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
        if (!\is_string($json)) {
            return '{}';
        }

        return \str_replace(['true', 'false', 'null'], ['True', 'False', 'None'], $json);
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('caseEnumKey', fn(string $value): string => $this->toUpperSnakeCase($value)),
            new TwigFilter('getPropertyType', fn(Schema|Parameter $value): string => $this->getTypeName($value)),
            new TwigFilter('hasGenericType', fn(string $model, Specification $spec): bool => $this->hasGenericType($model, $spec)),
            new TwigFilter('hasGenericTypeProperty', fn(array $properties, Specification $spec): bool => $this->hasGenericTypeProperty($properties, $spec)),
            new TwigFilter('getServicePropertyType', fn(Schema|Parameter $value, Tag $service, Specification $spec): string => $this->getServicePropertyType($value, $spec)),
            new TwigFilter('getServiceEnumName', fn(Parameter $parameter, Tag $service, Specification $spec): string => $this->getServiceEnumName($parameter, $spec)),
            new TwigFilter('getModelPropertyType', fn(Schema $value, string $ownerName, Specification $spec): string => $this->getTypeName($value, $spec)),
            new TwigFilter('getModelFieldName', fn(Schema $value, array $properties): string => $this->getModelFieldName($value, $properties)),
            new TwigFilter('getResponseType', fn(Operation $method, string $serviceName = ''): string => ($models = $this->getOperationResponseModels($method)) === [] ? 'Any' : \implode(', ', \array_map($this->toPascalCase(...), $models))),
            new TwigFilter('formatParamValue', function (string $paramName, string $paramType, bool $isMultipartFormData): string {
                if ($isMultipartFormData && $paramType === self::TYPE_BOOLEAN) {
                    return "str({$paramName}).lower() if type({$paramName}) is bool else {$paramName}";
                }
                return $paramName;
            }),
            new TwigFilter('enumExample', function (Schema|Parameter $param): string {
                $schema = $this->getSchema($param);
                $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
                $enumValues = $enumSchema->enum;
                if ($enumValues === []) {
                    return '';
                }

                $enumKeys = $enumSchema->extensions['x-enum-keys'] ?? [];
                $enumName = $this->toPascalCase($enumSchema->extensions['x-enum-name'] ?? ($param instanceof Parameter ? $param->name : $enumSchema->title ?? ''));
                $example = $this->getSchemaExample($param);
                $isArray = $schema instanceof ArraySchema;

                $resolveKey = function ($value) use ($enumValues, $enumKeys): string {
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

                    if ($values === []) {
                        $values = [$enumValues[0]];
                    }

                    $items = array_map(fn($value): string => $enumName . '.' . $resolveKey($value), $values);

                    return '[' . implode(', ', $items) . ']';
                }

                $value = ($example !== null && $example !== '') ? $example : $enumValues[0];
                return $enumName . '.' . $resolveKey($value);
            }),
            new TwigFilter('requestModelExample', fn(Parameter $parameter, Specification $spec, string $serviceName = ''): string => $this->getRequestModelExample($parameter, $spec, $serviceName)),
            new TwigFilter('responseModelExample', fn(string $model, Specification $spec): string => $this->toPythonValue($this->getResponseModelExample($model, $spec))),
            new TwigFilter('additionalPropertiesExpectations', fn(string $model, Specification $spec, string $target): string => $this->getAdditionalPropertiesExpectations($model, $spec, $target))
        ];
    }
}
