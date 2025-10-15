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
                return "File";
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
                self::TYPE_FILE => 'InputFile.fromPath(\'/path/to/file\', \'filename\')',
                self::TYPE_INTEGER, self::TYPE_NUMBER, self::TYPE_BOOLEAN => 'null',
                self::TYPE_OBJECT => '{}',
                self::TYPE_STRING => "''",
            };
        }

        switch ($type) {
            case self::TYPE_ARRAY:
                if (is_array($example)) {
                    return json_encode($example, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }

                $decoded = json_decode($example, true);
                if (null !== $decoded && is_array($decoded)) {
                    return json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }

                // Fallback for permission-like tokens inside array elements: read("role") -> read('role')
                $fixed = preg_replace_callback('/([a-zA-Z_][a-zA-Z0-9_]*)\(\s*"([^"\)]*)"\s*\)/', function ($m) {
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
                return json_encode((string) $example, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
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
        ];
    }
}
