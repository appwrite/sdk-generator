<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\Schema\ArraySchema;
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
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('caseEnumKey', fn(string $value): string => $this->toUpperSnakeCase($value)),
            new TwigFilter('getPropertyType', fn(Schema|Parameter $value): string => $this->getTypeName($value)),
            new TwigFilter('hasGenericType', fn(string $model, Specification $spec): bool => false),
            new TwigFilter('hasGenericTypeProperty', fn(array $properties, Specification $spec): bool => false),
            new TwigFilter('getServicePropertyType', fn(Schema|Parameter $value, Tag $service): string => $this->getTypeName($value)),
            new TwigFilter('getServiceEnumName', fn(Parameter $parameter, Tag $service): string => $this->toPascalCase($parameter->schema?->extensions['x-enum-name'] ?? $parameter->name)),
            new TwigFilter('getModelPropertyType', fn(Schema $value, string $ownerName = ''): string => $this->getTypeName($value)),
            new TwigFilter('getModelFieldName', fn(Schema $value, array $properties): string => ''),
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
            new TwigFilter('requestModelExample', fn(Parameter $parameter, Specification $spec, string $serviceName = ''): string => '{}'),
            new TwigFilter('responseModelExample', fn(string $model, Specification $spec): string => '{}'),
            new TwigFilter('additionalPropertiesExpectations', fn(string $model, Specification $spec, string $target): string => '')
        ];
    }
}
