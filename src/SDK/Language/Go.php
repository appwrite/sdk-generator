<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\ArraySchema;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema;
use Utopia\OpenAPI\Specification;
use Override;
use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Go extends Language
{
    public function getName(): string
    {
        return 'Go';
    }

    /**
     * Get Language Keywords List
     */
    public function getKeywords(): array
    {
        return [
            'bool',
            'error',
            'for',
            'func',
            'go',
            'if',
            'import',
            'make',
            'map',
            'nil',
            'package',
            'range',
            'return',
            'string',
            'struct',
            'type',
            'var',
            'default'
        ];
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

    public function getIdentifierOverrides(): array
    {
        return [];
    }

    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'go.mod',
                'template'      => 'go/go.mod.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'go/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'go/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'go/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'appwrite/appwrite.go',
                'template'      => 'go/appwrite.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'client/client.go',
                'template'      => 'go/client.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'file/inputFile.go',
                'template'      => 'go/inputFile.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'query/query.go',
                'template'      => 'go/query.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'operator/operator.go',
                'template'      => 'go/operator.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'permission/permission.go',
                'template'      => 'go/permission.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'role/role.go',
                'template'      => 'go/role.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'id/id.go',
                'template'      => 'go/id.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'id/id_test.go',
                'template'      => 'go/id_test.go.twig',
            ],

            [
                'scope'         => 'default',
                'destination'   => 'role/role_test.go',
                'template'      => 'go/role_test.go.twig',
            ],

            [
                'scope'         => 'default',
                'destination'   => 'permission/permission_test.go',
                'template'      => 'go/permission_test.go.twig',
            ],

            [
                'scope'         => 'default',
                'destination'   => 'query/query_test.go',
                'template'      => 'go/query_test.go.twig',
            ],

            [
                'scope'         => 'default',
                'destination'   => 'operator/operator_test.go',
                'template'      => 'go/operator_test.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'client/client_test.go',
                'template'      => 'go/client_test.go.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'models/model_interface.go',
                'template'      => 'go/models/model_interface.go.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '{{ service.name | caseLower}}/{{service.name | caseSnake}}.go',
                'template'      => 'go/services/service.go.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '{{ service.name | caseLower}}/{{service.name | caseSnake}}_test.go',
                'template'      => 'go/services/service_test.go.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => 'go/docs/example.md.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => 'models/{{ definitionName | caseCamel }}.go',
                'template'      => 'go/models/model.go.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => 'models/{{ definitionName | caseCamel }}_test.go',
                'template'      => 'go/models/model_test.go.twig',
            ],
            [
                'scope'         => 'requestModel',
                'destination'   => 'models/{{ requestModelName | caseCamel }}.go',
                'template'      => 'go/models/request_model.go.twig',
            ],
        ];
    }

    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        if (\str_contains($parameter->description, 'Collection attributes') || \str_contains($parameter->description, 'List of attributes')) {
            return '[]map[string]any';
        }

        $schema = $this->getSchema($parameter);
        $model = $this->getSchemaModel($parameter);
        if ($model !== null) {
            $type = 'models.' . $this->toPascalCase($model);
            return $schema instanceof ArraySchema ? '[]' . $type : $type;
        }
        return match ($this->getSchemaType($parameter)) {
            self::TYPE_INTEGER => 'int',
            self::TYPE_NUMBER => 'float64',
            self::TYPE_FILE => 'file.InputFile',
            self::TYPE_STRING => 'string',
            self::TYPE_BOOLEAN => 'bool',
            self::TYPE_OBJECT => 'interface{}',
            // A nested array's element type is not carried through, so the
            // inner element stays untyped rather than being resolved deeper
            // than the published SDKs express it.
            self::TYPE_ARRAY => $this->isUntypedNestedArray($parameter, $schema)
                ? '[][]interface{}'
                : '[]' . $this->getTypeName($this->getArraySchema($parameter) ?? $schema),
            default => 'interface{}',
        };
    }

    public function getParamDefault(Schema|Parameter $param): string
    {
        $type       = $this->getSchemaType($param);
        $default    = $this->getSchemaDefault($param);
        $required   = ($param instanceof Parameter && $param->required);

        if ($required) {
            return '';
        }

        $output = ' = ';

        if (empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= "0";
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_OBJECT:
                    $output .= 'nil';
                    break;
                case self::TYPE_ARRAY:
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
                    $output .= "\"$default\"";
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "nil";
                    break;
            }
        }

        return $output;
    }

    public function getParamExample(Schema|Parameter $param, string $lang = ''): string
    {
        $type       = $this->getSchemaType($param);
        $example    = $this->getSchemaExample($param);

        $output = '';

        if (empty($example) && $example !== 0 && $example !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= '0';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_OBJECT:
                    $output .= 'map[string] interface{}{}';
                break;
                
                case self::TYPE_ARRAY:
                $typeName = $this->getTypeName($param);
                $output .= match ($typeName) {
               '[]string' => '[]string{"example"}',
               '[]int', '[]int64', '[]float64' => $typeName . '{0}',
               '[]bool' => $typeName . '{false}',
                default => $typeName . '{}',
                 };
                
               break;
                
                case self::TYPE_FILE:
                    $output .= 'file.NewInputFile("/path/to/file.png", "file.png")';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= $example;
                    break;
                case self::TYPE_ARRAY:
                    $values = \json_decode((string) $example, true);
                    if (\is_array($values) && \array_is_list($values)) {
                        $output .= $this->getTypeName($param) . '{' . \implode(', ', \array_map(
                            $this->formatGoExampleValue(...),
                            $values,
                        )) . '}';
                        break;
                    }

                    if (\str_starts_with((string) $example, '[')) {
                        $example = \substr((string) $example, 1);
                    }
                    if (\str_ends_with((string) $example, ']')) {
                        $example = \substr((string) $example, 0, -1);
                    }
                    $output .= $this->getTypeName($param) . '{' . $example . '}';
                    break;
                case self::TYPE_OBJECT:
                    $value = \json_decode((string) $example, true);
                    $output .= \is_array($value)
                        ? $this->formatGoExampleValue($value)
                        : 'map[string]interface{}{}';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$example}\"";
                    break;
                case self::TYPE_FILE:
                    $output .= 'file.NewInputFile("/path/to/file.png", "file.png")';
                    break;
            }
        }

        return $output;
    }

    private function formatGoExampleValue(mixed $value): string
    {
        if (\is_array($value)) {
            if (\array_is_list($value)) {
                return '[]interface{}{' . \implode(', ', \array_map(
                    $this->formatGoExampleValue(...),
                    $value,
                )) . '}';
            }

            $items = [];
            foreach ($value as $key => $item) {
                $items[] = \json_encode((string) $key, JSON_THROW_ON_ERROR)
                    . ': ' . $this->formatGoExampleValue($item);
            }

            return 'map[string]interface{}{' . \implode(', ', $items) . '}';
        }

        if ($value === null) {
            return 'nil';
        }

        return \json_encode($value, JSON_THROW_ON_ERROR);
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('godocComment', function ($value, $indent = 0): string {
                $value = trim($value);
                $value = explode("\n", $value);
                $indent = \str_repeat(' ', $indent);
                foreach ($value as $key => $line) {
                    $line = trim($line);
                    $value[$key] = $line === ''
                        ? '//'
                        : "// " . wordwrap($line, 75, "\n" . $indent . "// ");
                }
                return implode("\n" . $indent, $value);
            }, ['is_safe' => ['html']]),
            new TwigFilter('propertyType', fn(Schema $property, Specification $spec, string $generic = 'map[string]interface{}'): string => $this->getPropertyType($property, $spec, $generic)),
            new TwigFilter('returnType', fn(Operation $method, Specification $spec, string $namespace, string $generic = 'map[string]interface{}'): string => $this->getReturnType($method, $spec, $namespace, $generic)),
            new TwigFilter('caseEnumKey', fn(string $value): string => $this->toUpperSnakeCase($value)),
            new TwigFilter('goPackagePath', fn(array $sdk): string => $this->getPackagePath($sdk)),
        ];
    }

    protected function getPackagePath(array $sdk): string
    {
        $user = $sdk['gitUserName'] ?? '';
        $repo = $sdk['gitRepoName'] ?? 'sdk-for-go';
        $suffix = $this->getMajorVersionSuffix($sdk['version'] ?? '');

        if ($user === '') {
            return $repo . $suffix;
        }

        return 'github.com/' . $user . '/' . $repo . $suffix;
    }

    protected function getMajorVersionSuffix(string $version): string
    {
        if (!\preg_match('/^v?(?<major>\d+)/', $version, $matches)) {
            return '';
        }

        $major = (int) ($matches['major'] ?? 0);

        return $major >= 2 ? '/v' . $major : '';
    }

    protected function getPropertyType(Schema $property, Specification $spec, string $generic = 'map[string]interface{}'): string
    {
        return \str_replace('models.', '', $this->getTypeName($property, $spec));
    }

    protected function getReturnType(Operation $method, Specification $spec, string $namespace, string $generic = 'map[string]interface{}'): string
    {
        $type = $method->extensions['x-appwrite']['type'] ?? '';
        if ($type === 'webAuth') {
            return 'bool';
        }
        if ($type === 'location') {
            return '[]byte';
        }
        $models = \array_values(\array_filter(
            $this->getOperationResponseModels($method),
            static fn(string $model): bool => $model !== 'any',
        ));
        if (\count($models) > 1) {
            return 'models.Model';
        }
        return $models === [] ? 'interface{}' : 'models.' . $this->toPascalCase($models[0]);
    }
}
