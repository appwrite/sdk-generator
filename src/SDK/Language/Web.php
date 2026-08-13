<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\Schema\ArraySchema;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema\Schema;
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
            $name = $enumSchema->extensions['x-enum-name'] ?? ($parameter instanceof Parameter ? $parameter->name : $enumSchema->title ?? '');
            $type = \ucfirst((string) $name);
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
            self::TYPE_ARRAY => $this->getTypeName($this->getArraySchema($parameter) ?? $schema) . '[]',
            self::TYPE_OBJECT => 'Record<string, any>',
            default => 'any',
        };
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

        $models = $this->getOperationResponseModels($method);
        if ($models !== []) {
            return 'Promise<' . \implode(' | ', \array_map(fn(string $model): string => 'Models.' . $this->toPascalCase($model), $models)) . '>';
        }
        return 'Promise<{}>';
    }

    public function getSubSchema(Schema $property, Specification $spec, string $methodName = ''): string
    {
        return $this->getTypeName($property, $spec);
    }

    #[Override]
    public function getFilters(): array
    {
        return \array_merge(parent::getFilters(), [
            new TwigFilter('getPropertyType', fn(Schema|Parameter $value): string => $this->getTypeName($value)),
            new TwigFilter('getSubSchema', fn(Schema $property, Specification $spec, string $methodName = ''): string => $this->getSubSchema($property, $spec, $methodName)),
            new TwigFilter('getGenerics', fn(string $model, Specification $spec, bool $skipAdditional = false): string => ''),
            new TwigFilter('getReturn', fn(Operation $method, Specification $spec): string => $this->getReturn($method, $spec)),
            new TwigFilter('getOverloadCondition', function (Operation $method): string {
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

                $firstParamType = $this->getTypeName($params[0]);
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
