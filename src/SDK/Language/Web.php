<?php

namespace Appwrite\SDK\Language;

use InvalidArgumentException;
use Utopia\OpenAPI\Model\ArraySchema;
use Utopia\OpenAPI\Model\ObjectSchema;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema;
use Utopia\OpenAPI\Specification;
use Override;
use Twig\TwigFilter;

class Web extends JS
{
    public function getName(): string
    {
        return 'Web';
    }

    public function getStaticAccessOperator(): string
    {
        return '.';
    }

    public function getStringQuote(): string
    {
        return "'";
    }

    public function getArrayOf(string $elements): string
    {
        return '[' . $elements . ']';
    }

    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'src/index.ts',
                'template'      => 'web/src/index.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/client.ts',
                'template'      => 'web/src/client.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/service.ts',
                'template'      => 'web/src/service.ts.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'src/services/{{service.name | caseKebab}}.ts',
                'template'      => 'web/src/services/template.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/services/realtime.ts',
                'template'      => 'web/src/services/realtime.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/models.ts',
                'template'      => 'web/src/models.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/permission.ts',
                'template'      => 'web/src/permission.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/role.ts',
                'template'      => 'web/src/role.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/id.ts',
                'template'      => 'web/src/id.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/channel.ts',
                'template'      => 'web/src/channel.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/query.ts',
                'template'      => 'web/src/query.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/operator.ts',
                'template'      => 'web/src/operator.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'web/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'web/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'web/LICENSE.twig',
            ],
            [
            'scope'         => 'default',
            'destination'   => 'package.json',
            'template'      => 'web/package.json.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => 'web/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tsconfig.json',
                'template'      => '/web/tsconfig.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'rollup.config.mjs',
                'template'      => '/web/rollup.config.mjs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'dist/cjs/package.json',
                'template'      => '/web/dist/cjs/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'dist/esm/package.json',
                'template'      => '/web/dist/esm/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'web/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'src/enums/{{ enum.title | caseKebab }}.ts',
                'template'      => 'web/src/enums/enum.ts.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.gitignore',
                'template'      => 'web/.gitignore',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.npmrc',
                'template'      => 'web/.npmrc',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package-lock.json',
                'template'      => 'web/package-lock.json.twig',
            ],
        ];
    }

    public function getParamExample(Schema|Parameter $param, string $lang = ''): string
    {
        $type       = $this->getSchemaType($param);
        $example    = $this->getSchemaExample($param);

        $hasExample = !empty($example) || $example === 0 || $example === false;

        if (!$hasExample) {
            return match ($type) {
                self::TYPE_ARRAY => '[]',
                self::TYPE_FILE => 'document.getElementById(\'uploader\').files[0]',
                self::TYPE_INTEGER, self::TYPE_NUMBER, self::TYPE_BOOLEAN => 'null',
                self::TYPE_OBJECT => '{}',
                self::TYPE_STRING => "''",
            };
        }

        return match ($type) {
            self::TYPE_ARRAY => $this->isPermissionString($example) ? $this->getPermissionExample($example) : $example,
            self::TYPE_INTEGER, self::TYPE_NUMBER => $example,
            self::TYPE_FILE => 'document.getElementById(\'uploader\').files[0]',
            self::TYPE_BOOLEAN => ($example) ? 'true' : 'false',
            self::TYPE_OBJECT => ($example === '{}')
            ? '{}'
            : (($formatted = json_encode(json_decode((string) $example, true), JSON_PRETTY_PRINT))
                ? preg_replace('/\n/', "\n    ", $formatted)
                : $example),
            self::TYPE_STRING => "'{$example}'",
        };
    }
    #[Override]
    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        $schema = $this->getSchema($parameter);
        $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
        if ($enumSchema->enum !== []) {
            $type = $this->toPascalCase($this->getSchemaEnumName($parameter, $spec));
            return $schema instanceof ArraySchema ? $type . '[]' : $type;
        }

        $models = $this->getSchemaModels($parameter);
        if ($models !== []) {
            $type = \implode(' | ', \array_map(fn(string $model): string => 'Models.' . $this->toPascalCase($model), $models));
            return $schema instanceof ArraySchema ? '(' . $type . ')[]' : $type;
        }

        return match ($this->getSchemaType($parameter)) {
            self::TYPE_INTEGER, self::TYPE_NUMBER => $schema->format === 'int64' ? 'number | bigint' : 'number',
            self::TYPE_STRING => 'string',
            self::TYPE_BOOLEAN => 'boolean',
            self::TYPE_FILE => 'File',
            self::TYPE_ARRAY => $this->isUntypedNestedArray($parameter, $schema)
                ? 'any[][]'
                : $this->getTypeName($this->getArraySchema($parameter) ?? $schema, $spec) . '[]',
            self::TYPE_OBJECT => 'Record<string, any>',
            default => 'any',
        };
    }

    /** @return list<string> */
    protected function getGenericTypes(string $modelName, Specification $spec, bool $skipFirst = false, array $visited = []): array
    {
        if (isset($visited[$modelName])) {
            return [];
        }

        // `any` carries additionalProperties but is deliberately not emitted as
        // a model, so a generic parameterised on it would reference a type that
        // does not exist. Keep this in step with the exclusion in SDK::getDefinitions().
        if ($modelName === 'any') {
            return [];
        }

        $model = $spec->schemas[$modelName] ?? null;
        if (!$model instanceof ObjectSchema) {
            return [];
        }

        $visited[$modelName] = true;
        $generics = [];
        if (!$skipFirst && $model->additionalProperties) {
            $generics[] = $this->toPascalCase($modelName);
        }
        foreach ($model->properties as $property) {
            foreach ($this->getSchemaModels($property) as $dependency) {
                \array_push($generics, ...$this->getGenericTypes($dependency, $spec, false, $visited));
            }
        }
        return \array_values(\array_unique($generics));
    }

    public function getGenerics(string $modelName, Specification $spec, bool $skipFirst = false): string
    {
        $generics = \array_map(
            static fn(string $type): string => "{$type} extends Models.{$type} = Models.Default{$type}",
            $this->getGenericTypes($modelName, $spec, $skipFirst),
        );
        return $generics === [] ? '' : '<' . \implode(', ', $generics) . '>';
    }

    protected function getResponseType(string $modelName, Specification $spec): string
    {
        $model = $spec->schemas[$modelName] ?? null;
        $type = ($model instanceof ObjectSchema && $model->additionalProperties ? '' : 'Models.') . $this->toPascalCase($modelName);
        $generics = \array_values(\array_filter(
            $this->getGenericTypes($modelName, $spec),
            fn(string $generic): bool => $generic !== $this->toPascalCase($modelName),
        ));
        return $generics === [] ? $type : $type . '<' . \implode(', ', $generics) . '>';
    }

    public function getReturn(Operation $method, Specification $spec): string
    {
        $type = $method->extensions['x-appwrite']['type'] ?? '';
        if ($type === 'webAuth') {
            return 'void | string';
        }
        if ($type === 'location') {
            return 'string';
        }

        $models = \array_values(\array_filter(
            $this->getOperationResponseModels($method),
            static fn(string $model): bool => $model !== 'any',
        ));
        if ($models !== []) {
            return 'Promise<' . \implode(' | ', \array_map(fn(string $model): string => $this->getResponseType($model, $spec), $models)) . '>';
        }
        return 'Promise<{}>';
    }

    public function getSubSchema(Schema $property, Specification $spec, string $methodName = ''): string
    {
        $schema = $this->getSchema($property);
        $models = $this->getSchemaModels($property);

        // A union is only expressible as a member list when it is the element
        // type of an array. A bare union property stays `object`, as it was
        // before unions were resolved outside arrays at all.
        if (\count($models) > 1 && !($schema instanceof ArraySchema)) {
            return 'object';
        }

        if ($models !== []) {
            // A union names its members from outside the declaring namespace,
            // so those need qualifying; a single model is written bare.
            $qualify = \count($models) > 1;
            $types = \array_map(function (string $modelName) use ($spec, $qualify): string {
                $type = $this->toPascalCase($modelName);
                $generics = \array_values(\array_filter(
                    $this->getGenericTypes($modelName, $spec),
                    fn(string $generic): bool => $generic !== $type,
                ));
                if ($qualify) {
                    $type = 'Models.' . $type;
                }
                return $generics === [] ? $type : $type . '<' . \implode(', ', $generics) . '>';
            }, $models);
            $type = \implode(' | ', $types);
            return $schema instanceof ArraySchema
                ? (\count($types) > 1 ? '(' . $type . ')[]' : $type . '[]')
                : $type;
        }
        if ($this->getSchemaType($property) === self::TYPE_OBJECT) {
            return 'object';
        }
        return $this->getTypeName($property, $spec);
    }

    protected function getPropertyType(Schema|Parameter $value, Operation $method, Specification $spec): string
    {
        if ($this->getSchemaType($value) === self::TYPE_OBJECT) {
            $responseModel = $this->getOperationResponseModels($method)[0] ?? '';
            if ($responseModel === 'user') {
                return 'Partial<Preferences>';
            }
            if (\in_array($responseModel, ['document', 'row'], true)) {
                $generic = $this->toPascalCase($responseModel);
                $methodName = $method->method->value;
                if ($methodName === 'post') {
                    return "{$generic} extends Models.Default{$generic} ? Partial<Models.{$generic}> & Record<string, any> : Partial<Models.{$generic}> & Omit<{$generic}, keyof Models.{$generic}>";
                }
                if (\in_array($methodName, ['patch', 'put'], true)) {
                    return "{$generic} extends Models.Default{$generic} ? Partial<Models.{$generic}> & Record<string, any> : Partial<Models.{$generic}> & Partial<Omit<{$generic}, keyof Models.{$generic}>>";
                }
            }
        }
        $schema = $this->getSchema($value);
        if ($this->getSchemaModels($value) === []) {
            if ($schema instanceof ArraySchema && $this->getSchemaType($schema->items) === self::TYPE_OBJECT) {
                return 'object[]';
            }
            if ($this->getSchemaType($value) === self::TYPE_OBJECT) {
                return 'object';
            }
        }
        return $this->getTypeName($value, $spec);
    }

    #[Override]
    public function getFilters(): array
    {
        return \array_merge(parent::getFilters(), [
            new TwigFilter('getPropertyType', fn(Schema|Parameter $value, Operation|Specification $context, ?Specification $spec = null): string => $context instanceof Operation
                ? $this->getPropertyType($value, $context, $spec ?? throw new InvalidArgumentException('Specification is required'))
                : $this->getTypeName($value, $context)),
            new TwigFilter('getSubSchema', fn(Schema $property, Specification $spec, string $methodName = ''): string => $this->getSubSchema($property, $spec, $methodName)),
            new TwigFilter('getGenerics', fn(string $model, Specification $spec, bool $skipAdditional = false): string => $this->getGenerics($model, $spec, $skipAdditional)),
            new TwigFilter('getReturn', fn(Operation $method, Specification $spec): string => $this->getReturn($method, $spec)),
            new TwigFilter('getOverloadCondition', function (Operation $method, Specification $spec): string {
                $params = $this->getOperationParameters($method);

                $hasRequired = false;
                foreach ($params as $param) {
                    if ($param->required) {
                        $hasRequired = true;
                        break;
                    }
                }

                $condition = '';
                if (!$hasRequired) {
                    $condition .= '!paramsOrFirst || ';
                }

                $condition .= "(paramsOrFirst && typeof paramsOrFirst === 'object' && !Array.isArray(paramsOrFirst)";

                $firstParamType = $this->getPropertyType($params[0], $method, $spec);
                $isPrimitive = str_starts_with($firstParamType, 'string')
                    || str_starts_with($firstParamType, 'number')
                    || str_starts_with($firstParamType, 'boolean');

                if (!$isPrimitive) {
                    $keys = [];
                    foreach ($params as $param) {
                        $name = $this->toCamelCase($param->name);
                        $name = $this->escapeKeyword($name);
                        $keys[] = "'" . $name . "' in paramsOrFirst";
                    }

                    if (isset($method->requestBody?->content['multipart/form-data'])) {
                        $keys[] = "'onProgress' in paramsOrFirst";
                    }

                    $condition .= ' && (' . implode(' || ', $keys) . ')';
                }

                return $condition . ')';
            }, ['is_safe' => ['html']]),
        ]);
    }
}
