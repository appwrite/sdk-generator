<?php

namespace Appwrite\SDK\Language;

use Twig\TwigFilter;

class ReactNative extends Web
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'ReactNative';
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
                'template'      => 'react-native/src/index.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/client.ts',
                'template'      => 'react-native/src/client.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/service.ts',
                'template'      => 'react-native/src/service.ts.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'src/services/{{service.name | caseKebab}}.ts',
                'template'      => 'react-native/src/services/template.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/models.ts',
                'template'      => 'react-native/src/models.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/permission.ts',
                'template'      => 'react-native/src/permission.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/role.ts',
                'template'      => 'react-native/src/role.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/id.ts',
                'template'      => 'react-native/src/id.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/query.ts',
                'template'      => 'react-native/src/query.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/operator.ts',
                'template'      => 'react-native/src/operator.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'react-native/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'react-native/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'react-native/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package.json',
                'template'      => 'react-native/package.json.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
                'template'      => 'react-native/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tsconfig.json',
                'template'      => '/react-native/tsconfig.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'rollup.config.js',
                'template'      => '/react-native/rollup.config.js.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'dist/cjs/package.json',
                'template'      => '/react-native/dist/cjs/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'dist/esm/package.json',
                'template'      => '/react-native/dist/esm/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'react-native/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'src/enums/{{ enum.name | caseKebab }}.ts',
                'template'      => 'react-native/src/enums/enum.ts.twig',
            ],
        ];
    }

    /**
     * @param array $parameter
     * @param array $nestedTypes
     * @return string
     */
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
                return 'number | bigint';
            case self::TYPE_ARRAY:
                if (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type'])) {
                    return $this->getTypeName($parameter['array']) . '[]';
                }
                return 'any[]';
            case self::TYPE_FILE:
                return '{name: string, type: string, size: number, uri: string}';
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
                self::TYPE_BOOLEAN => 'false',
                self::TYPE_FILE => 'InputFile.fromPath(\'/path/to/file\', \'filename\')',
                self::TYPE_INTEGER, self::TYPE_NUMBER => '0',
                self::TYPE_OBJECT => '{}',
                self::TYPE_STRING => "''",
            };
        }

        return match ($type) {
            self::TYPE_ARRAY, self::TYPE_FILE, self::TYPE_INTEGER, self::TYPE_NUMBER => $example,
            self::TYPE_BOOLEAN => ($example) ? 'true' : 'false',
            self::TYPE_OBJECT => ($example === '{}')
            ? '{}'
            : (($formatted = json_encode(json_decode($example, true), JSON_PRETTY_PRINT))
                ? preg_replace('/\n/', "\n    ", $formatted)
                : $example),
            self::TYPE_STRING => "'{$example}'",
        };
    }

    public function getReturn(array $method, array $spec): string
    {
        if ($method['type'] === 'webAuth') {
            return 'void | URL';
        } elseif ($method['type'] === 'location') {
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
}
