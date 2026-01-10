<?php

namespace Appwrite\SDK\Language;

class Node extends Web
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'NodeJS';
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

    protected function getPermissionPrefix(): string
    {
        return 'sdk.';
    }

    public function getTypeName(array $parameter, array $method = []): string
    {
        if (isset($parameter['enumName'])) {
            return \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return \ucfirst($parameter['name']);
        }
        if (!empty($parameter['array']['model'])) {
            return 'Models.' . $this->toPascalCase($parameter['array']['model']) . '[]';
        }
        if (!empty($parameter['model'])) {
            $modelType = 'Models.' . $this->toPascalCase($parameter['model']);
            return $parameter['type'] === self::TYPE_ARRAY ? $modelType . '[]' : $modelType;
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
                return "File | InputFile";
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

    public function getReturn(array $method, array $spec): string
    {
        if ($method['type'] === 'webAuth') {
            return 'Promise<string>';
        }

        if ($method['type'] === 'location') {
            return 'Promise<ArrayBuffer>';
        }

        // check for union types i.e. multiple response models
        $unionType = $this->getUnionReturnType($method, $spec);
        if ($unionType !== null) {
            return $unionType;
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
            $models = array_filter($models, fn ($model) => $model != $this->toPascalCase($method['responseModel']));

            if (!empty($models)) {
                $ret .= '<' . implode(', ', $models) . '>';
            }

            $ret .= '>';

            return $ret;
        }
        return 'Promise<{}>';
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

        $hasExample = !empty($example) || $example === 0 || $example === false;

        if (!$hasExample) {
            return match ($type) {
                self::TYPE_ARRAY => '[]',
                self::TYPE_FILE => 'InputFile.fromPath(\'/path/to/file\', \'filename\')',
                self::TYPE_INTEGER, self::TYPE_NUMBER, self::TYPE_BOOLEAN => 'null',
                self::TYPE_OBJECT => '{}',
                self::TYPE_STRING => "''",
            };
        }

        return match ($type) {
            self::TYPE_ARRAY => $this->isPermissionString($example) ? $this->getPermissionExample($example) : $example,
            self::TYPE_FILE, self::TYPE_INTEGER, self::TYPE_NUMBER => $example,
            self::TYPE_BOOLEAN => ($example) ? 'true' : 'false',
            self::TYPE_OBJECT => ($example === '{}')
            ? '{}'
            : (($formatted = json_encode(json_decode($example, true), JSON_PRETTY_PRINT))
                ? preg_replace('/\n/', "\n    ", $formatted)
                : $example),
            self::TYPE_STRING => "'{$example}'",
        };
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
                'template'      => 'node/src/index.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/client.ts',
                'template'      => 'node/src/client.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/inputFile.ts',
                'template'      => 'node/src/inputFile.ts.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'src/services/{{service.name | caseKebab}}.ts',
                'template'      => 'node/src/services/template.ts.twig',
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
                'destination'   => 'src/operator.ts',
                'template'      => 'node/src/operator.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'node/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'node/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'node/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package.json',
                'template'      => 'node/package.json.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
                'template'      => 'node/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tsconfig.json',
                'template'      => '/node/tsconfig.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tsup.config.ts',
                'template'      => 'node/tsup.config.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'node/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'src/enums/{{ enum.name | caseKebab }}.ts',
                'template'      => 'web/src/enums/enum.ts.twig',
            ],
            [
                'scope'         => 'requestModel',
                'destination'   => 'src/models/{{ requestModel.name | caseKebab }}.ts',
                'template'      => 'node/src/models/requestModel.ts.twig',
            ],
        ];
    }
}
