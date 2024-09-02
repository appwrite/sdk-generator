<?php

namespace Appwrite\SDK\Language;

class Deno extends JS
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Deno';
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'mod.ts',
                'template'      => 'deno/mod.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/client.ts',
                'template'      => 'deno/src/client.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/permission.ts',
                'template'      => 'deno/src/permission.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/permission.test.ts',
                'template'      => 'deno/test/permission.test.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/role.ts',
                'template'      => 'deno/src/role.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/role.test.ts',
                'template'      => 'deno/test/role.test.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/id.ts',
                'template'      => 'deno/src/id.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/id.test.ts',
                'template'      => 'deno/test/id.test.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/query.ts',
                'template'      => 'deno/src/query.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'test/query.test.ts',
                'template'      => 'deno/test/query.test.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/payload.ts',
                'template'      => 'deno/src/payload.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/service.ts',
                'template'      => 'deno/src/service.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/models.d.ts',
                'template'      => 'deno/src/models.d.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/src/exception.ts',
                'template'      => 'deno/src/exception.ts.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/src/services/{{service.name | caseDash}}.ts',
                'template'      => 'deno/src/services/service.ts.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/test/services/{{service.name | caseDash}}.test.ts',
                'template'      => 'deno/test/services/service.test.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'deno/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'deno/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'deno/LICENSE.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'deno/docs/example.md.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'src/enums/{{ enum.name | caseDash }}.ts',
                'template'      => 'deno/src/enums/enum.ts.twig',
            ],
        ];
    }

    /**
     * @param array $parameter
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        if (isset($parameter['enumName'])) {
            return \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return \ucfirst($parameter['name']);
        }
        return match ($parameter['type']) {
            self::TYPE_INTEGER => 'number',
            self::TYPE_STRING => 'string',
            self::TYPE_FILE => 'Payload',
            self::TYPE_PAYLOAD => 'Payload',
            self::TYPE_BOOLEAN => 'boolean',
            self::TYPE_ARRAY => (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type']))
                ? $this->getTypeName($parameter['array']) . '[]'
                : 'string[]',
            self::TYPE_OBJECT => 'object',
            default => $parameter['type']
        };
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamExample(array $param): string
    {
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

        $output = '';

        if (empty($example) && $example !== 0 && $example !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'null';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_OBJECT:
                    $output .= '{}';
                    break;
                case self::TYPE_PAYLOAD:
                    $output .= 'Payload.fromJson({ "key": "value" })';
                    break;
                case self::TYPE_FILE:
                    $output .= "Payload.fromFile('/path/to/file.png')";
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= $example;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$example}'";
                    break;
                case self::TYPE_PAYLOAD:
                    $output .= 'Payload.fromJson({ "key": "value" })';
                    break;
                case self::TYPE_FILE:
                    $output .= "Payload.fromFile('/path/to/file.png')";
                    break;
            }
        }

        return $output;
    }
}
