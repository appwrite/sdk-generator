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
                'destination'   => 'src/inputFile.ts',
                'template'      => 'deno/src/inputFile.ts.twig',
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
        ];
    }

    /**
     * @param array $parameter
     * @return string
     */
    public function getTypeName(array $parameter): string
    {
        switch ($parameter['type']) {
            case self::TYPE_INTEGER:
                return 'number';
            case self::TYPE_STRING:
                return 'string';
            case self::TYPE_FILE:
                return 'InputFile';
            case self::TYPE_BOOLEAN:
                return 'boolean';
            case self::TYPE_ARRAY:
                if (!empty($parameter['array']['type'])) {
                    return $this->getTypeName($parameter['array']) . '[]';
                }
                return 'string[]';
            case self::TYPE_OBJECT:
                return 'object';
        }

        return $parameter['type'];
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
                case self::TYPE_FILE:
                    $output .= "InputFile.fromPath('/path/to/file.png', 'file.png')";
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
                case self::TYPE_FILE:
                    $output .= "InputFile.fromPath('/path/to/file.png', 'file.png')";
                    break;
            }
        }

        return $output;
    }
}
