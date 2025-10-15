<?php

namespace Appwrite\SDK\Language;

use Twig\TwigFilter;

class Web extends JS
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Web';
    }

    /**
     * @return array
     */
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
                'destination'   => 'src/query.ts',
                'template'      => 'web/src/query.ts.twig',
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
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
                'template'      => 'web/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tsconfig.json',
                'template'      => '/web/tsconfig.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'rollup.config.js',
                'template'      => '/web/rollup.config.js.twig',
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
                'destination'   => 'src/enums/{{ enum.name | caseKebab }}.ts',
                'template'      => 'web/src/enums/enum.ts.twig',
            ],
        ];
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamExample(array $param): string
    {
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

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

        switch ($type) {
            case self::TYPE_ARRAY:
                // Try to decode JSON array and re-encode to ensure valid JS array literal
                if (is_array($example)) {
                    return json_encode($example, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }

                $decoded = json_decode($example, true);
                if (null !== $decoded && is_array($decoded)) {
                    return json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }

                // Fallback: handle permission-like strings that contain unescaped inner quotes,
                // e.g. ["read("any")"] -> ["read('any')"] by converting inner double-quotes to single-quotes
                $fixed = preg_replace_callback('/([a-zA-Z_]+)\(\"([^\"]+)\"\)/', function ($m) {
                    return $m[1] . "('" . $m[2] . "')";
                }, $example);

                return $fixed ?? $example;

            case self::TYPE_INTEGER:
            case self::TYPE_NUMBER:
                return $example;

            case self::TYPE_FILE:
                return 'document.getElementById(\'uploader\').files[0]';

            case self::TYPE_BOOLEAN:
                return ($example) ? 'true' : 'false';

            case self::TYPE_OBJECT:
                if ($example === '{}') {
                    return '{}';
                }
                $decodedObj = json_decode($example, true);
                if (null !== $decodedObj && is_array($decodedObj)) {
                    $formatted = json_encode($decodedObj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    return preg_replace('/\n/', "\n    ", $formatted);
                }
                return $example;

            case self::TYPE_STRING:
            default:
                // Use json_encode to produce properly quoted and escaped JS string literal
                return json_encode((string) $example, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    }

    public function getReadOnlyProperties(array $parameter, string $responseModel, array $spec = []): array
    {
        $properties = [];

        if (
            !isset($spec['definitions'][$responseModel]['properties']) ||
            !is_array($spec['definitions'][$responseModel]['properties'])
        ) {
            return $properties;
        }

        foreach ($spec['definitions'][$responseModel]['properties'] as $property) {
            if (isset($property['readOnly']) && $property['readOnly']) {
                $properties[] = $property['name'];
            }
        }

        return $properties;
    }

    public function getTypeName(array $parameter, array $method = []): string
    {
        if (isset($parameter['enumName'])) {
            return \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return \ucfirst($parameter['name']);
        }
        if (isset($parameter['items'])) {
            // Map definition nested type to parameter nested type
            $parameter['array'] = $parameter['items'];
        }
        switch ($parameter['type']) {
            case self::TYPE_INTEGER:
            case self::TYPE_NUMBER:
                return 'number';
            case self::TYPE_ARRAY:
                if (!empty($parameter['array']['x-anyOf'] ?? [])) {
                    $unionTypes = [];
                    foreach ($parameter['array']['x-anyOf'] as $refType) {
                        if (isset($refType['$ref'])) {
                            $refParts = explode('/', $refType['$ref']);
                            $modelName = end($refParts);
                            $unionTypes[] = 'Models.' . $this->toPascalCase($modelName);
                        }
                    }
                    if (!empty($unionTypes)) {
                        return '(' . implode(' | ', $unionTypes) . ')[]';
                    }
                }
                if (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type'])) {
                    return $this->getTypeName($parameter['array']) . '[]';
                }
                return 'any[]';
            case self::TYPE_FILE:
                return 'File';
            case self::TYPE_OBJECT:
                if (empty($method)) {
                    return $parameter['type'];
                }
                switch ($method['responseModel']) {
                    case 'user':
                        return "Partial<Preferences>";
                    case 'document':
                        if ($method['method'] === 'post') {
                            return "Document extends Models.DefaultDocument ? Partial<Models.Document> & Record<string, any> : Partial<Models.Document> & Omit<Document, keyof Models.Document>";
                        }
                        if ($method['method'] === 'patch' || $method['method'] === 'put') {
                            return "Document extends Models.DefaultDocument ? Partial<Models.Document> & Record<string, any> : Partial<Models.Document> & Partial<Omit<Document, keyof Models.Document>>";
                        }
                        break;
                    case 'row':
                        if ($method['method'] === 'post') {
                            return "Row extends Models.DefaultRow ? Partial<Models.Row> & Record<string, any> : Partial<Models.Row> & Omit<Row, keyof Models.Row>";
                        }
                        if ($method['method'] === 'patch' || $method['method'] === 'put') {
                            return "Row extends Models.DefaultRow ? Partial<Models.Row> & Record<string, any> : Partial<Models.Row> & Partial<Omit<Row, keyof Models.Row>>";
                        }
                        break;
                }
                break;
        }
        return $parameter['type'];
    }

    protected function populateGenerics(string $model, array $spec, array &$generics, bool $skipFirst = false)
    {
        if (!$skipFirst && $spec['definitions'][$model]['additionalProperties']) {
            $generics[] = $this->toPascalCase($model);
        }

        $properties = $spec['definitions'][$model]['properties'];

        foreach ($properties as $property) {
            if (array_key_exists('sub_schema', $property) && $property['sub_schema']) {
                $this->populateGenerics($property['sub_schema'], $spec, $generics);
            }
        }
    }

    public function getGenerics(string $model, array $spec, bool $skipFirst = false): string
    {
        $generics = [];

        if (array_key_exists($model, $spec['definitions'])) {
            $this->populateGenerics($model, $spec, $generics, $skipFirst);
        }

        if (empty($generics)) {
            return '';
        }

        $generics = array_unique($generics);
        $generics = array_map(fn($type) => "{$type} extends Models.{$type} = Models.Default{$type}", $generics);

        return '<' . implode(', ', $generics) . '>';
    }

    public function getReturn(array $method, array $spec): string
    {
        if ($method['type'] === 'webAuth') {
            return 'void | string';
        }

        if ($method['type'] === 'location') {
            return 'string';
        }

        if (array_key_exists('responseModel', $method) && !empty($method['responseModel']) && $method['responseModel'] !== 'any') {
            $ret = 'Promise<';

            if (
                array_key_exists($method['responseModel'], $spec['definitions']) &&
                array_key_exists('additionalProperties', $spec['definitions'][$method['responseModel']]) &&
                !$spec['definitions'][$method['responseModel']]['additionalProperties']
            ) {
                $ret .= 'Models.';
            }

            $ret .= $this->toPascalCase($method['responseModel']);

            $models = [];

            $this->populateGenerics($method['responseModel'], $spec, $models);

            $models = array_unique($models);
            $models = array_filter($models, fn($model) => $model != $this->toPascalCase($method['responseModel']));

            if (!empty($models)) {
                $ret .= '<' . implode(', ', $models) . '>';
            }

            $ret .= '>';

            return $ret;
        }
        return 'Promise<{}>';
    }

    public function getSubSchema(array $property, array $spec, string $methodName = ''): string
    {
        if (array_key_exists('sub_schema', $property)) {
            $ret = '';
            $generics = [];
            $this->populateGenerics($property['sub_schema'], $spec, $generics);

            $generics = array_filter($generics, fn($model) => $model != $this->toPascalCase($property['sub_schema']));

            $ret .= $this->toPascalCase($property['sub_schema']);
            if (!empty($generics)) {
                $ret .= '<' . implode(', ', $generics) . '>';
            }
            if ($property['type'] === 'array') {
                $ret .= '[]';
            }

            return $ret;
        }

        if (array_key_exists('enum', $property) && !empty($methodName)) {
            if (isset($property['enumName'])) {
                return $this->toPascalCase($property['enumName']);
            }

            return $this->toPascalCase($methodName) . $this->toPascalCase($property['name']);
        }

        return $this->getTypeName($property);
    }

    public function getFilters(): array
    {
        return \array_merge(parent::getFilters(), [
            new TwigFilter('getPropertyType', function ($value, $method = []) {
                return $this->getTypeName($value, $method);
            }),
            new TwigFilter('getReadOnlyProperties', function ($value, $responseModel, $spec = []) {
                return $this->getReadOnlyProperties($value, $responseModel, $spec);
            }),
            new TwigFilter('getSubSchema', function (array $property, array $spec, string $methodName = '') {
                return $this->getSubSchema($property, $spec, $methodName);
            }),
            new TwigFilter('getGenerics', function (string $model, array $spec, bool $skipAdditional = false) {
                return $this->getGenerics($model, $spec, $skipAdditional);
            }),
            new TwigFilter('getReturn', function (array $method, array $spec) {
                return $this->getReturn($method, $spec);
            }),
            new TwigFilter('comment2', function ($value) {
                $value = explode("\n", $value);
                foreach ($value as $key => $line) {
                    $value[$key] = "     * " . wordwrap($line, 75, "\n     * ");
                }
                return implode("\n", $value);
            }, ['is_safe' => ['html']]),
            new TwigFilter('comment3', function ($value) {
                $value = explode("\n", $value);
                foreach ($value as $key => $line) {
                    $value[$key] = "         * " . wordwrap($line, 75, "\n         * ");
                }
                return implode("\n", $value);
            }, ['is_safe' => ['html']]),
        ]);
    }
}
